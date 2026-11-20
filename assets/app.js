/**
 * RELEASE.IT - MODERN & INTERACTIVE CLIENT ENGINE
 * Features:
 * - Web Audio API Ambient Sound Synthesizer (Rain, Ocean, Wind, Brown Noise)
 * - Tibetan Singing Bowl sound generator for breathing mindfulness
 * - Dynamic Chat with Rilis, typing bubble, copy-to-clipboard, audio cues
 * - Particle Dissolution for "Lepaskan Tulisan" with breeze sound effect
 * - Multi-mode Breathing Exercise (4-7-8, Box Breathing, Quick Calm) with Zen Mode
 * - Dinding Harapan live search, interactive warm hugs (floating hearts), and copy
 * - Toast notifications, scroll progress, responsive mobile navigation
 */

/* ==========================================================
   1. GLOBAL TOAST NOTIFICATION SYSTEM
   ========================================================== */
function tampilToast(pesan, jenis = 'info') {
  let wadah = document.getElementById('toastContainer');
  if (!wadah) {
    wadah = document.createElement('div');
    wadah.id = 'toastContainer';
    wadah.className = 'toast-container';
    document.body.appendChild(wadah);
  }

  const toast = document.createElement('div');
  toast.className = `toast-item toast-${jenis}`;
  
  let ikon = 'ℹ️';
  if (jenis === 'sukses') ikon = '✨';
  if (jenis === 'salah') ikon = '⚠️';
  if (jenis === 'salin') ikon = '📋';

  toast.innerHTML = `
    <span class="toast-ikon" aria-hidden="true">${ikon}</span>
    <span class="toast-pesan">${pesan}</span>
  `;

  wadah.appendChild(toast);
  requestAnimationFrame(() => toast.classList.add('muncul'));

  setTimeout(() => {
    toast.classList.remove('muncul');
    toast.classList.add('hilang');
    setTimeout(() => toast.remove(), 350);
  }, 3200);
}

/* ==========================================================
   2. WEB AUDIO API - SUASANA TEDUH & MEDITATION CHIMES
   ========================================================== */
