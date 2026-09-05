// AudioAgent — YouTube MP3 + TikTok MP4 no-watermark
const $ = (s, el=document) => el.querySelector(s);
const $$ = (s, el=document) => [...el.querySelectorAll(s)];

const form = $('#dlForm');
const urlInput = $('#ytUrl');
const formatSel = $('#format');
const ttQualitySel = $('#ttQuality');
const btnFetch = $('#btnFetch');
const btnDownload = $('#btnDownload');
const preview = $('#preview');
const alertBox = $('#alert');
const prevThumb = $('#prevThumb');
const prevTitle = $('#prevTitle');
const prevMeta = $('#prevMeta');
const prevDur = $('#prevDur');
const qualityNote = $('#qualityNote');
const toolTitle = $('#toolTitle');
const toolBadge = $('#toolBadge');
const ytRow = $('#ytFormatRow');
const ttRow = $('#ttQualityRow');
const ytMode = $('#ytMode');
const ytVideoQuality = $('#ytVideoQuality');
const ytFormat = $('#format');

let platform = 'youtube';
let lastInfo = null;
let lastUrl = '';

function setAlert(msg, type='err'){
  alertBox.className = 'alert show ' + (type==='ok'?'ok':'err');
  alertBox.innerHTML = (type==='err' ? '⚠️ ' : '✅ ') + msg;
}
function clearAlert(){ alertBox.className='alert'; alertBox.textContent=''; }
function setLoading(isLoading){
  btnFetch.disabled = isLoading;
  btnFetch.innerHTML = isLoading ? '<span class="spinner"></span> '+t_('proc') : 'Convert';
}
function setDlLoading(isLoading){
  if(!btnDownload) return;
  btnDownload.disabled = isLoading;
  const label = platform==='tiktok' ? '⬇ Download MP4' : '⬇ Download MP3';
  btnDownload.innerHTML = isLoading ? '<span class="spinner"></span> '+t_('preparing') : label;
}
function detectPlatform(url){
  if (/(tiktok\.com|vm\.tiktok|vt\.tiktok)/i.test(url)) return 'tiktok';
  if (/(youtube\.com|youtu\.be)/i.test(url)) return 'youtube';
  return platform;
}
function switchPlatform(p){
  platform = p;
  $$('.pt-btn').forEach(b=>{
    const isActive = b.dataset.platform===p;
    b.classList.toggle('active', isActive);
    b.setAttribute('aria-selected', isActive?'true':'false');
  });
  // i18n placeholder
  const L = (typeof i18n!=='undefined' && i18n[curLang||'id']) ? i18n[curLang] : null;
  if(p==='tiktok'){
    ytRow.style.display='none';
    ttRow.style.display='flex';
    urlInput.placeholder = (L?L.placeholderTT:'https://www.tiktok.com/@user/video/7107337212743830830 atau vm.tiktok.com/...');
    if(toolTitle) toolTitle.textContent='TikTok Extractor';
    if(toolBadge) toolBadge.textContent='MP4 • No Watermark';
    btnDownload.textContent=(L?L.downloadMp4:'⬇ Download MP4');
  } else {
    ytRow.style.display='flex';
    ttRow.style.display='none';
    urlInput.placeholder = (L?L.placeholderYT:'https://www.youtube.com/watch?v=... atau youtu.be/...');
    if(toolTitle) toolTitle.textContent=(L?L.toolTitle:'Audio Extractor');
    if(toolBadge) toolBadge.textContent=(L?L.toolBadge:'MP3 • M4A • OPUS • WAV');
    btnDownload.textContent=(L?L.downloadMp3:'⬇ Download MP3');
    // sync youtube mode UI
    if(ytMode){
      const v = ytMode.value==='video';
      if(ytFormat) ytFormat.style.display = v ? 'none' : '';
      if(ytVideoQuality) ytVideoQuality.style.display = v ? '' : 'none';
    }
  }
  // keep qualityNote in sync
  if(qualityNote) qualityNote.textContent = p==='tiktok' ? t_('qNoteTikTok') : '';
}

// toggle handlers
$$('.pt-btn').forEach(btn=>{
  btn.addEventListener('click', ()=> switchPlatform(btn.dataset.platform));
});
ytMode?.addEventListener('change', ()=>{
  const v = ytMode.value==='video';
  if(ytFormat) ytFormat.style.display = v ? 'none' : '';
  if(ytVideoQuality) ytVideoQuality.style.display = v ? '' : 'none';
  if(btnDownload) btnDownload.textContent = v ? '⬇ Download MP4' : '⬇ Download MP3';
});
urlInput.addEventListener('input', ()=>{
  const v = urlInput.value.trim();
  if(!v) return;
  const det = detectPlatform(v);
  if(det!==platform) switchPlatform(det);
});

// Fetch info
async function fetchInfo(url){
  clearAlert();
  setLoading(true);
  preview.classList.remove('show');
  try{
    const res = await fetch('api/info.php', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({url})
    });
    const data = await res.json();
    if(!res.ok || !data.success) throw new Error(data.error || t_('errInfo'));
    lastInfo = data.data;
    lastUrl = url;
    // auto-switch platform to match info
    if(lastInfo.platform && lastInfo.platform!==platform) switchPlatform(lastInfo.platform);

    prevThumb.src = lastInfo.thumbnail || '';
    prevThumb.alt = lastInfo.title;
    prevTitle.textContent = lastInfo.title;
    const views = Number(lastInfo.view_count||0).toLocaleString('id-ID');
    prevMeta.textContent = `${lastInfo.uploader} • ${lastInfo.duration_string} • ${views} views`;
    prevDur.textContent = lastInfo.duration_string;

    // TikTok qualities
    if(lastInfo.platform==='tiktok'){
      // populate quality dropdown with available qualities
      const qs = lastInfo.qualities || [];
      const cur = ttQualitySel.value;
      ttQualitySel.innerHTML = '<option value="">'+t_('qAuto')+'</option>';
      const labels = {540:t_('q540'),720:t_('q720'),1080:t_('q1080')};
      [1080,720,540].forEach(q=>{
        if(qs.includes(q)){
          const o=document.createElement('option');
          o.value=String(q); o.textContent=labels[q]|| (q+'p');
          ttQualitySel.appendChild(o);
        }
      });
      if(lastInfo.is_slideshow){
        if(qualityNote) qualityNote.textContent = t_('qSlideshow');
      } else if(qs.length){
        if(qualityNote) qualityNote.textContent = t_('qTersedia')+qs.join('p, ')+'p'+t_('qAutoSuffix');
      } else {
        if(qualityNote) qualityNote.textContent = t_('qAutoNote');
      }
      // try restore previous selection
      if(cur && [...ttQualitySel.options].some(o=>o.value===cur)) ttQualitySel.value=cur;
      btnDownload.textContent = '⬇ Download MP4';
    } else {
      if(qualityNote) qualityNote.textContent='';
      btnDownload.textContent = '⬇ Download MP3';
      // populate youtube video qualities
      if(ytVideoQuality){
        const vq = lastInfo.video_qualities || [];
        const curV = ytVideoQuality.value;
        ytVideoQuality.innerHTML = '<option value="">Auto — Best</option>';
        const labels = {360:'360p',480:'480p',720:'720p',1080:'1080p',1440:'1440p',2160:'2160p (4K)'};
        [2160,1440,1080,720,480,360].forEach(q=>{
          if(vq.includes(q)){ const o=document.createElement('option'); o.value=String(q); o.textContent=labels[q]||(q+'p'); ytVideoQuality.appendChild(o); }
        });
        if(curV && [...ytVideoQuality.options].some(o=>o.value===curV)) ytVideoQuality.value=curV;
      }
    }

    preview.classList.add('show');
    preview.scrollIntoView({behavior:'smooth', block:'nearest'});
    setAlert((lastInfo.platform==='tiktok' ? t_('tiktokOk') : t_('infoOk')), 'ok');
  }catch(e){
    setAlert(e.message || t_('errGeneric'));
  }finally{
    setLoading(false);
  }
}

