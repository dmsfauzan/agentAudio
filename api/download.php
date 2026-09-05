<?php
// download.php — stream audio (YouTube) atau video no-watermark (TikTok) + cache + trim + rate limit + stats
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/ratelimit.php';
require_once __DIR__ . '/../lib/cache.php';
require_once __DIR__ . '/../lib/stats.php';
require_once __DIR__ . '/../lib/logger.php';

// rate limit
if (($msg = ratelimit_check()) !== null) {
    header('Content-Type: application/json');
    header('Retry-After: 30');
    http_response_code(429);
    echo json_encode(['success'=>false,'error'=>$msg]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    http_response_code(200); exit;
}

$raw = file_get_contents('php://input');
$json = json_decode($raw, true);

$url = trim($json['url'] ?? $_POST['url'] ?? $_GET['url'] ?? '');
$format = strtolower(trim($json['format'] ?? $_POST['format'] ?? $_GET['format'] ?? ''));
$quality = trim($json['quality'] ?? $_POST['quality'] ?? $_GET['quality'] ?? '');
$mode = strtolower(trim($json['mode'] ?? $_POST['mode'] ?? $_GET['mode'] ?? 'audio'));
$platformParam = strtolower(trim($json['platform'] ?? $_POST['platform'] ?? $_GET['platform'] ?? ''));
$trimStart = trim($json['trim_start'] ?? $_POST['trim_start'] ?? $_GET['trim_start'] ?? '');
$trimEnd = trim($json['trim_end'] ?? $_POST['trim_end'] ?? $_GET['trim_end'] ?? '');
$trim = ($trimStart || $trimEnd) ? trim($trimStart.'-'.$trimEnd, '-') : '';
// normalize trim: allow mm:ss or seconds, validate
if ($trim) {
    if (!preg_match('/^[\d:]+-[\d:]*$/', $trim) && !preg_match('/^[\d:]+$/', $trim)) $trim='';
}

if (!$url) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'URL wajib diisi']);
    exit;
}

// Detect platform
$isTT = $platformParam === 'tiktok' || preg_match('/(tiktok\.com|vm\.tiktok\.com|vt\.tiktok\.com|m\.tiktok\.com)/i', $url);
$isYT = $platformParam === 'youtube' || preg_match('/(youtube\.com|youtu\.be)/i', $url);
if ($isTT) $platform = 'tiktok';
elseif ($isYT) $platform = 'youtube';
else {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'URL harus dari YouTube atau TikTok']);
    exit;
}

$ytDlp = realpath(__DIR__ . '/../vendor/yt-dlp.exe');
$ffmpegDir = realpath(__DIR__ . '/../vendor/ffmpeg');
$tempDir = realpath(__DIR__ . '/../temp');
if (!$tempDir) {
    $tempDir = __DIR__ . '/../temp';
    @mkdir($tempDir, 0777, true);
    $tempDir = realpath($tempDir);
}
if (!$ytDlp || !file_exists($ytDlp)) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'yt-dlp tidak ditemukan']);
    exit;
}

$reqId = bin2hex(random_bytes(6));
$escapedUrl = escapeshellarg($url);

set_time_limit(0);
ignore_user_abort(true);
foreach (glob($tempDir . DIRECTORY_SEPARATOR . '*') as $f) {
    if (is_file($f) && time() - filemtime($f) > 3600) @unlink($f);
}

