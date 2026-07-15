<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Oops!') — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800;900&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --accent:  @yield('accent',  '#a78bfa');
            --accent2: @yield('accent2', '#818cf8');
            --glow:    @yield('glow',    'rgba(167,139,250,0.3)');
        }
        html, body {
            height: 100%;
            font-family: 'Outfit', sans-serif;
            background: #0d0d1a;
            color: #e8e8f0;
            overflow: hidden;
        }
        #stars { position: fixed; inset: 0; z-index: 0; pointer-events: none; }
        .orb {
            position: fixed; border-radius: 50%; filter: blur(90px);
            pointer-events: none; z-index: 0;
            animation: drift 14s ease-in-out infinite alternate;
        }
        .orb-a { width:520px;height:520px;background:var(--accent);top:-160px;left:-120px;opacity:.15;animation-delay:0s; }
        .orb-b { width:420px;height:420px;background:var(--accent2);bottom:-130px;right:-90px;opacity:.15;animation-delay:-5s; }
        .orb-c { width:280px;height:280px;background:var(--accent);top:45%;left:50%;transform:translate(-50%,-50%);opacity:.07;animation-delay:-10s; }
        @keyframes drift {
            from { transform: translate(0,0) scale(1); }
            to   { transform: translate(50px,35px) scale(1.1); }
        }

        /* Card */
        .page { position:relative;z-index:10;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:1.5rem; }
        .glass {
            background: rgba(255,255,255,0.035);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 2rem;
            padding: 3.5rem 2.8rem;
            max-width: 580px;
            width: 100%;
            text-align: center;
            box-shadow: 0 0 90px var(--glow), 0 30px 70px rgba(0,0,0,0.55);
            animation: pop-in .65s cubic-bezier(.34,1.56,.64,1) both;
        }
        @keyframes pop-in {
            from { opacity:0; transform:scale(.75) translateY(40px); }
            to   { opacity:1; transform:scale(1) translateY(0); }
        }

        /* Emoji */
        .emoji { font-size:5.5rem; display:block; margin-bottom:.9rem; cursor:pointer;
                 animation:wiggle 2.6s ease-in-out infinite;
                 filter:drop-shadow(0 0 22px var(--glow)); user-select:none; }
        @keyframes wiggle {
            0%,100%{ transform:rotate(-7deg) scale(1); }
            25%    { transform:rotate(7deg) scale(1.08); }
            50%    { transform:rotate(-3deg) scale(.96); }
            75%    { transform:rotate(5deg) scale(1.04); }
        }

        /* Code */
        .code {
            font-family:'JetBrains Mono',monospace;
            font-size:7.5rem; font-weight:700; line-height:1; letter-spacing:-5px;
            background:linear-gradient(135deg, var(--accent), var(--accent2));
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
            background-clip:text;
            animation:pulse-glow 3.5s ease-in-out infinite;
        }
        @keyframes pulse-glow {
            0%,100%{ filter:drop-shadow(0 0 14px var(--glow)); }
            50%    { filter:drop-shadow(0 0 32px var(--glow)); }
        }

        h1 { font-size:1.65rem; font-weight:800; color:#e8e8f0; margin:.5rem 0 .6rem; }
        .sub { font-size:1rem; color:rgba(232,232,240,.5); line-height:1.7; margin-bottom:2rem; }

        /* Buttons */
        .btns { display:flex; gap:.7rem; justify-content:center; flex-wrap:wrap; }
        .btn {
            display:inline-flex; align-items:center; gap:.4rem;
            padding:.72rem 1.6rem; border-radius:.8rem;
            font-family:'Outfit',sans-serif; font-size:.92rem; font-weight:700;
            border:none; cursor:pointer; text-decoration:none;
            transition:transform .15s, box-shadow .15s;
        }
        .btn:hover  { transform:translateY(-3px); box-shadow:0 10px 28px rgba(0,0,0,.4); }
        .btn:active { transform:scale(.96); }
        .btn-primary { background:linear-gradient(135deg,var(--accent),var(--accent2)); color:#fff; box-shadow:0 4px 18px var(--glow); }
        .btn-ghost   { background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.1); color:#e8e8f0; }
        .btn-ghost:hover { background:rgba(255,255,255,.12); }

        /* Fun fact */
        .fact { font-size:.8rem; color:rgba(232,232,240,.45); font-style:italic; margin-top:1.6rem; min-height:1.3em; transition:opacity .5s; }

        /* Progress */
        .prog-wrap { width:100%;height:3px;background:rgba(255,255,255,.06);border-radius:99px;overflow:hidden;margin-top:1.6rem; }
        .prog-bar   { height:100%;width:0;background:linear-gradient(90deg,var(--accent),var(--accent2));border-radius:99px;
                      box-shadow:0 0 10px var(--glow); animation:fill-bar 6s linear forwards; }
        @keyframes fill-bar { to { width:100%; } }

        /* Particles */
        #particles { position:fixed;inset:0;pointer-events:none;z-index:1; }
        .pt {
            position:absolute; border-radius:50%;
            animation:float-pt linear infinite; opacity:0;
        }
        @keyframes float-pt {
            0%  { opacity:0; transform:translateY(0) scale(.5); }
            8%  { opacity:.9; }
            92% { opacity:.5; }
            100%{ opacity:0; transform:translateY(-110vh) scale(1.3); }
        }

        /* Konami hint */
        .secret { position:fixed;bottom:1.2rem;right:1.5rem;font-size:.65rem;
                  color:rgba(255,255,255,.12);font-family:'JetBrains Mono',monospace;z-index:20; }

        @yield('xstyle')
    </style>
</head>
<body>

<canvas id="stars"></canvas>
<div class="orb orb-a"></div>
<div class="orb orb-b"></div>
<div class="orb orb-c"></div>
<div id="particles"></div>

<div class="page">
    <div class="glass">

        <span class="emoji" id="emoji" title="Click me!">@yield('emoji','🤔')</span>
        <div class="code">@yield('code','???')</div>
        <h1>@yield('headline','Something went sideways!')</h1>
        <p class="sub">@yield('sub','We have no idea what happened. Neither does the server.')</p>

        <div class="btns">
            @yield('buttons')
        </div>

        @yield('extra')

        <p class="fact" id="fact"></p>

        <div class="prog-wrap"><div class="prog-bar"></div></div>

    </div>
</div>

<div class="secret">↑↑↓↓←→←→BA</div>

<script>
// ── Stars ─────────────────────────────────────────────────────────────────
(function(){
    const c=document.getElementById('stars'),ctx=c.getContext('2d');
    let W,H,stars=[];
    function resize(){W=c.width=innerWidth;H=c.height=innerHeight;}
    function init(){stars=Array.from({length:130},()=>({x:Math.random()*W,y:Math.random()*H,r:Math.random()*1.3+.3,a:Math.random()*.6+.2,f:Math.random()*Math.PI*2}));}
    function draw(){
        ctx.clearRect(0,0,W,H);
        stars.forEach(s=>{s.f+=.018;const a=s.a*(.7+.3*Math.sin(s.f));ctx.beginPath();ctx.arc(s.x,s.y,s.r,0,Math.PI*2);ctx.fillStyle=`rgba(255,255,255,${a})`;ctx.fill();});
        requestAnimationFrame(draw);
    }
    window.addEventListener('resize',()=>{resize();init();});
    resize();init();draw();
})();

// ── Particles ────────────────────────────────────────────────────────────
(function(){
    const box=document.getElementById('particles');
    const emojis=['✨','⭐','💫','🌟','🎉','💥','🔮','🪄','🦄','🍀'];
    const colors=['#a78bfa','#818cf8','#f472b6','#fb923c','#34d399','#38bdf8'];
    function spawn(){
        const el=document.createElement('div');
        el.className='pt';
        const useE=Math.random()>.5;
        if(useE){
            el.style.cssText=`font-size:${Math.random()*14+10}px;left:${Math.random()*100}%;bottom:-40px;animation-duration:${Math.random()*9+7}s;animation-delay:${Math.random()*3}s;border-radius:0;background:transparent;`;
            el.textContent=emojis[Math.floor(Math.random()*emojis.length)];
        }else{
            const sz=Math.random()*8+4;
            el.style.cssText=`width:${sz}px;height:${sz}px;left:${Math.random()*100}%;bottom:-40px;background:${colors[Math.floor(Math.random()*colors.length)]};animation-duration:${Math.random()*9+7}s;animation-delay:${Math.random()*3}s;`;
        }
        box.appendChild(el);
        setTimeout(()=>el.remove(),14000);
    }
    for(let i=0;i<10;i++)spawn();
    setInterval(spawn,700);
})();

// ── Fun facts ────────────────────────────────────────────────────────────
const FACTS=[
    "🐙 Octopuses have 3 hearts. So does this error page, it really cares.",
    "🦆 A duck's quack doesn't echo. This error, however, echoes through eternity.",
    "🐝 Bees flap wings 200×/sec. This error loaded faster than that. Progress!",
    "🌈 A group of flamingos is called a flamboyance. Very relatable right now.",
    "🧀 There are 1,800 types of cheese. None of them caused this error.",
    "🪐 Saturn could float on water. This error sank immediately.",
    "🎲 More chess games exist than atoms in the universe. Yet THIS happened.",
    "🐦 Crows recognise human faces. This page recognises your pain.",
    "🍕 The first pizza delivery was to a king in 1889. Ours is still en route.",
    "🦁 Lions sleep 20h/day. The server was inspired by this.",
];
const factEl=document.getElementById('fact');
function rotateFact(){
    factEl.style.opacity='0';
    setTimeout(()=>{factEl.textContent=FACTS[Math.floor(Math.random()*FACTS.length)];factEl.style.opacity='1';},500);
}
rotateFact();setInterval(rotateFact,6500);

// ── Emoji cycle ──────────────────────────────────────────────────────────
(function(){
    const el=document.getElementById('emoji');
    if(!el)return;
    const pool=@yield('emoji-pool',"['😱','🙈','🤖','👾','💀','🦄','🎭','🤯','👻','🫠','🥴','🤡']");
    let i=0;
    el.addEventListener('click',()=>{
        i=(i+1)%pool.length; el.textContent=pool[i];
        el.style.animation='none'; el.offsetHeight; el.style.animation='';
    });
    // confetti on hover
    el.addEventListener('mouseenter',()=>{
        const r=el.getBoundingClientRect();
        for(let j=0;j<16;j++){
            const p=document.createElement('div');
            p.className='pt';
            p.style.cssText=`width:7px;height:7px;left:${r.left+Math.random()*r.width}px;bottom:${innerHeight-r.top}px;background:hsl(${Math.random()*360},80%,70%);animation-duration:${Math.random()*1.5+.8}s;border-radius:2px;`;
            document.getElementById('particles').appendChild(p);
            setTimeout(()=>p.remove(),3000);
        }
    });
})();

// ── Konami code ──────────────────────────────────────────────────────────
(function(){
    const seq=[38,38,40,40,37,39,37,39,66,65]; let pos=0;
    document.addEventListener('keydown',e=>{
        pos=(e.keyCode===seq[pos])?pos+1:0;
        if(pos===seq.length){
            pos=0;
            const s=document.createElement('style');
            s.textContent='@keyframes rb{0%{filter:hue-rotate(0deg)}100%{filter:hue-rotate(360deg)}}';
            document.head.appendChild(s);
            document.body.style.animation='rb 1s linear 3';
            const t=document.createElement('div');
            t.textContent='🎉 LEGEND UNLOCKED! You found the secret!';
            t.style.cssText='position:fixed;bottom:2rem;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,#a78bfa,#f472b6);color:#fff;padding:.8rem 2rem;border-radius:1rem;font-family:Outfit,sans-serif;font-weight:800;font-size:1rem;z-index:9999;box-shadow:0 8px 32px rgba(167,139,250,.5);animation:pop-in .4s cubic-bezier(.34,1.56,.64,1) both;';
            document.body.appendChild(t);
            setTimeout(()=>t.remove(),5000);
        }
    });
})();

@yield('xscript')
</script>
</body>
</html>
