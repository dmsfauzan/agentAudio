<?php
// AudioAgent config — Free + Traktir (Midtrans)

define('BASE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on' ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/AudioAgent');

define('DATA_DIR', __DIR__ . '/data');
define('DONATIONS_FILE', DATA_DIR . '/donations.json');

// Midtrans — ganti dengan key asli dari dashboard.sandbox.midtrans.com
// Sandbox: Server Key diawali SB-Mid-server-... , Client Key SB-Mid-client-...
define('MIDTRANS_SERVER_KEY', getenv('MIDTRANS_SERVER_KEY') ?: 'SB-Mid-server-xxxxxxxxxxxxxxxx');
define('MIDTRANS_CLIENT_KEY', getenv('MIDTRANS_CLIENT_KEY') ?: 'SB-Mid-client-xxxxxxxxxxxxxxxx');
define('MIDTRANS_IS_PRODUCTION', false); // false = sandbox, true = production

// Allowed donation amounts (preset) — custom tetap boleh
define('TRAKTIR_MIN', 1000);
define('TRAKTIR_MAX', 10000000);

// Async worker queue (Fase D)
define('JOBS_DIR', DATA_DIR . '/jobs');
define('JOBS_OUT_DIR', DATA_DIR . '/jobs_out');
define('JOB_SECRET', getenv('JOB_SECRET') ?: 'audioagent-secret-change-me');
define('WORKER_MAX', 3);            // max parallel yt-dlp jobs
define('JOB_TTL', 3600);            // result file expiry (seconds)
