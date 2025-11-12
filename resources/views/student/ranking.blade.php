@extends('layouts.student')

@section('content')

<head>
  <style>
    #confettiCanvas { z-index: 9999 !important; }
    
    /* Small custom animations and shapes */
    .podium-shadow {
      filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.12));
    }

    .float {
      animation: float 3s ease-in-out infinite;
    }

    @keyframes float {

      0%,
      100% {
        transform: translateY(0);
      }

      50% {
        transform: translateY(-8px);
      }
    }

    .badge-shine {
      background: linear-gradient(90deg, rgba(255, 255, 255, 0.6) 0%, rgba(255, 255, 255, 0.2) 50%, rgba(255, 255, 255, 0.6) 100%);
      background-size: 200% 100%;
      animation: shine 3s linear infinite;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    @keyframes shine {
      0% {
        background-position: 200% 0;
      }

      100% {
        background-position: -200% 0;
      }
    }

    /* confetti canvas sits on top */
    #confettiCanvas {
      position: fixed;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
      z-index: 60;
    }
  </style>
</head>

<body>

  <!-- Confetti canvas -->
  <!-- Canvas para confeti -->
  <canvas id="confettiCanvas" class="fixed top-0 left-0 w-full h-full pointer-events-none z-[9999]"></canvas>


  <!-- APP WRAPPER -->
  <div class="max-w-6xl mx-auto px-6 py-8">

    <!-- HEADER -->
    <header
      class="hidden flex items-center justify-between gap-4 bg-white/80 backdrop-blur-sm p-4 rounded-2xl shadow-md mb-6">
      <div class="flex items-center gap-4">
        <div id="petContainer" class="bg-primary-400 p-3 rounded-2xl relative">
          <img id="petImg" src="https://cdn-icons-png.flaticon.com/512/616/616408.png" alt="Tindell" class="w-12 h-12">
          <div id="petMood"
            class="absolute -top-2 -right-2 bg-white rounded-full w-6 h-6 flex items-center justify-center text-xs">😊
          </div>
        </div>
        <div>
          <p class="text-sm text-gray-600">Tindell's English Adventure</p>
          <h1 id="greet" class="text-2xl font-extrabold">Hola, Sofía 👋</h1>
          <p id="levelLabel" class="text-xs text-primary-600 mt-1">Nivel 3 — Exploradora</p>
        </div>
      </div>

      <div class="text-right">
        <p class="text-sm text-gray-500">Progreso</p>
        <div class="relative w-56 bg-gray-200 rounded-full h-3 mt-2 overflow-hidden">
          <div id="xpBar"
            class="absolute left-0 top-0 h-full rounded-full bg-gradient-to-r from-yellow-300 to-primary-500 w-1/4 transition-all">
          </div>
        </div>
        <p id="xpText" class="text-sm font-semibold mt-1">650 / 1000 XP</p>
      </div>
    </header>

    <!-- MAIN: Podio + Controls + Ranking -->
    <main>

      <!-- Title -->
      <section class="text-center mb-6">
        <h2 class="flex justify-center text-4xl font-extrabold text-primary-600"><span><img height="43" width="43"
              class="mr-2" src="{{ asset('icons/trophy-64.png') }}" alt=""></span> Weekly Ranking</h2>
        <p class="text-gray-600 mt-2">Compete, earn medals, and help Tindell find his way.</p>
      </section>

      <!-- Podio -->
      <section class="flex flex-col md:flex-row items-end justify-center gap-6 mb-8 mt-20">
        <!-- Second place -->
        <div id="card-2" class="podium-card flex flex-col items-center w-48">
          <div
            class="bg-gradient-to-b from-gray-200 to-gray-400 rounded-2xl p-4 text-center podium-shadow transform transition">
            <img src="https://randomuser.me/api/portraits/lego/5.jpg"
              data-src="https://randomuser.me/api/portraits/lego/5.jpg"
              class="w-20 h-20 rounded-full border-4 border-white mx-auto mb-2 avatar" alt="player2">
            <h3 class="font-bold text-gray-800">Oliv</h3>
            <p class="text-sm text-gray-600">920 XP</p>
          </div>
          <div class="bg-gray-300 w-48 h-20 rounded-t-xl mt-[-8px]"></div>
          <div class="mt-2 text-2xl">🥈</div>
        </div>

        <!-- First place -->
        <div id="card-1" class="podium-card flex flex-col items-center -mt-6">
          <div
            class="bg-gradient-to-b from-yellow-300 to-yellow-500 rounded-3xl p-6 text-center podium-shadow transform transition scale-110 z-10">
            <img src="https://randomuser.me/api/portraits/lego/7.jpg"
              data-src="https://randomuser.me/api/portraits/lego/7.jpg"
              class="w-24 h-24 rounded-full border-4 border-white mx-auto mb-2 avatar" alt="player1">
            <h3 id="firstName" class="font-extrabold text-gray-900 text-2xl">Ethan</h3>
            <p id="firstXp" class="text-sm text-gray-800">950 XP</p>
            <div id="firstBadge" class="mt-2 text-sm text-yellow-900">🎉 Weekly Best Student</div>
          </div>
          <div class="bg-yellow-400 w-56 h-28 rounded-t-xl mt-[-10px] shadow-lg"></div>
          <div class="mt-2 text-3xl">🥇</div>
        </div>

        <!-- Third place -->
        <div id="card-3" class="podium-card flex flex-col items-center w-48">
          <div
            class="bg-gradient-to-b from-orange-300 to-orange-500 rounded-2xl p-4 text-center podium-shadow transform transition">
            <img src="https://randomuser.me/api/portraits/lego/3.jpg"
              data-src="https://randomuser.me/api/portraits/lego/3.jpg"
              class="w-20 h-20 rounded-full border-4 border-white mx-auto mb-2 avatar" alt="player3">
            <h3 class="font-bold text-white">Noah</h3>
            <p class="text-sm text-white">900 XP</p>
          </div>
          <div class="bg-orange-400 w-48 h-16 rounded-t-xl mt-[-8px]"></div>
          <div class="mt-2 text-2xl">🥉</div>
        </div>
      </section>

      <!-- Ranking table + controls -->
      <section class="grid md:grid-cols-3 gap-6">

        <!-- Ranking table -->
        <div class="md:col-span-2 bg-white rounded-3xl shadow-lg overflow-hidden border border-primary-300">
          <table class="w-full text-sm">
            <thead class="bg-primary-400 text-primary-600 uppercase font-semibold text-xs">
              <tr>
                <th class="px-4 py-3 text-center">Ranking</th>
                <th class="px-4 py-3 text-left">Student</th>
                <th class="px-4 py-3 text-right">XP</th>
                <th class="px-4 py-3 text-center">Reward</th>
              </tr>
            </thead>
            <tbody id="rankingBody" class="divide-y divide-green-50">
              <!-- Generado por JS -->
            </tbody>
          </table>
        </div>

        <!-- Sidebar: Retos, Medallas, CTA -->
        <aside class="flex flex-col gap-4">
          <!-- Reto diario -->
          <div class="bg-gradient-to-br from-yellow-100 to-yellow-50 rounded-2xl p-4 shadow-md">
            <div class="flex items-center justify-between">
              <div>
                <h4 class="text-lg font-bold text-yellow-700">🎯 Daily Challenge</h4>
                <p class="text-sm text-gray-700">Earn 50 XP + bonus coins by complete 3 tasks.</p>
              </div>
              <div>
                <button id="acceptChallengeBtn"
                  class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-2 rounded-xl shadow">Accept</button>
              </div>
            </div>
            <div id="challengeProgress" class="mt-3">
              <div class="w-full bg-white/60 rounded-full h-2">
                <div id="challengeBar" class="h-2 rounded-full bg-yellow-500 w-0"></div>
              </div>
              <p id="challengeText" class="text-xs text-gray-500 mt-2">0 / 3 completed</p>
            </div>
          </div>

          <!-- Medallas -->
          <div class="bg-white rounded-2xl p-4 shadow-md">
            <h4 class="text-lg font-bold text-gray-800">🎖️ Medals</h4>
            <div id="badgesRow" class="flex gap-2 mt-3">
              <!-- Badges generadas por JS -->
            </div>
            <p class="text-xs text-gray-500 mt-2">Complete tasks to unlock badges.</p>
          </div>

          <!-- CTA: Ver progreso -->
          <div class="bg-green-50 rounded-2xl p-4 shadow-md text-center">
            <h4 class="font-bold text-primary-600">Reach a ranked up level</h4>
            <p class="text-sm text-gray-600 mt-2">Level up your skills and unlock exclusive rewards. </p>
            <x-button id="claimXPBtn">
              Claim 50 XP
            </x-button>
          </div>
        </aside>
      </section>

      <!-- Footer CTA -->
      <div class="text-center mt-8">
        <button id="simulateBtn" class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-3 rounded-full shadow">Simular:
          +30 XP a jugador aleatorio</button>
      </div>

    </main>
  </div>
    
  <!-- Anime.js CDN -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
