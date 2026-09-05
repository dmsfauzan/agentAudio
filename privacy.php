<?php require_once __DIR__ . '/config.php'; ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Kebijakan Privasi — AudioAgent</title>
  <meta name="description" content="Kebijakan Privasi AudioAgent — data minimal, file auto-hapus, Midtrans.">
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
    <div class="nav-links"><a href="index.php#tool">Tool</a><a href="index.php#faq">FAQ</a><a href="contact.php">Kontak</a></div>
    <div class="nav-cta"><button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode">🌙</button><a class="btn btn-primary" href="index.php#tool">Mulai Download 🚀</a></div>
  </div>
</nav>
<section class="section" style="padding-top:32px">
  <div class="container" style="max-width:820px">
    <a href="index.php" style="display:inline-flex;align-items:center;gap:6px;color:var(--muted);font-size:13px;margin-bottom:18px">← Kembali ke Beranda</a>
    <div class="eyebrow" style="margin-bottom:12px">Privasi • Diperbarui 1 Sep 2026</div>
    <h1 style="font-size:38px;letter-spacing:-.03em;line-height:1.1">Kebijakan Privasi</h1>
    <p style="color:var(--muted);margin-top:10px">Kami ambil data <b>sesedikit mungkin</b>. Tidak perlu daftar untuk download. Halaman ini jelaskan apa yang kami simpan & kenapa.</p>

    <div style="margin-top:28px;display:grid;gap:18px">
      <div class="card">
        <h3>Data yang kami kumpulkan</h3>
        <p><b>Tanpa login:</b> kami tidak minta email/nama untuk download. Yang tercatat hanya <code>URL yang kamu tempel</code> sementara untuk proses, lalu hilang.<br><b>Traktir:</b> nama, pesan, nominal, dan status pembayaran Midtrans (disimpan di <code>data/donations.json</code> untuk Hall of Fame).<br><b>Log teknis:</b> IP & user-agent singkat untuk keamanan (anti-spam) — tidak dijual.</p>
      </div>
      <div class="card">
        <h3>Penyimpanan file</h3>
        <p>File hasil download dibuat di folder <code>temp/</code> dan <b>otomatis terhapus</b> setelah kamu download. File lama (&gt;1 jam) dibersihkan tiap ada request baru. Kami tidak menyimpan koleksi video kamu.</p>
      </div>
      <div class="card">
        <h3>Cookies</h3>
        <p>Hanya <b>theme</b> (light/dark) di <code>localStorage</code> dan session teknis. Tidak ada tracker iklan. Traktir via Midtrans pakai cookies Midtrans-nya sendiri (lihat kebijakan Midtrans).</p>
      </div>
      <div class="card">
        <h3>Pembayaran (Midtrans)</h3>
        <p>Saat traktir, kamu dialihkan ke halaman aman Midtrans (VA/QRIS/e-wallet). Kami tidak menyimpan nomor kartu/VA — Midtrans yang memproses. Kami hanya simpan status & nominal.</p>
      </div>
      <div class="card">
        <h3>Hak kamu</h3>
        <p>Kamu bisa minta hapus nama/pesan dari Hall of Fame kapan saja via <a href="contact.php" style="color:#2563eb;text-decoration:underline">Kontak</a>. Donasi yang sudah settlement tidak bisa refund kecuali kesalahan sistem.</p>
      </div>
      <div class="card" style="background:var(--grad-soft);border-color:rgba(37,99,235,.15)">
        <h3>Kontak privasi</h3>
        <p>Ada pertanyaan soal data? Hubungi via <a href="contact.php" style="color:#2563eb;text-decoration:underline">halaman Kontak</a> atau pesan Traktir. Kami jawab secepatnya.</p>
      </div>
    </div>
  </div>
</section>
<footer class="footer"><div class="container" style="text-align:center;color:var(--muted2);font-size:12px"><a href="index.php" style="color:var(--muted);text-decoration:underline">← Kembali ke Beranda</a> • <b>AudioAgent</b> © 2026</div></footer>
<script src="assets/js/main.js"></script>
</body>
</html>