const ZenAudio = (() => {
  let audioCtx = null;
  let masterGain = null;
  let activeSource = null;
  let currentSound = 'rain';
  let isPlaying = false;
  let volume = 0.40;

  function initContext() {
    if (!audioCtx) {
      const AudioContextClass = window.AudioContext || window.webkitAudioContext;
      if (AudioContextClass) {
        audioCtx = new AudioContextClass();
        masterGain = audioCtx.createGain();
        masterGain.gain.setValueAtTime(volume, audioCtx.currentTime);
        masterGain.connect(audioCtx.destination);
      }
    }
    if (audioCtx && audioCtx.state === 'suspended') {
      audioCtx.resume();
    }
  }

  // Buat buffer noise acak
  function buatNoiseBuffer(durasiDetik = 5) {
    initContext();
    const sampleRate = audioCtx.sampleRate;
    const bufferSize = sampleRate * durasiDetik;
    const buffer = audioCtx.createBuffer(1, bufferSize, sampleRate);
    const data = buffer.getChannelData(0);
    let lastOut = 0.0;
    for (let i = 0; i < bufferSize; i++) {
      const white = Math.random() * 2 - 1;
      // Filter sederhana kecenderungan pink/brown noise
      data[i] = (lastOut + (0.02 * white)) / 1.02;
      lastOut = data[i];
      data[i] *= 3.5; // Gain kompensasi
    }
    return buffer;
  }

  function stopCurrent() {
    if (activeSource) {
      try {
        if (activeSource.stop) activeSource.stop();
        if (activeSource.disconnect) activeSource.disconnect();
      } catch (e) {}
      activeSource = null;
    }
    isPlaying = false;
  }

  function playRain() {
    stopCurrent();
    initContext();
    const buffer = buatNoiseBuffer(6);
    const noise = audioCtx.createBufferSource();
    noise.buffer = buffer;
    noise.loop = true;

    const filter = audioCtx.createBiquadFilter();
    filter.type = 'lowpass';
    filter.frequency.setValueAtTime(950, audioCtx.currentTime);

    noise.connect(filter);
    filter.connect(masterGain);
    noise.start();
    activeSource = noise;
    isPlaying = true;
  }

  function playWaves() {
    stopCurrent();
    initContext();
    const buffer = buatNoiseBuffer(6);
    const noise = audioCtx.createBufferSource();
    noise.buffer = buffer;
    noise.loop = true;

    const filter = audioCtx.createBiquadFilter();
    filter.type = 'bandpass';
    filter.Q.value = 1.2;

    // LFO untuk ritme deburan ombak naik dan turun
    const lfo = audioCtx.createOscillator();
    const lfoGain = audioCtx.createGain();
    lfo.frequency.setValueAtTime(0.12, audioCtx.currentTime); // ~8 detik siklus ombak
    lfoGain.gain.setValueAtTime(320, audioCtx.currentTime);

    filter.frequency.setValueAtTime(450, audioCtx.currentTime);
    lfo.connect(lfoGain);
    lfoGain.connect(filter.frequency);

    noise.connect(filter);
    filter.connect(masterGain);
    lfo.start();
    noise.start();

    activeSource = {
      stop: () => {
        noise.stop();
        lfo.stop();
      },
      disconnect: () => {
        noise.disconnect();
        lfo.disconnect();
      }
    };
    isPlaying = true;
  }

  function playWind() {
    stopCurrent();
    initContext();
    const buffer = buatNoiseBuffer(6);
    const noise = audioCtx.createBufferSource();
    noise.buffer = buffer;
    noise.loop = true;

    const filter = audioCtx.createBiquadFilter();
    filter.type = 'lowpass';
    filter.frequency.setValueAtTime(500, audioCtx.currentTime);

    const lfo = audioCtx.createOscillator();
    const lfoGain = audioCtx.createGain();
    lfo.frequency.setValueAtTime(0.2, audioCtx.currentTime);
    lfoGain.gain.setValueAtTime(260, audioCtx.currentTime);

    lfo.connect(lfoGain);
    lfoGain.connect(filter.frequency);

    noise.connect(filter);
    filter.connect(masterGain);
    lfo.start();
    noise.start();

    activeSource = {
      stop: () => { noise.stop(); lfo.stop(); },
      disconnect: () => { noise.disconnect(); lfo.disconnect(); }
    };
    isPlaying = true;
  }

  function playBrown() {
    stopCurrent();
    initContext();
    const buffer = buatNoiseBuffer(6);
    const noise = audioCtx.createBufferSource();
    noise.buffer = buffer;
    noise.loop = true;

    const filter = audioCtx.createBiquadFilter();
    filter.type = 'lowpass';
    filter.frequency.setValueAtTime(320, audioCtx.currentTime);

    noise.connect(filter);
    filter.connect(masterGain);
    noise.start();
    activeSource = noise;
    isPlaying = true;
  }

  // Suara lonceng mangkuk meditasi (Tibetan Singing Bowl)
  function playSingingBowl() {
    try {
      initContext();
      if (!audioCtx) return;
      const now = audioCtx.currentTime;

      // Harmoni 432Hz dan nada atas
      const freqs = [432, 864, 1296];
      const gains = [0.25, 0.08, 0.03];

      freqs.forEach((f, idx) => {
        const osc = audioCtx.createOscillator();
        const g = audioCtx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(f, now);

        g.gain.setValueAtTime(gains[idx] * volume, now);
        g.gain.exponentialRampToValueAtTime(0.0001, now + 3.2);

        osc.connect(g);
        g.connect(audioCtx.destination);
        osc.start(now);
        osc.stop(now + 3.3);
      });
    } catch (e) {}
  }

  // Suara lembut hembusan angin untuk "Lepaskan Tulisan"
  function playBreeze() {
    try {
      initContext();
      if (!audioCtx) return;
      const now = audioCtx.currentTime;
      const buffer = buatNoiseBuffer(3);
      const src = audioCtx.createBufferSource();
      src.buffer = buffer;

      const filter = audioCtx.createBiquadFilter();
      filter.type = 'bandpass';
      filter.frequency.setValueAtTime(400, now);
      filter.frequency.exponentialRampToValueAtTime(1400, now + 1.2);
      filter.frequency.exponentialRampToValueAtTime(200, now + 2.8);

      const g = audioCtx.createGain();
      g.gain.setValueAtTime(0.01, now);
      g.gain.linearRampToValueAtTime(0.25 * volume, now + 0.6);
      g.gain.exponentialRampToValueAtTime(0.001, now + 2.8);

      src.connect(filter);
      filter.connect(g);
      g.connect(audioCtx.destination);
      src.start(now);
      src.stop(now + 2.9);
    } catch (e) {}
  }

  // Nada lembut pop ketika pesan masuk
  function playSoftChime() {
    try {
      initContext();
      if (!audioCtx) return;
      const now = audioCtx.currentTime;
      const osc = audioCtx.createOscillator();
      const g = audioCtx.createGain();

      osc.type = 'sine';
      osc.frequency.setValueAtTime(587.33, now); // D5
      osc.frequency.exponentialRampToValueAtTime(880, now + 0.18); // A5

      g.gain.setValueAtTime(0.12 * volume, now);
      g.gain.exponentialRampToValueAtTime(0.001, now + 0.45);

      osc.connect(g);
      g.connect(audioCtx.destination);
      osc.start(now);
      osc.stop(now + 0.46);
    } catch (e) {}
  }

  function setVolume(v) {
    volume = Math.max(0, Math.min(1, v));
    if (masterGain && audioCtx) {
      masterGain.gain.setValueAtTime(volume, audioCtx.currentTime);
    }
  }

  function start(type) {
    if (type) currentSound = type;
    if (currentSound === 'rain') playRain();
    else if (currentSound === 'waves') playWaves();
    else if (currentSound === 'wind') playWind();
    else if (currentSound === 'brown') playBrown();
  }

  function toggle() {
    if (isPlaying) {
      stopCurrent();
      return false;
    } else {
      start();
      return true;
    }
  }

  return {
    start,
    stop: stopCurrent,
    toggle,
    setVolume,
    playSingingBowl,
    playBreeze,
    playSoftChime,
    getIsPlaying: () => isPlaying,
    getCurrentSound: () => currentSound
  };
})();