</body>
<!-- --- SCRIPTS: Lógica de gamificación y animaciones --- -->
<script>
/* ---------- Confetti (canvas) ---------- */
(function() {
  // Ensure there's a single canvas in the DOM
  let canvas = document.getElementById('confettiCanvas');
  if (!canvas) {
    canvas = document.createElement('canvas');
    canvas.id = 'confettiCanvas';
    canvas.className = 'fixed top-0 left-0 w-full h-full pointer-events-none z-[9999]';
    document.body.appendChild(canvas);
  }

  function resizeCanvas() {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
  }
  window.addEventListener('resize', resizeCanvas);
  resizeCanvas();

  window.fireConfetti = function(amount = 80) {
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    // Prepare particles
    const colors = ['#FFD166', '#06D6A0', '#118AB2', '#EF476F', '#FF6B6B'];
    const pieces = Array.from({ length: amount }, () => ({
      x: Math.random() * canvas.width,
      y: Math.random() * -canvas.height,
      w: 6 + Math.random() * 8,
      h: 8 + Math.random() * 10,
      color: colors[Math.floor(Math.random() * colors.length)],
      rotation: Math.random() * 360,
      speed: 2 + Math.random() * 4,
      rotSpeed: (Math.random() - 0.5) * 6
    }));

    let raf;
    function draw() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      for (const p of pieces) {
        p.y += p.speed;
        p.x += Math.sin(p.y / 30) * 1.5;
        p.rotation += p.rotSpeed;
        ctx.save();
        ctx.translate(p.x, p.y);
        ctx.rotate((p.rotation * Math.PI) / 180);
        ctx.fillStyle = p.color;
        ctx.fillRect(-p.w / 2, -p.h / 2, p.w, p.h);
        ctx.restore();
      }
      // Continue while some particle is onscreen
      if (pieces.some(p => p.y < canvas.height + 40)) {
        raf = requestAnimationFrame(draw);
      } else {
        cancelAnimationFrame(raf);
        ctx.clearRect(0, 0, canvas.width, canvas.height);
      }
    }
    draw();
  };
})();

