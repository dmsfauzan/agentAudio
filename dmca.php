<?php require_once __DIR__ . '/config.php'; ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>DMCA — AudioAgent</title>
  <meta name="description" content="DMCA AudioAgent — prosedur pengaduan hak cipta.">
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
    <div class="eyebrow" style="margin-bottom:12px">Legal • DMCA</div>
    <h1 style="font-size:38px;letter-spacing:-.03em;line-height:1.1">DMCA — Hak Cipta</h1>
    <p style="color:var(--muted);margin-top:10px">Kami menghormati hak kreator. Jika kamu pemilik hak dan keberatan, kami siap bantu.</p>

    <div style="margin-top:28px;display:grid;gap:18px">
      <div class="card">
        <h3>Peran AudioAgent</h3>
        <p>Kami tidak menyimpan atau meng-host video. Tool hanya memproses link yang kamu tempel via <code>yt-dlp</code> langsung ke sumber (YouTube/TikTok), lalu file sementara auto-hapus. Kami tidak punya katalog konten.</p>
      </div>
      <div class="card">
        <h3>Cara mengajukan pengaduan</h3>
        <p>Kirim email via <a href="contact.php" style="color:#2563eb;text-decoration:underline">Kontak</a> dengan subjek <b>"DMCA Takedown"</b> dan sertakan:</p>
        <ul style="margin-top:10px;display:grid;gap:6px;color:var(--muted);font-size:13px;list-style:disc;padding-left:18px">
          <li>Bukti kamu pemilik hak (identitas / link channel resmi)</li>
          <li>URL video asli (YouTube/TikTok) & deskripsi karya</li>
          <li>Pernyataan bahwa penggunaan tidak diizinkan</li>
          <li>Kontak yang bisa dihubungi (email/WA)</li>
        </ul>
      </div>
      <div class="card">
        <h3>Yang akan kami lakukan</h3>
        <p>Kami cek laporan, dan jika valid kami bisa <b>blokir URL spesifik</b> agar tidak bisa diproses lagi, serta hapus file temp terkait. Kami tidak bisa menghapus video di YouTube/TikTok — itu harus lapor ke platform sumber.</p>
      </div>
      <div class="card">
        <h3>Gunakan dengan bijak</h3>
        <p>Gunakan tool hanya untuk konten yang kamu punya haknya (milik sendiri, free-to-share, atau pribadi). Jangan gunakan untuk menyebarkan ulang konten berhak cipta tanpa izin.</p>
      </div>
      <div class="card" style="background:var(--grad-soft);border-color:rgba(37,99,235,.15)">
        <h3>Kontak DMCA</h3>
        <p>Hubungi via <a href="contact.php" style="color:#2563eb;text-decoration:underline">halaman Kontak</a> — tulis "DMCA" di pesan. Kami usahakan balas ≤ 2×24 jam.</p>
      </div>
    </div>
  </div>
</section>
<footer class="footer"><div class="container" style="text-align:center;color:var(--muted2);font-size:12px"><a href="index.php" style="color:var(--muted);text-decoration:underline">← Kembali ke Beranda</a> • <b>AudioAgent</b> © 2026</div></footer>
<script src="assets/js/main.js"></script>
</body>
</html>
