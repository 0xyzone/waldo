@extends('errors.layout')

@section('title', '503 – Service Unavailable')
@section('accent',  '#06b6d4')
@section('accent2', '#22d3ee')
@section('glow',    'rgba(6,182,212,0.28)')

@section('emoji', '🛠️')
@section('emoji-pool', "['🔧','⚙️','🪛','🏗️','👷','🦺','🪜','🔩','🛸','😴']")
@section('code',     '503')
@section('headline', "We're in the workshop! 👷")
@section('sub',      "The service is taking a well-deserved nap while we make it even more awesome. We'll be back shortly — or, you know, \"shortly\" by server standards.")

@section('buttons')
    <button onclick="window.location.reload()" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Check Again
    </button>
    <a href="mailto:support@{{ request()->getHost() }}" class="btn btn-ghost">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        Contact Us
    </button>
@endsection

@section('extra')
<div style="margin-top:1.6rem;">
    {{-- Status checklist --}}
    <div style="background:rgba(6,182,212,0.06);border:1px solid rgba(6,182,212,0.18);border-radius:1rem;padding:1.1rem 1.4rem;text-align:left;">
        <p style="font-size:.8rem;font-weight:700;color:rgba(232,232,240,.6);margin-bottom:.8rem;text-transform:uppercase;letter-spacing:.06em;">System Status</p>
        <div id="status-checks" style="display:flex;flex-direction:column;gap:.45rem;"></div>
    </div>

    {{-- Auto-refresh --}}
    <div style="margin-top:1.2rem;text-align:center;">
        <p style="font-size:.8rem;color:rgba(232,232,240,.4);">🔄 Auto-checking in <span id="sc-cd" style="font-family:'JetBrains Mono',monospace;color:#06b6d4;font-weight:700;">30</span>s</p>
        <div style="width:100%;height:3px;background:rgba(255,255,255,.05);border-radius:99px;overflow:hidden;margin-top:.5rem;">
            <div id="sc-bar" style="height:100%;width:100%;background:linear-gradient(90deg,#06b6d4,#22d3ee);border-radius:99px;transition:width 1s linear;"></div>
        </div>
    </div>
</div>
@endsection

@section('xstyle')
.sc-item { display:flex;align-items:center;gap:.6rem;font-size:.82rem;color:rgba(232,232,240,.55); }
.sc-icon { font-size:1rem;width:1.4rem;flex-shrink:0; }
.sc-spin { display:inline-block;animation:spin 1s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }
@endsection

@section('xscript')
// Animated status checklist
const checks = [
    { label: 'Database connection',  delay: 400,  result: 'ok',      icon: '🗄️' },
    { label: 'Cache layer',          delay: 900,  result: 'ok',      icon: '⚡' },
    { label: 'Queue workers',        delay: 1400, result: 'warn',    icon: '📋' },
    { label: 'Application server',   delay: 2000, result: 'error',   icon: '🖥️' },
    { label: 'CDN / Asset delivery', delay: 2600, result: 'ok',      icon: '🌐' },
    { label: 'Maintenance mode',     delay: 3200, result: 'active',  icon: '🛠️' },
];
const outcomes = {
    ok:     { emoji:'✅', color:'#34d399', text:'Operational' },
    warn:   { emoji:'⚠️', color:'#fbbf24', text:'Degraded'    },
    error:  { emoji:'❌', color:'#f87171', text:'Offline'     },
    active: { emoji:'🔧', color:'#06b6d4', text:'In maintenance' },
};
const checkBox = document.getElementById('status-checks');

checks.forEach(ch => {
    const row = document.createElement('div');
    row.className = 'sc-item';
    row.innerHTML = `<span class="sc-icon">${ch.icon}</span><span style="flex:1">${ch.label}</span><span class="sc-spin" style="font-size:.85rem;">⏳</span>`;
    checkBox.appendChild(row);

    setTimeout(() => {
        const o = outcomes[ch.result];
        const spinner = row.querySelector('.sc-spin');
        if (spinner) {
            spinner.style.animation = 'none';
            spinner.textContent = o.emoji;
            spinner.style.color = o.color;
            const label = document.createElement('span');
            label.textContent = o.text;
            label.style.cssText = `font-size:.72rem;color:${o.color};font-weight:600;margin-left:.3rem;font-family:'JetBrains Mono',monospace;`;
            row.appendChild(label);
        }
    }, ch.delay);
});

// Auto-reload countdown
let sc = 30;
const scEl  = document.getElementById('sc-cd');
const scBar = document.getElementById('sc-bar');
const scT = setInterval(() => {
    sc--;
    if (scEl)  scEl.textContent  = sc;
    if (scBar) scBar.style.width = (sc / 30 * 100) + '%';
    if (sc <= 0) { clearInterval(scT); window.location.reload(); }
}, 1000);
@endsection
