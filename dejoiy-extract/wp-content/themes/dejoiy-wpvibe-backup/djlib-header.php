<?php
if(!defined('ABSPATH'))exit;
// DEJOIY LIBRARY CUSTOM HEADER v3
add_action('wp_enqueue_scripts','djlib_enqueue');
function djlib_enqueue(){
    if(!is_page(4708))return;
    wp_enqueue_style('djlib-hdr',get_stylesheet_directory_uri().'/djlib-header.css',[],false,'all');
}
add_action('wp_ajax_djlib_my_books','djlib_my_books_handler');
function djlib_my_books_handler(){
    check_ajax_referer('djlib_nonce','nonce');
    if(!is_user_logged_in()){wp_send_json_error('not_logged_in');return;}
    $uid=get_current_user_id();
    $orders=wc_get_orders(['customer'=>$uid,'status'=>['wc-completed','wc-processing','wc-on-hold'],'limit'=>60,'orderby'=>'date','order'=>'DESC']);
    $books=[];
    foreach($orders as $order){
        foreach($order->get_items() as $item){
            $pid=$item->get_product_id();
            $terms=get_the_terms($pid,'product_cat');
            $ok=false;
            if($terms&&!is_wp_error($terms)){foreach($terms as $t){if(stripos($t->slug,'librar')!==false||stripos($t->name,'librar')!==false||stripos($t->slug,'book')!==false||stripos($t->name,'book')!==false){$ok=true;break;}}}
            if(!$ok)continue;
            $thumb=get_the_post_thumbnail_url($pid,'thumbnail')?:wc_placeholder_img_src('thumbnail');
            $d=$order->get_date_created();
            $books[]=['id'=>$pid,'title'=>$item->get_name(),'thumb'=>$thumb,'url'=>get_permalink($pid),'order_id'=>$order->get_id(),'date'=>$d?$d->date('M j, Y'):'','status'=>wc_get_order_status_name($order->get_status())];
        }
    }
    wp_send_json_success($books);
}
add_action('wp_footer','djlib_js',5);
function djlib_js(){
    if(!is_page(4708))return;
    $aj=esc_js(admin_url('admin-ajax.php'));
    $n=wp_create_nonce('djlib_nonce');
    ?><script id="djlib-js">(function(){
'use strict';
var AJ='<?=$aj?>',N='<?=$n?>',root=document.getElementById('djlib-root');
var burg=document.getElementById('djlib-burger'),mob=document.getElementById('djlib-mob-menu'),cls=document.getElementById('djlib-mob-close');
var expW=document.getElementById('djlib-explore-wrap'),expB=document.getElementById('djlib-explore-btn');
var p=window.location.pathname;
document.querySelectorAll('.djlib-navlink').forEach(function(a){var h=a.getAttribute('href');if(h&&h!=='/'&&p.indexOf(h.replace(/^https?:\/\/[^/]+/,''))===0){document.querySelectorAll('.djlib-navlink').forEach(function(x){x.classList.remove('djlib-current');});a.classList.add('djlib-current');}});
window.addEventListener('scroll',function(){if(root)root.style.boxShadow=window.scrollY>6?'0 4px 28px rgba(124,58,237,.1)':'';},{passive:true});
var shopW=document.querySelector('.djlib-shop-wrap'),shopC=document.querySelector('.djlib-shop-chevron');
if(shopC&&shopW){shopC.addEventListener('click',function(e){e.stopPropagation();shopW.classList.toggle('open');});var sl=document.querySelector('.djlib-shop-wrap > .djlib-navlink');if(sl)sl.addEventListener('click',function(e){if(window.innerWidth<901){e.preventDefault();shopW.classList.toggle('open');}});document.addEventListener('click',function(e){if(shopW&&!shopW.contains(e.target))shopW.classList.remove('open');});}
if(expB){expB.addEventListener('click',function(e){e.stopPropagation();var o=expW.classList.toggle('open');expB.setAttribute('aria-expanded',o?'true':'false');});document.addEventListener('click',function(e){if(expW&&!expW.contains(e.target)){expW.classList.remove('open');expB.setAttribute('aria-expanded','false');}});}
var aBtn=document.getElementById('djlib-acc-btn'),aPanel=document.getElementById('djlib-acc-panel'),aClose=document.getElementById('djlib-acc-close'),aOvl=document.getElementById('djlib-acc-overlay');
function oAcc(){if(aPanel)aPanel.classList.add('open');if(aOvl)aOvl.classList.add('open');document.body.style.overflow='hidden';}
function cAcc(){if(aPanel)aPanel.classList.remove('open');if(aOvl)aOvl.classList.remove('open');document.body.style.overflow='';}
if(aBtn)aBtn.addEventListener('click',function(e){e.preventDefault();oAcc();});
if(aClose)aClose.addEventListener('click',cAcc);
if(aOvl)aOvl.addEventListener('click',cAcc);
var bBtn=document.getElementById('djlib-books-btn'),bPanel=document.getElementById('djlib-books-panel'),bClose=document.getElementById('djlib-books-close'),bOvl=document.getElementById('djlib-books-overlay'),bBody=document.getElementById('djlib-books-body'),bLoaded=false;
function oBks(){if(bPanel)bPanel.classList.add('open');if(bOvl)bOvl.classList.add('open');document.body.style.overflow='hidden';if(!bLoaded)loadBks();}
function cBks(){if(bPanel)bPanel.classList.remove('open');if(bOvl)bOvl.classList.remove('open');document.body.style.overflow='';}
function loadBks(){if(!bBody)return;bBody.innerHTML='<div class="djlib-panel-loading"><span class="djlib-spin"></span><span>Loading\u2026</span></div>';var fd=new FormData();fd.append('action','djlib_my_books');fd.append('nonce',N);fetch(AJ,{method:'POST',body:fd}).then(function(r){return r.json();}).then(function(d){bLoaded=true;if(!d.success){bBody.innerHTML='<div class="djlib-panel-empty"><p>Please <a href="/my-account/">sign in</a> to view your library.</p><a class="djlib-panel-cta" href="/my-account/">Sign In</a></div>';return;}var bks=d.data;if(!bks||!bks.length){bBody.innerHTML='<div class="djlib-panel-empty"><p>No library purchases yet.</p><a class="djlib-panel-cta" href="/dejoiy-library/">Browse Library</a></div>';return;}var h='';bks.forEach(function(b){h+='<a class="djlib-book-item" href="'+b.url+'"><img src="'+b.thumb+'" alt="" loading="lazy"><div class="djlib-book-info"><div class="djlib-book-title">'+b.title+'</div><div class="djlib-book-meta">Order #'+b.order_id+(b.date?' \u2022 '+b.date:'')+'</div><span class="djlib-book-status">'+b.status+'</span></div></a>';});bBody.innerHTML=h;}).catch(function(){bBody.innerHTML='<div class="djlib-panel-empty"><p>Could not load. Please try again.</p></div>';});}
if(bBtn)bBtn.addEventListener('click',function(e){e.preventDefault();oBks();});
if(bClose)bClose.addEventListener('click',cBks);
if(bOvl)bOvl.addEventListener('click',cBks);
function oMob(){if(mob)mob.classList.add('djlib-open');if(burg)burg.setAttribute('aria-expanded','true');document.body.style.overflow='hidden';}
function cMob(){if(mob)mob.classList.remove('djlib-open');if(burg)burg.setAttribute('aria-expanded','false');document.body.style.overflow='';}
if(burg)burg.addEventListener('click',oMob);
if(cls)cls.addEventListener('click',cMob);
if(mob)mob.addEventListener('click',function(e){if(e.target===mob)cMob();});
document.addEventListener('keydown',function(e){if(e.key==='Escape'){cAcc();cBks();cMob();}});
})();</script><?php
}
add_action('wp_body_open','djlib_html',5);
function djlib_html(){
    if(!is_page(4708))return;
    $home=esc_url('https://dejoiy.tech/');
    $shop=esc_url('https://dejoiy.tech/shop/');
    $lib=esc_url('https://dejoiy.tech/dejoiy-library/');
    $acc=esc_url('https://dejoiy.tech/my-account/');
    $cs=esc_url('https://dejoiy.tech/dejoiy-custom-studio/');
    $ref=esc_url('https://dejoiy.tech/dejoiy-refurbished/');
    $svc=esc_url('https://dejoiy.tech/dejoiy-services/');
    $sup=esc_url('https://dejoiy.tech/support-page/');
    $deals=esc_url('https://dejoiy.tech/');
    $biz=esc_url('https://dejoiy.tech/');
    $qm=esc_url('https://dejoiy.tech/dejoiy-quick-mart/');
    $lid=(int)get_theme_mod('custom_logo');
    $lsrc=$lid?wp_get_attachment_image_url($lid,'full'):'';
    $cats=get_terms(['taxonomy'=>'product_cat','parent'=>0,'hide_empty'=>true,'exclude'=>(int)get_option('default_product_cat'),'number'=>30,'orderby'=>'name','order'=>'ASC']);
    ?><div id="djlib-root" role="banner">
  <div id="djlib-blobs" aria-hidden="true"><div class="djlib-blob djlib-blob-1"></div><div class="djlib-blob djlib-blob-2"></div><div class="djlib-blob djlib-blob-3"></div></div>
  <div id="djlib-topbar">
    <a id="djlib-logo" href="<?=$lib?>" aria-label="DEJOIY"><?php if($lsrc):?><img id="djlib-logo-img" src="<?=esc_url($lsrc)?>" alt="DEJOIY" width="350" height="100"><?php else:?><span id="djlib-logo-text">DEJOIY</span><?php endif;?></a>
    <div id="djlib-right">
      <a class="djlib-ibtn" id="djlib-acc-btn" href="<?=$acc?>" aria-label="Account"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></a>
      <button class="djlib-ibtn djlib-books-btn" id="djlib-books-btn" aria-label="My Books"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg><span class="djlib-books-label">My Books</span></button>
      <button id="djlib-burger" class="djlib-ibtn" aria-label="Open menu" aria-expanded="false"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
    </div>
  </div>
  <nav id="djlib-navbar" aria-label="Library Navigation">
    <div class="djlib-explore-wrap" id="djlib-explore-wrap">
      <button class="djlib-explore-btn" id="djlib-explore-btn" aria-haspopup="true" aria-expanded="false">Explore <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></button>
      <div class="djlib-explore-dd" id="djlib-explore-dd" role="menu"><?php if(!is_wp_error($cats)&&!empty($cats)):foreach($cats as $cat):?><a href="<?=esc_url(get_term_link($cat))?>" role="menuitem"><?=esc_html($cat->name)?></a><?php endforeach;else:?><a href="<?=$shop?>" role="menuitem">All Products</a><?php endif;?></div>
    </div>
    <div class="djlib-nav-div"></div>
    <a class="djlib-navlink" href="<?=$home?>">Home</a>
    <div class="djlib-shop-wrap">
      <a class="djlib-navlink" href="<?=$shop?>">Shop</a>
      <button class="djlib-shop-chevron" aria-label="Shop menu"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></button>
      <div class="djlib-shop-dd" role="menu"><a href="<?=$shop?>" role="menuitem">Shop</a><a href="<?=$lib?>" role="menuitem">Library</a><a href="<?=$cs?>" role="menuitem">Custom Studio</a><a href="<?=$ref?>" role="menuitem">Refurbished</a><a href="<?=$svc?>" role="menuitem">Services</a><a href="<?=$shop?>" class="djlib-shop-dd-more" role="menuitem">More &rarr;</a></div>
    </div>
    <a class="djlib-navlink" href="<?=$cs?>">Custom Studio</a>
    <a class="djlib-navlink" href="<?=$ref?>">Refurbished</a>
    <a class="djlib-navlink" href="<?=$qm?>">QuickMart</a>
    <a class="djlib-navlink" href="<?=$lib?>">Library</a>
    <a class="djlib-navlink" href="<?=$deals?>">Deals</a>
    <a class="djlib-navlink" href="<?=$biz?>">Business</a>
    <a class="djlib-navlink" href="<?=$svc?>">Services</a>
    <a class="djlib-navlink" href="<?=$sup?>">Support</a>
  </nav>
</div>
<?php include get_stylesheet_directory().'/djlib-panels.php';
}
