<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/db.php';

$order_id = trim($_GET['order_id'] ?? $_POST['order_id'] ?? '');
if (!$order_id) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'order_id wajib']); exit; }

// expiry check: pending >15 menit jadi expire
$all = load_donations();
$now = time();
$changed=false;
foreach ($all as &$d) {
    if (($d['status']??'')==='pending' && isset($d['created_at'])) {
        $t = strtotime($d['created_at']);
        if ($t && $now - $t > 15*60) { $d['status']='expire'; $d['updated_at']=date('c'); $changed=true; }
    }
}
if ($changed) save_donations($all);

$d = find_donation($order_id);
if (!$d) { http_response_code(404); echo json_encode(['success'=>false,'error'=>'Order tidak ditemukan']); exit; }
echo json_encode(['success'=>true, 'data'=>['order_id'=>$d['order_id'],'status'=>$d['status'],'amount'=>$d['amount'],'name'=>$d['name']]]);
