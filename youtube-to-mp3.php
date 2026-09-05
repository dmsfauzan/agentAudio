<?php
// SEO landing — YouTube to MP3
$_GET['platform'] = 'youtube';
require_once __DIR__ . '/config.php';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>YouTube to MP3 — Download MP3 320kbps Gratis | AudioAgent</title>
  <meta name="description" content="YouTube to MP3 converter gratis. Paste link YouTube, pilih 320/256/128kbps, download MP3 tanpa daftar & tanpa iklan.">
  <link rel="canonical" href="<?= BASE_URL ?>/youtube-to-mp3">
  <meta property="og:title" content="YouTube to MP3 — 320kbps Gratis">
  <meta property="og:description" content="Convert YouTube ke MP3 320kbps. Gratis, cepat, tanpa iklan.">
  <link rel="stylesheet" href="assets/css/style.css">
  <script>(function(){try{var t=localStorage.getItem('theme');if(!t)t=matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';document.documentElement.setAttribute('data-theme',t);}catch(e){}})();</script>
</head>
<body>
<nav class="nav"><div class="container nav-inner"><a class="brand" href="index.php"><span class="brand-mark"><svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg></span><span>AudioAgent</span></a><div class="nav-cta"><button class="theme-toggle" id="themeToggle">🌙</button><a class="btn btn-primary" href="index.php#tool">Buka Tool</a></div></div></nav>
<section class="section" style="padding-top:28px">
  <div class="container" style="max-width:780px;text-align:center">
    <div class="eyebrow" style="margin:0 auto">YOUTUBE → MP3 • 320 KBPS</div>
    <h1 style="font-size:42px;margin-top:12px">YouTube to <span class="grad">MP3</span> — 320 kbps</h1>
    <p style="color:var(--muted);margin-top:10px">Paste link YouTube, pilih kualitas, file MP3 siap dalam detik. Gratis & tanpa iklan.</p>
    <a class="btn btn-primary btn-lg" href="index.php#tool" style="margin-top:18px">Buka Converter →</a>
    <div style="margin-top:28px;text-align:left" class="card">
      <h3>Cara pakai</h3><p>1. Copy link youtube.com/watch atau youtu.be<br>2. Paste di <a href="index.php#tool" style="color:#2563eb">AudioAgent</a> → pilih MP3 320kbps<br>3. Klik Convert → Download</p>
      <h3 style="margin-top:16px">Kualitas</h3><p>320 kbps untuk musik, 128 kbps hemat kuota, 64 kbps untuk podcast.</p>
    </div>
  </div>
</section>
<footer class="footer"><div class="container" style="text-align:center"><a href="index.php">← Beranda</a> • <a href="tiktok-downloader.php">TikTok Downloader</a></div></footer>
<script src="assets/js/main.js"></script>
</body>
</html>