/* ---------- App state & utilities (kept minimal) ---------- */
const DEFAULT_PLAYERS = [
  { id: 1, name: 'Ethan', xp: 950, avatar: 'https://randomuser.me/api/portraits/lego/7.jpg', streak: 4, badges: ['champion'], dailyCompleted: 0 },
  { id: 2, name: 'Olivia', xp: 920, avatar: 'https://randomuser.me/api/portraits/lego/5.jpg', streak: 6, badges: [], dailyCompleted: 1 },
  { id: 3, name: 'Noah', xp: 900, avatar: 'https://randomuser.me/api/portraits/lego/3.jpg', streak: 2, badges: [] },
  { id: 4, name: 'Ava', xp: 880, avatar: 'https://randomuser.me/api/portraits/lego/8.jpg', streak: 3, badges: ['streak3'] },
  { id: 5, name: 'Liam', xp: 850, avatar: 'https://randomuser.me/api/portraits/lego/2.jpg', streak: 1, badges: [], dailyCompleted: 0 },
  { id: 6, name: 'Sophia', xp: 820, avatar: 'https://randomuser.me/api/portraits/lego/6.jpg', streak: 2, badges: [] }
];

let players = loadState('players') || DEFAULT_PLAYERS;
let currentUserId = 1;
let dailyChallenge = loadState('dailyChallenge') || { accepted: false, target: 3, progress: 0 };
const LEVELS = [0, 200, 500, 1000, 1600, 2300];
const badgesCatalog = {
  champion: { label: 'Campeón', icon: '🏆', color: 'bg-yellow-300' },
  streak3: { label: 'Racha 3', icon: '🔥', color: 'bg-red-100' },
  helper: { label: 'Ayudante', icon: '🤝', color: 'bg-blue-200' },
  consistent: { label: 'Constancia', icon: '📅', color: 'bg-green-200' }
};

function saveState(key, value) { localStorage.setItem(key, JSON.stringify(value)); }
function loadState(key) { try { return JSON.parse(localStorage.getItem(key)); } catch(e) { return null; } }
function persist() { saveState('players', players); saveState('dailyChallenge', dailyChallenge); }

/* ---------- Render functions (safe DOM checks) ---------- */
function renderAll() {
  renderPodium();
  renderRankingTable();
  renderBadgesRow();
  renderChallenge();
  renderHeaderSafe();
}

