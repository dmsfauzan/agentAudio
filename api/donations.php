<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/db.php';

$settled = get_settled_donations(24);
echo json_encode(['success'=>true, 'data'=>$settled]);
