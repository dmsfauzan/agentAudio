<?php require_once __DIR__ . '/config.php'; ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Tentang — AudioAgent</title>
  <meta name="description" content="Tentang AudioAgent — tool gratis download YouTube & TikTok.">
  <link rel="stylesheet" href="assets/css/style.css">
  <script>(function(){try{var t=localStorage.getItem('theme');if(!t)t=matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';document.documentElement.setAttribute('data-theme',t);}catch(e){}})();</script>
</head>
<body>
<nav class="nav"><div class="container nav-inner"><a class="brand" href="index.php"><span class="brand-mark"><svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg></span><span>AudioAgent</span></a><div class="nav-cta"><button class="theme-toggle" id="themeToggle">🌙</button><a class="btn btn-primary" href="index.php#tool">Buka Tool</a></div></div></nav>
<section class="section" style="padding-top:28px">
  <div class="container" style="max-width:820px">
    <a href="index.php" style="color:var(--muted);font-size:13px">← Beranda</a>
    <h1 style="font-size:38px;margin-top:12px">Tentang AudioAgent</h1>
    <div style="margin-top:18px;display:grid;gap:16px">
      <div class="card"><h3>Misi</h3><p>Membuat download audio/video jadi sesederhana paste link — tanpa iklan, tanpa daftar, tanpa watermark. Dibuat dengan yt-dlp + FFmpeg, gratis selamanya.</p></div>
      <div class="card"><h3>Changelog</h3><p><b>1 Sep 2026</b> — Rilis awal: YouTube MP3 + TikTok MP4, blue-white clean theme + dark mode, traktir Midtrans, Hall of Fame.<br><b>Sep 2026</b> — Tambah trim, playlist ZIP, cache, rate limit, PWA, SEO.</p></div>
      <div class="card"><h3>Teknologi</h3><p>PHP 8.3 + yt-dlp + FFmpeg + Midtrans Snap. File di <code>temp/</code> auto-hapus 1 jam. Donasi di <code>data/donations.json</code>.</p></div>
    </div>
  </div>
</section>
<footer class="footer"><div class="container" style="text-align:center"><a href="index.php">← Beranda</a></div></footer>
<script src="assets/js/main.js"></script>
</body>
</html>
