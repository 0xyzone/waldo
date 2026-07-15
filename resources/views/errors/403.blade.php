@extends('errors.layout')

@section('title', '403 – Forbidden')
@section('accent',  '#f97316')
@section('accent2', '#fb923c')
@section('glow',    'rgba(249,115,22,0.28)')

@section('emoji', '🚫')
@section('emoji-pool', "['🔒','🛑','🙅','👮','🚷','⛔','🤐','🦺','🕵️','🔐']")
@section('code',     '403')
@section('headline', "Whoa there, cowboy! 🤠")
@section('sub',      "You don't have permission to view this page. Either you're lost, very sneaky, or both. We're not judging. (We're a little judging.)")

@section('buttons')
    <a href="{{ url('/') }}" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Take Me Home
    </a>
    <button onclick="history.back()" class="btn btn-ghost">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Go Back
    </button>
@endsection

@section('extra')
<div style="margin-top:1.5rem;padding:.9rem 1.2rem;background:rgba(249,115,22,0.1);border:1px solid rgba(249,115,22,0.25);border-radius:1rem;font-size:.82rem;color:rgba(255,255,255,.6);line-height:1.6;">
    🔑 If you believe you <em>should</em> have access, please contact your system administrator — or perform the secret knock. We accept both.
</div>
@endsection

@section('xstyle')
.code { font-size: 7rem !important; }
@endsection