// Toast
function showToast(msg){
  const t=$('#toast'); if(!t) return;
  t.textContent=msg; t.style.opacity='1'; t.style.transform='translateX(-50%) translateY(0)';
  setTimeout(()=>{ t.style.opacity='0'; t.style.transform='translateX(-50%) translateY(80px)'; }, 2800);
}
// History
function getHistory(){ try{ return JSON.parse(localStorage.getItem('dl_history')||'[]'); }catch(e){ return []; } }
function addHistory(item){
  let h=getHistory();
  h.unshift(item);
  h=h.slice(0,8);
  try{ localStorage.setItem('dl_history', JSON.stringify(h)); }catch(e){}
  renderHistory();
}
function renderHistory(){
  const wrap=$('#historyWrap'), list=$('#historyList');
  if(!wrap||!list) return;
  const h=getHistory();
  if(!h.length){ wrap.style.display='none'; return; }
  wrap.style.display='block';
  list.innerHTML = h.map(x=>`<div style="display:flex;align-items:center;gap:10px;padding:8px 10px;background:var(--card);border:1px solid var(--border);border-radius:10px"><img src="${x.thumb||''}" style="width:48px;height:36px;object-fit:cover;border-radius:6px;background:var(--card2)" onerror="this.style.display='none'"><div style="flex:1;min-width:0"><div style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${x.title}</div><div style="font-size:11px;color:var(--muted)">${x.platform} • ${x.time}</div></div><a href="#" onclick="event.preventDefault(); navigator.clipboard.writeText('${(x.url||'').replace(/'/g,"\\'")}'); showToast(t_('copied'))" style="font-size:12px;color:#2563eb">Copy</a></div>`).join('');
}
$('#clearHistory')?.addEventListener('click',()=>{ localStorage.removeItem('dl_history'); renderHistory(); showToast(t_('cleared')); });
renderHistory();

// Paste otomatis
$('#pasteBtn')?.addEventListener('click', async (e)=>{
  e.preventDefault();
  try{
    const t = await navigator.clipboard.readText();
    if(t){ urlInput.value=t.trim(); urlInput.dispatchEvent(new Event('input')); showToast(t_('linkPasted')); urlInput.focus(); }
  }catch(err){ showToast(t_('pasteFail')); }
});
window.addEventListener('load', async ()=>{
  try{
    const t = await navigator.clipboard.readText();
    if(t && /(youtube\.com|youtu\.be|tiktok\.com|vm\.tiktok)/i.test(t) && !urlInput.value.trim()){
      urlInput.value=t.trim(); urlInput.dispatchEvent(new Event('input'));
    }
  }catch(e){}
});

// Playlist detection
function isPlaylist(url){ return /[?&]list=/.test(url) && /youtube\.com/i.test(url); }
const playlistNote = $('#playlistNote'), playlistInfo=$('#playlistInfo'), playlistDownload=$('#playlistDownload');
urlInput.addEventListener('input', ()=>{
  const v=urlInput.value.trim();
  if(isPlaylist(v)){
    if(playlistNote) playlistNote.style.display='block';
    if(playlistInfo) playlistInfo.textContent=t_('playlistDetecting');
  } else {
    if(playlistNote) playlistNote.style.display='none';
  }
});
playlistDownload?.addEventListener('click', async (e)=>{
  e.preventDefault();
  const url=urlInput.value.trim();
  if(!isPlaylist(url)) return;
  const fmt=formatSel?formatSel.value:'mp3';
  showProgress(10);
  setAlert(t_('plReady'),'ok');
  try{
    const res=await fetch(`api/playlist.php?url=${encodeURIComponent(url)}&format=${encodeURIComponent(fmt)}`);
    if(!res.ok){ const j=await res.json().catch(()=>({})); throw new Error(j.error||t_('plFail')); }
    const blob=await res.blob();
    const a=document.createElement('a'); a.href=URL.createObjectURL(blob); a.download='playlist.zip'; document.body.appendChild(a); a.click(); a.remove();
    showToast(t_('plZipReady'));
    showProgress(100); setTimeout(hideProgress,800);
    setAlert(t_('plDone'),'ok');
  }catch(err){ setAlert(err.message||t_('plFail')); hideProgress(); }
});

