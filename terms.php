<?php require_once __DIR__ . '/config.php'; ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Syarat Layanan — AudioAgent</title>
  <meta name="description" content="Syarat Layanan AudioAgent — aturan penggunaan download YouTube & TikTok.">
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
    <div class="eyebrow" style="margin-bottom:12px">Legal • Diperbarui 1 Sep 2026</div>
    <h1 style="font-size:38px;letter-spacing:-.03em;line-height:1.1">Syarat Layanan</h1>
    <p style="color:var(--muted);margin-top:10px">Dengan memakai AudioAgent, kamu setuju dengan syarat di bawah. Baca santai — kami tulis sejelas mungkin, tanpa bahasa hukum berbelit.</p>

    <div style="margin-top:28px;display:grid;gap:18px">
      <div class="card">
        <h3>1. Untuk apa tool ini?</h3>
        <p>AudioAgent membantu kamu menyimpan <b>konten yang kamu punya haknya</b> — upload sendiri, konten free-to-share, atau untuk keperluan pribadi (mis. dengar offline). Gratis, tanpa iklan mengganggu.</p>
      </div>
      <div class="card">
        <h3>2. Yang tidak boleh</h3>
        <p>Jangan pakai tool ini untuk <b>menyebarkan ulang konten berhak cipta tanpa izin</b>, menjual kembali hasil download, atau melanggar hak pemilik konten. Kamu bertanggung jawab atas link yang kamu tempel.</p>
      </div>
      <div class="card">
        <h3>3. Batasan teknis</h3>
        <p>Kami batasi durasi ±90 menit, tidak support live stream & video private/member-only. YouTube/TikTok kadang mengubah sistem — jika gagal, coba lagi nanti atau pakai video publik lain. Maksimal kualitas tergantung sumber aslinya.</p>
      </div>
      <div class="card">
        <h3>4. Tidak ada jaminan 100%</h3>
        <p>Kami berusaha jaga agar tool selalu jalan, tapi tidak menjamin selalu tersedia tanpa gangguan. Kami bisa membatasi pemakaian jika ada penyalahgunaan (spam/bot).</p>
      </div>
      <div class="card">
        <h3>5. Traktir itu sukarela</h3>
        <p>Traktir via Midtrans bersifat donasi sukarela. Tidak ada langganan otomatis — bayar sekali, masuk Hall of Fame. Tidak ada refund kecuali kesalahan sistem (hubungi kontak).</p>
      </div>
      <div class="card">
        <h3>6. Perubahan syarat</h3>
        <p>Syarat bisa diperbarui sewaktu-waktu. Jika ada perubahan penting, kami tampilkan tanggal "Diperbarui" di atas.</p>
      </div>
      <div class="card" style="background:var(--grad-soft);border-color:rgba(37,99,235,.15)">
        <h3>Intinya</h3>
        <p>Gunakan dengan bijak untuk keperluan pribadi & hargai kreator. Jika kamu pemilik konten dan keberatan, lihat halaman <a href="dmca.php" style="color:#2563eb;text-decoration:underline">DMCA</a>.</p>
      </div>
    </div>
  </div>
</section>
<footer class="footer">
  <div class="container" style="text-align:center;color:var(--muted2);font-size:12px"> <a href="index.php" style="color:var(--muted);text-decoration:underline">← Kembali ke Beranda</a> • <b>AudioAgent</b> © 2026</div>
</footer>
<script src="assets/js/main.js"></script>
</body>
</html>
