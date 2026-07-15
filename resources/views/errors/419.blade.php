@extends('errors.layout')

@section('title', '419 – Page Expired')
@section('accent',  '#f59e0b')
@section('accent2', '#fbbf24')
@section('glow',    'rgba(245,158,11,0.28)')

@section('emoji', '⏰')
@section('emoji-pool', "['⌛','⏳','🕰️','🪄','💨','😴','🦥','🐌','🧓','⌚']")
@section('code',     '419')
@section('headline', "Your page took a nap! 😴")
@section('sub',      "This page expired while you were gone. The session token got bored and left for a coffee break. Honestly, same.")

@section('buttons')
    <button onclick="window.location.reload()" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Wake It Up
    </button>
    <a href="{{ url('/') }}" class="btn btn-ghost">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Go Home
    </a>
@endsection

@section('extra')
<div style="margin-top:1.5rem;">
    <p style="font-size:.82rem;color:rgba(232,232,240,.45);margin-bottom:.6rem;">⏳ Auto-refreshing in <span id="countdown" style="font-family:'JetBrains Mono',monospace;color:#f59e0b;font-weight:700;">30</span>s</p>
    <div style="width:100%;height:6px;background:rgba(255,255,255,.06);border-radius:99px;overflow:hidden;">
        <div id="auto-bar" style="height:100%;width:100%;background:linear-gradient(90deg,#f59e0b,#fbbf24);border-radius:99px;box-shadow:0 0 10px rgba(245,158,11,.4);transition:width 1s linear;"></div>
    </div>
</div>
@endsection

@section('xscript')
// Auto-reload countdown
let secs = 30;
const countEl = document.getElementById('countdown');
const barEl   = document.getElementById('auto-bar');
const timer = setInterval(() => {
    secs--;
    if (countEl) countEl.textContent = secs;
    if (barEl) barEl.style.width = (secs / 30 * 100) + '%';
    if (secs <= 0) { clearInterval(timer); window.location.reload(); }
}, 1000);
@endsection