// Bahasa ID/EN — full site
const i18n={
  id:{
    title:'AudioAgent — Download Audio YouTube & Video TikTok Tanpa Watermark Gratis',
    navTool:'Tool', navCara:'Cara Pakai', navFitur:'Fitur', navDonatur:'Donatur', navFaq:'FAQ',
    heroEyebrow:'✏️ YOUTUBE → MP3 • TIKTOK → MP4 NO WATERMARK • FREE',
    heroTitle:'Download<br><span class="grad">YouTube & TikTok.</span><br>Tanpa watermark.',
    heroSub:'Satu tool untuk dua platform. YouTube → MP3 320kbps. TikTok → MP4 tanpa watermark hingga 1080p. <b>Gratis selamanya</b> — kalau terbantu, traktir developer via Midtrans.',
    heroCta:'Mulai Download 🚀', heroTraktir:'☕ Traktir Rp 10k',
    stat1:'media terdownload ✨', stat2:'rata-rata proses', stat3:'donatur traktir ❤️',
    toolTitle:'Media Extractor', toolBadge:'MP3 • MP4 no WM', ytBtn:'▶️ YouTube → MP3', ttBtn:'🎵 TikTok → MP4',
    formatLabel:'Format:', qualityLabel:'Kualitas:', powered:'yt-dlp + ffmpeg', noWatermark:'MP4 • no watermark',
    hint:'💡 Tip:', exampleYT:'contoh YouTube', exampleTT:'contoh TikTok', paste:'📋 Paste',
    advTitle:'⚙️ Opsi lanjutan — potong durasi & playlist', advStart:'Mulai 00:00', advEnd:'Selesai 01:30', advNote:'Kosongkan jika tidak perlu dipotong',
    playlistDetected:'Playlist terdeteksi — download semua jadi ZIP (max 10)', playlistBtn:'Download ZIP (max 10)',
    convert:'Convert', changeLink:'Ganti Link', downloadMp3:'⬇ Download MP3', downloadMp4:'⬇ Download MP4',
    freeNote:'Gratis & tanpa iklan • 320 kbps • Tanpa watermark •', traktirLink:'☕ Traktir',
    historyTitle:'🕘 Riwayat download', historyClear:'Hapus', historyCopied:'Link disalin', historyCleared:'Riwayat dihapus',
    caraKicker:'# Cara Pakai', caraTitle:'3 langkah jadi file', caraSub:'Sama simpelnya untuk YouTube & TikTok — tanpa iklan ganggu, tanpa redirect aneh.',
    cara1Title:'Tempel Link', cara1Desc:'Copy link YouTube (youtube.com/shorts/music) atau TikTok (vm.tiktok.com / tiktok.com/@...), pilih platform, paste di kolom.',
    cara2Title:'Preview & Pilih Kualitas', cara2Desc:'Sistem ambil judul, thumbnail, & durasi via yt-dlp. Untuk TikTok pilih 540p/720p/1080p atau Auto Best tanpa watermark.',
    cara3Title:'Download Gratis', cara3Desc:'YouTube → MP3/M4A hingga 320kbps. TikTok → MP4 tanpa watermark. Semua gratis — traktir opsional via Midtrans.',
    fiturKicker:'Fitur Utama', fiturTitle:'Kenapa pilih AudioAgent?', fiturSub:'Cepat, gratis, dan transparan — satu tool, dua platform beres.',
    fitur1Title:'Super Cepat', fitur1Desc:'Ekstrak langsung di server Laragon + ffmpeg. Rata-rata 3–8 detik.',
    fitur2Title:'TikTok Tanpa Watermark', fitur2Desc:'Ambil stream play_addr asli (h264/h265) + --xff US.',
    fitur3Title:'Kualitas Tinggi', fitur3Desc:'MP3 320kbps / M4A/WAV. TikTok 540p/720p/1080p.',
    fitur4Title:'Aman & Privat', fitur4Desc:'File diproses lokal, auto-hapus 1 jam.',
    fitur5Title:'Support Semua Link', fitur5Desc:'YT: watch/shorts/youtu.be/music. TT: tiktok.com/vm.tiktok/vt.',
    fitur6Title:'Traktir via Midtrans', fitur6Desc:'VA BCA/BNI/BRI, QRIS, GoPay/OVO/DANA — donasi masuk Hall of Fame.',
    dukungKicker:'Dukung Developer', dukungTitle:'Gratis selamanya. Traktir kalau suka.', dukungSub:'Semua fitur terbuka tanpa login. Traktir bersifat sukarela — via Midtrans (VA/QRIS/e-wallet) & tampil di Hall of Fame.',
    freeTitle:'Gratis', freePrice:'Rp 0', freePer:'/ selamanya', free1:'Unlimited YouTube MP3 + TikTok MP4', free2:'TikTok tanpa watermark hingga 1080p', free3:'Pilih kualitas manual', free4:'Tanpa login & watermark', freeCta:'Mulai Gratis 🚀',
    traktirTitle:'Traktir ☕', traktirPrice:'Rp 10k', traktirPer:'/ sekali', traktir1:'Dukung server tetap nyala', traktir2:'Nama di Hall of Fame', traktir3:'VA / QRIS / GoPay / OVO / DANA', traktir4:'Midtrans aman & instan', traktirCta:'☕ Traktir Rp 10k',
    selfTitle:'Self-Host', selfPrice:'Rp 0', selfPer:'/ open source', self1:'Clone dari Laragon www', self2:'yt-dlp + ffmpeg included', self3:'Data donasi JSON (data/donations.json)', self4:'Ganti MIDTRANS keys di config.php',
    hofKicker:'Hall of Fame ❤️', hofTitle:'Terima kasih Orang Baik!', hofEmpty:'Jadilah yang pertama traktir — namamu akan tampil di sini', hofBtn:'☕ Traktir Sekarang',
    faqKicker:'Tanya Jawab', faqTitle:'Pertanyaan yang sering ditanyakan', faqSub:'Masih bingung? Hubungi kami via Traktir — kami bantu jawab.',
    faq1Q:'Apakah gratis? Ada batasan?', faq1A:'<b>Gratis 100%</b> untuk semua. Tidak perlu daftar, tidak ada batas harian, dan tidak ada iklan pop-up. Pilih kualitas sesuka hati (MP3 64–320kbps, TikTok 540p–1080p) — semua terbuka.',
    faq2Q:'Gimana cara pakainya?', faq2A:'<b>3 detik jadi:</b> ① Copy link YouTube/TikTok ② Paste di kotak atas (pilih YouTube/TikTok & kualitas) ③ Klik <b>Convert</b> → preview muncul → <b>Download</b>. File langsung masuk folder Download HP/laptop.',
    faq3Q:'Video apa saja yang bisa di-download?', faq3A:'YouTube: link <code>youtube.com/watch</code>, <code>youtu.be</code>, Shorts, Music. TikTok: <code>tiktok.com/@user/video/...</code>, <code>vm.tiktok.com</code>, <code>vt.tiktok.com</code>. Maksimal ± 90 menit. Video live, private, atau member-only tidak bisa.',
    faq4Q:'Kualitas mana yang paling bagus?', faq4A:'<b>Musik:</b> pilih <b>MP3 320 kbps</b> atau TikTok <b>1080p</b> (paling jernih). <b>Podcast/ceramah:</b> 128 kbps / 720p sudah cukup & hemat kuota. Semakin tinggi angka, file semakin besar.',
    faq5Q:'TikTok beneran tanpa watermark?', faq5A:'Ya. Kami ambil video <b>asli tanpa logo TikTok yang memantul</b>, bukan di-crop. Jadi hasilnya bersih seperti upload awal. Kalau videonya memang hanya tersedia versi watermark di TikTok, akan kami infokan.',
    faq6Q:'Apakah aman & privasi terjaga?', faq6A:'Aman. File diproses di server, <b>langsung terhapus otomatis</b> setelah kamu download (sisa file lama dibersihkan tiap 1 jam). Kami tidak menyimpan video kamu dan tidak minta data pribadi.',
    faq7Q:'Berapa lama prosesnya?', faq7A:'Rata-rata <b>3–8 detik</b> untuk lagu 3–4 menit. Video panjang atau kualitas 1080p butuh sedikit lebih lama karena filenya lebih besar.',
    faq8Q:'Bisa dipakai di HP?', faq8A:'Bisa. Buka <code>localhost/AudioAgent</code> di HP yang satu Wi-Fi, atau akses saat sudah di-hosting. Tampilan sudah responsif — tidak perlu install aplikasi.',
    faq9Q:'Kenapa kadang gagal?', faq9A:'Biasanya karena link salah, video sudah dihapus/private, atau dibatasi umur/negara. Pastikan link-nya benar dan coba video publik lain. Kalau tetap gagal, coba lagi beberapa menit.',
    faq10Q:'Traktir itu apa? Wajib?', faq10A:'Tidak wajib. Traktir adalah donasi sukarela via <b>Midtrans</b> (VA BCA/BNI/BRI, QRIS, GoPay/OVO/DANA) untuk bantu biaya server. Sebagai terima kasih, namamu tampil di <a href="#donatur" style="color:#2563eb;text-decoration:underline">Hall of Fame</a>. Website tetap gratis walau tidak traktir.',
    footerBrandDesc:'Download audio YouTube & video TikTok tanpa watermark. Gratis selamanya, tanpa iklan mengganggu. Dibuat untuk memudahkan kamu menyimpan konten favorit.',
    footerNav:'Navigasi', footerHelp:'Bantuan', footerLegal:'Legal',
    footerTool:'Tool Download', footerCara:'Cara Pakai', footerFitur:'Fitur', footerHof:'Hall of Fame',
    footerFaq:'FAQ', footerTraktir:'Traktir / Donasi', footerYt:'Powered by yt-dlp', footerFfmpeg:'Powered by FFmpeg',
    footerTerms:'Syarat Layanan', footerPrivacy:'Kebijakan Privasi', footerDmca:'DMCA', footerContact:'Kontak',
    footerBottom:'Gratis & tanpa iklan • Clean blue-white theme', serverActive:'Server aktif', footerDisclaimer:'Gunakan tool ini hanya untuk konten yang kamu punya haknya (upload sendiri, free-to-share, atau untuk keperluan pribadi). Jangan gunakan untuk menyebarkan konten berhak cipta tanpa izin.',
    navCta:'Mulai Download 🚀', navTraktir:'☕ Traktir',
    postDlTitle:'🎉 Beres! Suka tool ini?', postDlSub:'Traktir developer biar server tetap nyala ☕', postDlBtn:'☕ Traktir Rp 10k',
    traktirModalTitle:'☕ Traktir Developer', traktirDesc:'Dukung server tetap nyala. Pilih nominal, bayar via VA/QRIS/e-wallet (Midtrans).', traktirAmountLabel:'Nominal custom (Rp)', traktirNameLabel:'Nama (opsional)', traktirMsgLabel:'Pesan (opsional)', traktirNamePh:'Hamba Allah', traktirMsgPh:'Semangat terus!', traktirPay:'Bayar via Midtrans →', traktirSecure:'Aman via Midtrans Sandbox/Production • VA BCA/BNI/BRI • QRIS • GoPay/OVO/DANA',
    placeholderYT:'https://www.youtube.com/watch?v=... atau youtu.be/...', placeholderTT:'https://www.tiktok.com/@user/video/... atau vm.tiktok.com/...',
    pasteUrl:'Tempel link dulu', analyzeFirst:'Klik Convert dulu', invalidLink:'Link harus dari youtube.com / youtu.be / tiktok.com', infoOk:'Video ditemukan — siap download!', tiktokOk:'Video TikTok ditemukan — tanpa watermark, siap download!', errInfo:'Gagal mengambil info', errGeneric:'Terjadi kesalahan', proc:'Memproses...', preparing:'Menyiapkan...', dlStart:'Download dimulai: ', fromCache:'Dari cache — instan!', dlReady:'Download dimulai', dlFailed:'Gagal download', copied:'Link disalin', cleared:'Riwayat dihapus', pasteFail:'Gagal baca clipboard', linkPasted:'Link ditempel', plReady:'Menyiapkan playlist ZIP...', plDone:'Playlist ZIP terdownload', plFail:'Gagal playlist', plZipReady:'Playlist ZIP siap', fbTry:'Kualitas ', fbTry2:'p gagal, coba ', fbTry3:'p...', fbOk:'Download fallback berhasil', fbDone:'Download fallback ', qAuto:'Auto — Best (tanpa watermark)', q540:'540p — hemat kuota', q720:'720p — HD', q1080:'1080p — Full HD', qSlideshow:'⚠️ Slideshow — hanya audio yang tersedia, akan download sebagai audio.', qTersedia:'Tersedia: ', qAutoSuffix:' • Auto pilih best non-watermark', qAutoNote:'Auto: best non-watermark', qNoteTikTok:'Tanpa watermark: filter b[format_note!*=watermark] + --xff US',
    toastLangId:'Bahasa: Indonesia', toastLangEn:'Language: English'
  },
  en:{
    title:'AudioAgent — Download YouTube Audio & TikTok Video Without Watermark Free',
    navTool:'Tool', navCara:'How to', navFitur:'Features', navDonatur:'Donors', navFaq:'FAQ',
    heroEyebrow:'✏️ YOUTUBE → MP3 • TIKTOK → MP4 NO WATERMARK • FREE',
    heroTitle:'Download<br><span class="grad">YouTube & TikTok.</span><br>Without watermark.',
    heroSub:'One tool for both. YouTube → MP3 320kbps. TikTok → MP4 without watermark up to 1080p. <b>Forever free</b> — buy us a coffee via Midtrans if you like it.',
    heroCta:'Start Download 🚀', heroTraktir:'☕ Buy Coffee Rp 10k',
    stat1:'media downloaded ✨', stat2:'avg. process time', stat3:'supporters ❤️',
    toolTitle:'Media Extractor', toolBadge:'MP3 • MP4 no WM', ytBtn:'▶️ YouTube → MP3', ttBtn:'🎵 TikTok → MP4',
    formatLabel:'Format:', qualityLabel:'Quality:', powered:'yt-dlp + ffmpeg', noWatermark:'MP4 • no watermark',
    hint:'💡 Tip:', exampleYT:'YouTube example', exampleTT:'TikTok example', paste:'📋 Paste',
    advTitle:'⚙️ Advanced — trim & playlist', advStart:'Start 00:00', advEnd:'End 01:30', advNote:'Leave empty if no trim needed',
    playlistDetected:'Playlist detected — download all as ZIP (max 10)', playlistBtn:'Download ZIP (max 10)',
    convert:'Convert', changeLink:'Change Link', downloadMp3:'⬇ Download MP3', downloadMp4:'⬇ Download MP4',
    freeNote:'Free & ad-free • 320 kbps • No watermark •', traktirLink:'☕ Coffee',
    historyTitle:'🕘 Download history', historyClear:'Clear', historyCopied:'Link copied', historyCleared:'History cleared',
    caraKicker:'# How To', caraTitle:'3 steps to your file', caraSub:'Same simple flow for YouTube & TikTok — no ads, no redirects.',
    cara1Title:'Paste Link', cara1Desc:'Copy YouTube (youtube.com/shorts/music) or TikTok (vm.tiktok.com / tiktok.com/@...) link, pick platform, paste.',
    cara2Title:'Preview & Pick Quality', cara2Desc:'We fetch title, thumbnail & duration via yt-dlp. For TikTok pick 540p/720p/1080p or Auto Best without watermark.',
    cara3Title:'Download Free', cara3Desc:'YouTube → MP3/M4A up to 320kbps. TikTok → MP4 without watermark. All free — coffee via Midtrans is optional.',
    fiturKicker:'Features', fiturTitle:'Why AudioAgent?', fiturSub:'Fast, free, transparent — one tool for both.',
    fitur1Title:'Super Fast', fitur1Desc:'Direct extraction on server + ffmpeg. Avg. 3–8 seconds.',
    fitur2Title:'TikTok No Watermark', fitur2Desc:'Real play_addr stream (h264/h265) + --xff US.',
    fitur3Title:'High Quality', fitur3Desc:'MP3 320kbps / M4A/WAV. TikTok 540p/720p/1080p.',
    fitur4Title:'Safe & Private', fitur4Desc:'Processed locally, auto-deleted after 1 hour.',
    fitur5Title:'All Links Supported', fitur5Desc:'YT: watch/shorts/youtu.be/music. TT: tiktok.com/vm.tiktok/vt.',
    fitur6Title:'Coffee via Midtrans', fitur6Desc:'VA BCA/BNI/BRI, QRIS, GoPay/OVO/DANA — donors in Hall of Fame.',
    dukungKicker:'Support', dukungTitle:'Free forever. Buy us a coffee if you like it.', dukungSub:'All features open without login. Coffee is voluntary — via Midtrans (VA/QRIS/e-wallet) & Hall of Fame.',
    freeTitle:'Free', freePrice:'Rp 0', freePer:'/ forever', free1:'Unlimited YouTube MP3 + TikTok MP4', free2:'TikTok no watermark up to 1080p', free3:'Manual quality pick', free4:'No login & no watermark', freeCta:'Start Free 🚀',
    traktirTitle:'Coffee ☕', traktirPrice:'Rp 10k', traktirPer:'/ once', traktir1:'Keep server alive', traktir2:'Name in Hall of Fame', traktir3:'VA / QRIS / GoPay / OVO / DANA', traktir4:'Midtrans safe & instant', traktirCta:'☕ Buy Coffee Rp 10k',
    selfTitle:'Self-Host', selfPrice:'Rp 0', selfPer:'/ open source', self1:'Clone from Laragon www', self2:'yt-dlp + ffmpeg included', self3:'Donation JSON (data/donations.json)', self4:'Change MIDTRANS keys in config.php',
    hofKicker:'Hall of Fame ❤️', hofTitle:'Thank you, kind people!', hofEmpty:'Be the first to support — your name will be here', hofBtn:'☕ Buy Coffee Now',
    faqKicker:'Q&A', faqTitle:'Frequently asked questions', faqSub:'Still confused? Contact us via Coffee — we will help.',
    faq1Q:'Is it free? Any limits?', faq1A:'<b>100% free</b> for everyone. No signup, no daily limits, no pop-up ads. Pick any quality (MP3 64–320kbps, TikTok 540p–1080p) — all open.',
    faq2Q:'How to use it?', faq2A:'<b>3 seconds:</b> ① Copy YouTube/TikTok link ② Paste above (pick platform & quality) ③ Click <b>Convert</b> → preview → <b>Download</b>. File goes to your Download folder.',
    faq3Q:'Which videos can I download?', faq3A:'YouTube: <code>youtube.com/watch</code>, <code>youtu.be</code>, Shorts, Music. TikTok: <code>tiktok.com/@user/video/...</code>, <code>vm.tiktok.com</code>, <code>vt.tiktok.com</code>. Max ±90 minutes. Live/private/member-only not supported.',
    faq4Q:'Which quality is best?', faq4A:'<b>Music:</b> <b>MP3 320 kbps</b> or TikTok <b>1080p</b> (clearest). <b>Podcast:</b> 128 kbps / 720p is enough & saves data. Higher number = bigger file.',
    faq5Q:'Really no watermark on TikTok?', faq5A:'Yes. We fetch the <b>original video without the bouncing TikTok logo</b>, not cropped. Clean as uploaded. If only watermark version exists, we will tell you.',
    faq6Q:'Is it safe & private?', faq6A:'Safe. Files are processed on server, <b>auto-deleted</b> after download (old files cleaned every 1 hour). We don’t keep your videos or ask for personal data.',
    faq7Q:'How long does it take?', faq7A:'Avg. <b>3–8 seconds</b> for a 3–4 min song. Longer or 1080p takes a bit more.',
    faq8Q:'Works on phone?', faq8A:'Yes. Open <code>localhost/AudioAgent</code> on same Wi-Fi phone, or once hosted. Responsive — no app needed.',
    faq9Q:'Why does it sometimes fail?', faq9A:'Usually wrong link, deleted/private, or age/region restriction. Check the link and try a public video. Retry in a few minutes if still fails.',
    faq10Q:'What is Coffee? Required?', faq10A:'Not required. Coffee is a voluntary donation via <b>Midtrans</b> (VA BCA/BNI/BRI, QRIS, GoPay/OVO/DANA) to help server costs. Your name appears in <a href="#donatur" style="color:#2563eb;text-decoration:underline">Hall of Fame</a>. Site stays free.',
    footerBrandDesc:'Download YouTube audio & TikTok video without watermark. Forever free, no annoying ads. Made to help you save favorites.',
    footerNav:'Navigation', footerHelp:'Help', footerLegal:'Legal',
    footerTool:'Tool', footerCara:'How to', footerFitur:'Features', footerHof:'Hall of Fame',
    footerFaq:'FAQ', footerTraktir:'Coffee / Donate', footerYt:'Powered by yt-dlp', footerFfmpeg:'Powered by FFmpeg',
    footerTerms:'Terms', footerPrivacy:'Privacy', footerDmca:'DMCA', footerContact:'Contact',
    footerBottom:'Free & ad-free • Clean blue-white theme', serverActive:'Server online', footerDisclaimer:'Use this tool only for content you have rights to (your own uploads, free-to-share, or personal use). Do not redistribute copyrighted content without permission.',
    navCta:'Start Download 🚀', navTraktir:'☕ Coffee',
    postDlTitle:'🎉 Done! Like this tool?', postDlSub:'Buy us a coffee to keep the server running ☕', postDlBtn:'☕ Coffee Rp 10k',
    traktirModalTitle:'☕ Buy a Coffee', traktirDesc:'Keep the server running. Pick an amount, pay via VA/QRIS/e-wallet (Midtrans).', traktirAmountLabel:'Custom amount (Rp)', traktirNameLabel:'Name (optional)', traktirMsgLabel:'Message (optional)', traktirNamePh:'Anonymous', traktirMsgPh:'Keep it up!', traktirPay:'Pay via Midtrans →', traktirSecure:'Secure via Midtrans Sandbox/Production • VA BCA/BNI/BRI • QRIS • GoPay/OVO/DANA',
    placeholderYT:'https://www.youtube.com/watch?v=... or youtu.be/...', placeholderTT:'https://www.tiktok.com/@user/video/... or vm.tiktok.com/...',
    pasteUrl:'Paste a link first', analyzeFirst:'Click Convert first', invalidLink:'Link must be from youtube.com / youtu.be / tiktok.com', infoOk:'Video found — ready to download!', tiktokOk:'TikTok video found — no watermark, ready to download!', errInfo:'Failed to get info', errGeneric:'Something went wrong', proc:'Processing...', preparing:'Preparing...', dlStart:'Download started: ', fromCache:'From cache — instant!', dlReady:'Download started', dlFailed:'Download failed', copied:'Link copied', cleared:'History cleared', pasteFail:'Could not read clipboard', linkPasted:'Link pasted', plReady:'Preparing playlist ZIP...', plDone:'Playlist ZIP downloaded', plFail:'Playlist failed', plZipReady:'Playlist ZIP ready', fbTry:'Quality ', fbTry2:'p failed, trying ', fbTry3:'p...', fbOk:'Fallback download succeeded', fbDone:'Fallback download ', qAuto:'Auto — Best (no watermark)', q540:'540p — data saver', q720:'720p — HD', q1080:'1080p — Full HD', qSlideshow:'⚠️ Slideshow — only audio available, will download as audio.', qTersedia:'Available: ', qAutoSuffix:' • Auto picks best no-watermark', qAutoNote:'Auto: best no-watermark', qNoteTikTok:'No watermark: play_addr stream + --xff US',
    toastLangId:'Bahasa: Indonesia', toastLangEn:'Language: English'
  }
};