/* ==========================================================
   3. HEADER, MOBILE MENU & SCROLL PROGRESS
   ========================================================== */
document.addEventListener('DOMContentLoaded', () => {
  // Indikator Progres Scroll
  const scrollBar = document.getElementById('scrollProgress');
  if (scrollBar) {
    window.addEventListener('scroll', () => {
      const winScroll = document.documentElement.scrollTop || document.body.scrollTop;
      const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
      const scrolled = height > 0 ? (winScroll / height) * 100 : 0;
      scrollBar.style.width = scrolled + '%';
    }, { passive: true });
  }

  // Toggle Menu Navigasi Mobile
  const btnMenuToggle = document.getElementById('btnMenuToggle');
  const navUtama = document.getElementById('navUtama');
  if (btnMenuToggle && navUtama) {
    btnMenuToggle.addEventListener('click', () => {
      const open = navUtama.classList.toggle('nav-terbuka');
      btnMenuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      btnMenuToggle.classList.toggle('aktif', open);
    });
  }

  // Kontrol Modal Suasana Teduh (Ambient Audio)
  const btnAmbience = document.getElementById('btnAmbience');
  const modalAmbience = document.getElementById('ambienceModal');
  const btnTutupAmbience = document.getElementById('btnTutupAmbience');
  const btnPlayAmbience = document.getElementById('btnPlayAmbience');
  const volumeSlider = document.getElementById('volumeAmbience');
  const volumeAngka = document.getElementById('volumeAngka');
  const playIcon = document.getElementById('playIcon');
  const playText = document.getElementById('playText');
  const ambienceDot = document.getElementById('ambienceDot');
  const soundChips = document.querySelectorAll('.ambience-chip');

  if (btnAmbience && modalAmbience) {
    btnAmbience.addEventListener('click', () => {
      modalAmbience.hidden = !modalAmbience.hidden;
      if (!modalAmbience.hidden) modalAmbience.focus();
    });

    if (btnTutupAmbience) {
      btnTutupAmbience.addEventListener('click', () => {
        modalAmbience.hidden = true;
      });
    }

    // Tutup saat klik di luar kotak modal
    modalAmbience.addEventListener('click', (e) => {
      if (e.target === modalAmbience) modalAmbience.hidden = true;
    });

    // Pilihan jenis suara
    soundChips.forEach(chip => {
      chip.addEventListener('click', () => {
        soundChips.forEach(c => c.classList.remove('aktif'));
        chip.classList.add('aktif');
        const jenis = chip.dataset.sound;
        if (ZenAudio.getIsPlaying()) {
          ZenAudio.start(jenis);
          tampilToast(`Suasana diubah ke: ${chip.querySelector('.chip-text').textContent}`, 'info');
        }
      });
    });

    // Slider Volume
    if (volumeSlider) {
      volumeSlider.addEventListener('input', (e) => {
        const val = parseInt(e.target.value, 10);
        if (volumeAngka) volumeAngka.textContent = val + '%';
        ZenAudio.setVolume(val / 100);
      });
    }

    // Tombol Putar / Jeda
    if (btnPlayAmbience) {
      btnPlayAmbience.addEventListener('click', () => {
        const aktifChip = document.querySelector('.ambience-chip.aktif');
        const jenis = aktifChip ? aktifChip.dataset.sound : 'rain';
        const jalan = ZenAudio.toggle();

        if (jalan) {
          if (playIcon) playIcon.textContent = '⏸';
          if (playText) playText.textContent = 'Hentikan Suasana';
          btnAmbience.classList.add('aktif-memutar');
          if (ambienceDot) ambienceDot.classList.add('memutar');
          tampilToast('Suara relaksasi mulai diputar 🍃', 'sukses');
        } else {
          if (playIcon) playIcon.textContent = '▶';
          if (playText) playText.textContent = 'Putar Suasana';
          btnAmbience.classList.remove('aktif-memutar');
          if (ambienceDot) ambienceDot.classList.remove('memutar');
          tampilToast('Suara relaksasi dihentikan', 'info');
        }
      });
    }
  }
});

/* ==========================================================
   4. CHAT DENGAN RILIS & PARTIKEL TULISAN MELAYANG
   ========================================================== */
const kataRisiko = [
  'bunuh diri','mengakhiri hidup','mati aja','ingin mati','pengen mati','log out dari bumi',
  'logout dari bumi','nggak mau hidup','gak mau hidup','tidak mau hidup','menyerah','lelah hidup','capek hidup','menyakiti diri'
];

