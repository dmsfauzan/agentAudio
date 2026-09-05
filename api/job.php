<?php
// api/job.php — async job create/status
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/jobs.php';
require_once __DIR__ . '/../lib/ratelimit.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
if ($_SERVER['REQUEST_METHOD']==='OPTIONS'){ http_response_code(200); exit; }

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'status') {
    $id = trim($_GET['id'] ?? $_POST['id'] ?? '');
    if (!$id) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'id wajib']); exit; }
    $j = job_get($id);
    if (!$j) { http_response_code(404); echo json_encode(['success'=>false,'error'=>'Job tidak ditemukan']); exit; }
    $out = [
        'success'=>true,
        'data'=>[
            'id'=>$id,
            'status'=>$j['status'],
            'progress'=>(int)($j['progress']??0),
            'title'=>$j['title'] ?? '',
            'error'=>$j['error'] ?? '',
        ]
    ];
    if (($j['status'] ?? '') === 'done' && !empty($j['result']['file']) && file_exists($j['result']['file'])) {
        $out['data']['download_url'] = job_signed_url($id);
        $out['data']['filename'] = $j['result']['filename'] ?? 'download';
        $out['data']['mime'] = $j['result']['mime'] ?? 'application/octet-stream';
    }
    echo json_encode($out);
    exit;
}

// create
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'error'=>'Method not allowed']); exit; }
if (($msg = ratelimit_check()) !== null) { http_response_code(429); echo json_encode(['success'=>false,'error'=>$msg]); exit; }

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

$url = trim($data['url'] ?? '');
$platform = strtolower(trim($data['platform'] ?? ''));
$mode = strtolower(trim($data['mode'] ?? 'audio'));
$quality = trim($data['quality'] ?? '');
$format = strtolower(trim($data['format'] ?? 'mp3'));
$trimStart = trim($data['trim_start'] ?? '');
$trimEnd = trim($data['trim_end'] ?? '');

if (!$url || !preg_match('/(youtube\.com|youtu\.be|tiktok\.com|vm\.tiktok|vt\.tiktok)/i', $url)) {
    http_response_code(400); echo json_encode(['success'=>false,'error'=>'URL wajib dari YouTube/TikTok']); exit;
}
$isTT = preg_match('/(tiktok\.com|vm\.tiktok|vt\.tiktok)/i', $url);
$platform = $isTT ? 'tiktok' : 'youtube';
if (!in_array($mode, ['audio','video'])) $mode = $platform==='tiktok' ? 'video' : 'audio';
if (!in_array($format, ['mp3','m4a','opus','wav','flac'])) $format = $mode==='audio' ? 'mp3' : 'mp4';

$id = job_create([
    'url'=>$url,
    'platform'=>$platform,
    'mode'=>$mode,
    'quality'=>$quality,
    'format'=>$format,
    'trim_start'=>$trimStart,
    'trim_end'=>$trimEnd,
]);

// auto-spawn worker once in background (best effort; Linux cron / Windows task also fine)
$php = PHP_BINARY;
$script = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'worker.php';
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    @pclose(popen('cmd /C start /B "" "' . $php . '" "' . $script . '" --once > NUL 2>&1', 'r'));
} else {
    @exec('nohup "' . $php . '" "' . $script . '" --once > /dev/null 2>&1 &');
}

echo json_encode(['success'=>true, 'job_id'=>$id]);