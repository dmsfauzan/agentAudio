<?php
// Simple JSON file storage for donations (no MySQL needed)

function donations_file(): string {
    return DONATIONS_FILE;
}

function ensure_data_dir(): void {
    if (!is_dir(DATA_DIR)) @mkdir(DATA_DIR, 0777, true);
    $f = donations_file();
    if (!file_exists($f)) @file_put_contents($f, json_encode([], JSON_PRETTY_PRINT));
}

function load_donations(): array {
    ensure_data_dir();
    $f = donations_file();
    $raw = @file_get_contents($f);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function save_donations(array $data): void {
    ensure_data_dir();
    $f = donations_file();
    // atomic write with lock
    $tmp = $f . '.tmp';
    file_put_contents($tmp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    @rename($tmp, $f);
}

function add_donation(array $donation): array {
    $all = load_donations();
    $donation['id'] = bin2hex(random_bytes(8));
    $donation['created_at'] = date('c');
    $donation['updated_at'] = date('c');
    $all[] = $donation;
    save_donations($all);
    return $donation;
}

function update_donation_status(string $order_id, string $status, array $extra=[]): bool {
    $all = load_donations();
    $found = false;
    foreach ($all as &$d) {
        if (($d['order_id'] ?? '') === $order_id) {
            $d['status'] = $status;
            $d['updated_at'] = date('c');
            foreach ($extra as $k=>$v) $d[$k]=$v;
            $found = true; break;
        }
    }
    if ($found) save_donations($all);
    return $found;
}

function get_settled_donations(int $limit=12): array {
    $all = load_donations();
    $settled = array_filter($all, fn($d)=>($d['status']??'')==='settlement' || ($d['status']??'')==='settled');
    usort($settled, fn($a,$b)=>strtotime($b['created_at']??'') <=> strtotime($a['created_at']??''));
    return array_slice($settled, 0, $limit);
}

function find_donation(string $order_id): ?array {
    foreach (load_donations() as $d) if(($d['order_id']??'')===$order_id) return $d;
    return null;
}