const chat = document.getElementById('chat');
if (chat) {
  const csrf = document.querySelector('meta[name=csrf]') ? document.querySelector('meta[name=csrf]').content : '';
  const input = document.getElementById('curhat');
  const bantuan = document.getElementById('bantuan');
  const hitungKarakter = document.getElementById('hitungKarakter');
  const sapaanWaktu = document.getElementById('sapaanWaktu');
  const btnHapusChat = document.getElementById('btnHapusChat');
  const btnSuaraChat = document.getElementById('btnSuaraChat');
  const labelSuaraChat = document.getElementById('labelSuaraChat');

  let sibuk = false;
  let suaraChatAktif = localStorage.getItem('rilis_suara') !== 'mati';
  const riwayat = [];

  if (labelSuaraChat) {
    labelSuaraChat.textContent = suaraChatAktif ? 'Aktif' : 'Mati';
  }

  if (btnSuaraChat) {
    btnSuaraChat.addEventListener('click', () => {
      suaraChatAktif = !suaraChatAktif;
      localStorage.setItem('rilis_suara', suaraChatAktif ? 'aktif' : 'mati');
      if (labelSuaraChat) labelSuaraChat.textContent = suaraChatAktif ? 'Aktif' : 'Mati';
      tampilToast(`Suara interaksi obrolan: ${suaraChatAktif ? 'Aktif 🔔' : 'Mati 🔕'}`, 'info');
    });
  }

  // Sapaan waktu dinamis
  if (sapaanWaktu) {
    const jam = new Date().getHours();
    let salam = 'Halo';
    if (jam >= 4 && jam < 11) salam = 'Selamat pagi';
    else if (jam >= 11 && jam < 15) salam = 'Selamat siang';
    else if (jam >= 15 && jam < 18) salam = 'Selamat sore';
    else salam = 'Selamat malam';
    sapaanWaktu.textContent = `${salam}. Rilis siap mendengarkan tanpa menghakimi 🌙`;
  }

  // Hitung karakter & Auto-resize textarea
  if (input) {
    input.addEventListener('input', () => {
      const len = input.value.length;
      if (hitungKarakter) {
        hitungKarakter.textContent = `${len} / 1000 karakter`;
        if (len > 900) hitungKarakter.classList.add('mendekati-batas');
        else hitungKarakter.classList.remove('mendekati-batas');
      }
      // Auto expand tinggi textarea
      input.style.height = 'auto';
      input.style.height = Math.min(input.scrollHeight, 180) + 'px';
    });
  }

  function getFormatWaktu() {
    const now = new Date();
    return now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  }

  function tambah(teks, dari, lampiran, saran) {
    const d = document.createElement('div');
    d.className = 'pesan ' + dari;
    
    // Bubble teks
    const p = document.createElement('p');
    p.textContent = teks;
    d.appendChild(p);

    // Lampiran jika ada
    if (lampiran) {
      const x = document.createElement('div');
      x.className = 'lampiran';
      x.innerHTML = lampiran;
      d.appendChild(x);
    }

    // Tombol saran cepat
    if (Array.isArray(saran) && saran.length) {
      const grup = document.createElement('div');
      grup.className = 'saran-chat';
      const label = document.createElement('span');
      label.textContent = 'Langkah berikutnya:';
      grup.appendChild(label);
      saran.forEach(item => {
        const tombolSaran = item.tautan ? document.createElement('a') : document.createElement('button');
        if (item.tautan) {
          tombolSaran.href = item.tautan;
        } else {
          tombolSaran.type = 'button';
          tombolSaran.addEventListener('click', () => kirim(item.teks));
        }
        tombolSaran.className = 'chip';
        tombolSaran.textContent = item.teks;
        grup.appendChild(tombolSaran);
      });
      d.appendChild(grup);
    }

    // Footer pesan (Waktu & Tombol Salin)
    const footerPesan = document.createElement('div');
    footerPesan.className = 'pesan-meta';
    footerPesan.innerHTML = `<span class="pesan-waktu">${getFormatWaktu()}</span>`;

    if (dari.includes('bot') && !dari.includes('ketik')) {
      const btnSalin = document.createElement('button');
      btnSalin.type = 'button';
      btnSalin.className = 'btn-salin-pesan';
      btnSalin.title = 'Salin kata penenang ini';
      btnSalin.innerHTML = '📋 Salin';
      btnSalin.addEventListener('click', () => {
        navigator.clipboard.writeText(teks).then(() => {
          tampilToast('Tanggapan Rilis berhasil disalin ke papan klip ✨', 'salin');
        });
      });
      footerPesan.appendChild(btnSalin);
    }

    d.appendChild(footerPesan);
    chat.appendChild(d);
    chat.scrollTo({ top: chat.scrollHeight, behavior: 'smooth' });
    return d;
  }

  function tambahIndikatorKetik() {
    const d = document.createElement('div');
    d.className = 'pesan bot ketik';
    d.innerHTML = `
      <div class="bubble-ketik" aria-label="Rilis sedang merangkai kata...">
        <span class="titik-ketik"></span>
        <span class="titik-ketik"></span>
        <span class="titik-ketik"></span>
      </div>
    `;
    chat.appendChild(d);
    chat.scrollTo({ top: chat.scrollHeight, behavior: 'smooth' });
    return d;
  }

  function cekRisiko(t) {
    if (kataRisiko.some(k => t.toLowerCase().includes(k))) {
      bantuan.hidden = false;
      bantuan.scrollIntoView({ behavior: 'smooth' });
    }
  }

  async function kirim(teks) {
    if (sibuk) return;
    sibuk = true;
    tambah(teks, 'saya');
    cekRisiko(teks);

    if (suaraChatAktif) {
      ZenAudio.playSoftChime();
    }

    const ketik = tambahIndikatorKetik();
    try {
      const r = await fetch('chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF': csrf },
        body: JSON.stringify({ pesan: teks, riwayat: riwayat.slice(-10) })
      });
      const d = await r.json();
      await new Promise(s => setTimeout(s, 700));
      ketik.remove();
      tambah(d.balasan, 'bot', d.konten, d.saran);
      riwayat.push({ role: 'user', text: teks }, { role: 'assistant', text: d.balasan });
      if (d.krisis) {
        bantuan.hidden = false;
        bantuan.scrollIntoView({ behavior: 'smooth' });
      }
      if (suaraChatAktif) {
        ZenAudio.playSoftChime();
      }
    } catch (e) {
      ketik.remove();
      tambah('Maaf, ada kendala koneksi sebentar. Coba kirim ulang ya.', 'bot');
    }
    sibuk = false;
  }

  const formChat = document.getElementById('formChat');
  if (formChat) {
    formChat.addEventListener('submit', ev => {
      ev.preventDefault();
      const t = input.value.trim();
      if (!t) return;
      input.value = '';
      input.style.height = 'auto';
      if (hitungKarakter) hitungKarakter.textContent = '0 / 1000 karakter';
      kirim(t);
    });
  }

  input.addEventListener('keydown', ev => {
    if (ev.key === 'Enter' && !ev.shiftKey) {
      ev.preventDefault();
      formChat.requestSubmit();
    }
  });

  // Mood chips cepat
  document.querySelectorAll('#cepat .chip').forEach(b => {
    b.addEventListener('click', () => {
      kirim(b.dataset.teks);
      // Efek visual lembut pada hero
      const hero = document.querySelector('.hero.langit');
      if (hero) {
        hero.classList.add('aura-perubahan');
        setTimeout(() => hero.classList.remove('aura-perubahan'), 1000);
      }
    });
  });

  // Tombol Hapus / Mulai Ulang Chat
  if (btnHapusChat) {
    btnHapusChat.addEventListener('click', () => {
      if (confirm('Mulai ulang percakapan dengan Rilis dari awal?')) {
        chat.innerHTML = '';
        riwayat.length = 0;
        tambah('Hai, aku Rilis. Di sini kamu boleh cerita apa saja tanpa dinilai. Hari ini rasanya bagaimana?', 'bot');
        tampilToast('Sesi curhat berhasil disegarkan ✨', 'sukses');
      }
    });
  }

  // Interaktif Lepaskan Tulisan (Partikel debu bintang & hembusan angin)
  const btnLepas = document.getElementById('btnLepas');
  if (btnLepas) {
    btnLepas.addEventListener('click', () => {
      const teks = input.value.trim();
      if (!teks) {
        input.focus();
        tampilToast('Tuliskan beban atau pikiranmu di kotak curhat terlebih dahulu.', 'info');
        return;
      }
      cekRisiko(teks);

      // Mainkan suara hembusan angin pelepasan
      ZenAudio.playBreeze();

      const wadah = document.getElementById('melayang');
      const kotak = input.getBoundingClientRect();
      const kataList = teks.split(/\s+/).slice(0, 50);

      kataList.forEach((kata, i) => {
        const s = document.createElement('span');
        s.className = 'kata';
        s.textContent = kata;
        s.style.left = Math.random() * Math.max(kotak.width - 70, 15) + 'px';
        s.style.top = Math.random() * Math.max(kotak.height - 35, 10) + 'px';
        s.style.setProperty('--dx', (Math.random() * 200 - 100) + 'px');
        s.style.setProperty('--r', (Math.random() * 60 - 30) + 'deg');
        s.style.animationDelay = (i * 0.035) + 's';
        wadah.appendChild(s);
        setTimeout(() => s.remove(), 4500);
      });

      // Partikel kilau bintang tambahan
      for (let k = 0; k < 18; k++) {
        const bintang = document.createElement('span');
        bintang.className = 'partikel-bintang';
        bintang.textContent = ['✦', '✧', '⋆', '•'][Math.floor(Math.random() * 4)];
        bintang.style.left = Math.random() * kotak.width + 'px';
        bintang.style.top = Math.random() * kotak.height + 'px';
        bintang.style.setProperty('--dx', (Math.random() * 140 - 70) + 'px');
        bintang.style.animationDelay = (k * 0.05) + 's';
        wadah.appendChild(bintang);
        setTimeout(() => bintang.remove(), 3500);
      }

      input.value = '';
      input.style.height = 'auto';
      if (hitungKarakter) hitungKarakter.textContent = '0 / 1000 karakter';

      tampilToast('🍃 Beban pikiranmu telah dilepaskan dan tidak tersimpan di mana pun.', 'sukses');

      setTimeout(() => {
        tambah('Tulisanmu sudah kamu lepaskan. Menuliskan apa yang mengganjal adalah langkah berani untuk meringankan dada. Mau cerita lagi, atau butuh waktu istirahat?', 'bot');
      }, 1400);
    });
  }

  // Pesan sambutan awal Rilis
  tambah('Hai, aku Rilis. Di sini kamu boleh cerita apa saja tanpa dinilai. Hari ini rasanya bagaimana?', 'bot');
}

