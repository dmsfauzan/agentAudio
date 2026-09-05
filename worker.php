<?php
// worker.php — CLI async worker. Run:  php worker.php   (loop)
// or one-shot:  php worker.php --once
// Concurrency controlled via slot locks (WORKER_MAX).
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/jobs.php';
require_once __DIR__ . '/lib/logger.php';

if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

$once = in_array('--once', $argv, true);

function run_job(array $job): void {
    $id = $job['id'];
    $ytDlp = realpath(__DIR__ . '/vendor/yt-dlp.exe');
    $ffmpegDir = realpath(__DIR__ . '/vendor/ffmpeg');
    $outDir = JOBS_OUT_DIR;
    ensure_jobs_dirs();
    if (!$ytDlp || !file_exists($ytDlp)) { job_update($id, ['status'=>'error','error'=>'yt-dlp tidak ditemukan']); return; }

    $url = $job['url'];
    $platform = $job['platform'];
    $mode = $job['mode'];
    $quality = trim($job['quality'] ?? '');
    $format = $job['format'] ?? 'mp3';
    $trim = '';
    $ts = trim($job['trim_start'] ?? ''); $te = trim($job['trim_end'] ?? '');
    if ($ts || $te) $trim = trim($ts . '-' . $te, '-');

    $reqId = $id;
    $escapedUrl = escapeshellarg($url);

    if ($platform === 'tiktok') {
        $q = (int)$quality;
        $formatSel = '';
        if ($q > 0) {
            $qMap = [540=>'540p',720=>'720p',1080=>'1080p'];
            $label = $qMap[$q] ?? $q.'p';
            $formatSel = 'b[format_id*=_'.$label.']/b/ba';
        } else {
            $formatSel = 'b/bv*+ba/b/ba';
        }
        $expectedExt = 'mp4';
        $outputTpl = $outDir . DIRECTORY_SEPARATOR . $reqId . '.%(ext)s';
        $escapedTpl = '"' . str_replace('"','""',$outputTpl) . '"';
        $trimArg = $trim ? ' --download-sections "*'.$trim.'"' : '';
        $cmd = '"'.$ytDlp.'" -f '.escapeshellarg($formatSel).' --merge-output-format mp4 --no-playlist --no-warnings --no-check-certificate --xff US --ffmpeg-location "'.$ffmpegDir.'"'.$trimArg.' -o '.$escapedTpl.' --newline --progress '.$escapedUrl.' 2>&1';
    } elseif ($mode === 'video') {
        // YouTube video
        $q = (int)$quality;
        $sel = 'bv*+ba/b';
        if ($q > 0) {
            $sel = 'bv*[height<='.$q.']+ba/b';
        }
        $expectedExt = 'mp4';
        $outputTpl = $outDir . DIRECTORY_SEPARATOR . $reqId . '.%(ext)s';
        $escapedTpl = '"' . str_replace('"','""',$outputTpl) . '"';
        $trimArg = $trim ? ' --download-sections "*'.$trim.'"' : '';
        $cmd = '"'.$ytDlp.'" -f '.escapeshellarg($sel).' --merge-output-format mp4 --no-playlist --no-warnings --no-check-certificate --ffmpeg-location "'.$ffmpegDir.'"'.$trimArg.' -o '.$escapedTpl.' --newline --progress '.$escapedUrl.' 2>&1';
    } else {
        // YouTube audio
        $audioQuality = ($format==='mp3') ? ($quality ?: '0') : '0';
        $expectedExt = $format;
        $outputTpl = $outDir . DIRECTORY_SEPARATOR . $reqId . '.%(ext)s';
        $escapedTpl = '"' . str_replace('"','""',$outputTpl) . '"';
        $trimArg = $trim ? ' --download-sections "*'.$trim.'"' : '';
        $cmd = '"'.$ytDlp.'" -x --audio-format '.escapeshellarg($format).' --audio-quality '.escapeshellarg($audioQuality).' --no-playlist --no-warnings --no-check-certificate --ffmpeg-location "'.$ffmpegDir.'"'.$trimArg.' -o '.$escapedTpl.' --newline --progress '.$escapedUrl.' 2>&1';
    }

    // stream process, parse progress
    $descriptors = [1=>['pipe','w'], 2=>['pipe','w']];
    $proc = proc_open($cmd, $descriptors, $pipes);
    if (!is_resource($proc)) { job_update($id, ['status'=>'error','error'=>'Gagal menjalankan worker']); return; }
    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);
    $outBuf='';
    $start=time();
    while (true) {
        $read = '';
        if (!feof($pipes[1])) $read .= stream_get_contents($pipes[1]);
        if (!feof($pipes[2])) $read .= stream_get_contents($pipes[2]);
        if ($read) {
            $outBuf .= $read;
            // parse progress like [download]  45.2% of ...
            if (preg_match_all('/\[download\]\s+(\d+\.?\d*)%/', $outBuf, $m)) {
                $last = (float)end($m[1]);
                $p = (int)round($last);
                if ($p > ($job['progress']??0)) job_update($id, ['progress'=>$p]);
            }
        }
        $status = proc_get_status($proc);
        if (!$status['running']) break;
        if (time() - $start > 1500) { proc_terminate($proc); job_update($id, ['status'=>'error','error'=>'Timeout']); return; }
        usleep(200000);
    }
    fclose($pipes[1]); fclose($pipes[2]);
    $exitCode = proc_close($proc);

    if ($exitCode !== 0) {
        $err = trim($outBuf);
        if (stripos($err,'private')!==false) $err='Video private / tidak tersedia';
        elseif (stripos($err,'not available')!==false) $err='Video tidak tersedia';
        elseif (stripos($err,'sign in')!==false) $err='Video memerlukan login';
        elseif (strlen($err)>600) $err=substr($err,0,600).'...';
        log_error('job failed', ['id'=>$id,'err'=>$err]);
        job_update($id, ['status'=>'error','error'=>$err]);
        return;
    }

    // find result file
    $result = null;
    foreach (glob($outDir . DIRECTORY_SEPARATOR . $reqId . '.*') as $f) {
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        if (strpos($ext,'f')===0 && is_numeric(substr($ext,1))) continue; // skip .f137 fragments
        if ($result === null || filesize($f) > filesize($result)) $result = $f;
    }
    if (!$result) { job_update($id, ['status'=>'error','error'=>'File hasil tidak ditemukan']); return; }

    $ext = pathinfo($result, PATHINFO_EXTENSION) ?: $expectedExt;
    $mimeMap = ['mp3'=>'audio/mpeg','m4a'=>'audio/mp4','opus'=>'audio/opus','wav'=>'audio/wav','flac'=>'audio/flac','mp4'=>'video/mp4'];
    $mime = $mimeMap[$ext] ?? 'application/octet-stream';

    // title for filename
    $safe = 'download';
    $tcmd = '"'.$ytDlp.'" --get-title --no-playlist --no-warnings'.($platform==='tiktok'?' --xff US':'').' '.$escapedUrl.' 2>&1';
    exec($tcmd, $tout, $tcode);
    if ($tcode===0 && !empty($tout[0])) {
        $t2 = preg_replace('/[\\\\\/:*?"<>|]/','_', trim($tout[0]));
        $t2 = trim(mb_substr($t2,0,70));
        if ($t2) $safe = $t2;
    }
    $filename = $safe . '.' . $ext;

    job_update($id, [
        'status'=>'done',
        'progress'=>100,
        'title'=>$safe,
        'result'=>['file'=>$result,'ext'=>$ext,'mime'=>$mime,'filename'=>$filename,'size'=>filesize($result)],
    ]);
    log_download($url, $platform.'/'.$mode, $_SERVER['REMOTE_ADDR'] ?? 'cli');
    cleanup_jobs();
}

// main loop
if ($once) {
    if ($slot = worker_acquire_slot()) {
        $job = worker_next_job();
        if ($job) run_job($job);
        flock($slot, LOCK_UN); fclose($slot);
    }
    exit(0);
}

echo "AudioAgent worker running (max ".WORKER_MAX." parallel). Ctrl+C to stop.\n";
while (true) {
    cleanup_jobs();
    $slot = worker_acquire_slot();
    if ($slot) {
        $job = worker_next_job();
        if ($job) {
            echo '['.date('H:i:s')."] processing {$job['id']} ({$job['platform']}/{$job['mode']})\n";
            run_job($job);
            echo '['.date('H:i:s')."] done {$job['id']}\n";
        }
        flock($slot, LOCK_UN); fclose($slot);
    }
    sleep(2);
}