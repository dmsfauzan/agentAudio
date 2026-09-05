<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/db.php';
$settledDonors = get_settled_donations(12);
$totalDonasi = array_sum(array_column(array_filter(load_donations(), fn($d)=>($d['status']??'')==='settlement'), 'amount'));
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>AudioAgent — Download Audio YouTube & Video TikTok Tanpa Watermark Gratis</title>
  <meta name="description" content="Download audio YouTube ke MP3 320kbps & video TikTok tanpa watermark gratis. Paste link, pilih kualitas, file siap dalam detik. Tanpa daftar, tanpa iklan.">
  <link rel="canonical" href="<?= BASE_URL ?>/">
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= BASE_URL ?>/">
  <meta property="og:title" content="AudioAgent — Download YouTube & TikTok Gratis">
  <meta property="og:description" content="Download MP3 YouTube 320kbps & video TikTok tanpa watermark. Gratis, cepat, tanpa iklan.">
  <meta property="og:image" content="<?= BASE_URL ?>/assets/img/og.png">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="AudioAgent — Download YouTube & TikTok Gratis">
  <meta name="twitter:description" content="Paste link, pilih kualitas, download. YouTube MP3 & TikTok MP4 tanpa watermark.">
  <meta name="twitter:image" content="<?= BASE_URL ?>/assets/img/og.png">
  <link rel="icon" href="assets/img/favicon.png" type="image/png">
  <link rel="manifest" href="manifest.json">
  <meta name="theme-color" content="#2563eb">
  <script type="application/ld+json">{"@context":"https://schema.org","@type":"WebApplication","name":"AudioAgent","url":"<?= BASE_URL ?>/","description":"Download audio YouTube & video TikTok tanpa watermark gratis.","applicationCategory":"MultimediaApplication","operatingSystem":"All","offers":{"@type":"Offer","price":"0","priceCurrency":"IDR"}}</script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <script>
    (function(){
      try{
        var t = localStorage.getItem('theme');
        if(!t) t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme', t);
      }catch(e){}
    })();
  </script>
  <?php if (MIDTRANS_CLIENT_KEY !== 'SB-Mid-client-xxxxxxxxxxxxxxxx'): ?>
  <script type="text/javascript" src="<?= MIDTRANS_IS_PRODUCTION ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' ?>" data-client-key="<?= htmlspecialchars(MIDTRANS_CLIENT_KEY) ?>"></script>
  <?php else: ?>
  <!-- Midtrans belum dikonfigurasi — ganti MIDTRANS_CLIENT_KEY di config.php untuk enable Snap -->
  <?php endif; ?>
</head>
<body>

<!-- NAV -->
<nav class="nav">
  <div class="container nav-inner">
    <a class="brand" href="#">
      <span class="brand-mark" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
      </span>
      <span>AudioAgent</span>
      <small>YT • TT</small>
    </a>
    <div class="nav-links" id="navLinks">
      <a href="#tool">Tool</a>
      <a href="#cara">Cara Pakai</a>
      <a href="#fitur">Fitur</a>
      <a href="#donatur">Donatur</a>
      <a href="#faq">FAQ</a>
    </div>
    <div class="nav-cta">
      <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode" title="Toggle dark mode">🌙</button>
      <button class="btn btn-ghost" id="langToggle" aria-label="Ganti bahasa" title="Ganti bahasa">ID</button>
      <button class="btn btn-ghost" id="navTraktir">☕ Traktir</button>
      <a class="btn btn-primary" href="#tool">Mulai Download 🚀</a>
      <button class="hamburger" id="ham" aria-label="Menu">☰</button>
    </div>
  </div>
</nav>