/* ==========================================================
   5. RUANG TENANG - MULTI-METODE PERNAPASAN & MODE ZEN
   ========================================================== */
const lingkaran = document.getElementById('lingkaran');
if (lingkaran) {
  const aba = document.getElementById('aba');
  const subAba = document.getElementById('subAba');
  const btn = document.getElementById('btnNapas');
  const hitungDetik = document.getElementById('hitungDetik');
  const infoPutaran = document.getElementById('infoPutaran');
  const angkaPutaran = document.getElementById('angkaPutaran');
  const faseNapasBar = document.getElementById('faseNapasBar');
  const deskripsiTeknik = document.getElementById('deskripsiTeknik');
  const btnSuaraNapas = document.getElementById('btnSuaraNapas');
  const labelSuaraNapas = document.getElementById('labelSuaraNapas');
  const btnModeZen = document.getElementById('btnModeZen');
  const areaNapas = document.getElementById('areaNapas');
  const auraNapas = document.getElementById('auraNapas');
  const metodeButtons = document.querySelectorAll('.btn-metode-napas');

  let jalan = false;
  let timerFase = null;
  let timerDetik = null;
  let hitunganPutaran = 0;
  let loncengAktif = localStorage.getItem('napas_lonceng') !== 'mati';
  let modeZenAktif = false;
  let metodeAktif = '478';

  const konfigurasiMetode = {
    '478': {
      nama: 'Metode 4-7-8',
      fase: [
        { nama: 'Tarik Napas', sub: 'Perlahan lewat hidung', detik: 4, skala: 1.15, warna: 'rgba(59, 130, 246, 0.4)' },
        { nama: 'Tahan Napas', sub: 'Pertahankan dengan tenang', detik: 7, skala: 1.15, warna: 'rgba(245, 158, 11, 0.4)' },
        { nama: 'Hembuskan', sub: 'Lepaskan perlahan lewat mulut', detik: 8, skala: 0.58, warna: 'rgba(16, 185, 129, 0.4)' }
      ],
      deskripsi: '<strong>Teknik 4-7-8</strong>: Tarik napas lembut lewat hidung (4 detik), tahan di dada dengan santai (7 detik), lalu hembuskan perlahan lewat mulut (8 detik). Sangat ampuh untuk meredakan gelisah dan membantu tidur lelap.'
    },
    'box': {
      nama: 'Box Breathing (4-4-4-4)',
      fase: [
        { nama: 'Tarik Napas', sub: 'Isi paru-paru penuh', detik: 4, skala: 1.15, warna: 'rgba(59, 130, 246, 0.4)' },
        { nama: 'Tahan', sub: 'Jaga ketenangan tubuh', detik: 4, skala: 1.15, warna: 'rgba(245, 158, 11, 0.4)' },
        { nama: 'Hembuskan', sub: 'Keluarkan secara stabil', detik: 4, skala: 0.58, warna: 'rgba(16, 185, 129, 0.4)' },
        { nama: 'Tahan Kosong', sub: 'Biarkan tubuh rileks', detik: 4, skala: 0.58, warna: 'rgba(139, 92, 246, 0.4)' }
      ],
      deskripsi: '<strong>Box Breathing</strong>: Tarik (4s) - Tahan (4s) - Hembus (4s) - Tahan Kosong (4s). Teknik kendali stres yang digunakan untuk menstabilkan detak jantung dan memulihkan fokus mental dengan cepat.'
    },
    'quick': {
      nama: 'Napas Cepat (4-2-4)',
      fase: [
        { nama: 'Tarik Napas', sub: 'Ambil udara segar', detik: 4, skala: 1.15, warna: 'rgba(59, 130, 246, 0.4)' },
        { nama: 'Tahan Sejenak', sub: 'Tenangkan pikiran', detik: 2, skala: 1.15, warna: 'rgba(245, 158, 11, 0.4)' },
        { nama: 'Hembuskan', sub: 'Buang rasa sesak', detik: 4, skala: 0.58, warna: 'rgba(16, 185, 129, 0.4)' }
      ],
      deskripsi: '<strong>Napas Cepat 4-2-4</strong>: Tarik (4s) - Tahan (2s) - Hembuskan (4s). Cocok saat kamu tiba-tiba diserang rasa panik, gugup menjelang presentasi, atau dada terasa berat.'
    }
  };

  if (labelSuaraNapas) {
    labelSuaraNapas.textContent = loncengAktif ? 'Aktif' : 'Mati';
  }

  // Render indikator tahapan secara dinamis
  function renderTahapanBar() {
    if (!faseNapasBar) return;
    const cfg = konfigurasiMetode[metodeAktif];
    faseNapasBar.innerHTML = '';
    cfg.fase.forEach((f, idx) => {
      const item = document.createElement('div');
      item.className = 'tahap-item';
      item.id = `tahapItem-${idx}`;
      item.innerHTML = `
        <span class="tahap-nomor">${idx + 1}</span>
        <span class="tahap-label">${f.nama} (${f.detik}s)</span>
      `;
      faseNapasBar.appendChild(item);
    });
  }
  renderTahapanBar();

  // Pilihan Tab Metode
  metodeButtons.forEach(btnTab => {
    btnTab.addEventListener('click', () => {
      if (jalan) {
        if (!confirm('Mengganti teknik akan mengulang sesi latihan saat ini. Lanjutkan?')) return;
        hentikanLatihan();
      }
      metodeButtons.forEach(b => {
        b.classList.remove('aktif');
        b.setAttribute('aria-selected', 'false');
      });
      btnTab.classList.add('aktif');
      btnTab.setAttribute('aria-selected', 'true');
      metodeAktif = btnTab.dataset.metode;
      renderTahapanBar();
      if (deskripsiTeknik) {
        deskripsiTeknik.innerHTML = konfigurasiMetode[metodeAktif].deskripsi;
      }
      tampilToast(`Teknik pernapasan: ${konfigurasiMetode[metodeAktif].nama}`, 'info');
    });
  });

  // Toggle Lonceng Meditasi
  if (btnSuaraNapas) {
    btnSuaraNapas.addEventListener('click', () => {
      loncengAktif = !loncengAktif;
      localStorage.setItem('napas_lonceng', loncengAktif ? 'aktif' : 'mati');
      if (labelSuaraNapas) labelSuaraNapas.textContent = loncengAktif ? 'Aktif' : 'Mati';
      tampilToast(`Lonceng mangkuk meditasi: ${loncengAktif ? 'Aktif 🔔' : 'Mati 🔕'}`, 'info');
      if (loncengAktif) ZenAudio.playSingingBowl();
    });
  }

  // Toggle Mode Zen (Layar Fokus Penuh)
  if (btnModeZen && areaNapas) {
    btnModeZen.addEventListener('click', () => {
      modeZenAktif = !modeZenAktif;
      document.body.classList.toggle('mode-zen-aktif', modeZenAktif);
      areaNapas.classList.toggle('zen-fokus', modeZenAktif);
      btnModeZen.classList.toggle('aktif', modeZenAktif);
      btnModeZen.textContent = modeZenAktif ? '✕ Keluar Mode Zen' : '🧘 Mode Zen';
      tampilToast(modeZenAktif ? 'Mode Zen aktif. Fokuskan seluruh perhatian pada napasmu.' : 'Kembali ke tampilan normal.', 'info');
    });
  }

  function setTahapAktif(index) {
    const items = faseNapasBar ? faseNapasBar.querySelectorAll('.tahap-item') : [];
    items.forEach((el, idx) => {
      if (idx === index) el.classList.add('aktif');
      else el.classList.remove('aktif');
    });
  }

  function mulaiCountdown(detikAwal) {
    clearInterval(timerDetik);
    let sisa = detikAwal;
    if (hitungDetik) hitungDetik.textContent = sisa;
    timerDetik = setInterval(() => {
      sisa--;
      if (sisa > 0) {
        if (hitungDetik) hitungDetik.textContent = sisa;
      } else {
        clearInterval(timerDetik);
      }
    }, 1000);
  }

  function putar(i = 0) {
    if (!jalan) return;
    const cfg = konfigurasiMetode[metodeAktif];
    const totalFase = cfg.fase.length;
    const indexFase = i % totalFase;
    const current = cfg.fase[indexFase];

    if (indexFase === 0 && i > 0) {
      hitunganPutaran++;
      if (angkaPutaran) angkaPutaran.textContent = hitunganPutaran;
      if (hitunganPutaran % 4 === 0) {
        tampilToast(`Hebat! Kamu telah menyelesaikan ${hitunganPutaran} siklus pernapasan 🌸`, 'sukses');
      }
    }

    // Mainkan lonceng meditasi
    if (loncengAktif) {
      ZenAudio.playSingingBowl();
    }

    aba.textContent = current.nama;
    if (subAba) subAba.textContent = current.sub;
    setTahapAktif(indexFase);
    mulaiCountdown(current.detik);

    lingkaran.style.transitionDuration = current.detik + 's';
    lingkaran.style.transform = 'scale(' + current.skala + ')';

    // Efek aura cahaya menyesuaikan
    if (auraNapas) {
      auraNapas.style.background = current.warna;
      auraNapas.style.transition = `background ${current.detik}s ease-in-out`;
    }

    timerFase = setTimeout(() => putar(i + 1), current.detik * 1000);
  }

  function hentikanLatihan() {
    jalan = false;
    btn.innerHTML = '<span class="ikon-tombol-napas">▶</span> <span class="teks-tombol-napas">Mulai Latihan Napas</span>';
    btn.classList.remove('aktif');
    clearTimeout(timerFase);
    clearInterval(timerDetik);
    aba.textContent = 'Siap?';
    if (subAba) subAba.textContent = 'Tekan mulai di bawah';
    if (hitungDetik) hitungDetik.textContent = '';
    if (infoPutaran) infoPutaran.hidden = true;
    setTahapAktif(-1);
    lingkaran.style.transitionDuration = '1s';
    lingkaran.style.transform = 'scale(0.58)';
    if (auraNapas) auraNapas.style.background = 'transparent';
  }

  btn.addEventListener('click', () => {
    jalan = !jalan;
    if (jalan) {
      btn.innerHTML = '<span class="ikon-tombol-napas">⏸</span> <span class="teks-tombol-napas">Selesaikan Latihan</span>';
      btn.classList.add('aktif');
      hitunganPutaran = 0;
      if (angkaPutaran) angkaPutaran.textContent = '0';
      if (infoPutaran) infoPutaran.hidden = false;
      putar(0);
    } else {
      hentikanLatihan();
      tampilToast('Latihan napas selesai. Semoga dadamu terasa lebih lega 🌿', 'sukses');
    }
  });
}

