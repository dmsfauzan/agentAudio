<?php
define('CACHE_DIR', DATA_DIR . '/cache');
define('CACHE_TTL', 3600); // 1 jam

function cache_key(string $url, string $platform, string $quality, string $format, string $trim='', string $mode='audio'): string {
  return hash('sha256', $url.'|'.$platform.'|'.$mode.'|'.$quality.'|'.$format.'|'.$trim);
}
function cache_path(string $key, string $ext): string {
  if (!is_dir(CACHE_DIR)) @mkdir(CACHE_DIR,0777,true);
  return CACHE_DIR . '/' . $key . '.' . $ext;
}
function cache_get(string $url,string $platform,string $quality,string $format,string $trim='',string $mode='audio'): ?string {
  $ext = $platform==='tiktok' ? 'mp4' : ($format?:'mp3');
  $key = cache_key($url,$platform,$quality,$format,$trim,$mode);
  $p = cache_path($key, $ext);
  if (file_exists($p) && time()-filemtime($p) < CACHE_TTL) return $p;
  foreach (glob(CACHE_DIR.'/'.$key.'.*') as $f) if (time()-filemtime($f)<CACHE_TTL) return $f;
  return null;
}
function cache_put(string $url,string $platform,string $quality,string $format,string $trim, string $src, string $mode='audio'): string {
  $ext = pathinfo($src, PATHINFO_EXTENSION) ?: ($platform==='tiktok'?'mp4':($format?:'mp3'));
  $dst = cache_path(cache_key($url,$platform,$quality,$format,$trim,$mode), $ext);
  @copy($src, $dst);
  foreach (glob(CACHE_DIR.'/*') as $f) if (is_file($f) && time()-filemtime($f) > CACHE_TTL*2) @unlink($f);
  return $dst;
}
