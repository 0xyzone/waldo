@extends('errors.layout')

@section('title', '429 – Too Many Requests')
@section('accent',  '#ec4899')
@section('accent2', '#f472b6')
@section('glow',    'rgba(236,72,153,0.28)')

@section('emoji', '🚀')
@section('emoji-pool', "['💨','⚡','🏎️','🦅','🚄','🏃','💪','🤸','🐇','🔥']")
@section('code',     '429')
@section('headline', "Slow down, Flash! ⚡")
@section('sub',      "You're sending requests faster than a caffeinated squirrel on a keyboard. Our server needs a moment to breathe. Humans do that too, y'know.")

@section('buttons')
    <button onclick="window.location.reload()" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Try Again
    </button>
    <a href="{{ url('/') }}" class="btn btn-ghost">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Chill at Home
    </button>
@endsection

@section('extra')
<div style="margin-top:1.5rem;padding:1rem 1.2rem;background:rgba(236,72,153,0.08);border:1px solid rgba(236,72,153,0.2);border-radius:1rem;">
    <p style="font-size:.85rem;color:rgba(232,232,240,.55);margin-bottom:.8rem;">🧘 Take a deep breath while we cool things down:</p>
    <div id="breathe-ring" style="width:80px;height:80px;margin:0 auto;border-radius:50%;border:3px solid rgba(236,72,153,0.4);display:flex;align-items:center;justify-content:center;animation:breathe-anim 4s ease-in-out infinite;cursor:default;">
        <span style="font-size:1.8rem;" id="breathe-emoji">🫁</span>
    </div>
    <p id="breathe-text" style="font-size:.78rem;color:rgba(236,72,153,.7);margin-top:.6rem;font-weight:600;">Inhale… 😮‍💨</p>
    <p style="font-size:.75rem;color:rgba(232,232,240,.3);margin-top:.4rem;">Retry available in <span id="retry-cd" style="font-family:'JetBrains Mono',monospace;color:#ec4899;font-weight:700;">60</span>s</p>
</div>
@endsection

@section('xstyle')
@keyframes breathe-anim {
    0%,100% { transform: scale(1);   border-color: rgba(236,72,153,0.3); box-shadow: none; }
    50%     { transform: scale(1.35);border-color: rgba(236,72,153,0.8); box-shadow: 0 0 24px rgba(236,72,153,0.35); }
}
@endsection

@section('xscript')
// Breathe text cycle
const breathePhases = [
    { text:'Inhale… 😮‍💨', duration: 2000 },
    { text:'Hold it… 😶', duration: 1000 },
    { text:'Exhale… 😌', duration: 2000 },
    { text:'Ahhh 😊', duration: 1000 },
];
let bPhase = 0;
const bEl = document.getElementById('breathe-text');
function nextPhase() {
    if (!bEl) return;
    bEl.textContent = breathePhases[bPhase].text;
    setTimeout(() => { bPhase = (bPhase + 1) % breathePhases.length; nextPhase(); }, breathePhases[bPhase].duration);
}
nextPhase();

// Retry countdown
let rSecs = 60;
const rEl = document.getElementById('retry-cd');
const rTimer = setInterval(() => {
    rSecs--;
    if (rEl) rEl.textContent = rSecs;
    if (rSecs <= 0) { clearInterval(rTimer); if (rEl) rEl.textContent = '0'; }
}, 1000);
@endsection
