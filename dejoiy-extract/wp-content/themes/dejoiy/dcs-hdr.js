(function(){
    var hdr=document.getElementById('dcs-hdr'),
        burger=document.getElementById('dcs-burger'),
        drawer=document.getElementById('dcs-drawer'),
        overlay=document.getElementById('dcs-overlay'),
        closeBtn=document.getElementById('dcs-drawer-close'),
        sIn=document.getElementById('dcs-search-in'),
        sDrop=document.getElementById('dcs-search-drop');
    if(!hdr)return;

    /* Scroll glass */
    window.addEventListener('scroll',function(){hdr.classList.toggle('scrolled',window.scrollY>40);},{passive:true});

    /* Burger */
    function openD(){drawer.classList.add('open');overlay.classList.add('open');burger.setAttribute('aria-expanded','true');burger.children[0].style.transform='rotate(45deg) translate(5px,5px)';burger.children[1].style.opacity='0';burger.children[2].style.transform='rotate(-45deg) translate(5px,-5px)';}
    function closeD(){drawer.classList.remove('open');overlay.classList.remove('open');burger.setAttribute('aria-expanded','false');burger.children[0].style.transform='';burger.children[1].style.opacity='';burger.children[2].style.transform='';}
    burger.addEventListener('click',function(){drawer.classList.contains('open')?closeD():openD();});
    overlay.addEventListener('click',closeD);
    if(closeBtn)closeBtn.addEventListener('click',closeD);
    document.addEventListener('keydown',function(e){if(e.key==='Escape'){closeD();if(sDrop)sDrop.hidden=true;}});

    /* Live Search */
    if(sIn&&sDrop){
      var stimer=null,lastQ='';
      function render(data,q){
        if(!data||!data.length){sDrop.innerHTML='<div class="dcs-sd-msg">No results for "'+q+'"</div>';sDrop.hidden=false;return;}
        var prods=data.filter(function(x){return x.subtype==='product';});
        var pages=data.filter(function(x){return x.subtype!=='product';});
        var h='';
        if(prods.length){h+='<div class="dcs-sd-hd">Products</div>';prods.slice(0,6).forEach(function(it){h+='<a class="dcs-sd-item" href="'+it.url+'"><div class="dcs-sd-thumb"></div><div><div class="dcs-sd-name">'+it.title+'</div><div class="dcs-sd-type">Product</div></div></a>';});}
        if(pages.length){h+='<div class="dcs-sd-hd">Pages</div>';pages.slice(0,3).forEach(function(it){h+='<a class="dcs-sd-item" href="'+it.url+'"><div class="dcs-sd-thumb"></div><div><div class="dcs-sd-name">'+it.title+'</div><div class="dcs-sd-type">Page</div></div></a>';});}
        h+='<a class="dcs-sd-all" href="/?s='+encodeURIComponent(q)+'">View all results &rarr;</a>';
        sDrop.innerHTML=h;sDrop.hidden=false;
      }
      function doSearch(q){
        if(q===lastQ)return;lastQ=q;
        sDrop.innerHTML='<div class="dcs-sd-spin">Searching…</div>';sDrop.hidden=false;
        var base='https://dejoiy.com/wp-json/wp/v2/search?search='+encodeURIComponent(q)+'&per_page=10&_fields=id,title,url,subtype,type';
        fetch(base).then(function(r){return r.ok?r.json():[];}).then(function(d){render(d,q);}).catch(function(){sDrop.innerHTML='<div class="dcs-sd-msg">Search unavailable</div>';sDrop.hidden=false;});
      }
      sIn.addEventListener('input',function(){clearTimeout(stimer);var q=this.value.trim();if(q.length<2){sDrop.hidden=true;lastQ='';return;}stimer=setTimeout(function(){doSearch(q);},300);});
      sIn.addEventListener('focus',function(){if(this.value.trim().length>=2)doSearch(this.value.trim());});
      document.addEventListener('click',function(e){if(!e.target.closest('#dcs-search-form'))sDrop.hidden=true;});
    }

    /* 3D tilt on nav */
    document.querySelectorAll('#dcs-hdr-list > li > a').forEach(function(a){
      a.addEventListener('mouseenter',function(){this.style.transition='transform .1s ease-out';});
      a.addEventListener('mousemove',function(e){var r=this.getBoundingClientRect(),rx=((e.clientY-r.top)/r.height-.5)*24,ry=((e.clientX-r.left)/r.width-.5)*-24;this.style.transform='perspective(460px) rotateX('+rx+'deg) rotateY('+ry+'deg) translateZ(7px)';});
      a.addEventListener('mouseleave',function(){this.style.transition='transform .5s cubic-bezier(.2,1,.4,1)';this.style.transform='';});
    });

    /* Stagger entrance */
    document.querySelectorAll('#dcs-hdr-list > li').forEach(function(li,i){li.style.opacity='0';li.style.transform='translateY(-7px)';li.style.transition='opacity .36s '+(0.055*i+0.04)+'s ease,transform .36s '+(0.055*i+0.04)+'s ease';requestAnimationFrame(function(){li.style.opacity='1';li.style.transform='translateY(0)';});});
    var logo=document.getElementById('dcs-logo');
    if(logo){logo.style.opacity='0';logo.style.transform='translateX(-12px)';logo.style.transition='opacity .42s .04s ease,transform .42s .04s ease';requestAnimationFrame(function(){logo.style.opacity='1';logo.style.transform='translateX(0)';});}

    /* Sale badge pulse */
    var sale=document.getElementById('dcs-sale-badge');
    if(sale&&sale.animate){sale.animate([{boxShadow:'0 0 14px rgba(124,92,228,.24)'},{boxShadow:'0 0 26px rgba(232,96,154,.5)'},{boxShadow:'0 0 14px rgba(124,92,228,.24)'}],{duration:2600,iterations:Infinity});}

    /* Icon glow */
    document.querySelectorAll('.dcs-icon-link').forEach(function(el){
      el.addEventListener('mouseenter',function(){this.style.filter='drop-shadow(0 0 7px rgba(124,92,228,.44))';});
      el.addEventListener('mouseleave',function(){this.style.filter='';});
    });
  })();

    /* ── Explore dropdown ── */
    var expBtn=document.getElementById('dcs-explore-btn'),
        expPanel=document.getElementById('dcs-explore-panel');
    if(expBtn&&expPanel){
      function toggleExp(force){
        var open=expPanel.classList.contains('dcs-exp-open');
        if(force===false||(force===undefined&&open)){
          expPanel.classList.remove('dcs-exp-open');
          expBtn.classList.remove('dcs-exp-active');
          expBtn.setAttribute('aria-expanded','false');
        } else {
          expPanel.hidden=false;
          expPanel.classList.add('dcs-exp-open');
          expBtn.classList.add('dcs-exp-active');
          expBtn.setAttribute('aria-expanded','true');
        }
      }
      expBtn.addEventListener('click',function(e){e.stopPropagation();toggleExp();});
      document.addEventListener('click',function(e){if(!e.target.closest('#dcs-explore-panel')&&!e.target.closest('#dcs-explore-btn'))toggleExp(false);});
      document.addEventListener('keydown',function(e){if(e.key==='Escape')toggleExp(false);});
    }

    /* ── Shop dropdown: click-toggle for touch devices ── */
    document.querySelectorAll('.has-sub').forEach(function(li){
      var a=li.querySelector('a'),sub=li.querySelector('.dcs-sub');
      if(!a||!sub)return;
      var isTouchOpen=false;
      a.addEventListener('click',function(e){
        var isMobile=window.innerWidth<=1024;
        if(isMobile){e.preventDefault();isTouchOpen=!isTouchOpen;sub.style.display=isTouchOpen?'block':'none';}
      });
      document.addEventListener('click',function(e){if(!e.target.closest('.has-sub')){sub.style.display='';isTouchOpen=false;}});
    });

    /* ── Mobile nav card clicks (overlay + redirect) ── */
    (function(){
      var nav=document.getElementById('djNav'),
          scrl=document.getElementById('djScroll'),
          ovl=document.getElementById('djOverlay');
      if(!nav)return;
      if(!sessionStorage.getItem('dj_h')){
        setTimeout(function(){scrl&&scrl.classList.add('show-hint');sessionStorage.setItem('dj_h','1');},800);
        scrl&&scrl.addEventListener('animationend',function(){scrl.classList.remove('show-hint');});
      }
      var lastActive=localStorage.getItem('dj_a');
      if(lastActive){var ac=nav.querySelector('[data-nav="'+lastActive+'"]');if(ac)ac.classList.add('active');}
      nav.querySelectorAll('.dejoiy-nav-card').forEach(function(c){
        c.addEventListener('click',function(e){
          e.preventDefault();
          var href=c.getAttribute('data-href'),nav_key=c.getAttribute('data-nav');
          if(!href)return;
          localStorage.setItem('dj_a',nav_key);
          nav.querySelectorAll('.dejoiy-nav-card').forEach(function(x){x.classList.remove('active');});
          c.classList.add('active');
          if(ovl){ovl.classList.add('active');}
          setTimeout(function(){window.location.href=href;},900);
        });
      });
    })();

    /* Mobile AJAX search & bottom nav menu */
    (function(){
      var mI=document.getElementById('dcs-msearch-in'),mD=document.getElementById('dcs-msearch-drop'),mF=document.getElementById('dcs-msearch-form');
      if(!mI||!mD||!mF)return;
      var mt=null,mL='',au=location.protocol+'//'+location.host+'/wp-admin/admin-ajax.php';
      function mRnd(d,q){
        if(!d||!d.length){mD.innerHTML='<div class="dcs-msd-msg">No results</div>';mD.hidden=false;return;}
        var h='',pr=d.filter(function(x){return x.type==='product';}),pg=d.filter(function(x){return x.type==='page';});
        if(pr.length){h+='<div class="dcs-msd-hd">Products</div>';pr.slice(0,6).forEach(function(it){h+='<a class="dcs-msd-prod" href="'+it.url+'">'+(it.thumb?'<img class="dcs-msd-thumb" src="'+it.thumb+'" alt="" loading="lazy">':'<div class="dcs-msd-thumb"></div>')+'<div class="dcs-msd-info"><div class="dcs-msd-name">'+it.title+'</div>'+(it.price?'<div class="dcs-msd-price">'+it.price+'</div>':'')+'</div></a>';}); }
        if(pg.length){h+='<div class="dcs-msd-hd">Pages</div>';pg.slice(0,3).forEach(function(it){h+='<a class="dcs-msd-page" href="'+it.url+'">'+it.title+'</a>';});}
        h+='<a class="dcs-msd-all" href="/shop/?s='+encodeURIComponent(q)+'&post_type=product">See all →</a>';
        mD.innerHTML=h;mD.hidden=false;
      }
      function mSrch(q){if(q===mL)return;mL=q;mD.innerHTML='<div class="dcs-msd-spin">Searching…</div>';mD.hidden=false;
        fetch(au+'?action=dcs_search&term='+encodeURIComponent(q)).then(function(r){return r.ok?r.json():[];}).then(function(d){mRnd(d,q);}).catch(function(){mD.innerHTML='<div class="dcs-msd-spin">Unavailable</div>';mD.hidden=false;});}
      mI.addEventListener('input',function(){clearTimeout(mt);var q=this.value.trim();if(q.length<2){mD.hidden=true;mL='';return;}mt=setTimeout(function(){mSrch(q);},320);});
      mI.addEventListener('focus',function(){if(this.value.trim().length>=2)mSrch(this.value.trim());});
      mF.addEventListener('submit',function(){mD.hidden=true;});
      document.addEventListener('click',function(e){if(!e.target.closest('#dcs-msearch-form'))mD.hidden=true;});
      var bm=document.getElementById('dcs-bn-menu'),bg=document.getElementById('dcs-burger');
      if(bm&&bg){bm.addEventListener('click',function(){bg.click();});}
    })();