<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/db.php';
$sent = false;
$err = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $topic = trim($_POST['topic'] ?? 'Umum');
  $message = trim($_POST['message'] ?? '');
  if (!$name || !$message) $err = 'Nama dan pesan wajib diisi.';
  elseif (mb_strlen($message) < 10) $err = 'Pesan minimal 10 karakter.';
  else {
    // simpan ke file sederhana — untuk demo
    $logFile = __DIR__ . '/data/messages.json';
    $all = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : [];
    if (!is_array($all)) $all=[];
    $all[] = ['name'=>$name,'email'=>$email,'topic'=>$topic,'message'=>$message,'at'=>date('c'),'ip'=>$_SERVER['REMOTE_ADDR']??''];
    @mkdir(__DIR__.'/data',0777,true);
    file_put_contents($logFile, json_encode($all, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    $sent = true;
  }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Kontak — AudioAgent</title>
  <meta name="description" content="Kontak AudioAgent — tanya, lapor bug, atau DMCA.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <script>(function(){try{var t=localStorage.getItem('theme');if(!t)t=window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';document.documentElement.setAttribute('data-theme',t);}catch(e){}})();</script>
</head>
<body>
<nav class="nav">
  <div class="container nav-inner">
    <a class="brand" href="index.php"><span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg></span><span>AudioAgent</span><small>YT • TT</small></a>
    <div class="nav-links"><a href="index.php#tool">Tool</a><a href="index.php#faq">FAQ</a><a href="index.php#donatur">Donatur</a></div>
    <div class="nav-cta"><button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode">🌙</button><a class="btn btn-primary" href="index.php#tool">Mulai Download 🚀</a></div>
  </div>
</nav>
<section class="section" style="padding-top:32px">
  <div class="container" style="max-width:780px">
    <a href="index.php" style="display:inline-flex;align-items:center;gap:6px;color:var(--muted);font-size:13px;margin-bottom:18px">← Kembali ke Beranda</a>
    <div class="eyebrow" style="margin-bottom:12px">Kontak • Respon ≤ 2×24 jam</div>
    <h1 style="font-size:38px;letter-spacing:-.03em;line-height:1.1">Hubungi kami</h1>
    <p style="color:var(--muted);margin-top:10px">Punya pertanyaan, lapor bug, atau pengaduan DMCA? Tulis di sini — atau traktir sambil kirim pesan via <a href="index.php#donatur" style="color:#2563eb;text-decoration:underline">Hall of Fame</a>.</p>

    <div style="margin-top:24px;display:grid;grid-template-columns:1fr 1fr;gap:16px">
      <div class="card" style="text-align:center"><div class="ic" style="margin:0 auto 10px">💬</div><b>Form di bawah</b><br><span style="color:var(--muted);font-size:13px">Paling cepat dibaca</span></div>
      <div class="card" style="text-align:center"><div class="ic" style="margin:0 auto 10px">☕</div><b>Via Traktir</b><br><span style="color:var(--muted);font-size:13px">Kirim pesan + donasi</span></div>
    </div>

    <div class="card" style="margin-top:18px">
      <?php if ($sent): ?>
        <div class="alert show ok" style="margin-top:0">✅ Terima kasih, <b><?= htmlspecialchars($name) ?></b>! Pesanmu sudah kami terima. Kami balas secepatnya.</div>
        <a href="index.php" class="btn btn-ghost" style="margin-top:14px">← Kembali ke Beranda</a>
      <?php else: ?>
        <?php if ($err): ?><div class="alert show err"><?= htmlspecialchars($err) ?></div><?php endif; ?>
        <form method="POST" style="display:grid;gap:14px">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <label style="display:grid;gap:6px;font-size:13px;font-weight:600">Nama
              <input name="name" required maxlength="60" placeholder="Nama kamu" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" style="background:var(--bg);border:1px solid var(--border);color:var(--text);padding:10px 14px;border-radius:10px">
            </label>
            <label style="display:grid;gap:6px;font-size:13px;font-weight:600">Email (opsional)
              <input name="email" type="email" maxlength="120" placeholder="kamu@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" style="background:var(--bg);border:1px solid var(--border);color:var(--text);padding:10px 14px;border-radius:10px">
            </label>
          </div>
          <label style="display:grid;gap:6px;font-size:13px;font-weight:600">Topik
            <select name="topic" style="background:var(--bg);border:1px solid var(--border);color:var(--text);padding:10px 14px;border-radius:10px">
              <option <?= ($_POST['topic']??'')==='Umum'?'selected':'' ?>>Umum</option>
              <option <?= ($_POST['topic']??'')==='Bug / Gagal download'?'selected':'' ?>>Bug / Gagal download</option>
              <option <?= ($_POST['topic']??'')==='DMCA Takedown'?'selected':'' ?>>DMCA Takedown</option>
              <option <?= ($_POST['topic']??'')==='Traktir / Donasi'?'selected':'' ?>>Traktir / Donasi</option>
            </select>
          </label>
          <label style="display:grid;gap:6px;font-size:13px;font-weight:600">Pesan
            <textarea name="message" required rows="5" maxlength="2000" placeholder="Tulis pesanmu di sini — sertakan link video jika lapor bug/DMCA" style="background:var(--bg);border:1px solid var(--border);color:var(--text);padding:12px 14px;border-radius:12px;resize:vertical"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
          </label>
          <button type="submit" class="btn btn-primary" style="justify-content:center">Kirim Pesan →</button>
          <div style="color:var(--muted2);font-size:11px;text-align:center">Pesan disimpan lokal di <code>data/messages.json</code> (demo). Untuk produksi, hubungkan ke email.</div>
        </form>
      <?php endif; ?>
    </div>
  </div>
</section>
<footer class="footer"><div class="container" style="text-align:center;color:var(--muted2);font-size:12px"><a href="index.php" style="color:var(--muted);text-decoration:underline">← Kembali ke Beranda</a> • <b>AudioAgent</b> © 2026</div></footer>
<script src="assets/js/main.js"></script>
</body>
</html>
