const kataRisiko = ['bunuh diri','mengakhiri hidup','mati aja','ingin mati','pengen mati','log out dari bumi',
  'logout dari bumi','nggak mau hidup','gak mau hidup','tidak mau hidup','menyerah','lelah hidup','capek hidup','menyakiti diri'];

const chat = document.getElementById('chat');
if (chat) {
  const csrf = document.querySelector('meta[name=csrf]').content;
  const input = document.getElementById('curhat');
  const bantuan = document.getElementById('bantuan');
  let sibuk = false;
  const riwayat = [];   // hanya di memori browser, dikirim sebagai konteks percakapan

  function tambah(teks, dari, lampiran, saran) {
    const d = document.createElement('div');
    d.className = 'pesan ' + dari;
    const p = document.createElement('p');
    p.textContent = teks;                       // teks selalu lewat textContent (aman dari XSS)
    d.appendChild(p);
    if (lampiran) {                             // lampiran sudah di-escape server (tampil_konten)
      const x = document.createElement('div');
      x.className = 'lampiran';
      x.innerHTML = lampiran;
      d.appendChild(x);
    }
    if (Array.isArray(saran) && saran.length) {
      const grup = document.createElement('div');
      grup.className = 'saran-chat';
      const label = document.createElement('span');
      label.textContent = 'Kita bisa lanjut dengan cara ini:';
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
    chat.appendChild(d);
    chat.scrollTop = chat.scrollHeight;
    return d;
  }
  function cekRisiko(t) {
    if (kataRisiko.some(k => t.toLowerCase().includes(k))) bantuan.hidden = false;
  }

  async function kirim(teks) {
    if (sibuk) return;
    sibuk = true;
    tambah(teks, 'saya');
    cekRisiko(teks);
    const ketik = tambah('Rilis sedang mengetik', 'bot ketik');
    try {
      const r = await fetch('chat.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF': csrf},
        body: JSON.stringify({pesan: teks, riwayat: riwayat.slice(-10)})
      });
      const d = await r.json();
      await new Promise(s => setTimeout(s, 800));
      ketik.remove();
      tambah(d.balasan, 'bot', d.konten, d.saran);
      riwayat.push({role: 'user', text: teks}, {role: 'assistant', text: d.balasan});
      if (d.krisis) bantuan.hidden = false;
    } catch (e) {
      ketik.remove();
      tambah('Maaf, koneksi sedang bermasalah. Coba kirim lagi sebentar lagi.', 'bot');
    }
    sibuk = false;
  }

  document.getElementById('formChat').addEventListener('submit', ev => {
    ev.preventDefault();
    const t = input.value.trim();
    if (!t) return;
    input.value = '';
    kirim(t);
  });
  input.addEventListener('keydown', ev => {
    if (ev.key === 'Enter' && !ev.shiftKey) { ev.preventDefault(); document.getElementById('formChat').requestSubmit(); }
  });
  document.querySelectorAll('#cepat .chip').forEach(b => b.addEventListener('click', () => kirim(b.dataset.teks)));

  // Lepaskan: tulisan melayang hilang, tidak dikirim dan tidak disimpan
  document.getElementById('btnLepas').addEventListener('click', () => {
    const teks = input.value.trim();
    if (!teks) { input.focus(); return; }
    cekRisiko(teks);
    const wadah = document.getElementById('melayang');
    const kotak = input.getBoundingClientRect();
    teks.split(/\s+/).slice(0, 60).forEach((kata, i) => {
      const s = document.createElement('span');
      s.className = 'kata';
      s.textContent = kata;
      s.style.left = Math.random() * Math.max(kotak.width - 60, 10) + 'px';
      s.style.top = Math.random() * Math.max(kotak.height - 30, 5) + 'px';
      s.style.setProperty('--dx', (Math.random() * 160 - 80) + 'px');
      s.style.setProperty('--r', (Math.random() * 50 - 25) + 'deg');
      s.style.animationDelay = (i * 0.04) + 's';
      wadah.appendChild(s);
      setTimeout(() => s.remove(), 5500);
    });
    input.value = '';
    setTimeout(() => tambah('Tulisanmu sudah kamu lepaskan dan tidak disimpan. Kadang menuliskannya saja sudah membuat dada sedikit lebih ringan. Mau lanjut cerita, atau istirahat sebentar?', 'bot'), 1200);
  });

  tambah('Hai, aku Rilis. Di sini kamu boleh cerita apa saja tanpa dinilai. Hari ini rasanya bagaimana?', 'bot');
}

// Latihan napas 4-7-8
const lingkaran = document.getElementById('lingkaran');
if (lingkaran) {
  const aba = document.getElementById('aba'), btn = document.getElementById('btnNapas');
  let jalan = false, timer;
  const fase = [['Tarik napas', 4, 1], ['Tahan', 7, 1], ['Hembuskan', 8, .55]];
  function putar(i = 0) {
    if (!jalan) return;
    const [nama, detik, skala] = fase[i % 3];
    aba.textContent = nama;
    lingkaran.style.transitionDuration = detik + 's';
    lingkaran.style.transform = 'scale(' + skala + ')';
    timer = setTimeout(() => putar(i + 1), detik * 1000);
  }
  btn.addEventListener('click', () => {
    jalan = !jalan;
    btn.textContent = jalan ? 'Berhenti' : 'Mulai';
    clearTimeout(timer);
    if (jalan) putar(); else { aba.textContent = 'Siap?'; lingkaran.style.transitionDuration = '1s'; lingkaran.style.transform = 'scale(.55)'; }
  });
}
