<?php
// api/playlist.php — batch download playlist as ZIP
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/ratelimit.php';
require_once __DIR__ . '/../lib/logger.php';
if (($msg = ratelimit_check()) !== null) { header('Content-Type: application/json'); http_response_code(429); echo json_encode(['success'=>false,'error'=>$msg]); exit; }

$url = trim($_GET['url'] ?? $_POST['url'] ?? '');
$format = strtolower(trim($_GET['format'] ?? 'mp3'));
$allowed = ['mp3','m4a','opus','wav'];
if (!in_array($format,$allowed)) $format='mp3';
if (!$url || !preg_match('/(youtube\.com|youtu\.be)/i',$url)) { header('Content-Type: application/json'); http_response_code(400); echo json_encode(['success'=>false,'error'=>'URL playlist YouTube wajib diisi']); exit; }

$ytDlp = realpath(__DIR__ . '/../vendor/yt-dlp.exe');
$ffmpegDir = realpath(__DIR__ . '/../vendor/ffmpeg');
$tempDir = realpath(__DIR__ . '/../temp') ?: __DIR__.'/../temp';
@mkdir($tempDir,0777,true);

// get flat playlist
$cmd = '"'.$ytDlp.'" --flat-playlist --dump-single-json --no-warnings --no-check-certificate '.escapeshellarg($url).' 2>&1';
exec($cmd, $out, $code);
$json = implode("\n",$out);
if ($code!==0) { header('Content-Type: application/json'); http_response_code(422); echo json_encode(['success'=>false,'error'=>'Gagal baca playlist','raw'=>$json]); exit; }
$data = json_decode($json,true);
$entries = $data['entries'] ?? [];
if (empty($entries)) { header('Content-Type: application/json'); http_response_code(422); echo json_encode(['success'=>false,'error'=>'Playlist kosong atau tidak ditemukan']); exit; }
// limit 10 for free
$entries = array_slice($entries,0,10);
$zipId = bin2hex(random_bytes(4));
$workDir = $tempDir . DIRECTORY_SEPARATOR . 'pl_'.$zipId;
@mkdir($workDir,0777,true);
$files=[];
foreach ($entries as $e) {
  $vid = $e['id'] ?? $e['url'] ?? '';
  if (!$vid) continue;
  $vurl = 'https://www.youtube.com/watch?v='.$vid;
  $outTpl = $workDir . DIRECTORY_SEPARATOR . '%(title)s.%(ext)s';
  $escapedTpl = '"' . str_replace('"','""',$outTpl) . '"';
  $cmd2 = '"'.$ytDlp.'" -x --audio-format '.escapeshellarg($format).' --audio-quality 0 --no-playlist --no-warnings --ffmpeg-location "'.$ffmpegDir.'" -o '.$escapedTpl.' '.escapeshellarg($vurl).' 2>&1';
  exec($cmd2, $o2, $c2);
  if ($c2===0) {
    foreach (glob($workDir.'/*') as $f) if (!in_array($f,$files) && is_file($f)) $files[]=$f;
  }
}
if (empty($files)) { header('Content-Type: application/json'); http_response_code(500); echo json_encode(['success'=>false,'error'=>'Gagal download playlist']); exit; }
$zipPath = $tempDir . DIRECTORY_SEPARATOR . 'playlist_'.$zipId.'.zip';
$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE)!==true) { header('Content-Type: application/json'); http_response_code(500); echo json_encode(['success'=>false,'error'=>'Gagal buat ZIP']); exit; }
foreach ($files as $f) $zip->addFile($f, basename($f));
$zip->close();
// cleanup work dir files
foreach ($files as $f) @unlink($f);
@rmdir($workDir);
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="playlist_'.$zipId.'.zip"');
header('Content-Length: '.filesize($zipPath));
readfile($zipPath);
@unlink($zipPath);
log_error('playlist done', ['url'=>$url,'count'=>count($files)]);