<!-- HERO — centered ala gmpion.pl / YTMP3 -->
<section class="hero">
  <div class="container" style="max-width:780px;text-align:center">
    <div class="eyebrow" style="margin:0 auto">✏️ YOUTUBE → MP3 • TIKTOK → MP4 NO WATERMARK • FREE</div>
    <h1 style="text-align:center">
      Download<br>
      <span class="grad">YouTube & TikTok.</span><br>
      Tanpa watermark.
    </h1>
    <p class="sub" style="margin:14px auto 0;text-align:center;max-width:640px">
      Satu tool untuk dua platform. YouTube → MP3 320kbps. TikTok → MP4 tanpa watermark hingga 1080p. <b>Gratis selamanya</b> — kalau terbantu, traktir developer via Midtrans.
    </p>

    <!-- TOOL CARD — centered -->
    <div class="tool-card" id="tool" style="max-width:660px;margin:28px auto 0;text-align:left">
      <div class="tool-head">
        <span class="dot"></span><b id="toolTitle">Media Extractor</b>
        <span id="toolBadge">MP3 • MP4 no WM</span>
      </div>

      <div class="platform-toggle" id="platformToggle" role="tablist" aria-label="Pilih platform">
        <button class="pt-btn active" data-platform="youtube" role="tab" aria-selected="true">▶️ YouTube → MP3</button>
        <button class="pt-btn" data-platform="tiktok" role="tab" aria-selected="false">🎵 TikTok → MP4</button>
      </div>

      <form id="dlForm">
        <div class="url-row">
          <label class="url-input" for="ytUrl" style="flex:1">
            <span class="icon">🔗</span>
            <input id="ytUrl" type="url" placeholder="https://www.youtube.com/watch?v=... atau youtu.be/..." autocomplete="off" required>
          </label>
          <button class="btn btn-primary" type="submit" id="btnFetch">Convert</button>
        </div>
        <div class="format-row" id="ytFormatRow">
          <label style="font-size:13px;color:var(--muted);font-weight:600">Format:</label>
          <select id="ytMode" class="select">
            <option value="audio">🎵 Audio</option>
            <option value="video">🎬 Video</option>
          </select>
          <select id="format" class="select">
            <option value="mp3">MP3 — 320 kbps</option>
            <option value="m4a">M4A — AAC 256 kbps</option>
            <option value="opus">OPUS — 160 kbps</option>
            <option value="wav">WAV — lossless</option>
          </select>
          <select id="ytVideoQuality" class="select" style="display:none">
            <option value="">Auto — Best</option>
          </select>
          <span style="margin-left:auto;color:var(--muted2);font-size:12px">yt-dlp + ffmpeg</span>
        </div>
        <div class="format-row" id="ttQualityRow" style="display:none">
          <label style="font-size:13px;color:var(--muted);font-weight:600">Kualitas:</label>
          <select id="ttQuality" class="select">
            <option value="">Auto — Best (tanpa watermark)</option>
            <option value="1080">1080p — Full HD</option>
            <option value="720">720p — HD</option>
            <option value="540">540p — hemat kuota</option>
          </select>
          <span style="margin-left:auto;color:var(--muted2);font-size:12px">MP4 • no watermark</span>
        </div>
        <div class="hint" id="hintRow">💡 Tip: <a href="#" data-example="https://www.youtube.com/watch?v=dQw4w9WgXcQ" data-platform="youtube">contoh YouTube</a> · <a href="#" data-example="https://www.tiktok.com/@scout2015/video/6718335390845095173" data-platform="tiktok">contoh TikTok</a> • <a href="#" id="pasteBtn">📋 Paste</a></div>
        <details style="margin-top:10px"><summary style="cursor:pointer;color:var(--muted);font-size:13px;font-weight:600">⚙️ Opsi lanjutan — potong durasi & playlist</summary>
          <div style="display:flex;gap:8px;margin-top:10px;flex-wrap:wrap">
            <input id="trimStart" placeholder="Mulai 00:00" style="flex:1;min-width:110px;background:var(--bg);border:1px solid var(--border);padding:8px 12px;border-radius:999px;color:var(--text);font-size:13px">
            <span style="align-self:center;color:var(--muted)">—</span>
            <input id="trimEnd" placeholder="Selesai 01:30" style="flex:1;min-width:110px;background:var(--bg);border:1px solid var(--border);padding:8px 12px;border-radius:999px;color:var(--text);font-size:13px">
            <span style="font-size:11px;color:var(--muted2);align-self:center">Kosongkan jika tidak perlu dipotong</span>
          </div>
          <div id="playlistNote" style="display:none;margin-top:10px;padding:10px;background:rgba(37,99,235,.06);border:1px solid rgba(37,99,235,.15);border-radius:10px;font-size:13px"><span id="playlistInfo"></span> <a id="playlistDownload" class="btn btn-primary" style="padding:6px 12px;font-size:12px;margin-left:8px">Download ZIP (max 10)</a></div>
        </details>
      </form>

      <div id="alert" class="alert"></div>
      <div id="progressBar" style="display:none;margin-top:12px;height:6px;background:var(--card2);border-radius:999px;overflow:hidden"><div id="progressFill" style="width:0%;height:100%;background:var(--grad);transition:width .3s"></div></div>

      <div id="preview" class="preview">
        <div class="prev-thumb">
          <img id="prevThumb" alt="" loading="lazy">
          <span class="dur" id="prevDur">0:00</span>
        </div>
        <div class="prev-body">
          <div class="prev-title" id="prevTitle">Judul video</div>
          <div class="prev-meta" id="prevMeta">Channel • durasi • views</div>
          <div class="prev-actions">
            <button class="btn btn-primary" id="btnDownload">⬇ Download</button>
            <a class="btn btn-ghost" href="#" onclick="document.getElementById('ytUrl').select();return false">Ganti Link</a>
          </div>
          <div style="margin-top:8px;color:var(--muted2);font-size:11px" id="qualityNote"></div>
          <div id="postDownloadTraktir" style="display:none;margin-top:12px;padding:12px;background:rgba(37,99,235,.06);border:1px solid rgba(37,99,235,.15);border-radius:12px;text-align:center">
            <div style="font-size:13px;font-weight:700">🎉 Beres! Suka tool ini?</div>
            <div style="font-size:12px;color:var(--muted);margin:4px 0 8px">Traktir developer biar server tetap nyala ☕</div>
            <button class="btn btn-primary" style="width:100%" onclick="openTraktir(10000)">☕ Traktir Rp 10k</button>
          </div>
        </div>
      </div>
    </div>

    <p style="margin-top:14px;color:var(--muted2);font-size:12px;text-align:center">Gratis & tanpa iklan • 320 kbps • Tanpa watermark • <a href="#" onclick="openTraktir(10000);return false" style="color:var(--muted);text-decoration:underline">☕ Traktir</a></p>
    <div class="stats" style="justify-content:center;margin-top:18px">
      <div class="stat"><strong id="statTotal">425.983+</strong><span>media terdownload ✨</span></div>
      <div class="stat"><strong>~3 detik</strong><span>rata-rata proses</span></div>
      <div class="stat"><strong><?= count($settledDonors) ?>+</strong><span>donatur traktir ❤️</span></div>
    </div>
    <div id="historyWrap" style="display:none;max-width:660px;margin:18px auto 0;text-align:left">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px"><b style="font-size:13px">🕘 Riwayat download</b><button id="clearHistory" style="background:none;border:0;color:var(--muted);font-size:12px;cursor:pointer;text-decoration:underline">Hapus</button></div>
      <div id="historyList" style="display:grid;gap:8px"></div>
    </div>
  </div>
