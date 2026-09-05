<?php
function log_error(string $msg, array $ctx=[]): void {
  $line = date('c').' '.$msg;
  if ($ctx) $line .= ' '.json_encode($ctx, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
  @file_put_contents(DATA_DIR.'/error.log', $line.PHP_EOL, FILE_APPEND);
}
function log_download(string $url, string $platform, string $ip): void {
  @file_put_contents(DATA_DIR.'/downloads.log', date('c')." $platform $ip $url".PHP_EOL, FILE_APPEND);
}
