/* DEJOIY CUSTOM STUDIO v7 — Cinematic 3D Engine */
  (function(){
  'use strict';
  function boot(){
    if(typeof gsap==='undefined'||typeof ScrollTrigger==='undefined'||typeof Lenis==='undefined'||typeof VanillaTilt==='undefined'){setTimeout(boot,80);return;}
    run();
  }
  function run(){
    var wrap=document.getElementById('dcs');
    if(wrap)wrap.style.perspective='1400px';

      /* ── Inject background video into hero ── */
      (function(){
        var hero=document.getElementById('dcs-hero');if(!hero)return;
        // Wrapper
        var vw=document.createElement('div');vw.id='dcs-vid-wrap';
        vw.style.cssText='position:absolute;inset:0;overflow:hidden;z-index:0;pointer-events:none;';
        // Video
        var vid=document.createElement('video');vid.id='dcs-vid';
        vid.src='https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260328_065045_c44942da-53c6-4804-b734-f9e07fc22e08.mp4';
        vid.muted=true;vid.playsInline=true;vid.preload='auto';vid.setAttribute('playsinline','');
        vid.style.cssText='position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:0;';
        vw.appendChild(vid);hero.insertBefore(vw,hero.firstChild);
        // Blur overlay
        var bl=document.createElement('div');
        bl.style.cssText='position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:984px;height:527px;background:#030712;opacity:.9;filter:blur(82px);border-radius:50%;pointer-events:none;z-index:1;';
        hero.insertBefore(bl,vw.nextSibling);
        // Fix hero overflow
        hero.style.overflow='visible';
        // Fade loop using requestAnimationFrame
        var fadeT=500;
        function fadeIn(){
          var s=null;
          (function step(ts){if(!s)s=ts;var p=Math.min((ts-s)/fadeT,1);vid.style.opacity=p;if(p<1)requestAnimationFrame(step);})();
        }
        function fadeOut(cb){
          var s=null,o=parseFloat(vid.style.opacity)||1;
          (function step(ts){if(!s)s=ts;var p=Math.min((ts-s)/fadeT,1);vid.style.opacity=o*(1-p);if(p<1){requestAnimationFrame(step);}else{cb();}})();
        }
        function replay(){vid.style.opacity=0;vid._fading=false;vid.currentTime=0;vid.play().then(fadeIn).catch(function(){});}
        vid.addEventListener('timeupdate',function(){
          var rem=(vid.duration||0)-vid.currentTime;
          if(rem>0&&rem<=0.55&&!vid._fading){vid._fading=true;fadeOut(function(){setTimeout(replay,100);});}
        });
        vid.addEventListener('ended',function(){if(!vid._fading){vid.style.opacity=0;setTimeout(replay,100);}});
        vid.play().then(fadeIn).catch(function(){});
      })();

    /* Lenis + ScrollTrigger bridge */
    var ln=new Lenis({duration:1.2,easing:function(t){return Math.min(1,1.001-Math.pow(2,-10*t));},smoothWheel:true,wheelMultiplier:.88});
    gsap.registerPlugin(ScrollTrigger);
    gsap.ticker.add(function(t){ln.raf(t*1000);});
    gsap.ticker.lagSmoothing(0);
    ln.on('scroll',function(){ScrollTrigger.update();});

    /* Particle canvas */
    (function(){
      var cv=document.getElementById('dcs-cv');if(!cv)return;
      var ctx=cv.getContext('2d'),W,H,mx,my,pts;
      function resize(){W=cv.width=window.innerWidth;H=cv.height=window.innerHeight;mx=W/2;my=H/2;}
      resize();window.addEventListener('resize',resize);
      window.addEventListener('mousemove',function(e){mx=e.clientX;my=e.clientY;},{passive:true});
      pts=Array.from({length:90},function(){return{x:Math.random()*W,y:Math.random()*H,vx:(Math.random()-.5)*.3,vy:(Math.random()-.5)*.3,r:Math.random()*1.6+.4,a:Math.random()*.7+.15,g:Math.random()>.5};});
      (function draw(){
        ctx.clearRect(0,0,W,H);
        for(var i=0;i<pts.length;i++){
          var p=pts[i];p.x+=p.vx;p.y+=p.vy;
          if(p.x<0)p.x=W;if(p.x>W)p.x=0;if(p.y<0)p.y=H;if(p.y>H)p.y=0;
          var dx=mx-p.x,dy=my-p.y,d=Math.sqrt(dx*dx+dy*dy);
          if(d<140){p.vx-=dx*.0002;p.vy-=dy*.0002;}
          ctx.beginPath();ctx.arc(p.x,p.y,p.r,0,Math.PI*2);
          ctx.fillStyle=p.g?'rgba(124,92,228,'+p.a*.75+')':'rgba(255,255,255,'+p.a*.18+')';ctx.fill();
          if(p.g){for(var j=i+1;j<pts.length;j++){var q=pts[j];if(q.g){var dx2=p.x-q.x,dy2=p.y-q.y,d2=Math.sqrt(dx2*dx2+dy2*dy2);if(d2<110){ctx.beginPath();ctx.moveTo(p.x,p.y);ctx.lineTo(q.x,q.y);ctx.strokeStyle='rgba(124,92,228,'+(1-d2/110)*.14+')';ctx.lineWidth=.5;ctx.stroke();}}}}
        }
        requestAnimationFrame(draw);
      })();
    })();

    /* Custom cursor */
    (function(){
      var cur=document.getElementById('dcs-cursor'),dot=document.getElementById('dcs-cursor-dot');if(!cur)return;
      var cx=window.innerWidth/2,cy=window.innerHeight/2,lx=cx,ly=cy;
      window.addEventListener('mousemove',function(e){cx=e.clientX;cy=e.clientY;if(dot){dot.style.left=cx-2+'px';dot.style.top=cy-2+'px';}},{passive:true});
      (function ac(){lx+=(cx-lx)*.1;ly+=(cy-ly)*.1;cur.style.left=lx-18+'px';cur.style.top=ly-18+'px';requestAnimationFrame(ac);})();
      document.querySelectorAll('#dcs a,#dcs button,.dcs-bt-card,.dcs-car-card').forEach(function(el){
        el.addEventListener('mouseenter',function(){cur.style.transform='scale(2.2)';cur.style.borderColor='rgba(232,96,154,.9)';});
        el.addEventListener('mouseleave',function(){cur.style.transform='scale(1)';cur.style.borderColor='';});
      });
    })();

    /* Progress bar + scroll-to-top */
    var pb=document.getElementById('dcs-pb');
    window.addEventListener('scroll',function(){if(pb)pb.style.width=(window.scrollY/(document.body.scrollHeight-window.innerHeight)*100)+'%';},{passive:true});
    var tt=document.getElementById('dcs-tt');
    if(tt){window.addEventListener('scroll',function(){tt.classList.toggle('on',window.scrollY>500);},{passive:true});tt.addEventListener('click',function(){ln.scrollTo(0,{duration:1.4});});}

    /* Orb mouse parallax */
    var orbs=document.querySelectorAll('.dcs-o1,.dcs-o2,.dcs-o3,.dcs-o4,.dcs-o5');
    document.addEventListener('mousemove',function(e){var rx=(e.clientX/window.innerWidth-.5)*2,ry=(e.clientY/window.innerHeight-.5)*2;orbs.forEach(function(o,i){gsap.to(o,{x:rx*(i+1)*22,y:ry*(i+1)*22,duration:1.8,ease:'power2.out'});});});

    /* Smooth anchor scroll */
    document.querySelectorAll('a[href^="#dcs"]').forEach(function(a){a.addEventListener('click',function(e){var t=document.querySelector(a.getAttribute('href'));if(t){e.preventDefault();ln.scrollTo(t,{duration:1.4,offset:-90});}});});

    /* Mobile nav */
    var mnIds=['dcs-hero','dcs-bento','dcs-ai','dcs-process','dcs-cta'];
    function setMobNav(){var sy=window.scrollY+window.innerHeight*.35,active='dcs-hero';mnIds.forEach(function(id){var el=document.getElementById(id);if(el&&el.offsetTop<=sy)active=id;});document.querySelectorAll('.dcs-mn-a').forEach(function(a){a.classList.toggle('active',a.dataset.section===active);});}
    window.addEventListener('scroll',setMobNav,{passive:true});setMobNav();

    /* ═══════ 3D GSAP ANIMATIONS ═══════ */
    /* Pre-hide all animated elements */
    var heroEls=document.querySelectorAll('#dcs-hero [data-ga]');
    var scrollEls=[];
    document.querySelectorAll('[data-ga]').forEach(function(el){if(!el.closest('#dcs-hero'))scrollEls.push(el);});
    var btCards=document.querySelectorAll('.dcs-bt-card');
    var procSteps=document.querySelectorAll('.dcs-proc-step');
    var crCards=document.querySelectorAll('.dcs-cr-card');
    var statBoxes=document.querySelectorAll('.dcs-stat-box');
    var floats=document.querySelectorAll('.dcs-hero-float');

    gsap.set(heroEls,{opacity:0,y:80,rotateX:32,transformPerspective:1200,transformOrigin:'center bottom',force3D:true});
    gsap.set(floats,{opacity:0,y:25,force3D:true});
    gsap.set(btCards,{opacity:0,y:55,rotateX:20,scale:.93,transformPerspective:900,transformOrigin:'center bottom',force3D:true});
    gsap.set(crCards,{opacity:0,y:45,rotateX:18,transformPerspective:800,force3D:true});
    gsap.set(statBoxes,{opacity:0,y:40,scale:.95,force3D:true});
    scrollEls.forEach(function(el){
      var ga=el.getAttribute('data-ga');
      if(ga==='fu')gsap.set(el,{opacity:0,y:65,rotateX:28,transformPerspective:1100,transformOrigin:'center bottom',force3D:true});
      else if(ga==='fr')gsap.set(el,{opacity:0,x:-80,rotateY:22,transformPerspective:1000,transformOrigin:'right center',force3D:true});
      else if(ga==='fl')gsap.set(el,{opacity:0,x:80,rotateY:-22,transformPerspective:1000,transformOrigin:'left center',force3D:true});
    });

    /* Hero entrance */
    if(heroEls.length){
      gsap.to(heroEls,{opacity:1,y:0,rotateX:0,duration:1.35,ease:'power3.out',force3D:true,stagger:{each:.18,from:'start'},delay:.2});
      gsap.to(floats,{opacity:1,y:0,duration:1.1,ease:'power2.out',stagger:.2,delay:.9});
    }

    /* Scroll reveals */
    scrollEls.forEach(function(el){
      var ga=el.getAttribute('data-ga'),dl=parseFloat(el.dataset.dl||0)/1000;
      var to={opacity:1,duration:1.1,delay:dl,ease:'power3.out',force3D:true,scrollTrigger:{trigger:el,start:'top 85%',toggleActions:'play none none none'}};
      if(ga==='fu'){to.y=0;to.rotateX=0;}
      if(ga==='fr'){to.x=0;to.rotateY=0;}
      if(ga==='fl'){to.x=0;to.rotateY=0;}
      gsap.to(el,to);
    });

    /* Bento cards cascade */
    btCards.forEach(function(c,i){
      gsap.to(c,{opacity:1,y:0,rotateX:0,scale:1,duration:1.05,delay:i*.07,ease:'back.out(1.5)',force3D:true,
        scrollTrigger:{trigger:c,start:'top 90%',toggleActions:'play none none none'}});
    });

    /* Process steps alternating flip */
    procSteps.forEach(function(s,i){
      var from=(i%2===0)?{x:-70,rotateY:20}:{x:70,rotateY:-20};
      gsap.set(s,Object.assign({opacity:0,transformPerspective:900,force3D:true},from));
      gsap.to(s,{opacity:1,x:0,rotateY:0,duration:1.0,ease:'power3.out',force3D:true,
        scrollTrigger:{trigger:s,start:'top 85%',toggleActions:'play none none none'}});
    });

    /* Creator cards */
    crCards.forEach(function(c,i){
      gsap.to(c,{opacity:1,y:0,rotateX:0,duration:.95,delay:i*.12,ease:'power3.out',force3D:true,
        scrollTrigger:{trigger:c,start:'top 88%',toggleActions:'play none none none'}});
    });

    /* Stat boxes pop */
    statBoxes.forEach(function(b,i){
      gsap.to(b,{opacity:1,y:0,scale:1,duration:.9,delay:i*.1,ease:'back.out(1.6)',force3D:true,
        scrollTrigger:{trigger:b,start:'top 88%',toggleActions:'play none none none'}});
    });

    /* Animated counters */
    document.querySelectorAll('.dcs-stat-n').forEach(function(el){
      var tgt=parseInt(el.dataset.cnt||0);
      ScrollTrigger.create({trigger:el,start:'top 90%',once:true,onEnter:function(){
        gsap.to({v:0},{v:tgt,duration:2.4,ease:'power2.out',onUpdate:function(){el.textContent=Math.round(this.targets()[0].v).toLocaleString('en-IN');}});
      }});
    });

    /* VanillaTilt */
    VanillaTilt.init(document.querySelectorAll('.dcs-bt-card'),{max:12,speed:280,glare:true,'max-glare':.22,scale:1.04,perspective:600});
    VanillaTilt.init(document.querySelectorAll('.dcs-car-card'),{max:10,speed:300,glare:true,'max-glare':.18,scale:1.05,perspective:650});
    VanillaTilt.init(document.querySelectorAll('.dcs-cr-card'),{max:6,speed:350,'max-glare':.1,scale:1.02});

    /* Magnetic buttons */
    document.querySelectorAll('.dcs-mag').forEach(function(b){
      b.addEventListener('mousemove',function(e){var r=b.getBoundingClientRect();gsap.to(b,{x:(e.clientX-r.left-r.width/2)*.38,y:(e.clientY-r.top-r.height/2)*.38,duration:.4,ease:'power2.out'});});
      b.addEventListener('mouseleave',function(){gsap.to(b,{x:0,y:0,duration:.6,ease:'elastic.out(1,.5)'});});
    });

    /* Drag-scroll carousel */
    (function(){
      var wrap=document.querySelector('.dcs-car-wrap');if(!wrap)return;
      var isDown=false,startX,scrollL;
      wrap.addEventListener('mousedown',function(e){isDown=true;wrap.classList.add('grabbing');startX=e.pageX-wrap.offsetLeft;scrollL=wrap.scrollLeft;});
      wrap.addEventListener('mouseleave',function(){isDown=false;wrap.classList.remove('grabbing');});
      wrap.addEventListener('mouseup',function(){isDown=false;wrap.classList.remove('grabbing');});
      wrap.addEventListener('mousemove',function(e){if(!isDown)return;e.preventDefault();var x=e.pageX-wrap.offsetLeft;wrap.scrollLeft=scrollL-(x-startX)*1.4;});
    })();

    /* Terminal typing animation */
    (function(){
      var lines=document.querySelectorAll('.dcs-term-line[data-delay]');
      lines.forEach(function(l){
        var d=parseFloat(l.getAttribute('data-delay')||0);
        l.style.opacity='0';
        ScrollTrigger.create({trigger:l,start:'top 90%',once:true,onEnter:function(){
          setTimeout(function(){gsap.to(l,{opacity:1,duration:.4});},d*1000);
        }});
      });
    })();

    /* Orb section parallax scrub */
    ScrollTrigger.create({trigger:'#dcs',start:'top top',end:'bottom bottom',scrub:.7,
      onUpdate:function(self){gsap.set('.dcs-bg-w',{y:self.progress*-65});}});

    setTimeout(function(){ScrollTrigger.refresh();},500);
  }
  boot();
  })();
