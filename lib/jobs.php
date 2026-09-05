<?php
// Async job storage (file-based, no DB)

function ensure_jobs_dirs(): void {
    foreach ([JOBS_DIR, JOBS_OUT_DIR] as $d) if (!is_dir($d)) @mkdir($d, 0777, true);
}

function job_file(string $id): string { return JOBS_DIR . '/' . $id . '.json'; }

function job_create(array $job): string {
    ensure_jobs_dirs();
    $job['id'] = bin2hex(random_bytes(8));
    $job['status'] = 'pending';
    $job['progress'] = 0;
    $job['created_at'] = time();
    $job['updated_at'] = time();
    $job['error'] = '';
    $job['result'] = null;
    @file_put_contents(job_file($job['id']), json_encode($job, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    return $job['id'];
}

function job_get(string $id): ?array {
    $f = job_file($id);
    if (!file_exists($f)) return null;
    $d = json_decode(@file_get_contents($f), true);
    return is_array($d) ? $d : null;
}

function job_update(string $id, array $patch): void {
    $d = job_get($id);
    if (!$d) return;
    foreach ($patch as $k=>$v) $d[$k] = $v;
    $d['updated_at'] = time();
    @file_put_contents(job_file($id), json_encode($d, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
}

// claim a slot: returns a worker slot handle or null if all busy
function worker_acquire_slot() {
    ensure_jobs_dirs();
    $slot = null;
    for ($i=0; $i<WORKER_MAX; $i++) {
        $f = JOBS_DIR . '/slot.' . $i . '.lock';
        $h = fopen($f, 'c');
        if (flock($h, LOCK_EX | LOCK_NB)) { $slot = $h; break; }
        fclose($h);
    }
    return $slot;
}

// pick next pending job (oldest first); atomic claim via rename
function worker_next_job(): ?array {
    ensure_jobs_dirs();
    $cands = glob(JOBS_DIR . '/*.json');
    if (!$cands) return null;
    // sort by mtime oldest first
    usort($cands, fn($a,$b)=>filemtime($a) <=> filemtime($b));
    foreach ($cands as $f) {
        $d = json_decode(@file_get_contents($f), true);
        if (!is_array($d)) continue;
        if (($d['status'] ?? '') === 'pending') {
            // mark processing via direct edit (we're the only one with slot)
            $d['status']='processing';
            $d['started_at']=time();
            @file_put_contents($f, json_encode($d, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
            return $d;
        }
        // stale processing (>10 min) => requeue
        if (($d['status'] ?? '') === 'processing' && time() - ($d['started_at'] ?? 0) > 600) {
            $d['status']='pending'; unset($d['started_at']);
            @file_put_contents($f, json_encode($d, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
            return worker_next_job();
        }
    }
    return null;
}

function cleanup_jobs(): void {
    ensure_jobs_dirs();
    $now = time();
    // remove job meta older than 2h (result already delivered or expired)
    foreach (glob(JOBS_DIR . '/*.json') as $f) if ($now - filemtime($f) > JOB_TTL*2) @unlink($f);
    // remove result files older than TTL
    foreach (glob(JOBS_OUT_DIR . '/*') as $f) if ($now - filemtime($f) > JOB_TTL) @unlink($f);
    // remove old locks
    foreach (glob(JOBS_DIR . '/slot.*.lock') as $f) if ($now - filemtime($f) > 3600) @unlink($f);
}

function job_signed_url(string $id): string {
    $exp = time() + 300; // 5 menit
    $token = hash_hmac('sha256', $id . ':' . $exp, JOB_SECRET);
    return BASE_URL . '/api/file.php?id=' . urlencode($id) . '&exp=' . $exp . '&token=' . $token;
}

function job_verify_url(string $id, int $exp, string $token): bool {
    if (time() > $exp) return false;
    $expect = hash_hmac('sha256', $id . ':' . $exp, JOB_SECRET);
    return hash_equals($expect, $token);
}