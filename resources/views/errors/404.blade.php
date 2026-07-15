@extends('errors.layout')

@section('title', '404 – Not Found')
@section('accent',  '#a78bfa')
@section('accent2', '#818cf8')
@section('glow',    'rgba(167,139,250,0.28)')

@section('emoji', '🔍')
@section('emoji-pool', "['🗺️','🧭','👀','🕵️','🤷','😵','🌌','🪐','🚀','🛸']")
@section('code',     '404')
@section('headline', "Lost in the void! 🌌")
@section('sub',      "The page you're looking for packed its bags and left. It said something about \"finding itself.\" Very dramatic. Very gone.")

@section('buttons')
    <a href="{{ url('/') }}" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Beam Me Home
    </a>
    <button onclick="history.back()" class="btn btn-ghost">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Retrace Steps
    </button>
@endsection

@section('extra')
<div id="search-404" style="margin-top:1.5rem;">
    <input
        id="lost-search"
        type="text"
        placeholder="Search for what you lost… 🔦"
        style="width:100%;padding:.75rem 1rem;background:rgba(255,255,255,.06);border:1px solid rgba(167,139,250,.25);border-radius:.8rem;color:#e8e8f0;font-family:Outfit,sans-serif;font-size:.9rem;outline:none;transition:border .2s;"
        onfocus="this.style.borderColor='rgba(167,139,250,.7)'"
        onblur="this.style.borderColor='rgba(167,139,250,.25)'"
        onkeydown="if(event.key==='Enter'){ const v=this.value.trim(); if(v) window.location='/?q='+encodeURIComponent(v); }"
    >
    <p style="font-size:.75rem;color:rgba(232,232,240,.3);margin-top:.5rem;">Press Enter to search the universe ✨</p>
</div>
<div style="margin-top:1rem;" id="waldo-hunt">
    <p style="font-size:.85rem;color:rgba(232,232,240,.45);">👀 Can you find where the page went?</p>
    <div id="waldo-grid" style="display:flex;flex-wrap:wrap;gap:4px;justify-content:center;margin-top:.5rem;max-height:80px;overflow:hidden;"></div>
</div>
@endsection

@section('xscript')
// ── Waldo easter egg grid ────────────────────────────────────────────────
(function(){
    const grid = document.getElementById('waldo-grid');
    const items = ['📄','📃','📝','📋','🗒️','📑','📜','📄','📃','📄','📝','📋','📄','📃','🗒️','📑','📄','📝','🎯','📄','📃','📋','📄','📃','📝'];
    items.forEach((em, i) => {
        const el = document.createElement('div');
        el.textContent = em;
        el.style.cssText = 'font-size:1.3rem;cursor:pointer;transition:transform .15s;border-radius:4px;padding:2px;';
        el.title = em === '🎯' ? 'Found it! 🎉' : 'Nope...';
        el.addEventListener('mouseenter', () => el.style.transform = 'scale(1.3)');
        el.addEventListener('mouseleave', () => el.style.transform = 'scale(1)');
        el.addEventListener('click', () => {
            if (em === '🎯') {
                el.textContent = '🎉';
                const msg = document.createElement('p');
                msg.textContent = '🥳 YOU FOUND IT! Too bad it\'s not the page you wanted.';
                msg.style.cssText = 'font-size:.8rem;color:#a78bfa;margin-top:.4rem;font-weight:700;';
                document.getElementById('waldo-hunt').appendChild(msg);
            } else {
                el.style.transform = 'rotate(180deg) scale(.8)';
                setTimeout(() => el.style.transform = '', 400);
            }
        });
        grid.appendChild(el);
    });
})();
@endsection
