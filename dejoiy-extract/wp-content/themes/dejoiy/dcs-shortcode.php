<?php
  function dejoiy_custom_studio_output() {
      if (!function_exists('wc_get_product')) return '';
      $args = array('post_type'=>'product','posts_per_page'=>12,'post_status'=>'publish',
          'tax_query'=>array(array('taxonomy'=>'product_cat','field'=>'term_id','terms'=>array(143,153,154,155,156),'operator'=>'IN')));
      $pq = new WP_Query($args);
      if (!$pq->have_posts()) $pq = new WP_Query(array('post_type'=>'product','posts_per_page'=>12,'post_status'=>'publish'));
      $bento_cards = ''; $carousel_cards = '';
      $bento_cls = array('bt-xl','bt-lg','bt-md','bt-sm','bt-sm','bt-md','bt-sm','bt-sm');
      $i = 0;
      while ($pq->have_posts()) {
          $pq->the_post();
          $pr = wc_get_product(get_the_ID());
          $tit = esc_html(get_the_title());
          $prc = $pr->get_price_html();
          $img = get_the_post_thumbnail_url(get_the_ID(),'large') ?: wc_placeholder_img_src();
          $lnk = esc_url(get_permalink());
          $cats = wp_get_post_terms(get_the_ID(),'product_cat',array('fields'=>'names'));
          $cat  = (!is_wp_error($cats)&&!empty($cats)) ? esc_html($cats[0]) : 'Custom';
          $bc   = isset($bento_cls[$i]) ? $bento_cls[$i] : 'bt-sm';
          $bento_cards .= '<div class="dcs-bt-card '.$bc.'" data-ga="fu" data-dl="'.($i*80).'"><img class="dcs-bt-img" src="'.esc_attr($img).'" alt="'.$tit.'" loading="lazy"><div class="dcs-bt-overlay"></div><div class="dcs-bt-gov"></div><div class="dcs-bt-body"><span class="dcs-bt-badge">'.$cat.'</span><p class="dcs-bt-title">'.$tit.'</p><div class="dcs-bt-meta"><span class="dcs-bt-price">'.$prc.'</span><a href="'.$lnk.'" class="dcs-bt-cta"><span>Customize</span><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a></div></div></div>';
          $carousel_cards .= '<div class="dcs-car-card"><img class="dcs-car-img" src="'.esc_attr($img).'" alt="'.$tit.'" loading="lazy"><div class="dcs-car-body"><p class="dcs-car-cat">'.$cat.'</p><h3>'.$tit.'</h3><div class="dcs-car-footer"><span class="dcs-car-price">'.$prc.'</span><a href="'.$lnk.'" class="dcs-car-btn">Customize</a></div></div></div>';
          $i++;
      }
      wp_reset_postdata();
      $shop = esc_url(get_permalink(wc_get_page_id('shop')));
      ob_start();
  ?>
  <div id="dcs">
  <div id="dcs-cursor"></div><div id="dcs-cursor-dot"></div>
  <canvas id="dcs-cv"></canvas><div id="dcs-pb"></div>
  <button id="dcs-tt" aria-label="Top"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg></button>
  <nav id="dcs-mob-nav">
  <a href="#dcs-hero" class="dcs-mn-a active" data-section="dcs-hero"><svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg><span>Home</span></a>
  <a href="#dcs-bento" class="dcs-mn-a" data-section="dcs-bento"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg><span>Products</span></a>
  <a href="#dcs-ai" class="dcs-mn-a" data-section="dcs-ai"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4"/></svg><span>AI</span></a>
  <a href="#dcs-process" class="dcs-mn-a" data-section="dcs-process"><svg viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg><span>Process</span></a>
  <a href="#dcs-cta" class="dcs-mn-a" data-section="dcs-cta"><svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg><span>Order</span></a>
  </nav>
  <section id="dcs-hero">
  <div class="dcs-bg-w"><div class="dcs-o1"></div><div class="dcs-o2"></div><div class="dcs-o3"></div><div class="dcs-o4"></div><div class="dcs-o5"></div></div>
  <div class="dcs-hero-float dcs-hf1"><div class="dcs-hf-label">Products Live</div><div class="dcs-hf-val">500+</div><div class="dcs-hf-sub">Customizable Items</div></div>
  <div class="dcs-hero-float dcs-hf2"><div class="dcs-hf-label">Express Delivery</div><div class="dcs-hf-val">48hr</div><div class="dcs-hf-sub">Pan-India</div></div>
  <div class="dcs-hero-float dcs-hf3"><div class="dcs-hf-label">Satisfaction</div><div class="dcs-hf-val">98%</div><div class="dcs-hf-sub">Verified Reviews</div></div>
  <div class="dcs-hc">
  <div class="dcs-eyebrow" data-ga="fu"><span class="dcs-eyebrow-dot"></span>DEJOIY CUSTOM STUDIO &mdash; AI-Powered</div>
  <h1 class="dcs-h1" data-ga="fu" data-dl="100">Create Something<br><span class="dcs-gr">That&rsquo;s Truly Yours</span></h1>
  <p class="dcs-sub" data-ga="fu" data-dl="220">India&rsquo;s most immersive product personalization platform. Design, customize, and own something built just for you.</p>
  <div class="dcs-ctas" data-ga="fu" data-dl="340">
  <a href="#dcs-bento" class="dcs-pb-btn dcs-mag"><span>Start Creating</span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
  <a href="<?php echo $shop; ?>" class="dcs-gb dcs-mag"><span>Explore All Products</span></a>
  </div></div>
  <div class="dcs-scroll-hint"><span>scroll</span><div class="dcs-sl"></div></div>
  </section>
  <div class="dcs-mq-wrap"><div class="dcs-mq"><span class="dcs-mq-item"><span class="dcs-mq-dot"></span>PREMIUM QUALITY</span><span class="dcs-mq-item"><span class="dcs-mq-dot"></span>CUSTOM MADE</span><span class="dcs-mq-item"><span class="dcs-mq-dot"></span>48HR DELIVERY</span><span class="dcs-mq-item"><span class="dcs-mq-dot"></span>500+ PRODUCTS</span><span class="dcs-mq-item"><span class="dcs-mq-dot"></span>AI-POWERED STUDIO</span><span class="dcs-mq-item"><span class="dcs-mq-dot"></span>10,000+ CREATORS</span><span class="dcs-mq-item"><span class="dcs-mq-dot"></span>PAN-INDIA SHIPPING</span><span class="dcs-mq-item"><span class="dcs-mq-dot"></span>PREMIUM QUALITY</span><span class="dcs-mq-item"><span class="dcs-mq-dot"></span>CUSTOM MADE</span><span class="dcs-mq-item"><span class="dcs-mq-dot"></span>48HR DELIVERY</span><span class="dcs-mq-item"><span class="dcs-mq-dot"></span>500+ PRODUCTS</span><span class="dcs-mq-item"><span class="dcs-mq-dot"></span>AI-POWERED STUDIO</span><span class="dcs-mq-item"><span class="dcs-mq-dot"></span>10,000+ CREATORS</span><span class="dcs-mq-item"><span class="dcs-mq-dot"></span>PAN-INDIA SHIPPING</span></div></div>
  <section id="dcs-bento">
  <div class="dcs-sec-head" data-ga="fu"><span class="dcs-lbl">MARKETPLACE</span><h2>Pick Your Canvas</h2><p>Every product is a blank slate for your imagination. Choose one and make it unforgettable.</p></div>
  <div class="dcs-bt-grid"><?php echo $bento_cards; ?></div>
  <div style="text-align:center;margin-top:3rem" data-ga="fu"><a href="<?php echo $shop; ?>" class="dcs-ob">View All Products &rarr;</a></div>
  </section>
  <?php ?>
  <section id="dcs-ai">
  <div class="dcs-ai-grid">
  <div class="dcs-ai-left" data-ga="fr">
  <div class="dcs-ai-tag"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4"/></svg> AI-POWERED</div>
  <h2>Personalize with<br><span class="dcs-gr">Intelligence</span></h2>
  <p class="dcs-ai-desc">Our AI understands your vision &mdash; upload a reference, describe your idea, or start from a template. The studio handles the rest.</p>
  <div class="dcs-ai-feats">
  <div class="dcs-ai-feat"><div class="dcs-ai-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg></div><div><h4>Upload Any Design</h4><p>PNG, SVG, AI, PDF &mdash; any format</p></div></div>
  <div class="dcs-ai-feat"><div class="dcs-ai-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg></div><div><h4>Smart Typography</h4><p>Names, logos, text on any surface</p></div></div>
  <div class="dcs-ai-feat"><div class="dcs-ai-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg></div><div><h4>3D Live Preview</h4><p>See your creation before you order</p></div></div>
  </div></div>
  <div class="dcs-ai-right" data-ga="fl">
  <div class="dcs-terminal"><div class="dcs-term-bar"><div class="dcs-tb-dot r"></div><div class="dcs-tb-dot y"></div><div class="dcs-tb-dot g"></div><span>dejoiy-ai &middot; personalizer.js</span></div>
  <div class="dcs-term-body">
  <div class="dcs-term-line" data-delay="0"><span class="dcs-term-prompt">$ </span><span class="dcs-term-cmd">dejoiy init --ai --product=tshirt</span></div>
  <div class="dcs-term-line" data-delay="0.5"><span class="dcs-term-out">&#10003; <span class="dcs-term-hi2">AI Studio</span> initialized</span></div>
  <div class="dcs-term-line" data-delay="1.0"><span class="dcs-term-prompt">$ </span><span class="dcs-term-cmd">upload --file="my-logo.png"</span></div>
  <div class="dcs-term-line" data-delay="1.5"><span class="dcs-term-out">&#10003; Colors: <span class="dcs-term-hi">#7C5CE4</span> <span class="dcs-term-hi2">#E8609A</span> &bull; <span class="dcs-term-hi">98%</span></span></div>
  <div class="dcs-term-line" data-delay="2.0"><span class="dcs-term-prompt">$ </span><span class="dcs-term-cmd">preview --mode=3d</span></div>
  <div class="dcs-term-line" data-delay="2.5"><span class="dcs-term-out">&#10003; <span class="dcs-term-hi2">3D preview</span> ready &middot; <span class="dcs-term-hi">print-ready</span></span></div>
  <div class="dcs-term-line" data-delay="3.0"><span class="dcs-term-prompt">$ </span><span class="dcs-term-cmd">order --qty=1<span class="dcs-term-cursor"></span></span></div>
  </div></div></div></div>
  </section>
  <section id="dcs-stats">
  <div class="dcs-stats-grid">
  <div class="dcs-stat-box"><div class="dcs-stat-glow"></div><div style="display:flex;align-items:baseline;justify-content:center;gap:.1rem"><span class="dcs-stat-n" data-cnt="500">0</span><em>+</em></div><p class="dcs-stat-label">Customizable Products</p></div>
  <div class="dcs-stat-box"><div class="dcs-stat-glow"></div><div style="display:flex;align-items:baseline;justify-content:center;gap:.1rem"><span class="dcs-stat-n" data-cnt="10000">0</span><em>+</em></div><p class="dcs-stat-label">Happy Creators</p></div>
  <div class="dcs-stat-box"><div class="dcs-stat-glow"></div><div style="display:flex;align-items:baseline;justify-content:center;gap:.1rem"><span class="dcs-stat-n" data-cnt="48">0</span><em>hr</em></div><p class="dcs-stat-label">Express Delivery</p></div>
  <div class="dcs-stat-box"><div class="dcs-stat-glow"></div><div style="display:flex;align-items:baseline;justify-content:center;gap:.1rem"><span class="dcs-stat-n" data-cnt="100">0</span><em>%</em></div><p class="dcs-stat-label">Custom Made</p></div>
  </div>
  </section>
  <section id="dcs-process">
  <div class="dcs-sec-head" data-ga="fu"><span class="dcs-lbl">HOW IT WORKS</span><h2>Your Studio. Your Rules.</h2><p>Four cinematic steps from idea to doorstep.</p></div>
  <div class="dcs-proc-wrap"><div class="dcs-proc-line"></div>
  <div class="dcs-proc-step left"><div class="dcs-proc-content"><div class="dcs-proc-ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></div><h3>Choose Your Product</h3><p>Browse 500+ customizable items &mdash; T-shirts, mugs, phone cases, corporate gifts and more.</p></div><div class="dcs-proc-node"><div class="dcs-proc-num">01</div></div><div class="dcs-proc-empty"></div></div>
  <div class="dcs-proc-step right"><div class="dcs-proc-empty"></div><div class="dcs-proc-node"><div class="dcs-proc-num">02</div></div><div class="dcs-proc-content"><div class="dcs-proc-ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg></div><h3>Design &amp; Personalize</h3><p>Upload artwork, add names, pick colors. Our AI studio brings your exact vision to life.</p></div></div>
  <div class="dcs-proc-step left"><div class="dcs-proc-content"><div class="dcs-proc-ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div><h3>Review &amp; Approve</h3><p>See a 3D preview. Approve and our artisans start crafting with care.</p></div><div class="dcs-proc-node"><div class="dcs-proc-num">03</div></div><div class="dcs-proc-empty"></div></div>
  <div class="dcs-proc-step right"><div class="dcs-proc-empty"></div><div class="dcs-proc-node"><div class="dcs-proc-num">04</div></div><div class="dcs-proc-content"><div class="dcs-proc-ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></div><h3>Delivered to You</h3><p>Premium packaging. Pan-India delivery in 48 hours. Unbox something made just for you.</p></div></div>
  </div></section>
  <section id="dcs-carousel"><div class="dcs-car-head"><div><span class="dcs-lbl">TRENDING NOW</span><h2>Most Customized Products</h2></div><p class="dcs-car-hint"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg> Drag to explore</p></div>
  <div class="dcs-car-wrap"><div class="dcs-car-track"><?php echo $carousel_cards.$carousel_cards; ?></div></div>
  </section>
  <section id="dcs-creators">
  <div class="dcs-sec-head" data-ga="fu"><span class="dcs-lbl">CREATOR STORIES</span><h2>They Built Something <span class="dcs-gr">Real</span></h2></div>
  <div class="dcs-cr-grid">
  <div class="dcs-cr-card"><div class="dcs-cr-glow"></div><div class="dcs-cr-stars"><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span></div><div class="dcs-cr-quote">&ldquo;</div><p class="dcs-cr-text">Ordered 50 custom tees for our startup launch. Premium print, perfect colors, delivered in 2 days. DEJOIY is the real deal.</p><div class="dcs-cr-author"><div class="dcs-cr-av">AR</div><div class="dcs-cr-info"><h4>Arjun Reddy</h4><p>Founder, TechStart India</p></div></div></div>
  <div class="dcs-cr-card"><div class="dcs-cr-glow"></div><div class="dcs-cr-stars"><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span></div><div class="dcs-cr-quote">&ldquo;</div><p class="dcs-cr-text">The AI studio is insane. Uploaded my logo, got a 3D preview in seconds. Corporate gift order came out exactly as imagined. Zero compromise.</p><div class="dcs-cr-author"><div class="dcs-cr-av">PS</div><div class="dcs-cr-info"><h4>Priya Sharma</h4><p>Creative Director, Brand Studio</p></div></div></div>
  <div class="dcs-cr-card"><div class="dcs-cr-glow"></div><div class="dcs-cr-stars"><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span></div><div class="dcs-cr-quote">&ldquo;</div><p class="dcs-cr-text">Best custom merchandise platform in India. Period. Endless personalization options and the finished product looks like a premium brand.</p><div class="dcs-cr-author"><div class="dcs-cr-av">VK</div><div class="dcs-cr-info"><h4>Vikram Kumar</h4><p>Creator, 500K subscribers</p></div></div></div>
  </div></section>
  <section id="dcs-cta"><div class="dcs-cta-mesh"></div>
  <div class="dcs-ctac" data-ga="fu"><span class="dcs-lbl">START TODAY</span>
  <h2>Ready to Create<br><span class="dcs-gr">Something Legendary?</span></h2>
  <p>Join 10,000+ creators who turned their ideas into something real. Your masterpiece is one click away.</p>
  <div class="dcs-ctas"><a href="<?php echo $shop; ?>" class="dcs-pb-btn dcs-mag"><span>Start Customizing Now</span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a></div>
  </div></section>
  </div>
  <?php
      return ob_get_clean();
  }
  add_shortcode('dejoiy_custom_studio','dejoiy_custom_studio_output');