</section>

<!-- CARA PAKAI -->
<section class="section" id="cara">
  <div class="container">
    <div class="section-head">
      <div class="kicker"># Cara Pakai</div>
      <h2>3 langkah jadi file</h2>
      <p>Sama simpelnya untuk YouTube & TikTok — tanpa iklan ganggu, tanpa redirect aneh.</p>
    </div>
    <div class="steps">
      <div class="step">
        <span class="step-num">1</span>
        <div class="ic">🔗</div>
        <h3>Tempel Link</h3>
        <p>Copy link YouTube (youtube.com/shorts/music) atau TikTok (vm.tiktok.com / tiktok.com/@...), pilih platform, paste di kolom.</p>
      </div>
      <div class="step">
        <span class="step-num">2</span>
        <div class="ic">👁️</div>
        <h3>Preview & Pilih Kualitas</h3>
        <p>Sistem ambil judul, thumbnail, & durasi via yt-dlp. Untuk TikTok pilih 540p/720p/1080p atau Auto Best tanpa watermark.</p>
      </div>
      <div class="step">
        <span class="step-num">3</span>
        <div class="ic">⬇️</div>
        <h3>Download Gratis</h3>
        <p>YouTube → MP3/M4A hingga 320kbps. TikTok → MP4 tanpa watermark. Semua gratis — traktir opsional via Midtrans.</p>
      </div>
    </div>
  </div>