function renderHeaderSafe() {
  const greet = document.getElementById('greet');
  const levelLabel = document.getElementById('levelLabel');
  const xpBar = document.getElementById('xpBar');
  const xpText = document.getElementById('xpText');
  const petMood = document.getElementById('petMood');

  const me = players.find(p => p.id === currentUserId);
  if (!me) return;
  const level = getLevel(me.xp);

  if (greet) greet.textContent = `Hello, ${me.name} 👋`;
  if (levelLabel) levelLabel.textContent = `Level ${level} — Explorer`;
  if (xpBar) {
    const nextXP = LEVELS[level] || LEVELS[LEVELS.length-1];
    const prevXP = LEVELS[level-1] || 0;
    const pct = Math.min(100, Math.round(100 * (me.xp - prevXP) / (nextXP - prevXP)));
    xpBar.style.width = pct + '%';
  }
  if (xpText) xpText.textContent = `${me.xp} / ${LEVELS[level] || LEVELS[LEVELS.length-1]} XP`;
  if (petMood) petMood.textContent = me.xp >= 900 ? '🤩' : me.xp >= 700 ? '😊' : '🙂';
}

function renderPodium() {
  const sorted = [...players].sort((a,b) => b.xp - a.xp);
  const [p1, p2, p3] = sorted;
  safeFillPodioCard('card-1', p1);
  safeFillPodioCard('card-2', p2);
  safeFillPodioCard('card-3', p3);
}

function safeFillPodioCard(cardId, player) {
  const card = document.getElementById(cardId);
  if (!card || !player) return;
  card.querySelectorAll('.avatar').forEach(img => { img.src = player.avatar; });
  if (cardId === 'card-1') {
    const fn = document.getElementById('firstName');
    const fx = document.getElementById('firstXp');
    if (fn) fn.textContent = player.name;
    if (fx) fx.textContent = player.xp + ' XP';
  } else {
    const h3 = card.querySelector('h3');
    const p = card.querySelector('p');
    if (h3) h3.textContent = player.name;
    if (p) p.textContent = player.xp + ' XP';
  }
}

function renderRankingTable() {
  const tbody = document.getElementById('rankingBody');
  if (!tbody) return;
  tbody.innerHTML = '';
  const sorted = [...players].sort((a,b) => b.xp - a.xp);
  sorted.forEach((p, idx) => {
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-green-50 transition';
    tr.innerHTML = `
      <td class="px-4 py-3 text-center font-bold text-green-600">${idx+1}</td>
      <td class="px-4 py-3 flex items-center gap-3">
        <img src="${p.avatar}" class="w-10 h-10 rounded-full">
        <div><div class="font-medium">${p.name}</div><div class="text-xs text-gray-500">Racha: ${p.streak || 0} días</div></div>
      </td>
      <td class="px-4 py-3 text-right font-semibold text-gray-700">${p.xp}</td>
      <td class="px-4 py-3 text-center">${renderRewardIcon(p)}</td>
    `;
    tbody.appendChild(tr);
  });
}

function renderBadgesRow() {
  const container = document.getElementById('badgesRow');
  if (!container) return;
  container.innerHTML = '';
  const me = players.find(p => p.id === currentUserId) || { badges: [] };
  Object.keys(badgesCatalog).forEach(key => {
    const html = document.createElement('div');
    const owned = me.badges && me.badges.includes(key);
    html.className = `p-2 rounded-xl flex gap-2 items-center text-xs ${owned ? badgesCatalog[key].color : 'bg-gray-100'} shadow-sm`;
    html.innerHTML = `<div class="text-xl">${badgesCatalog[key].icon}</div><div>${badgesCatalog[key].label}${owned ? '' : ' (Locked)'}</div>`;
    container.appendChild(html);
  });
}

function renderChallenge() {
  const bar = document.getElementById('challengeBar');
  const text = document.getElementById('challengeText');
  const btn = document.getElementById('acceptChallengeBtn');
  if (!bar || !text || !btn) return;
  bar.style.width = (dailyChallenge.progress / dailyChallenge.target) * 100 + '%';
  text.textContent = `${dailyChallenge.progress} / ${dailyChallenge.target} completed`;
  btn.textContent = dailyChallenge.accepted ? 'In progress' : 'Accept';
  btn.disabled = !!dailyChallenge.accepted;
}

function renderRewardIcon(player) {
  if (player.xp >= 900) return '🏅';
  if (player.xp >= 800) return '🥇';
  return '🍪';
}

