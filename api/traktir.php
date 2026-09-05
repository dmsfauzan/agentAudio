<?php
// api/traktir.php — create Snap token for donation
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD']==='OPTIONS'){ http_response_code(200); exit; }

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/midtrans.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) $data = $_POST;

$name = trim($data['name'] ?? '');
$message = trim($data['message'] ?? '');
$amount = (int)($data['amount'] ?? 0);

// presets: allow 5k,10k,20k,50k,100k but custom also allowed
if ($amount < TRAKTIR_MIN) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Nominal minimal Rp '.number_format(TRAKTIR_MIN,0,',','.')]); exit; }
if ($amount > TRAKTIR_MAX) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Nominal maksimal Rp '.number_format(TRAKTIR_MAX,0,',','.')]); exit; }
if (mb_strlen($name) > 60) $name = mb_substr($name,0,60);
if (mb_strlen($message) > 200) $message = mb_substr($message,0,200);
if (!$name) $name = 'Hamba Allah';

$order_id = 'TRX-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(3)));

// save pending donation
add_donation([
    'order_id' => $order_id,
    'name' => $name,
    'message' => $message,
    'amount' => $amount,
    'status' => 'pending',
    'payment_type' => '',
    'midtrans_transaction_id' => '',
]);

if (!midtrans_is_configured()) {
    // Demo mode — no real Midtrans keys: return mock token + instructions
    echo json_encode([
        'success'=>true,
        'demo'=>true,
        'order_id'=>$order_id,
        'snap_token'=>null,
        'message'=>'Midtrans belum dikonfigurasi (ganti MIDTRANS_SERVER_KEY/CLIENT_KEY di config.php). Donasi tersimpan sebagai pending.',
        'donation'=>['order_id'=>$order_id,'name'=>$name,'amount'=>$amount],
    ]);
    exit;
}

$res = midtrans_create_snap_token($amount, $order_id, $name, $message);
if (!$res['success']) {
    http_response_code(502);
    echo json_encode(['success'=>false,'error'=>'Gagal buat Snap: '.$res['error'],'order_id'=>$order_id,'raw'=>$res['raw'] ?? '']);
    exit;
}
echo json_encode([
    'success'=>true,
    'order_id'=>$order_id,
    'snap_token'=>$res['token'],
    'redirect_url'=>$res['redirect_url'],
]);