</section>

<!-- FITUR -->
<section class="section" id="fitur">
  <div class="container">
    <div class="section-head">
      <div class="kicker">Fitur Utama</div>
      <h2>Kenapa pilih AudioAgent?</h2>
      <p>Cepat, gratis, dan transparan — satu tool, dua platform beres.</p>
    </div>
    <div class="grid3">
      <div class="card"><div class="ic">⚡</div><h3>Super Cepat</h3><p>Ekstrak langsung di server Laragon + ffmpeg. Rata-rata 3–8 detik.</p></div>
      <div class="card"><div class="ic">💧</div><h3>TikTok Tanpa Watermark</h3><p>Ambil stream play_addr asli (h264/h265) + --xff US.</p></div>
      <div class="card"><div class="ic">🎧</div><h3>Kualitas Tinggi</h3><p>MP3 320kbps / M4A/WAV. TikTok 540p/720p/1080p.</p></div>
      <div class="card"><div class="ic">🔒</div><h3>Aman & Privat</h3><p>File diproses lokal, auto-hapus 1 jam.</p></div>
      <div class="card"><div class="ic">🌊</div><h3>Support Semua Link</h3><p>YT: watch/shorts/youtu.be/music. TT: tiktok.com/vm.tiktok/vt.</p></div>
      <div class="card"><div class="ic">☕</div><h3>Traktir via Midtrans</h3><p>VA BCA/BNI/BRI, QRIS, GoPay/OVO/DANA — donasi masuk Hall of Fame.</p></div>
    </div>
    <div style="text-align:center;margin-top:28px">
      <div class="kicker" style="margin-bottom:12px">Platform Didukung</div>
      <div class="platforms">
        <span class="plat">▶️ <b>YouTube</b></span>
        <span class="plat">🔗 <b>youtu.be</b></span>
        <span class="plat">📱 <b>Shorts</b></span>
        <span class="plat">🎵 <b>TikTok</b></span>
        <span class="plat">🔗 <b>vm.tiktok</b></span>
      </div>
    </div>
  </div>
</section>

<!-- GRATIS + TRAKTIR -->
<section class="section">
  <div class="container">
    <div class="section-head">
      <div class="kicker">Dukung Developer</div>
      <h2>Gratis selamanya. Traktir kalau suka.</h2>
      <p>Semua fitur terbuka tanpa login. Traktir bersifat sukarela — via Midtrans (VA/QRIS/e-wallet) & tampil di Hall of Fame.</p>
    </div>
    <div class="pricing">
      <div class="price">
        <h3>Gratis</h3>
        <div class="rp">Rp 0 <small>/ selamanya</small></div>
        <ul>
          <li><i>✓</i> Unlimited YouTube MP3 + TikTok MP4</li>
          <li><i>✓</i> TikTok tanpa watermark hingga 1080p</li>
          <li><i>✓</i> Pilih kualitas manual</li>
          <li><i>✓</i> Tanpa login & watermark</li>
        </ul>
        <a class="btn btn-primary" href="#tool">Mulai Gratis 🚀</a>
      </div>
      <div class="price popular">
        <h3>Traktir ☕</h3>
        <div class="rp">Rp 10k <small>/ sekali</small></div>
        <ul>
          <li><i>✓</i> Dukung server tetap nyala</li>
          <li><i>✓</i> Nama di Hall of Fame</li>
          <li><i>✓</i> VA / QRIS / GoPay / OVO / DANA</li>
          <li><i>✓</i> Midtrans aman & instan</li>
        </ul>
        <button class="btn btn-primary" onclick="openTraktir(10000)" style="width:100%;margin-top:18px">☕ Traktir Rp 10k</button>
        <div style="display:flex;gap:8px;margin-top:8px">
          <button class="btn btn-ghost" onclick="openTraktir(5000)" style="flex:1">5k</button>
          <button class="btn btn-ghost" onclick="openTraktir(20000)" style="flex:1">20k</button>
          <button class="btn btn-ghost" onclick="openTraktir(50000)" style="flex:1">50k</button>
        </div>
      </div>
      <div class="price">
        <h3>Self-Host</h3>
        <div class="rp">Rp 0 <small>/ open source</small></div>
        <ul>
          <li><i>✓</i> Clone dari Laragon www</li>
          <li><i>✓</i> yt-dlp + ffmpeg included</li>
          <li><i>✓</i> Data donasi JSON (data/donations.json)</li>
          <li><i>✓</i> Ganti MIDTRANS keys di config.php</li>
        </ul>
        <a class="btn btn-ghost" href="#faq">Lihat FAQ →</a>
      </div>
    </div>
  </div>
