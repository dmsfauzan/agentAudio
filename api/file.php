<?php
// api/file.php — deliver finished job file via signed URL, then delete
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/jobs.php';

$id = trim($_GET['id'] ?? '');
$exp = (int)($_GET['exp'] ?? 0);
$token = trim($_GET['token'] ?? '');
if (!$id || !$exp || !$token || !job_verify_url($id, $exp, $token)) {
    http_response_code(403);
    die('Link kadaluarsa atau tidak valid.');
}

$j = job_get($id);
if (!$j || ($j['status'] ?? '') !== 'done' || empty($j['result']['file'])) {
    http_response_code(404);
    die('File tidak ditemukan.');
}
$file = $j['result']['file'];
if (!file_exists($file)) { http_response_code(404); die('File sudah terhapus.'); }

$filename = $j['result']['filename'] ?? 'download';
$mime = $j['result']['mime'] ?? 'application/octet-stream';
$size = filesize($file);

header('Access-Control-Allow-Origin: *');
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . addcslashes($filename, '"') . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
header('Content-Length: ' . $size);
header('X-File-Name: ' . $filename);
if (ob_get_level()) ob_end_clean();
readfile($file);
@unlink($file);
exit;