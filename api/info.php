<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'error'=>'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$url = trim($data['url'] ?? $_POST['url'] ?? '');

if (!$url) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'URL wajib diisi']);
    exit;
}

// Detect platform
$isYT = preg_match('/(youtube\.com|youtu\.be)/i', $url);
$isTT = preg_match('/(tiktok\.com|vm\.tiktok\.com|vt\.tiktok\.com|m\.tiktok\.com)/i', $url);

if (!$isYT && !$isTT) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'URL harus dari YouTube atau TikTok']);
    exit;
}
$platform = $isTT ? 'tiktok' : 'youtube';

$ytDlp = __DIR__ . '/../vendor/yt-dlp.exe';
$ffmpegDir = __DIR__ . '/../vendor/ffmpeg';

if (!file_exists($ytDlp)) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'yt-dlp tidak ditemukan di server']);
    exit;
}

// Build command
$extra = '';
if ($platform === 'tiktok') {
    // --xff US gives more formats & often non-watermark variants
    $extra = ' --xff US';
}
$cmd = '"' . $ytDlp . '" --dump-single-json --no-playlist --no-warnings --skip-download --no-check-certificate --ffmpeg-location "' . $ffmpegDir . '"' . $extra . ' ' . escapeshellarg($url) . ' 2>&1';

set_time_limit(60);
exec($cmd, $output, $code);
$jsonStr = implode("\n", $output);

if ($code !== 0) {
    $err = trim($jsonStr);
    if (stripos($err, 'private') !== false) $err = 'Video private / tidak tersedia';
    elseif (stripos($err, 'not available') !== false) $err = 'Video tidak tersedia';
    elseif (stripos($err, 'sign in') !== false) $err = 'Video memerlukan login (private/age-restricted)';
    elseif (stripos($err, 'unsupported url') !== false) $err = 'URL tidak didukung';
    elseif (strlen($err) > 600) $err = substr($err, 0, 600) . '...';
    if (!$err) $err = 'Gagal mengambil info video';
    http_response_code(422);
    echo json_encode(['success'=>false,'error'=>$err,'raw'=>$jsonStr,'platform'=>$platform]);
    exit;
}

$info = json_decode($jsonStr, true);
if (!$info) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'Gagal parse info video','raw'=>substr($jsonStr,0,1200)]);
    exit;
}

// thumbnails — try several fields
$thumb = '';
if (!empty($info['thumbnail'])) $thumb = $info['thumbnail'];
elseif (!empty($info['thumbnails']) && is_array($info['thumbnails'])) {
    // pick highest preference last
    $last = end($info['thumbnails']);
    $thumb = $last['url'] ?? '';
}
// TikTok fallback: cover / originCover
if (!$thumb) {
    $thumb = $info['cover'] ?? $info['origin_cover'] ?? $info['dynamic_cover'] ?? '';
}

$duration = $info['duration'] ?? 0;
$durationStr = gmdate($duration >= 3600 ? "H:i:s" : "i:s", (int)$duration);

$title = $info['title'] ?? $info['description'] ?? 'Unknown';
if (is_string($title) && mb_strlen($title) > 120) $title = mb_substr($title, 0, 120) . '…';

$uploader = $info['uploader'] ?? $info['channel'] ?? $info['uploader_id'] ?? $info['creator'] ?? 'Unknown';
if ($platform === 'tiktok' && !empty($info['uploader'])) {
    // TikTok uploader is @handle
    if ($uploader[0] !== '@') $uploader = '@' . $uploader;
}

// TikTok qualities: collect distinct heights from non-watermarked video formats
$qualities = [];
$isSlideshow = false;
if ($platform === 'tiktok' && !empty($info['formats']) && is_array($info['formats'])) {
    $heights = [];
    foreach ($info['formats'] as $f) {
        if (($f['vcodec'] ?? '') === 'none') continue;
        $note = strtolower($f['format_note'] ?? '');
        if (strpos($note, 'watermark') !== false) continue;
        if (strpos($note, 'unplayable') !== false) continue;
        $w = (int)($f['width'] ?? 0);
        $h = (int)($f['height'] ?? 0);
        if ($w <= 0 && $h <= 0) continue;
        // portrait: width is short side, height long side. Use min side / orientation to bucket
        // 540p => 576x1024, 720p => 720x1280, 1080p => 1080x1920
        // bucket by width for portrait, by height for landscape
        $shortSide = min($w ?: PHP_INT_MAX, $h ?: PHP_INT_MAX);
        // for TikTok vertical, shortSide == width
        $label = $shortSide;
        if ($shortSide === 576) $label = 540;
        if (!in_array($label, [540,720,1080])) {
            if ($label <= 600) $label = 540;
            elseif ($label <= 800) $label = 720;
            else $label = 1080;
        }
        $heights[$label] = true;
    }
    $qualities = array_keys($heights);
    sort($qualities);
    // if no video formats but audio exists => slideshow
    if (empty($qualities)) {
        // check if at least audio format exists
        foreach ($info['formats'] as $f) {
            if (($f['vcodec'] ?? '') === 'none' && !empty($f['url'])) { $isSlideshow = true; break; }
        }
        // also check music playUrl
        if (!$isSlideshow && !empty($info['music'])) $isSlideshow = true;
    }
}

// YouTube video qualities (for video mode)
$videoQualities = [];
if ($platform === 'youtube' && !empty($info['formats']) && is_array($info['formats'])) {
    $heights = [];
    foreach ($info['formats'] as $f) {
        if (($f['vcodec'] ?? '') === 'none') continue;
        $h = (int)($f['height'] ?? 0);
        if ($h <= 0) continue;
        if (!in_array($h, [360,480,720,1080,1440,2160])) {
            if ($h <= 360) $h = 360;
            elseif ($h <= 480) $h = 480;
            elseif ($h <= 720) $h = 720;
            elseif ($h <= 1080) $h = 1080;
            elseif ($h <= 1440) $h = 1440;
            else $h = 2160;
        }
        $heights[$h] = true;
    }
    $videoQualities = array_keys($heights);
    sort($videoQualities);
}

echo json_encode([
    'success' => true,
    'data' => [
        'id' => $info['id'] ?? '',
        'platform' => $platform,
        'title' => $title,
        'uploader' => $uploader,
        'duration' => $duration,
        'duration_string' => $durationStr,
        'thumbnail' => $thumb,
        'view_count' => $info['view_count'] ?? $info['play_count'] ?? 0,
        'like_count' => $info['like_count'] ?? 0,
        'qualities' => $qualities,
        'video_qualities' => $videoQualities,
        'is_slideshow' => $isSlideshow,
        'url' => $url,
    ]
]);