</section>

<!-- HALL OF FAME -->
<section class="section" id="donatur">
  <div class="container">
    <div class="section-head">
      <div class="kicker">Hall of Fame ❤️</div>
      <h2>Terima kasih para traktir!</h2>
      <p><?= $totalDonasi > 0 ? 'Total terkumpul <b>Rp '.number_format($totalDonasi,0,',','.').'</b> dari '.count($settledDonors).' donatur' : 'Jadilah yang pertama traktir — namamu akan tampil di sini' ?></p>
    </div>
    <div id="hofGrid" class="grid3">
      <?php if (empty($settledDonors)): ?>
        <div class="card" style="grid-column:1/-1;text-align:center;color:var(--muted)"><div class="ic" style="margin:0 auto 12px">☕</div>Belum ada donatur — jadilah yang pertama! <br><button class="btn btn-primary" onclick="openTraktir(10000)" style="margin-top:12px">☕ Traktir Sekarang</button></div>
      <?php else: foreach ($settledDonors as $d): ?>
        <div class="card" style="border-color:rgba(37,99,235,.2)">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
            <div style="width:36px;height:36px;border-radius:50%;background:var(--grad);display:grid;place-items:center;color:white;font-weight:800"><?= strtoupper(mb_substr($d['name']??'H',0,1)) ?></div>
            <div><b><?= htmlspecialchars($d['name'] ?? 'Hamba Allah') ?></b><br><small style="color:var(--muted)"><?= date('d M Y', strtotime($d['created_at'] ?? 'now')) ?> • <?= htmlspecialchars($d['payment_type'] ?? 'Midtrans') ?></small></div>
            <span style="margin-left:auto;background:rgba(34,197,94,.12);color:#22c55e;border:1px solid rgba(34,197,94,.2);padding:4px 10px;border-radius:999px;font-weight:700;font-size:12px">Rp <?= number_format($d['amount'] ?? 0,0,',','.') ?></span>
          </div>
          <?php if (!empty($d['message'])): ?><p style="margin:0;color:var(--muted);font-style:italic">“<?= htmlspecialchars($d['message']) ?>”</p><?php endif; ?>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</section>

