@extends('errors.layout')

@section('title', '500 – Server Error')
@section('accent',  '#ef4444')
@section('accent2', '#f87171')
@section('glow',    'rgba(239,68,68,0.28)')

@section('emoji', '💥')
@section('emoji-pool', "['🔥','💣','😱','🤯','🧨','🤖','🛸','👾','☠️','🫠']")
@section('code',     '500')
@section('headline', "The server had a meltdown! 🔥")
@section('sub',      "Something exploded on our end. Don't worry — we already felt bad about it. Our developers have been paged, and they are not happy. Neither are we.")

@section('buttons')
    <button onclick="window.location.reload()" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Try Again
    </button>
    <a href="{{ url('/') }}" class="btn btn-ghost">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Escape to Safety
    </button>
@endsection

@section('extra')
<div style="margin-top:1.5rem;">
    {{-- Fake terminal --}}
    <div style="background:#0a0a0f;border:1px solid rgba(239,68,68,0.25);border-radius:1rem;padding:1rem 1.2rem;text-align:left;font-family:'JetBrains Mono',monospace;font-size:.72rem;line-height:1.8;overflow:hidden;">
        <div style="display:flex;gap:.4rem;margin-bottom:.7rem;">
            <span style="width:10px;height:10px;border-radius:50%;background:#ef4444;"></span>
            <span style="width:10px;height:10px;border-radius:50%;background:#f59e0b;"></span>
            <span style="width:10px;height:10px;border-radius:50%;background:#22c55e;"></span>
        </div>
        <div id="terminal-lines"></div>
    </div>
    <p style="font-size:.73rem;color:rgba(232,232,240,.3);margin-top:.6rem;">📡 Our team has been notified automatically.</p>
</div>
@endsection

@section('xstyle')
.terminal-line { color: rgba(232,232,240,.65); }
.terminal-line.error { color: #f87171; }
.terminal-line.warn  { color: #fbbf24; }
.terminal-line.ok    { color: #34d399; }
.cursor { display:inline-block;width:8px;height:1em;background:#ef4444;animation:blink .8s step-end infinite;vertical-align:middle;margin-left:2px; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:0} }
@endsection

@section('xscript')
// Fake terminal output
const lines = [
    { text: '$ server --status', cls: '' },
    { text: '[ERROR] Unhandled exception in core/engine.js:42', cls: 'error' },
    { text: '[WARN]  Memory pressure detected (97.3% used)', cls: 'warn' },
    { text: '[ERROR] Stack trace: NullPointerException at 0x0000', cls: 'error' },
    { text: '[INFO]  Attempting emergency restart…', cls: '' },
    { text: '[WARN]  Coffee supply critically low in server room', cls: 'warn' },
    { text: '[ERROR] Restart failed — server is being dramatic', cls: 'error' },
    { text: '[INFO]  Paging on-call engineer (they are not pleased)', cls: '' },
    { text: '[OK]    Incident report filed. We\'re sorry. 🙏', cls: 'ok' },
];
const termEl = document.getElementById('terminal-lines');
let li = 0;
function typeNextLine() {
    if (!termEl || li >= lines.length) {
        if (termEl) {
            const cur = document.createElement('span');
            cur.className = 'cursor';
            termEl.appendChild(cur);
        }
        return;
    }
    const { text, cls } = lines[li++];
    const div = document.createElement('div');
    div.className = 'terminal-line ' + cls;
    termEl.appendChild(div);
    let ci = 0;
    const typeChar = () => {
        if (ci < text.length) {
            div.textContent += text[ci++];
            setTimeout(typeChar, Math.random() * 28 + 12);
        } else {
            setTimeout(typeNextLine, Math.random() * 300 + 150);
        }
    };
    typeChar();
}
setTimeout(typeNextLine, 600);
@endsection
