<?php
// Midtrans helpers — Snap API

function midtrans_is_configured(): bool {
    return MIDTRANS_SERVER_KEY !== 'SB-Mid-server-xxxxxxxxxxxxxxxx'
        && MIDTRANS_SERVER_KEY !== ''
        && MIDTRANS_CLIENT_KEY !== 'SB-Mid-client-xxxxxxxxxxxxxxxx';
}

function midtrans_signature(string $order_id, string $status_code, string $gross_amount, string $server_key): string {
    return hash('sha512', $order_id . $status_code . $gross_amount . $server_key);
}

function midtrans_create_snap_token(int $amount, string $order_id, string $customer_name, string $message): array {
    $isProd = MIDTRANS_IS_PRODUCTION;
    $url = $isProd ? 'https://app.midtrans.com/snap/v1/transactions' : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    $serverKey = MIDTRANS_SERVER_KEY;

    $payload = [
        'transaction_details' => [
            'order_id' => $order_id,
            'gross_amount' => $amount,
        ],
        'item_details' => [[
            'id' => 'traktir',
            'price' => $amount,
            'quantity' => 1,
            'name' => 'Traktir AudioAgent' . ($message ? ' - ' . mb_substr($message,0,40) : ''),
        ]],
        'customer_details' => [
            'first_name' => $customer_name ?: 'Hamba Allah',
        ],
        'callbacks' => [
            'finish' => BASE_URL . '/?traktir=success&order_id=' . urlencode($order_id),
        ],
        // custom expiry 15 menit untuk donasi
        'expiry' => [
            'start_time' => date('Y-m-d H:i:s O'),
            'unit' => 'minute',
            'duration' => 15,
        ],
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($serverKey . ':'),
        ],
        CURLOPT_TIMEOUT => 20,
    ]);
    $resp = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) return ['success'=>false,'error'=>'Curl error: '.$err];
    $data = json_decode($resp, true);
    if ($http >= 200 && $http < 300 && !empty($data['token'])) {
        return ['success'=>true, 'token'=>$data['token'], 'redirect_url'=>$data['redirect_url'] ?? ''];
    }
    $msg = $data['error_messages'][0] ?? $data['status_message'] ?? $resp;
    return ['success'=>false,'error'=>$msg,'raw'=>$resp,'http'=>$http];
}