<!-- FAQ — ramah user -->
<section class="section" id="faq">
  <div class="container">
    <div class="section-head">
      <div class="kicker">Tanya Jawab</div>
      <h2>Pertanyaan yang sering ditanyakan</h2>
      <p>Masih bingung? Hubungi kami via Traktir — kami bantu jawab.</p>
    </div>
    <div class="faq">
      <div class="faq-item open">
        <button class="faq-q"><span style="width:32px;height:32px;border-radius:8px;background:rgba(37,99,235,.1);display:grid;place-items:center;font-size:14px;flex-shrink:0">💰</span> Apakah gratis? Ada batasan? <span>+</span></button>
        <div class="faq-a"><b>Gratis 100%</b> untuk semua. Tidak perlu daftar, tidak ada batas harian, dan tidak ada iklan pop-up. Pilih kualitas sesuka hati (MP3 64–320kbps, TikTok 540p–1080p) — semua terbuka.</div>
      </div>
      <div class="faq-item">
        <button class="faq-q"><span style="width:32px;height:32px;border-radius:8px;background:rgba(37,99,235,.1);display:grid;place-items:center;font-size:14px;flex-shrink:0">⚡</span> Gimana cara pakainya? <span>+</span></button>
        <div class="faq-a"><b>3 detik jadi:</b> ① Copy link YouTube/TikTok ② Paste di kotak atas (pilih YouTube/TikTok & kualitas) ③ Klik <b>Convert</b> → preview muncul → <b>Download</b>. File langsung masuk folder Download HP/laptop.</div>
      </div>
      <div class="faq-item">
        <button class="faq-q"><span style="width:32px;height:32px;border-radius:8px;background:rgba(37,99,235,.1);display:grid;place-items:center;font-size:14px;flex-shrink:0">🎬</span> Video apa saja yang bisa di-download? <span>+</span></button>
        <div class="faq-a">YouTube: link <code>youtube.com/watch</code>, <code>youtu.be</code>, Shorts, Music. TikTok: <code>tiktok.com/@user/video/...</code>, <code>vm.tiktok.com</code>, <code>vt.tiktok.com</code>. Maksimal ± 90 menit. Video live, private, atau member-only tidak bisa.</div>
      </div>
      <div class="faq-item">
        <button class="faq-q"><span style="width:32px;height:32px;border-radius:8px;background:rgba(37,99,235,.1);display:grid;place-items:center;font-size:14px;flex-shrink:0">🎧</span> Kualitas mana yang paling bagus? <span>+</span></button>
        <div class="faq-a"><b>Musik:</b> pilih <b>MP3 320 kbps</b> atau TikTok <b>1080p</b> (paling jernih). <b>Podcast/ceramah:</b> 128 kbps / 720p sudah cukup & hemat kuota. Semakin tinggi angka, file semakin besar.</div>
      </div>
      <div class="faq-item">
        <button class="faq-q"><span style="width:32px;height:32px;border-radius:8px;background:rgba(37,99,235,.1);display:grid;place-items:center;font-size:14px;flex-shrink:0">✨</span> TikTok beneran tanpa watermark? <span>+</span></button>
        <div class="faq-a">Ya. Kami ambil video <b>asli tanpa logo TikTok yang memantul</b>, bukan di-crop. Jadi hasilnya bersih seperti upload awal. Kalau videonya memang hanya tersedia versi watermark di TikTok, akan kami infokan.</div>
      </div>
      <div class="faq-item">
        <button class="faq-q"><span style="width:32px;height:32px;border-radius:8px;background:rgba(37,99,235,.1);display:grid;place-items:center;font-size:14px;flex-shrink:0">🔒</span> Apakah aman & privasi terjaga? <span>+</span></button>
        <div class="faq-a">Aman. File diproses di server, <b>langsung terhapus otomatis</b> setelah kamu download (sisa file lama dibersihkan tiap 1 jam). Kami tidak menyimpan video kamu dan tidak minta data pribadi.</div>
      </div>
      <div class="faq-item">
        <button class="faq-q"><span style="width:32px;height:32px;border-radius:8px;background:rgba(37,99,235,.1);display:grid;place-items:center;font-size:14px;flex-shrink:0">⏱️</span> Berapa lama prosesnya? <span>+</span></button>
        <div class="faq-a">Rata-rata <b>3–8 detik</b> untuk lagu 3–4 menit. Video panjang atau kualitas 1080p butuh sedikit lebih lama karena filenya lebih besar.</div>
      </div>
      <div class="faq-item">
        <button class="faq-q"><span style="width:32px;height:32px;border-radius:8px;background:rgba(37,99,235,.1);display:grid;place-items:center;font-size:14px;flex-shrink:0">📱</span> Bisa dipakai di HP? <span>+</span></button>
        <div class="faq-a">Bisa. Buka <code>localhost/AudioAgent</code> di HP yang satu Wi-Fi, atau akses saat sudah di-hosting. Tampilan sudah responsif — tidak perlu install aplikasi.</div>
      </div>
      <div class="faq-item">
        <button class="faq-q"><span style="width:32px;height:32px;border-radius:8px;background:rgba(37,99,235,.1);display:grid;place-items:center;font-size:14px;flex-shrink:0">⚠️</span> Kenapa kadang gagal? <span>+</span></button>
        <div class="faq-a">Biasanya karena link salah, video sudah dihapus/private, atau dibatasi umur/negara. Pastikan link-nya benar dan coba video publik lain. Kalau tetap gagal, coba lagi beberapa menit.</div>
      </div>
      <div class="faq-item">
        <button class="faq-q"><span style="width:32px;height:32px;border-radius:8px;background:rgba(37,99,235,.1);display:grid;place-items:center;font-size:14px;flex-shrink:0">☕</span> Traktir itu apa? Wajib? <span>+</span></button>
        <div class="faq-a">Tidak wajib. Traktir adalah donasi sukarela via <b>Midtrans</b> (VA BCA/BNI/BRI, QRIS, GoPay/OVO/DANA) untuk bantu biaya server. Sebagai terima kasih, namamu tampil di <a href="#donatur" style="color:#2563eb;text-decoration:underline">Hall of Fame</a>. Website tetap gratis walau tidak traktir.</div>
      </div>
    </div>
  </div>
