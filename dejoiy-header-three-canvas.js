/**
 * DEJOIY Animated Header — Three.js Canvas Engine
 * 
 * Premium 3D animated background for the DEJOIY marketplace header.
 * Renders floating geometric shapes, particle field, and ambient light
 * that responds to scroll and mouse position.
 * 
 * Human-coded. No framework dependencies beyond Three.js.
 * 
 * @version 1.0.0
 * @license MIT
 */

(function () {
  'use strict';

  /* --------------------------------------------------------
   *  Config — tweak these to taste
   * ------------------------------------------------------ */
  var CONFIG = {
    particleCount: 120,
    particleSize: 0.018,
    particleSpread: 8,
    particleSpeed: 0.15,

    shapeCount: 6,
    shapeScale: 0.28,
    shapeDrift: 0.3,
    shapeRotSpeed: 0.004,

    ambientLightColor: 0x6366f1,
    ambientLightIntensity: 0.3,
    pointLightColor1: 0x06b6d4,
    pointLightColor2: 0xec4899,
    pointLightIntensity: 1.2,

    bgColor: 0x0b0f1a,
    fogNear: 4,
    fogFar: 14,

    scrollParallax: 0.4,
    mouseInfluence: 0.15,
    dprCap: 2,

    // DEJOIY brand palette
    DEJOIY_CYAN: 0x06b6d4,
    DEJOIY_INDIGO: 0x6366f1,
    DEJOIY_PINK: 0xec4899,
    DEJOIY_VIOLET: 0x7c3aed,
  };

  /* --------------------------------------------------------
   *  State
   * ------------------------------------------------------ */
  var canvas = null;
  var renderer = null;
  var scene = null;
  var camera = null;
  var particles = null;
  var shapes = [];
  var pointLight1 = null;
  var pointLight2 = null;
  var scrollY = 0;
  var mouseX = 0;
  var mouseY = 0;
  var targetMouseX = 0;
  var targetMouseY = 0;
  var clock = null;
  var rafId = null;
  var isVisible = true;

  /* --------------------------------------------------------
   *  Utility helpers — kept simple, readable
   * ------------------------------------------------------ */
  function lerp(a, b, t) {
    return a + (b - a) * t;
  }

  function randomRange(min, max) {
    return Math.random() * (max - min) + min;
  }

  function createGradientTexture() {
    // Radial gradient for soft particle glow — drawn to a small canvas
    var size = 64;
    var c = document.createElement('canvas');
    c.width = size;
    c.height = size;
    var ctx = c.getContext('2d');

    var gradient = ctx.createRadialGradient(
      size / 2, size / 2, 0,
      size / 2, size / 2, size / 2
    );
    gradient.addColorStop(0, 'rgba(255, 255, 255, 1)');
    gradient.addColorStop(0.3, 'rgba(255, 255, 255, 0.6)');
    gradient.addColorStop(1, 'rgba(255, 255, 255, 0)');

    ctx.fillStyle = gradient;
    ctx.fillRect(0, 0, size, size);

    var texture = new THREE.CanvasTexture(c);
    texture.needsUpdate = true;
    return texture;
  }

  /* --------------------------------------------------------
   *  Scene setup
   * ------------------------------------------------------ */
  function initScene() {
    // Ensure Three.js is loaded
    if (typeof THREE === 'undefined') {
      console.warn('[DEJOIY] Three.js not loaded — animated header disabled.');
      return false;
    }

    canvas = document.getElementById('dejoiy-header-canvas');
    if (!canvas) {
      console.warn('[DEJOIY] Canvas element #dejoiy-header-canvas not found.');
      return false;
    }

    // Renderer
    var dpr = Math.min(window.devicePixelRatio || 1, CONFIG.dprCap);
    renderer = new THREE.WebGLRenderer({
      canvas: canvas,
      alpha: true,
      antialias: true,
      powerPreference: 'high-performance',
    });
    renderer.setPixelRatio(dpr);
    renderer.setSize(canvas.parentElement.offsetWidth, canvas.parentElement.offsetHeight);
    renderer.setClearColor(CONFIG.bgColor, 1);

    // Scene
    scene = new THREE.Scene();
    scene.fog = new THREE.Fog(CONFIG.fogNear, CONFIG.fogNear, CONFIG.fogFar);

    // Camera
    var aspect = canvas.parentElement.offsetWidth / canvas.parentElement.offsetHeight;
    camera = new THREE.PerspectiveCamera(45, aspect, 0.1, 50);
    camera.position.set(0, 0.3, 5.5);
    camera.lookAt(0, 0, 0);

    // Lights
    var ambient = new THREE.AmbientLight(CONFIG.ambientLightColor, CONFIG.ambientLightIntensity);
    scene.add(ambient);

    pointLight1 = new THREE.PointLight(CONFIG.pointLightColor1, CONFIG.pointLightIntensity, 12);
    pointLight1.position.set(2, 1.5, 3);
    scene.add(pointLight1);

    pointLight2 = new THREE.PointLight(CONFIG.pointLightColor2, CONFIG.pointLightIntensity * 0.8, 10);
    pointLight2.position.set(-2, -1, 2);
    scene.add(pointLight2);

    // Clock
    clock = new THREE.Clock();

    return true;
  }

  /* --------------------------------------------------------
   *  Particle field
   * ------------------------------------------------------ */
  function createParticles() {
    var count = CONFIG.particleCount;
    var positions = new Float32Array(count * 3);
    var colors = new Float32Array(count * 3);
    var sizes = new Float32Array(count);

    var palette = [
      new THREE.Color(CONFIG.DEJOIY_CYAN),
      new THREE.Color(CONFIG.DEJOIY_INDIGO),
      new THREE.Color(CONFIG.DEJOIY_PINK),
      new THREE.Color(CONFIG.DEJOIY_VIOLET),
      new THREE.Color(0xffffff),
    ];

    for (var i = 0; i < count; i++) {
      var i3 = i * 3;

      positions[i3] = randomRange(-CONFIG.particleSpread, CONFIG.particleSpread);
      positions[i3 + 1] = randomRange(-CONFIG.particleSpread, CONFIG.particleSpread);
      positions[i3 + 2] = randomRange(-3, 3);

      var col = palette[Math.floor(Math.random() * palette.length)];
      colors[i3] = col.r;
      colors[i3 + 1] = col.g;
      colors[i3 + 2] = col.b;

      sizes[i] = randomRange(0.5, 1.5) * CONFIG.particleSize;
    }

    var geometry = new THREE.BufferGeometry();
    geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    geometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));
    geometry.setAttribute('size', new THREE.BufferAttribute(sizes, 1));

    var material = new THREE.PointsMaterial({
      size: CONFIG.particleSize,
      map: createGradientTexture(),
      vertexColors: true,
      transparent: true,
      opacity: 0.85,
      blending: THREE.AdditiveBlending,
      depthWrite: false,
      sizeAttenuation: true,
    });

    particles = new THREE.Points(geometry, material);
    scene.add(particles);
  }

  /* --------------------------------------------------------
   *  Floating geometric shapes
   * ------------------------------------------------------ */
  function createShapes() {
    var geometries = [
      new THREE.IcosahedronGeometry(CONFIG.shapeScale, 0),
      new THREE.OctahedronGeometry(CONFIG.shapeScale * 0.9, 0),
      new THREE.TetrahedronGeometry(CONFIG.shapeScale * 0.8, 0),
      new THREE.TorusGeometry(CONFIG.shapeScale * 0.7, CONFIG.shapeScale * 0.25, 8, 16),
      new THREE.DodecahedronGeometry(CONFIG.shapeScale * 0.85, 0),
      new THREE.BoxGeometry(CONFIG.shapeScale * 0.8, CONFIG.shapeScale * 0.8, CONFIG.shapeScale * 0.8),
    ];

    var accentColors = [
      CONFIG.DEJOIY_CYAN,
      CONFIG.DEJOIY_INDIGO,
      CONFIG.DEJOIY_PINK,
      CONFIG.DEJOIY_VIOLET,
    ];

    for (var i = 0; i < CONFIG.shapeCount; i++) {
      var geo = geometries[i % geometries.length];
      var color = accentColors[i % accentColors.length];

      var material = new THREE.MeshPhysicalMaterial({
        color: color,
        roughness: 0.35,
        metalness: 0.5,
        transparent: true,
        opacity: 0.7,
        wireframe: Math.random() > 0.5,  // mix of wireframe and solid
        clearcoat: 0.4,
        clearcoatRoughness: 0.3,
      });

      var mesh = new THREE.Mesh(geo, material);

      mesh.position.set(
        randomRange(-3.5, 3.5),
        randomRange(-1.5, 1.8),
        randomRange(-2, 1)
      );

      mesh.userData = {
        baseX: mesh.position.x,
        baseY: mesh.position.y,
        baseZ: mesh.position.z,
        driftX: randomRange(-CONFIG.shapeDrift, CONFIG.shapeDrift),
        driftY: randomRange(-CONFIG.shapeDrift, CONFIG.shapeDrift),
        rotSpeedX: randomRange(-CONFIG.shapeRotSpeed, CONFIG.shapeRotSpeed),
        rotSpeedY: randomRange(-CONFIG.shapeRotSpeed, CONFIG.shapeRotSpeed),
        phase: randomRange(0, Math.PI * 2),
      };

      scene.add(mesh);
      shapes.push(mesh);
    }
  }

  /* --------------------------------------------------------
   *  Animation loop
   * ------------------------------------------------------ */
  function animate() {
    if (!isVisible) {
      rafId = requestAnimationFrame(animate);
      return;
    }

    var elapsed = clock.getElapsedTime();
    var delta = clock.getDelta();

    // Smooth mouse follow
    mouseX = lerp(mouseX, targetMouseX, 0.06);
    mouseY = lerp(mouseY, targetMouseY, 0.06);

    // Camera subtle movement
    camera.position.x = lerp(camera.position.x, mouseX * CONFIG.mouseInfluence, 0.03);
    camera.position.y = lerp(camera.position.y, 0.3 - mouseY * CONFIG.mouseInfluence * 0.5, 0.03);
    camera.lookAt(0, 0, 0);

    // Animate particles
    if (particles) {
      var pos = particles.geometry.attributes.position.array;
      for (var i = 0; i < pos.length; i += 3) {
        pos[i] += Math.sin(elapsed * 0.3 + i) * 0.001;
        pos[i + 1] += Math.cos(elapsed * 0.2 + i * 0.5) * 0.001;

        // Wrap around
        if (pos[i] > CONFIG.particleSpread) pos[i] = -CONFIG.particleSpread;
        if (pos[i] < -CONFIG.particleSpread) pos[i] = CONFIG.particleSpread;
        if (pos[i + 1] > CONFIG.particleSpread) pos[i + 1] = -CONFIG.particleSpread;
        if (pos[i + 1] < -CONFIG.particleSpread) pos[i + 1] = CONFIG.particleSpread;
      }
      particles.geometry.attributes.position.needsUpdate = true;
      particles.rotation.y = elapsed * CONFIG.particleSpeed * 0.1;
    }

    // Animate shapes
    for (var j = 0; j < shapes.length; j++) {
      var s = shapes[j];
      var d = s.userData;

      s.position.x = d.baseX + Math.sin(elapsed * 0.5 + d.phase) * d.driftX;
      s.position.y = d.baseY + Math.cos(elapsed * 0.4 + d.phase) * d.driftY;
      s.position.z = d.baseZ + Math.sin(elapsed * 0.3 + d.phase * 0.7) * 0.15;

      s.rotation.x += d.rotSpeedX;
      s.rotation.y += d.rotSpeedY;

      // Pulse opacity subtly
      s.material.opacity = 0.55 + Math.sin(elapsed * 1.2 + d.phase) * 0.15;
    }

    // Animate lights — gentle float
    if (pointLight1) {
      pointLight1.position.x = 2 + Math.sin(elapsed * 0.6) * 1;
      pointLight1.position.y = 1.5 + Math.cos(elapsed * 0.4) * 0.8;
    }
    if (pointLight2) {
      pointLight2.position.x = -2 + Math.cos(elapsed * 0.5) * 0.8;
      pointLight2.position.y = -1 + Math.sin(elapsed * 0.7) * 0.6;
    }

    // Scroll parallax — shift scene vertically
    var scrollOffset = scrollY * CONFIG.scrollParallax * 0.001;
    scene.position.y = lerp(scene.position.y, scrollOffset, 0.05);

    renderer.render(scene, camera);
    rafId = requestAnimationFrame(animate);
  }

  /* --------------------------------------------------------
   *  Event handlers
   * ------------------------------------------------------ */
  function onMouseMove(e) {
    targetMouseX = (e.clientX / window.innerWidth - 0.5) * 2;
    targetMouseY = (e.clientY / window.innerHeight - 0.5) * 2;
  }

  function onScroll() {
    scrollY = window.scrollY || window.pageYOffset;
  }

  function onResize() {
    if (!renderer || !canvas) return;
    var parent = canvas.parentElement;
    if (!parent) return;

    var w = parent.offsetWidth;
    var h = parent.offsetHeight;
    var dpr = Math.min(window.devicePixelRatio || 1, CONFIG.dprCap);

    renderer.setSize(w, h);
    renderer.setPixelRatio(dpr);

    camera.aspect = w / h;
    camera.updateProjectionMatrix();
  }

  /* --------------------------------------------------------
   *  Visibility observer — pause when hidden
   * ------------------------------------------------------ */
  function setupVisibilityObserver() {
    if (!canvas) return;

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        isVisible = entry.isIntersecting;
      });
    }, { threshold: 0.05 });

    observer.observe(canvas);
  }

  /* --------------------------------------------------------
   *  Public API
   * ------------------------------------------------------ */
  window.dejoiyHeaderThree = {
    init: function () {
      if (!initScene()) return;
      createParticles();
      createShapes();
      setupVisibilityObserver();

      window.addEventListener('mousemove', onMouseMove, { passive: true });
      window.addEventListener('scroll', onScroll, { passive: true });
      window.addEventListener('resize', onResize, { passive: true });

      // Start the loop
      animate();

      console.log('[DEJOIY] Header Three.js canvas initialized.');
    },

    destroy: function () {
      if (rafId) cancelAnimationFrame(rafId);
      window.removeEventListener('mousemove', onMouseMove);
      window.removeEventListener('scroll', onScroll);
      window.removeEventListener('resize', onResize);
      if (renderer) renderer.dispose();
      if (scene) {
        scene.traverse(function (obj) {
          if (obj.geometry) obj.geometry.dispose();
          if (obj.material) {
            if (obj.material.map) obj.material.map.dispose();
            obj.material.dispose();
          }
        });
      }
      console.log('[DEJOIY] Header Three.js canvas destroyed.');
    },
  };

  // Auto-init when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      // Small delay to ensure Three.js is loaded
      setTimeout(function () {
        window.dejoiyHeaderThree.init();
      }, 100);
    });
  } else {
    setTimeout(function () {
      window.dejoiyHeaderThree.init();
    }, 100);
  }
})();