/* ==========================================================
   6. DINDING HARAPAN - LIVE SEARCH, PELUK & SALIN
   ========================================================== */
document.addEventListener('DOMContentLoaded', () => {
  // Live Character Counter pada Form Dinding & Pesanku
  const textareaDinding = document.getElementById('isi');
  const counterDinding = document.getElementById('counterDinding');
  if (textareaDinding && counterDinding) {
    const updateCounter = () => {
      const len = textareaDinding.value.length;
      counterDinding.textContent = len;
      if (len > 280) counterDinding.classList.add('mendekati-batas');
      else counterDinding.classList.remove('mendekati-batas');
    };
    textareaDinding.addEventListener('input', updateCounter);
    updateCounter();
  }

  // Tombol 1-Click Salin Kode Baru
  const btnSalinKode = document.getElementById('btnSalinKode');
  if (btnSalinKode) {
    btnSalinKode.addEventListener('click', () => {
      const kode = btnSalinKode.dataset.kode;
      navigator.clipboard.writeText(kode).then(() => {
        tampilToast(`Kode edit "${kode}" berhasil disalin ke papan klip! Simpan baik-baik ya.`, 'salin');
      });
    });
  }

  // Live Instant Search di Dinding Harapan
  const cariHarapan = document.getElementById('cariHarapan');
  const kartuHarapanList = document.querySelectorAll('.kartu-harapan');
  const pesanTidakDitemukan = document.getElementById('pesanTidakDitemukan');

  if (cariHarapan && kartuHarapanList.length > 0) {
    cariHarapan.addEventListener('input', () => {
      const query = cariHarapan.value.trim().toLowerCase();
      let ditemukan = 0;

      kartuHarapanList.forEach(kartu => {
        const isi = kartu.dataset.isi || '';
        const mood = kartu.dataset.mood || '';
        const match = isi.includes(query) || mood.includes(query);

        if (match) {
          kartu.style.display = '';
          ditemukan++;
        } else {
          kartu.style.display = 'none';
        }
      });

      if (pesanTidakDitemukan) {
        pesanTidakDitemukan.hidden = (ditemukan > 0);
      }
    });
  }

  // Interaksi Tombol Peluk (Reaksi Hangat dengan Partikel Hati Melayang)
  const buttonsPeluk = document.querySelectorAll('.btn-peluk-harapan');
  buttonsPeluk.forEach(btnP => {
    const id = btnP.dataset.id;
    const angkaEl = btnP.querySelector('.angka-peluk');
    
    // Ambil data reaksi lokal
    const disimpan = localStorage.getItem(`peluk_dinding_${id}`);
    if (disimpan) {
      if (angkaEl) angkaEl.textContent = disimpan;
      btnP.classList.add('sudah-dipeluk');
    }

    btnP.addEventListener('click', (e) => {
      e.stopPropagation();
      let currentVal = parseInt(angkaEl ? angkaEl.textContent : 0, 10) || 0;
      currentVal++;
      if (angkaEl) angkaEl.textContent = currentVal;
      localStorage.setItem(`peluk_dinding_${id}`, currentVal);
      btnP.classList.add('sudah-dipeluk');

      // Munculkan partikel hati melayang dari posisi tombol
      const rect = btnP.getBoundingClientRect();
      for (let h = 0; h < 6; h++) {
        const heart = document.createElement('span');
        heart.className = 'partikel-peluk';
        heart.textContent = ['🫂', '❤️', '✨', '🌸'][Math.floor(Math.random() * 4)];
        heart.style.left = (rect.left + rect.width / 2 + (Math.random() * 30 - 15)) + 'px';
        heart.style.top = (rect.top + (Math.random() * 20 - 10)) + 'px';
        heart.style.setProperty('--dx', (Math.random() * 80 - 40) + 'px');
        document.body.appendChild(heart);
        setTimeout(() => heart.remove(), 1800);
      }

      tampilToast('Kamu mengirimkan pelukan hangat untuk pesan ini 🫂', 'sukses');
    });
  });

  // Tombol Salin Kutipan di Dinding Harapan
  const buttonsSalin = document.querySelectorAll('.btn-salin-harapan');
  buttonsSalin.forEach(btnS => {
    btnS.addEventListener('click', (e) => {
      e.stopPropagation();
      const isi = btnS.dataset.isi || '';
      navigator.clipboard.writeText(isi).then(() => {
        tampilToast('Kalimat penyemangat disalin ke papan klip ✨', 'salin');
      });
    });
  });
});