</section>

<footer class="footer">
  <div class="container">
    <div class="footer-main">
      <div class="footer-brand">
        <a class="brand" href="#" style="margin-bottom:12px">
          <span class="brand-mark" style="width:32px;height:32px;border-radius:8px">
            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" style="width:16px;height:16px"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
          </span>
          <span style="font-size:17px">AudioAgent</span>
        </a>
        <p>Download audio YouTube & video TikTok tanpa watermark. Gratis selamanya, tanpa iklan mengganggu. Dibuat untuk memudahkan kamu menyimpan konten favorit.</p>
        <div style="display:flex;gap:8px;margin-top:14px">
          <button class="btn btn-primary" onclick="openTraktir(10000)" style="padding:8px 16px;font-size:13px">☕ Traktir Developer</button>
          <a class="btn btn-ghost" href="#tool" style="padding:8px 16px;font-size:13px">Mulai Download</a>
        </div>
      </div>
      <div class="footer-col">
        <h4>Navigasi</h4>
        <a href="#tool">Tool Download</a>
        <a href="#cara">Cara Pakai</a>
        <a href="#fitur">Fitur</a>
        <a href="#donatur">Hall of Fame</a>
      </div>
      <div class="footer-col">
        <h4>Bantuan</h4>
        <a href="#faq">FAQ</a>
        <a href="#" onclick="openTraktir(10000);return false">Traktir / Donasi</a>
        <a href="https://github.com/yt-dlp/yt-dlp" target="_blank" rel="noopener">Powered by yt-dlp</a>
        <a href="https://ffmpeg.org" target="_blank" rel="noopener">Powered by FFmpeg</a>
      </div>
      <div class="footer-col">
        <h4>Legal</h4>
        <a href="terms.php">Syarat Layanan</a>
        <a href="privacy.php">Kebijakan Privasi</a>
        <a href="dmca.php">DMCA</a>
        <a href="contact.php">Kontak</a>
      </div>
    </div>
    <div class="footer-bottom">
      <div><b>AudioAgent</b> © 2026 • Gratis & tanpa iklan •<span style="color:var(--muted2)">yt-dlp + FFmpeg + PHP 8.3</span></div>
      <div style="display:flex;gap:14px;align-items:center;flex-wrap:wrap">
        <span style="display:flex;align-items:center;gap:6px"><span style="width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block"></span> Server aktif</span>
        <span>•</span>
        <a href="#tool" style="color:var(--muted)">Tool</a>
        <span>•</span>
        <a href="#donatur" style="color:var(--muted)">Donatur</a>
      </div>
    </div>
    <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);color:var(--muted2);font-size:11px;line-height:1.6;text-align:center">
      Gunakan tool ini hanya untuk konten yang kamu punya haknya (upload sendiri, free-to-share, atau untuk keperluan pribadi). Jangan gunakan untuk menyebarkan konten berhak cipta tanpa izin.
    </div>
  </div>