/* ---------- Game actions (safe) ---------- */
function getLevel(xp) {
  for (let i = LEVELS.length-1; i>=0; i--) {
    if (xp >= LEVELS[i]) return i+1;
  }
  return 1;
}

function addXP(playerId, amount) {
  const p = players.find(x => x.id === playerId);
  if (!p) return;
  const oldXp = p.xp;
  p.xp += amount;
  checkBadges(p);
  persist();
  renderAll();
  if ((p.xp - oldXp) >= 50) {
    // fire confetti safely through global function
    if (typeof window.fireConfetti === 'function') window.fireConfetti(100);
  }
}

function acceptDailyChallenge() {
  dailyChallenge.accepted = true;
  dailyChallenge.progress = 0;
  persist();
  renderChallenge();
}

function completeChallenge() {
  if (!dailyChallenge.accepted) return;
  dailyChallenge.progress++;
  if (dailyChallenge.progress >= dailyChallenge.target) {
    dailyChallenge.progress = dailyChallenge.target;
    addXP(currentUserId, 100);
    awardBadgeTo(currentUserId, 'consistent');
    if (typeof window.fireConfetti === 'function') window.fireConfetti(120);
    setTimeout(() => {
      dailyChallenge.accepted = false;
      dailyChallenge.progress = 0;
      persist();
      renderChallenge();
    }, 1200);
  } else {
    persist();
    renderChallenge();
  }
}

function awardBadgeTo(playerId, badgeKey) {
  const p = players.find(x => x.id === playerId);
  if (!p) return;
  p.badges = p.badges || [];
  if (!p.badges.includes(badgeKey)) {
    p.badges.push(badgeKey);
    persist();
    renderBadgesRow();
  }
}

function checkBadges(p) {
  if (p.streak >= 3) awardBadgeTo(p.id, 'streak3');
  if (p.xp >= 950) awardBadgeTo(p.id, 'champion');
  if (p.streak >= 7) awardBadgeTo(p.id, 'consistent');
}

/* ---------- Safe binding of controls ---------- */
function bindControlsSafe() {
  // helper to add listener only if element exists
  const safeOn = (id, evt, fn) => {
    const el = document.getElementById(id);
    if (el) el.addEventListener(evt, fn);
  };

  safeOn('simulateBtn', 'click', () => {
    const idx = Math.floor(Math.random() * players.length);
    addXP(players[idx].id, 30);
    if (Math.random() > 0.6) incrementStreak(players[idx].id);
    setTimeout(() => { renderAll(); }, 300);
  });

  safeOn('claimXPBtn', 'click', () => {
    addXP(currentUserId, 50);
    const el = document.getElementById('claimXPBtn');
    if (el) { el.textContent = 'Claimed'; el.classList.add('opacity-60','cursor-not-allowed'); el.disabled = true; }
    if (typeof window.fireConfetti === 'function') window.fireConfetti(120);
  });

  safeOn('acceptChallengeBtn', 'click', () => {
    acceptDailyChallenge();
    const el = document.getElementById('acceptChallengeBtn');
    if (el) { el.textContent = 'In progress'; el.disabled = true; }
  });

  // clicking podium cards to simulate challenge complete (optional)
  document.querySelectorAll('.podium-card').forEach(card => {
    if (card) {
      card.addEventListener('click', () => completeChallenge());
    }
  });

  // keyboard shortcuts safely
  document.addEventListener('keydown', (e) => {
    if (e.key.toLowerCase() === 'x') {
      addXP(currentUserId, 20);
    }
    if (e.key.toLowerCase() === 'c' && typeof window.fireConfetti === 'function') {
      window.fireConfetti(120);
    }
  });
}

/* ---------- small helpers ---------- */
function incrementStreak(playerId) {
  const p = players.find(x => x.id === playerId);
  if (!p) return;
  p.streak = (p.streak || 0) + 1;
  checkBadges(p);
  persist();
}

function showToast(msg) {
  const t = document.createElement('div');
  t.className = 'fixed right-6 bottom-6 bg-gray-900 text-white px-4 py-2 rounded-md shadow-lg z-[99999]';
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 1700);
}

/* ---------- Init when DOM ready ---------- */
document.addEventListener('DOMContentLoaded', () => {
  // ensure state initialized
  if (!loadState('players')) persist();

  // render UI safely
  renderAll();

  // bind controls, but only to elements that exist
  bindControlsSafe();

  // If user wants initial confetti test, uncomment:
  // if (typeof window.fireConfetti === 'function') window.fireConfetti(30);
});
</script>

@endsection