let curLang='id';
try{ const saved=localStorage.getItem('lang'); if(saved && i18n[saved]) curLang=saved; }catch(e){}
function applyLang(lang){
  curLang=lang;
  const t=i18n[lang]; if(!t) return;
  try{ localStorage.setItem('lang', lang); }catch(e){}
  document.documentElement.lang = lang;
  document.title = t.title;
  const metaDesc=document.querySelector('meta[name="description"]'); if(metaDesc) metaDesc.content=t.heroSub.replace(/<[^>]*>/g,'');
  const ogTitle=document.querySelector('meta[property="og:title"]'); if(ogTitle) ogTitle.content=t.title;
  const ogDesc=document.querySelector('meta[property="og:description"]'); if(ogDesc) ogDesc.content=t.heroSub.replace(/<[^>]*>/g,'');
  const langBtn=document.getElementById('langToggle'); if(langBtn) langBtn.textContent = lang==='id' ? 'ID' : 'EN';
  const navLinks = document.querySelectorAll('.nav-links a');
  if(navLinks[0]) navLinks[0].textContent=t.navTool;
  if(navLinks[1]) navLinks[1].textContent=t.navCara;
  if(navLinks[2]) navLinks[2].textContent=t.navFitur;
  if(navLinks[3]) navLinks[3].textContent=t.navDonatur;
  if(navLinks[4]) navLinks[4].textContent=t.navFaq;
  const h1=document.querySelector('.hero h1'); if(h1) h1.innerHTML=t.heroTitle;
  const sub=document.querySelector('.hero p.sub'); if(sub) sub.innerHTML=t.heroSub;
  const eyebrow=document.querySelector('.hero .eyebrow'); if(eyebrow) eyebrow.textContent=t.heroEyebrow;
  const heroCta=document.querySelector('.hero-actions .btn-primary'); if(heroCta) heroCta.textContent=t.heroCta;
  const heroTraktirBtn=document.getElementById('heroTraktir'); if(heroTraktirBtn) heroTraktirBtn.textContent=t.heroTraktir;
  const stats = document.querySelectorAll('.stats .stat span');
  if(stats[0]) stats[0].textContent=t.stat1;
  if(stats[1]) stats[1].textContent=t.stat2;
  if(stats[2]) stats[2].textContent=t.stat3;
  if(toolTitle) toolTitle.textContent=t.toolTitle;
  if(toolBadge) toolBadge.textContent=t.toolBadge;
  const ptBtns=document.querySelectorAll('.pt-btn'); if(ptBtns[0]) ptBtns[0].textContent=t.ytBtn; if(ptBtns[1]) ptBtns[1].textContent=t.ttBtn;
  const fmtLabel=document.querySelector('#ytFormatRow label'); if(fmtLabel) fmtLabel.textContent=t.formatLabel;
  const qualLabel=document.querySelector('#ttQualityRow label'); if(qualLabel) qualLabel.textContent=t.qualityLabel;
  const hint=document.getElementById('hintRow');
  if(hint) hint.innerHTML='💡 '+t.hint+' <a href="#" data-example="https://www.youtube.com/watch?v=dQw4w9WgXcQ" data-platform="youtube">'+t.exampleYT+'</a> · <a href="#" data-example="https://www.tiktok.com/@scout2015/video/6718335390845095173" data-platform="tiktok">'+t.exampleTT+'</a> • <a href="#" id="pasteBtn">📋 '+t.paste+'</a>';
  hint?.querySelectorAll('[data-example]')?.forEach(el=>el.addEventListener('click', e=>{ e.preventDefault(); const ex=el.getAttribute('data-example'); const p=el.getAttribute('data-platform'); if(p) switchPlatform(p); if(urlInput&&ex){ urlInput.value=ex; urlInput.focus(); }}));
  const pasteBtn=document.getElementById('pasteBtn');
  if(pasteBtn) pasteBtn.addEventListener('click', async e=>{ e.preventDefault(); try{ const txt=await navigator.clipboard.readText(); if(txt){ urlInput.value=txt.trim(); urlInput.dispatchEvent(new Event('input')); showToast(t.historyCopied); urlInput.focus(); }}catch(err){ showToast('Failed'); }});
  const advSummary=document.querySelector('details summary'); if(advSummary) advSummary.textContent=t.advTitle;
  const trimS=document.getElementById('trimStart'); if(trimS) trimS.placeholder=t.advStart;
  const trimE=document.getElementById('trimEnd'); if(trimE) trimE.placeholder=t.advEnd;
  const advNote = document.querySelector('details div span:last-child');
  if(advNote) advNote.textContent=t.advNote;
  if(playlistInfo) playlistInfo.textContent=t.playlistDetected;
  if(playlistDownload) playlistDownload.textContent=t.playlistBtn;
  if(btnFetch) btnFetch.textContent=t.convert;
  const changeLink=document.querySelector('#preview .prev-actions a.btn-ghost'); if(changeLink) changeLink.textContent=t.changeLink;
  if(platform==='tiktok') btnDownload.textContent=t.downloadMp4; else btnDownload.textContent=t.downloadMp3;
  const histTitle=document.querySelector('#historyWrap b'); if(histTitle) histTitle.textContent=t.historyTitle;
  const clearBtn=document.getElementById('clearHistory'); if(clearBtn) clearBtn.textContent=t.historyClear;
  renderHistory();
  const caraKicker=document.querySelector('#cara .kicker'); if(caraKicker) caraKicker.textContent=t.caraKicker;
  const caraH2=document.querySelector('#cara h2'); if(caraH2) caraH2.textContent=t.caraTitle;
  const caraSub=document.querySelector('#cara .section-head p'); if(caraSub) caraSub.textContent=t.caraSub;
  const caraSteps=document.querySelectorAll('#cara .step h3'); if(caraSteps[0]) caraSteps[0].textContent=t.cara1Title; if(caraSteps[1]) caraSteps[1].textContent=t.cara2Title; if(caraSteps[2]) caraSteps[2].textContent=t.cara3Title;
  const caraDescs=document.querySelectorAll('#cara .step p'); if(caraDescs[0]) caraDescs[0].textContent=t.cara1Desc; if(caraDescs[1]) caraDescs[1].textContent=t.cara2Desc; if(caraDescs[2]) caraDescs[2].textContent=t.cara3Desc;
  const fiturKicker=document.querySelector('#fitur .kicker'); if(fiturKicker) fiturKicker.textContent=t.fiturKicker;
  const fiturH2=document.querySelector('#fitur h2'); if(fiturH2) fiturH2.textContent=t.fiturTitle;
  const fiturSub=document.querySelector('#fitur .section-head p'); if(fiturSub) fiturSub.textContent=t.fiturSub;
  const fiturTitles=document.querySelectorAll('#fitur .card h3'); const fiturDescs=document.querySelectorAll('#fitur .card p');
  if(fiturTitles[0]) fiturTitles[0].textContent=t.fitur1Title; if(fiturDescs[0]) fiturDescs[0].textContent=t.fitur1Desc;
  if(fiturTitles[1]) fiturTitles[1].textContent=t.fitur2Title; if(fiturDescs[1]) fiturDescs[1].textContent=t.fitur2Desc;
  if(fiturTitles[2]) fiturTitles[2].textContent=t.fitur3Title; if(fiturDescs[2]) fiturDescs[2].textContent=t.fitur3Desc;
  if(fiturTitles[3]) fiturTitles[3].textContent=t.fitur4Title; if(fiturDescs[3]) fiturDescs[3].textContent=t.fitur4Desc;
  if(fiturTitles[4]) fiturTitles[4].textContent=t.fitur5Title; if(fiturDescs[4]) fiturDescs[4].textContent=t.fitur5Desc;
  if(fiturTitles[5]) fiturTitles[5].textContent=t.fitur6Title; if(fiturDescs[5]) fiturDescs[5].textContent=t.fitur6Desc;
  const dukungSec=document.querySelector('.pricing').closest('.section');
  if(dukungSec){
    const dk=dukungSec.querySelector('.kicker'); if(dk) dk.textContent=t.dukungKicker;
    const dh2=dukungSec.querySelector('h2'); if(dh2) dh2.textContent=t.dukungTitle;
    const dp=dukungSec.querySelector('.section-head p'); if(dp) dp.textContent=t.dukungSub;
  }
  const priceTitles=document.querySelectorAll('.pricing .price h3'); if(priceTitles[0]) priceTitles[0].textContent=t.freeTitle; if(priceTitles[1]) priceTitles[1].textContent=t.traktirTitle; if(priceTitles[2]) priceTitles[2].textContent=t.selfTitle;
  const priceRps=document.querySelectorAll('.pricing .price .rp'); if(priceRps[0]) priceRps[0].innerHTML=t.freePrice+' <small>'+t.freePer+'</small>'; if(priceRps[1]) priceRps[1].innerHTML=t.traktirPrice+' <small>'+t.traktirPer+'</small>'; if(priceRps[2]) priceRps[2].innerHTML=t.selfPrice+' <small>'+t.selfPer+'</small>';
  const priceLists=document.querySelectorAll('.pricing .price ul');
  if(priceLists[0]) priceLists[0].innerHTML='<li><i>✓</i> '+t.free1+'</li><li><i>✓</i> '+t.free2+'</li><li><i>✓</i> '+t.free3+'</li><li><i>✓</i> '+t.free4+'</li>';
  if(priceLists[1]) priceLists[1].innerHTML='<li><i>✓</i> '+t.traktir1+'</li><li><i>✓</i> '+t.traktir2+'</li><li><i>✓</i> '+t.traktir3+'</li><li><i>✓</i> '+t.traktir4+'</li>';
  if(priceLists[2]) priceLists[2].innerHTML='<li><i>✓</i> '+t.self1+'</li><li><i>✓</i> '+t.self2+'</li><li><i>✓</i> '+t.self3+'</li><li><i>✓</i> '+t.self4+'</li>';
  const freeCta=document.querySelector('.pricing .price a.btn-primary'); if(freeCta) freeCta.textContent=t.freeCta;
  const traktirCtas=document.querySelectorAll('.pricing .price button.btn-primary'); if(traktirCtas[0]) traktirCtas[0].textContent=t.traktirCta;
  const hofKicker=document.querySelector('#donatur .kicker'); if(hofKicker) hofKicker.textContent=t.hofKicker;
  const hofH2=document.querySelector('#donatur h2'); if(hofH2) hofH2.textContent=t.hofTitle;
  const faqKicker=document.querySelector('#faq .kicker'); if(faqKicker) faqKicker.textContent=t.faqKicker;
  const faqH2=document.querySelector('#faq h2'); if(faqH2) faqH2.textContent=t.faqTitle;
  const faqSub=document.querySelector('#faq .section-head p'); if(faqSub) faqSub.textContent=t.faqSub;
  const faqQs=document.querySelectorAll('#faq .faq-q'); const faqAs=document.querySelectorAll('#faq .faq-a');
  const keys=['faq1Q','faq1A','faq2Q','faq2A','faq3Q','faq3A','faq4Q','faq4A','faq5Q','faq5A','faq6Q','faq6A','faq7Q','faq7A','faq8Q','faq8A','faq9Q','faq9A','faq10Q','faq10A'];
  let qi=0;
  for(let i=0;i<faqQs.length && qi<keys.length;i++){
    const icon = faqQs[i].querySelector('span:first-child');
    const iconHTML = icon ? icon.outerHTML : '';
    const plus = faqQs[i].querySelector('span:last-child');
    const plusHTML = plus ? plus.outerHTML : '<span>+</span>';
    if(t[keys[qi]]) faqQs[i].innerHTML = iconHTML + ' ' + t[keys[qi]] + ' ' + plusHTML;
    qi++;
    if(faqAs[i] && t[keys[qi]]) faqAs[i].innerHTML = t[keys[qi]];
    qi++;
  }
  const footerBrandDesc=document.querySelector('.footer-brand p'); if(footerBrandDesc) footerBrandDesc.textContent=t.footerBrandDesc;
  const fbBottom=document.querySelector('.footer-bottom div:first-child');
  if(fbBottom){ const b=fbBottom.querySelector('b'); fbBottom.innerHTML=(b?b.outerHTML:'')+' © '+new Date().getFullYear()+' • '+t.footerBottom+' • <span style="color:var(--muted2)">yt-dlp + FFmpeg + PHP 8.3</span>'; }
  const serverSpan=document.querySelector('.footer-bottom span'); 
  if(serverSpan && serverSpan.textContent.includes('Server')) serverSpan.textContent=t.serverActive;
  const disclaim=document.querySelector('.footer > .container > div:last-child');
  if(disclaim && (disclaim.textContent.includes('Gunakan') || disclaim.textContent.includes('Use this'))) disclaim.textContent=t.footerDisclaimer;
  const footerCols=document.querySelectorAll('.footer-col h4');
  if(footerCols[0]) footerCols[0].textContent=t.footerNav;
  if(footerCols[1]) footerCols[1].textContent=t.footerHelp;
  if(footerCols[2]) footerCols[2].textContent=t.footerLegal;
  const footerLinks=document.querySelectorAll('.footer-col a');
  if(footerLinks[0]) footerLinks[0].textContent=t.footerTool;
  if(footerLinks[1]) footerLinks[1].textContent=t.footerCara;
  if(footerLinks[2]) footerLinks[2].textContent=t.footerFitur;
  if(footerLinks[3]) footerLinks[3].textContent=t.footerHof;
  if(footerLinks[4]) footerLinks[4].textContent=t.footerFaq;
  if(footerLinks[5]) footerLinks[5].textContent=t.footerTraktir;
  if(footerLinks[6]) footerLinks[6].textContent=t.footerYt;
  if(footerLinks[7]) footerLinks[7].textContent=t.footerFfmpeg;
  if(footerLinks[8]) footerLinks[8].textContent=t.footerTerms;
  if(footerLinks[9]) footerLinks[9].textContent=t.footerPrivacy;
  if(footerLinks[10]) footerLinks[10].textContent=t.footerDmca;
  if(footerLinks[11]) footerLinks[11].textContent=t.footerContact;
  const ph2 = platform==='tiktok' ? t.placeholderTT : t.placeholderYT;
  urlInput.placeholder=ph2;
  if(btnFetch) btnFetch.textContent=t.convert;
  if(platform==='tiktok') btnDownload.textContent=t.downloadMp4; else btnDownload.textContent=t.downloadMp3;
  const navCta=document.querySelector('.nav-cta a.btn-primary'); if(navCta) navCta.textContent=t.navCta;
  const navTraktirBtn=document.getElementById('navTraktir'); if(navTraktirBtn) navTraktirBtn.textContent=t.navTraktir;
  const postBox=document.getElementById('postDownloadTraktir');
  if(postBox){
    postBox.innerHTML='<div style="font-size:13px;font-weight:700">'+t.postDlTitle+'</div><div style="font-size:12px;color:var(--muted);margin:4px 0 8px">'+t.postDlSub+'</div><button class="btn btn-primary" style="width:100%" onclick="openTraktir(10000)">'+t.postDlBtn+'</button>';
  }
  const traktirH3=document.querySelector('#traktirModal h3'); if(traktirH3) traktirH3.textContent=t.traktirModalTitle;
  const traktirDesc=document.querySelector('#traktirModal p'); if(traktirDesc) traktirDesc.textContent=t.traktirDesc;
  const labels=document.querySelectorAll('#traktirModal label');
  if(labels[0]) labels[0].textContent=t.traktirAmountLabel;
  if(labels[1]) labels[1].textContent=t.traktirNameLabel;
  if(labels[2]) labels[2].textContent=t.traktirMsgLabel;
  const namePh=document.getElementById('traktirName'); if(namePh) namePh.placeholder=t.traktirNamePh;
  const msgPh=document.getElementById('traktirMessage'); if(msgPh) msgPh.placeholder=t.traktirMsgPh;
  const payBtn=document.getElementById('btnTraktirPay'); if(payBtn) payBtn.textContent=t.traktirPay;
  const secureNote=document.querySelector('#traktirModal > div > div > div:last-child');
  if(secureNote && secureNote.textContent.includes('Aman') || secureNote && secureNote.textContent.includes('Secure')) secureNote.textContent=t.traktirSecure;
  showToast(lang==='id'?t.toastLangId:t.toastLangEn);
}
const langToggleBtn=document.getElementById('langToggle');
if(langToggleBtn) langToggleBtn.addEventListener('click',()=>{
  const next = curLang==='id' ? 'en' : 'id';
  applyLang(next);
});
applyLang(curLang);


