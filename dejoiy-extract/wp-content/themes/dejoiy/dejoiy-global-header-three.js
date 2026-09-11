/**
 * DEJOIY Global Header — Three.js Constellation Animation
 * Inspired by ThreeUI ConstellationField
 * Human-coded. Vanilla JS. No framework.
 */
(function () {
  'use strict';

  var cfg = {
    nodeCount: 60,
    nodeSize: 0.025,
    spread: 9,
    speed: 0.12,
    connectDist: 2.8,
    mouseInfluence: 0.12,
    scrollParallax: 0.3,
    dprCap: 2,
    bg: 0xf8fafc,
    CY: 0x007185,
    PK: 0xec4899,
    IN: 0x6366f1,
  };

  var cv = document.getElementById('dgh-canvas');
  var gl = document.getElementById('dgh-glass');
  if (!cv || typeof THREE === 'undefined') return;

  var R, SC, Ca, Cl, nodes = [], lines, mx = 0, my = 0, tmx = 0, tmy = 0, scY = 0, vis = true;

  function lp(a, b, t) { return a + (b - a) * t; }
  function rn(a, b) { return Math.random() * (b - a) + a; }

  function init() {
    var d = Math.min(window.devicePixelRatio || 1, cfg.dprCap);
    R = new THREE.WebGLRenderer({ canvas: cv, alpha: true, antialias: true, powerPreference: 'high-performance' });
    R.setPixelRatio(d);
    R.setSize(cv.parentElement.offsetWidth, cv.parentElement.offsetHeight);
    R.setClearColor(cfg.bg, 1);

    SC = new THREE.Scene();
    var w = cv.parentElement.offsetWidth, h = cv.parentElement.offsetHeight;
    Ca = new THREE.PerspectiveCamera(50, w / h, 0.1, 50);
    Ca.position.set(0, 0, 6);
    Ca.lookAt(0, 0, 0);

    Cl = new THREE.Clock();
    buildNodes();
    buildLines();
    bind();
    obs();
    animate();
  }

  function buildNodes() {
    var geo = new THREE.BufferGeometry();
    var pos = new Float32Array(cfg.nodeCount * 3);
    var col = new Float32Array(cfg.nodeCount * 3);
    var cols = [
      new THREE.Color(cfg.CY),
      new THREE.Color(cfg.PK),
      new THREE.Color(cfg.IN),
      new THREE.Color(0xffffff),
    ];
    for (var i = 0; i < cfg.nodeCount; i++) {
      var i3 = i * 3;
      pos[i3] = rn(-cfg.spread, cfg.spread);
      pos[i3 + 1] = rn(-cfg.spread, cfg.spread);
      pos[i3 + 2] = rn(-3, 2);
      var c = cols[Math.floor(Math.random() * cols.length)];
      col[i3] = c.r; col[i3 + 1] = c.g; col[i3 + 2] = c.b;
      nodes.push({ x: pos[i3], y: pos[i3 + 1], z: pos[i3 + 2], vx: rn(-0.003, 0.003), vy: rn(-0.003, 0.003) });
    }
    geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
    geo.setAttribute('color', new THREE.BufferAttribute(col, 3));
    var mat = new THREE.PointsMaterial({
      size: cfg.nodeSize, vertexColors: true, transparent: true, opacity: 0.9,
      blending: THREE.AdditiveBlending, depthWrite: false, sizeAttenuation: true,
    });
    var pts = new THREE.Points(geo, mat);
    SC.add(pts);
    lines = pts;
  }

  function buildLines() {
    var geo = new THREE.BufferGeometry();
    var pos = new Float32Array(cfg.nodeCount * cfg.nodeCount * 6);
    geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
    geo.setDrawRange(0, 0);
    var mat = new THREE.LineBasicMaterial({ color: 0x007185, transparent: true, opacity: 0.15, blending: THREE.AdditiveBlending });
    var ln = new THREE.LineSegments(geo, mat);
    SC.add(ln);
    lines._seg = ln;
  }

  function updateLines() {
    if (!lines || !lines._seg) return;
    var pos = lines.geometry.attributes.position.array;
    var idx = 0;
    for (var i = 0; i < cfg.nodeCount; i++) {
      for (var j = i + 1; j < cfg.nodeCount; j++) {
        var dx = nodes[i].x - nodes[j].x;
        var dy = nodes[i].y - nodes[j].y;
        var dz = nodes[i].z - nodes[j].z;
        var dist = Math.sqrt(dx * dx + dy * dy + dz * dz);
        if (dist < cfg.connectDist) {
          var i3a = idx, i3b = idx + 3;
          pos[i3a] = nodes[i].x; pos[i3a + 1] = nodes[i].y; pos[i3a + 2] = nodes[i].z;
          pos[i3b] = nodes[j].x; pos[i3b + 1] = nodes[j].y; pos[i3b + 2] = nodes[j].z;
          idx += 6;
        }
      }
    }
    lines._seg.geometry.setDrawRange(0, idx / 3);
    lines._seg.geometry.attributes.position.needsUpdate = true;
  }

  function obs() {
    if (!('IntersectionObserver' in window)) return;
    new IntersectionObserver(function (e) { vis = e[0].isIntersecting; }, { threshold: 0.05 }).observe(cv);
  }

  function animate() {
    if (!vis) { requestAnimationFrame(animate); return; }
    var t = Cl.getElapsedTime();
    mx = lp(mx, tmx, 0.05); my = lp(my, tmy, 0.05);
    Ca.position.x = lp(Ca.position.x, mx * cfg.mouseInfluence, 0.02);
    Ca.position.y = lp(Ca.position.y, -my * cfg.mouseInfluence * 0.5, 0.02);
    Ca.lookAt(0, 0, 0);

    // Move nodes
    var pos = lines.geometry.attributes.position.array;
    for (var i = 0; i < cfg.nodeCount; i++) {
      nodes[i].x += nodes[i].vx + Math.sin(t * 0.3 + i) * 0.001;
      nodes[i].y += nodes[i].vy + Math.cos(t * 0.2 + i * 0.7) * 0.001;
      if (nodes[i].x > cfg.spread) nodes[i].x = -cfg.spread;
      if (nodes[i].x < -cfg.spread) nodes[i].x = cfg.spread;
      if (nodes[i].y > cfg.spread) nodes[i].y = -cfg.spread;
      if (nodes[i].y < -cfg.spread) nodes[i].y = cfg.spread;
      var i3 = i * 3;
      pos[i3] = nodes[i].x; pos[i3 + 1] = nodes[i].y; pos[i3 + 2] = nodes[i].z;
    }
    lines.geometry.attributes.position.needsUpdate = true;
    lines.rotation.y = t * cfg.speed * 0.08;

    updateLines();

    SC.position.y = lp(SC.position.y, scY * cfg.scrollParallax * 0.001, 0.03);
    R.render(SC, Ca);
    requestAnimationFrame(animate);
  }

  function bind() {
    window.addEventListener('mousemove', function (e) {
      tmx = (e.clientX / window.innerWidth - 0.5) * 2;
      tmy = (e.clientY / window.innerHeight - 0.5) * 2;
    }, { passive: true });
    window.addEventListener('scroll', function () {
      scY = window.scrollY || window.pageYOffset;
      if (gl) gl.classList.toggle('is-scrolled', scY > 8);
    }, { passive: true });
    window.addEventListener('resize', function () {
      if (!R) return;
      var w = cv.parentElement.offsetWidth, h = cv.parentElement.offsetHeight;
      R.setSize(w, h);
      R.setPixelRatio(Math.min(window.devicePixelRatio || 1, cfg.dprCap));
      Ca.aspect = w / h;
      Ca.updateProjectionMatrix();
    }, { passive: true });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { setTimeout(init, 100); });
  } else { setTimeout(init, 100); }
})();
