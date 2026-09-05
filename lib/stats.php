<?php
function stats_file(){ return DATA_DIR . '/stats.json'; }
function get_stats(): array {
  $f = stats_file();
  if (!file_exists($f)) return ['total'=>425983,'today'=>0,'date'=>date('Y-m-d')];
  $d = json_decode(@file_get_contents($f), true);
  if (!$d) return ['total'=>425983,'today'=>0,'date'=>date('Y-m-d')];
  if (($d['date']??'') !== date('Y-m-d')) { $d['today']=0; $d['date']=date('Y-m-d'); }
  return $d;
}
function inc_stats(): array {
  $s = get_stats();
  $s['total'] = ($s['total']??425983)+1;
  $s['today'] = ($s['today']??0)+1;
  @file_put_contents(stats_file(), json_encode($s, JSON_PRETTY_PRINT));
  return $s;
}