</footer>

<!-- Traktir Modal -->
<div id="traktirModal" style="display:none;position:fixed;inset:0;z-index:99;align-items:center;justify-content:center;padding:16px">
  <div id="traktirBackdrop" style="position:absolute;inset:0;background:rgba(0,0,0,.6);backdrop-filter:blur(6px)"></div>
  <div style="position:relative;background:var(--card);border:1px solid var(--border);border-radius:20px;max-width:460px;width:100%;padding:22px;box-shadow:var(--shadow)">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
      <h3 style="font-size:18px">☕ Traktir Developer</h3>
      <button id="closeTraktir" style="width:32px;height:32px;border-radius:50%;border:1px solid var(--border);background:var(--card2);color:var(--muted);cursor:pointer">✕</button>
    </div>
    <p style="color:var(--muted);font-size:13px;margin-bottom:14px">Dukung server tetap nyala. Pilih nominal, bayar via VA/QRIS/e-wallet (Midtrans).</p>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:14px">
      <button class="btn btn-ghost traktir-preset" data-amount="5000">5k</button>
      <button class="btn btn-primary traktir-preset" data-amount="10000">10k</button>
      <button class="btn btn-ghost traktir-preset" data-amount="20000">20k</button>
      <button class="btn btn-ghost traktir-preset" data-amount="50000">50k</button>
    </div>
    <label style="font-size:13px;font-weight:600">Nominal custom (Rp)</label>
    <input id="traktirAmount" type="number" min="1000" max="10000000" value="10000" style="margin-top:6px;width:100%;background:var(--bg);border:1px solid var(--border);color:var(--text);padding:10px 14px;border-radius:999px">
    <label style="font-size:13px;font-weight:600;margin-top:12px;display:block">Nama (opsional)</label>
    <input id="traktirName" type="text" placeholder="Hamba Allah" maxlength="60" style="margin-top:6px;width:100%;background:var(--bg);border:1px solid var(--border);color:var(--text);padding:10px 14px;border-radius:999px">
    <label style="font-size:13px;font-weight:600;margin-top:12px;display:block">Pesan (opsional)</label>
    <input id="traktirMessage" type="text" placeholder="Semangat terus!" maxlength="200" style="margin-top:6px;width:100%;background:var(--bg);border:1px solid var(--border);color:var(--text);padding:10px 14px;border-radius:999px">
    <button class="btn btn-primary" id="btnTraktirPay" style="width:100%;margin-top:16px">Bayar via Midtrans →</button>
    <div id="traktirAlert" class="alert" style="margin-top:10px"></div>
    <div style="margin-top:10px;color:var(--muted2);font-size:11px;text-align:center">Aman via Midtrans Sandbox/Production • VA BCA/BNI/BRI • QRIS • GoPay/OVO/DANA</div>
  </div>
</div>

<div id="toast" style="position:fixed;bottom:18px;left:50%;transform:translateX(-50%) translateY(80px);background:#0f172a;color:white;padding:12px 18px;border-radius:999px;box-shadow:0 8px 24px rgba(0,0,0,.2);font-size:13px;font-weight:600;opacity:0;transition:.3s;z-index:99;pointer-events:none">Toast</div>
<script>
if('serviceWorker' in navigator){ navigator.serviceWorker.register('sw.js').catch(()=>{}); }
fetch('data/stats.json').then(r=>r.json()).then(s=>{ const el=document.getElementById('statTotal'); if(el && s.total) el.textContent = Number(s.total).toLocaleString('id-ID')+'+'; }).catch(()=>{});
</script>
<script src="assets/js/main.js"></script>
</body>
</html>
