<?php
// webhook/midtrans.php — Midtrans HTTP notification (payment callback)
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/midtrans.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) { http_response_code(400); echo json_encode(['error'=>'invalid json']); exit; }

$order_id = $data['order_id'] ?? '';
$status_code = $data['status_code'] ?? '';
$gross_amount = $data['gross_amount'] ?? '';
$signature_key = $data['signature_key'] ?? '';
$transaction_status = $data['transaction_status'] ?? '';
$payment_type = $data['payment_type'] ?? '';
$transaction_id = $data['transaction_id'] ?? '';

if (!$order_id || !$status_code || !$gross_amount || !$signature_key) {
    http_response_code(400); echo json_encode(['error'=>'missing fields']); exit;
}

$expected = midtrans_signature($order_id, $status_code, $gross_amount, MIDTRANS_SERVER_KEY);
if (!hash_equals($expected, $signature_key)) {
    http_response_code(403); echo json_encode(['error'=>'invalid signature']); exit;
}

// map Midtrans status to our status
$map = [
    'capture' => 'settlement', // credit card capture = settlement
    'settlement' => 'settlement',
    'pending' => 'pending',
    'deny' => 'deny',
    'expire' => 'expire',
    'cancel' => 'cancel',
    'failure' => 'failure',
];
$status = $map[$transaction_status] ?? $transaction_status;

update_donation_status($order_id, $status, [
    'payment_type' => $payment_type,
    'midtrans_transaction_id' => $transaction_id,
    'gross_amount' => $gross_amount,
    'transaction_status' => $transaction_status,
]);

http_response_code(200);
echo json_encode(['success'=>true]);