function t_(k){ const o=i18n[curLang]||i18n.id; return o[k]!==undefined ? o[k] : (i18n.id[k]||k); }
// Progress helpers
function showProgress(p){ const bar=$('#progressBar'), fill=$('#progressFill'); if(!bar||!fill) return; bar.style.display='block'; fill.style.width=p+'%'; }
function hideProgress(){ const bar=$('#progressBar'); if(bar) bar.style.display='none'; }

// sync fallback (works without worker)
async function syncDownload(p){
  let qs = `api/download.php?url=${encodeURIComponent(p.url)}&platform=${p.platform}&mode=${p.mode}&format=${encodeURIComponent(p.format)}`;
  if(p.quality) qs += `&quality=${encodeURIComponent(p.quality)}`;
  if(p.trimS||p.trimE) qs += `&trim_start=${encodeURIComponent(p.trimS)}&trim_end=${encodeURIComponent(p.trimE)}`;
  showProgress(40);
  const res = await fetch(qs);
  showProgress(85);
  if(!res.ok){ const j=await res.json().catch(()=>({})); throw new Error(j.error||t_('dlFailed')); }
  const blob=await res.blob();
  const disp=res.headers.get('content-disposition')||'';
  let filename=(p.title?p.title.replace(/[\\/:*?"<>|]/g,'_').slice(0,70):'download')+'.'+p.ext;
  const m=disp.match(/filename="([^"]+)"/); if(m) filename=m[1];
  const hdr=res.headers.get('x-file-name'); if(hdr) filename=hdr;
  const isCached=res.headers.get('x-cache')==='HIT';
  const url=URL.createObjectURL(blob); const a=document.createElement('a'); a.href=url; a.download=filename; document.body.appendChild(a); a.click(); a.remove();
  setTimeout(()=>URL.revokeObjectURL(url),5000);
  setAlert((isCached?'[Cache] ':'')+t_('dlStart')+filename,'ok');
  showToast(isCached?t_('fromCache'):t_('dlReady'));
  return filename;
}

// Download via async job (real progress) + fallback sync
async function downloadMedia(){
  if(!lastUrl) {
    const v = urlInput.value.trim();
    if(v) return setAlert(t_('analyzeFirst'));
    return setAlert(t_('pasteUrl'));
  }
  clearAlert();
  setDlLoading(true);
  showProgress(3);
  // params
  let mode='audio', quality='', fmt='mp3', ext='mp3';
  if(lastInfo.platform==='tiktok'){
    mode='video'; quality = ttQualitySel ? ttQualitySel.value : ''; fmt='mp4'; ext='mp4';
  } else if(ytMode && ytMode.value==='video'){
    mode='video'; quality = ytVideoQuality ? ytVideoQuality.value : ''; fmt='mp4'; ext='mp4';
  } else {
    fmt = formatSel ? formatSel.value : 'mp3';
    quality = (fmt==='mp3') ? '0' : '0';
    ext = fmt;
  }
  const trimS = ($('#trimStart')?.value||'').trim();
  const trimE = ($('#trimEnd')?.value||'').trim();
  const params = {url:lastUrl, platform:lastInfo.platform, mode, quality, format:fmt, trimS, trimE, title:lastInfo.title, ext};
  try{
    let downloaded=null;
    // try async job
    try{
      const createRes = await fetch('api/job.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({url:lastUrl,platform:lastInfo.platform,mode,quality,format:fmt,trim_start:trimS,trim_end:trimE})});
      const createData = await createRes.json();
      if(!createRes.ok || !createData.success) throw new Error(createData.error || t_('dlFailed'));
      const jobId = createData.job_id;
      let doneData=null, errMsg='';
      for(let i=0;i<6;i++){ // ~7s; if worker not running → quick fallback to sync
        await new Promise(r=>setTimeout(r,1200));
        let st;
        try{ st = await (await fetch('api/job.php?action=status&id='+jobId)).json(); }catch(e){ continue; }
        if(!st.success){ errMsg=st.error||t_('dlFailed'); break; }
        showProgress(Math.min(95, Math.max(3, st.data.progress||0)));
        if(st.data.status==='done'){ doneData=st.data; break; }
        if(st.data.status==='error'){ errMsg=st.data.error||t_('dlFailed'); break; }
      }
      if(doneData){
        const a=document.createElement('a');
        a.href=doneData.download_url;
        a.download = doneData.filename || ('download.'+ext);
        document.body.appendChild(a); a.click(); a.remove();
        setAlert(t_('dlStart')+(doneData.filename||''),'ok');
        showToast(t_('dlReady'));
        downloaded = doneData.filename||('download.'+ext);
      } else {
        throw new Error(errMsg || 'async-unavailable');
      }
    }catch(e){
      // job flow unavailable (no worker) → fallback to sync path
      downloaded = await syncDownload(params);
    }
    addHistory({title:lastInfo.title, thumb:lastInfo.thumbnail, platform:lastInfo.platform+(mode==='video'?' video':(quality?' '+quality:'')), url:lastUrl, time:new Date().toLocaleDateString('id-ID')});
    showProgress(100); setTimeout(hideProgress,800);
    const post=$('#postDownloadTraktir'); if(post) post.style.display='block';
  }catch(e){
    setAlert(e.message || t_('dlFailed'),'err');
    showToast(t_('dlFailed')+': '+(e.message||'error'));
    hideProgress();
  }finally{
    setDlLoading(false);
  }
}

// Events
if(form){
  form.addEventListener('submit', (e)=>{
    e.preventDefault();
    const url = urlInput.value.trim();
    if(!url) return setAlert(t_('pasteUrl'));
    // auto-detect but respect user toggle? we auto-switch above
    const det = detectPlatform(url);
    if(!/(tiktok|youtube|youtu\.be)/i.test(url)) return setAlert(t_('invalidLink'));
    // if user pasted tiktok but toggle still youtube, switch
    if(det!==platform) switchPlatform(det);
    fetchInfo(url);
  });
}
if(btnDownload) btnDownload.addEventListener('click', downloadMedia);

// FAQ accordion
$$('.faq-q').forEach(q=>{
  q.addEventListener('click', ()=>{
    const item = q.closest('.faq-item');
    const wasOpen = item.classList.contains('open');
    $$('.faq-item').forEach(i=>i.classList.remove('open'));
    if(!wasOpen) item.classList.add('open');
  });
});

// Mobile nav
const ham = $('#ham');
const navLinks = $('#navLinks');
if(ham && navLinks){
  ham.addEventListener('click', ()=>{
    const isOpen = navLinks.style.display==='flex';
    navLinks.style.display = isOpen ? 'none' : 'flex';
    if(!isOpen){
      navLinks.style.position='absolute';
      navLinks.style.top='64px';
      navLinks.style.left='0';
      navLinks.style.right='0';
      navLinks.style.background='var(--bg)';
      navLinks.style.flexDirection='column';
      navLinks.style.padding='16px 24px';
      navLinks.style.borderBottom='1px solid var(--border)';
      navLinks.style.boxShadow='var(--shadow-lg)';
    }
  });
}

// Demo: fill example URL on click
$$('[data-example]').forEach(el=>{
  el.addEventListener('click', (e)=>{
    e.preventDefault();
    const ex = el.getAttribute('data-example');
    const p = el.getAttribute('data-platform');
    if(p) switchPlatform(p);
    if(urlInput && ex){ urlInput.value = ex; urlInput.focus(); }
  });
});

// --- Traktir (Midtrans) ---
const traktirModal = $('#traktirModal');
const traktirAmount = $('#traktirAmount');
const traktirName = $('#traktirName');
const traktirMessage = $('#traktirMessage');
const traktirAlert = $('#traktirAlert');
const btnTraktirPay = $('#btnTraktirPay');
function openTraktir(amount){
  if(amount) traktirAmount.value = amount;
  // update preset active
  $$('.traktir-preset').forEach(b=>b.classList.toggle('btn-primary', parseInt(b.dataset.amount)===parseInt(traktirAmount.value)));
  $$('.traktir-preset').forEach(b=>b.classList.toggle('btn-ghost', parseInt(b.dataset.amount)!==parseInt(traktirAmount.value)));
  traktirModal.style.display='flex';
  traktirAlert.className='alert'; traktirAlert.textContent='';
}
function closeTraktir(){ traktirModal.style.display='none'; }
window.openTraktir = openTraktir;
$('#closeTraktir')?.addEventListener('click', closeTraktir);
$('#traktirBackdrop')?.addEventListener('click', closeTraktir);
$$('.traktir-preset').forEach(b=>{
  b.addEventListener('click', ()=>{
    traktirAmount.value = b.dataset.amount;
    $$('.traktir-preset').forEach(x=>{ x.classList.toggle('btn-primary', x===b); x.classList.toggle('btn-ghost', x!==b); });
  });
});
$('#navTraktir')?.addEventListener('click', ()=>openTraktir(10000));
$('#heroTraktir')?.addEventListener('click', ()=>openTraktir(10000));
function setTraktirAlert(msg,type='err'){
  traktirAlert.className='alert show '+(type==='ok'?'ok':'err');
  traktirAlert.textContent=(type==='err'?'⚠️ ':'✅ ')+msg;
}
function pollTraktir(orderId){
  let tries=0;
  const iv=setInterval(async ()=>{
    tries++;
    try{
      const r=await fetch('api/traktir-status.php?order_id='+encodeURIComponent(orderId));
      const d=await r.json();
      if(d.success){
        const st=d.data.status;
        if(st==='settlement' || st==='settled'){
          clearInterval(iv);
          setTraktirAlert('Terima kasih! Donasi berhasil ❤️','ok');
          setTimeout(()=>{ closeTraktir(); location.reload(); }, 1200);
        } else if(['expire','deny','cancel','failure'].includes(st)){
          clearInterval(iv);
          setTraktirAlert('Donasi '+st+' — coba lagi ya','err');
        } else {
          setTraktirAlert('Menunggu pembayaran...','ok');
        }
      }
    }catch(e){}
    if(tries>=30){ clearInterval(iv); setTraktirAlert('Status belum berubah — cek nanti via order '+orderId,'ok'); }
  },2000);
}
async function payTraktir(){
  const amount = parseInt(traktirAmount.value,10);
  const name = traktirName.value.trim();
  const message = traktirMessage.value.trim();
  if(!amount || amount < 1000) return setTraktirAlert('Nominal minimal Rp 1.000');
  if(amount > 10000000) return setTraktirAlert('Maksimal Rp 10.000.000');
  btnTraktirPay.disabled=true; btnTraktirPay.innerHTML='<span class="spinner"></span> Memproses...';
  try{
    const res = await fetch('api/traktir.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({amount,name,message})});
    const data = await res.json();
    if(!res.ok || !data.success) throw new Error(data.error||'Gagal');
    if(data.demo){
      setTraktirAlert('Demo mode: donasi tersimpan sebagai pending. Ganti MIDTRANS keys di config.php untuk Snap asli. Order '+data.order_id, 'ok');
      setTimeout(()=>{ closeTraktir(); location.reload(); }, 1500);
      return;
    }
    if(window.snap && data.snap_token){
      window.snap.pay(data.snap_token, {
        onSuccess: function(){ setTraktirAlert('Pembayaran diproses — konfirmasi...','ok'); pollTraktir(data.order_id); },
        onPending: function(){ setTraktirAlert('Menunggu pembayaran — selesaikan di jendela Midtrans','ok'); pollTraktir(data.order_id); },
        onError: function(){ setTraktirAlert('Pembayaran gagal'); },
        onClose: function(){ setTraktirAlert('Jendela pembayaran ditutup — memantau status...','ok'); pollTraktir(data.order_id); }
      });
    } else if(data.redirect_url){
      location.href = data.redirect_url;
      pollTraktir(data.order_id);
    } else {
      setTraktirAlert('Snap token dibuat: '+data.snap_token, 'ok');
    }
  }catch(e){ setTraktirAlert(e.message||'Gagal'); }
  finally{ btnTraktirPay.disabled=false; btnTraktirPay.innerHTML='Bayar via Midtrans →'; }
}
btnTraktirPay?.addEventListener('click', payTraktir);

// --- Theme toggle (light/dark) ---
const themeToggle = $('#themeToggle');
function applyTheme(t){
  document.documentElement.setAttribute('data-theme', t);
  try{ localStorage.setItem('theme', t); }catch(e){}
  if(themeToggle) themeToggle.textContent = t==='dark' ? '☀️' : '🌙';
  if(themeToggle) themeToggle.title = t==='dark' ? 'Ganti ke light mode' : 'Ganti ke dark mode';
}
function initTheme(){
  let t = null;
  try{ t = localStorage.getItem('theme'); }catch(e){}
  if(!t) t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  applyTheme(t);
}
themeToggle?.addEventListener('click', ()=>{
  const cur = document.documentElement.getAttribute('data-theme') || 'light';
  applyTheme(cur==='dark' ? 'light' : 'dark');
});
initTheme();

// init
switchPlatform('youtube');
