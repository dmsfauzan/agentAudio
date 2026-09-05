<?php
define('RATELIMIT_FILE', DATA_DIR . '/ratelimit.json');
define('RATELIMIT_MAX', 12); // 12 req / menit per IP
define('RATELIMIT_WINDOW', 60);

function ratelimit_check(): ?string {
  $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
  $now = time();
  $data = file_exists(RATELIMIT_FILE) ? json_decode(@file_get_contents(RATELIMIT_FILE), true) : [];
  if (!is_array($data)) $data=[];
  // cleanup
  foreach ($data as $k=>$v) if ($now - ($v['start']??0) > RATELIMIT_WINDOW) unset($data[$k]);
  if (!isset($data[$ip])) $data[$ip]=['count'=>0,'start'=>$now];
  if ($now - $data[$ip]['start'] > RATELIMIT_WINDOW) { $data[$ip]=['count'=>0,'start'=>$now]; }
  $data[$ip]['count']++;
  @file_put_contents(RATELIMIT_FILE, json_encode($data, JSON_PRETTY_PRINT));
  if ($data[$ip]['count'] > RATELIMIT_MAX) {
    $wait = RATELIMIT_WINDOW - ($now - $data[$ip]['start']);
    return "Terlalu banyak request. Coba lagi dalam {$wait} detik.";
  }
  return null;
}