// cache check (tiktok: format mp4, youtube: format)
$cacheExt = $platform==='tiktok' ? 'mp4' : ($format?:'mp3');
if ($cached = cache_get($url, $platform, $quality, $format, $trim, $mode)) {
    $generatedFile = $cached;
    $expectedExt = pathinfo($cached, PATHINFO_EXTENSION) ?: $cacheExt;
    $mime = $platform==='tiktok' ? 'video/mp4' : ($mode==='video' ? 'video/mp4' : (['mp3'=>'audio/mpeg','m4a'=>'audio/mp4','opus'=>'audio/opus','wav'=>'audio/wav','flac'=>'audio/flac'][$expectedExt] ?? 'audio/mpeg'));
    $ext = $expectedExt;
    $safeTitle = 'media';
    $titleCmd = '"'.$ytDlp.'" --get-title --no-playlist --no-warnings' . ($platform==='tiktok' ? ' --xff US' : '') . ' '.escapeshellarg($url).' 2>&1';
    exec($titleCmd, $titleOut, $titleCode);
    if ($titleCode===0 && !empty($titleOut[0])) {
        $rawTitle = preg_replace('/[\\\\\/:*?"<>|]/','_', trim($titleOut[0]));
        $rawTitle = trim(mb_substr($rawTitle,0,70));
        if ($rawTitle) $safeTitle=$rawTitle;
    }
    $downloadName = $safeTitle.'.'.$ext;
    $filesize = filesize($generatedFile);
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: '.$mime);
    header('Content-Disposition: attachment; filename="'.addcslashes($downloadName,'"').'"; filename*=UTF-8\'\''.rawurlencode($downloadName));
    header('Content-Length: '.$filesize);
    header('Cache-Control: no-cache');
    header('X-Cache: HIT');
    header('X-File-Name: '.$downloadName);
    if (ob_get_level()) ob_end_clean();
    // serve cached copy (don't delete)
    readfile($generatedFile);
    log_download($url,$platform,$_SERVER['REMOTE_ADDR']??'');
    exit;
}

if ($platform === 'tiktok') {
    $q = (int)$quality;
    $formatSel = '';
    if ($q > 0) {
        $qMap = [540=>'540p', 720=>'720p', 1080=>'1080p'];
        $label = $qMap[$q] ?? $q.'p';
        // avoid ! on Windows (escapeshellarg mangles !), use fallback to best
        $formatSel = 'b[format_id*=_'.$label.']/b/ba';
    } else {
        $formatSel = 'b/bv*+ba/b/ba';
    }
    $expectedExt = 'mp4';
    $mime = 'video/mp4';
    $outputTpl = $tempDir . DIRECTORY_SEPARATOR . $reqId . '.' . $expectedExt;
    $escapedTpl = '"' . str_replace('"', '""', $outputTpl) . '"';
    $escapedSel = escapeshellarg($formatSel);
    $trimArg = $trim ? ' --download-sections "*'.escapeshellarg($trim).'"' : '';
    // yt-dlp download-sections needs no shell escaping for * but we use escapeshellarg and trim quotes
    if ($trim) $trimArg = ' --download-sections "*'.$trim.'"';
    $cmd = '"'.$ytDlp.'" -f '.$escapedSel.' --merge-output-format mp4 --no-playlist --no-warnings --no-check-certificate --xff US --ffmpeg-location "'.$ffmpegDir.'"'.$trimArg.' -o '.$escapedTpl.' --print after_move:filepath '.$escapedUrl.' 2>&1';
} elseif ($mode === 'video') {
    // YouTube: video MP4 (merge best video+audio)
    $q = (int)$quality;
    $sel = 'bv*+ba/b';
    if ($q > 0) $sel = 'bv*[height<='.$q.']+ba/b';
    $expectedExt = 'mp4';
    $mime = 'video/mp4';
    $outputTpl = $tempDir . DIRECTORY_SEPARATOR . $reqId . '.' . $expectedExt;
    $escapedTpl = '"' . str_replace('"', '""', $outputTpl) . '"';
    $trimArg = $trim ? ' --download-sections "*'.$trim.'"' : '';
    $cmd = '"'.$ytDlp.'" -f '.escapeshellarg($sel).' --merge-output-format mp4 --no-playlist --no-warnings --no-check-certificate'
         . ' --ffmpeg-location "'.$ffmpegDir.'"'.$trimArg
         . ' -o '.$escapedTpl
         . ' --print after_move:filepath'
         . ' '.$escapedUrl.' 2>&1';
} else {
    // YouTube: audio extraction
    $allowedFormats = ['mp3','m4a','opus','wav','flac'];
    if (!$format || !in_array($format, $allowedFormats)) $format = 'mp3';
    $audioQuality = ($format === 'mp3') ? ($quality ?: '0') : '0';
    $expectedExt = $format;
    $mimeMap = ['mp3'=>'audio/mpeg','m4a'=>'audio/mp4','opus'=>'audio/opus','wav'=>'audio/wav','flac'=>'audio/flac'];
    $mime = $mimeMap[$format] ?? 'audio/mpeg';
    $outputTpl = $tempDir . DIRECTORY_SEPARATOR . $reqId . '.' . $expectedExt;
    $escapedTpl = '"' . str_replace('"', '""', $outputTpl) . '"';
    $trimArg = $trim ? ' --download-sections "*'.$trim.'"' : '';
    $cmd = '"'.$ytDlp.'" -x --audio-format '.escapeshellarg($format).' --audio-quality '.escapeshellarg($audioQuality)
         . ' --no-playlist --no-warnings --no-check-certificate'
         . ' --ffmpeg-location "'.$ffmpegDir.'"'.$trimArg
         . ' -o '.$escapedTpl
         . ' --print after_move:filepath'
         . ' '.$escapedUrl.' 2>&1';
}

exec($cmd, $outLines, $exitCode);
$outText = implode("\n", $outLines);

if ($exitCode !== 0) {
    log_error('download failed', ['url'=>$url,'platform'=>$platform,'err'=>$outText]);
    header('Content-Type: application/json');
    $err = trim($outText);
    if (stripos($err, 'private') !== false) $err = 'Video private / tidak tersedia';
    elseif (stripos($err, 'not available') !== false) $err = 'Video tidak tersedia atau diblokir';
    elseif (stripos($err, 'sign in') !== false) $err = 'Video memerlukan login';
    elseif (stripos($err, 'unsupported url') !== false) $err = 'URL tidak didukung';
    elseif (strlen($err) > 900) $err = substr($err, 0, 900) . '...';
    if (!$err) $err = 'Gagal download';
    http_response_code(422);
    echo json_encode(['success'=>false,'error'=>$err,'raw'=>$outText,'cmd'=>$cmd,'platform'=>$platform]);
    exit;
}

$generatedFile = trim(end($outLines));
if (!$generatedFile || !file_exists($generatedFile)) {
    $cands = glob($tempDir . DIRECTORY_SEPARATOR . $reqId . '.' . $expectedExt);
    if (!$cands) $cands = glob($tempDir . DIRECTORY_SEPARATOR . $reqId . '.*');
    $generatedFile = $cands[0] ?? null;
}
if (!$generatedFile || !file_exists($generatedFile)) {
    $cands = glob($tempDir . DIRECTORY_SEPARATOR . $reqId . '*');
    $generatedFile = $cands[0] ?? null;
}
if (!$generatedFile || !file_exists($generatedFile)) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'File tidak ditemukan setelah download','raw'=>$outText]);
    exit;
}

// cache + stats
cache_put($url,$platform,$quality,$format,$trim,$generatedFile,$mode);
inc_stats();
log_download($url,$platform.'/'.$mode,$_SERVER['REMOTE_ADDR']??'');

// Filename
$ext = pathinfo($generatedFile, PATHINFO_EXTENSION) ?: $expectedExt;
$safeTitle = 'media';
if (preg_match('/([A-Za-z0-9_\-]{5,})\.' . preg_quote($ext, '/') . '$/', $generatedFile, $m)) {
    $safeTitle = $m[1];
}
$titleCmd = '"'.$ytDlp.'" --get-title --no-playlist --no-warnings' . ($platform==='tiktok' ? ' --xff US' : '') . ' '.$escapedUrl.' 2>&1';
exec($titleCmd, $titleOut, $titleCode);
if ($titleCode === 0 && !empty($titleOut[0])) {
    $rawTitle = trim($titleOut[0]);
    $rawTitle = preg_replace('/[\\\\\/:*?"<>|]/', '_', $rawTitle);
    $rawTitle = preg_replace('/\s+/', ' ', $rawTitle);
    $rawTitle = trim(mb_substr($rawTitle, 0, 70));
    if ($rawTitle) $safeTitle = $rawTitle;
}
$downloadName = $safeTitle . '.' . $ext;

$filesize = filesize($generatedFile);
header('Access-Control-Allow-Origin: *');
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . addcslashes($downloadName, '"') . '"; filename*=UTF-8\'\'' . rawurlencode($downloadName));
header('Content-Length: ' . $filesize);
header('Cache-Control: no-cache');
header('X-File-Name: ' . $downloadName);

if (ob_get_level()) ob_end_clean();
readfile($generatedFile);
@unlink($generatedFile);
exit;
