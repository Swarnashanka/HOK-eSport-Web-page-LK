/* HOK Esports LK — Main JavaScript */

document.addEventListener('DOMContentLoaded', function () {

  // ---- Sticky Navbar ----
  const navbar = document.getElementById('mainNav');
  if (navbar) {
    window.addEventListener('scroll', function () {
      navbar.classList.toggle('scrolled', window.scrollY > 30);
    });
  }

  // ---- Mobile Nav Toggle ----
  const navToggle = document.getElementById('navToggle');
  const navMenu = document.getElementById('navMenu');
  if (navToggle && navMenu) {
    navToggle.addEventListener('click', function () {
      navMenu.classList.toggle('open');
      document.body.style.overflow = navMenu.classList.contains('open') ? 'hidden' : '';
    });
    navMenu.querySelectorAll('.nav-link').forEach(link => {
      link.addEventListener('click', () => {
        navMenu.classList.remove('open');
        document.body.style.overflow = '';
      });
    });
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') { navMenu.classList.remove('open'); document.body.style.overflow = ''; }
    });
  }

  // ---- Scroll Animations ----
  const animEls = document.querySelectorAll('.animate-on-scroll');
  if (animEls.length) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) { entry.target.classList.add('visible'); }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
    animEls.forEach(el => observer.observe(el));
  }

  // ---- Counter Animation ----
  const counters = document.querySelectorAll('.stat-number[data-count]');
  if (counters.length) {
    const counterObs = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const el = entry.target;
          const target = parseInt(el.getAttribute('data-count'));
          const duration = 1800;
          const step = Math.ceil(target / (duration / 16));
          let current = 0;
          const timer = setInterval(() => {
            current = Math.min(current + step, target);
            el.textContent = current.toLocaleString() + (el.getAttribute('data-suffix') || '');
            if (current >= target) clearInterval(timer);
          }, 16);
          counterObs.unobserve(el);
        }
      });
    }, { threshold: 0.5 });
    counters.forEach(el => counterObs.observe(el));
  }

  // ---- Lightbox ----
  const lightbox = document.getElementById('lightbox');
  const lightboxImg = document.getElementById('lightboxImg');
  const lightboxClose = document.getElementById('lightboxClose');

  document.querySelectorAll('[data-lightbox]').forEach(item => {
    item.addEventListener('click', function () {
      const src = this.getAttribute('data-lightbox');
      if (lightbox && lightboxImg) {
        lightboxImg.src = src;
        lightbox.classList.add('open');
        document.body.style.overflow = 'hidden';
      }
    });
  });

  function closeLightbox() {
    if (lightbox) {
      lightbox.classList.remove('open');
      document.body.style.overflow = '';
    }
  }

  if (lightboxClose) lightboxClose.addEventListener('click', closeLightbox);
  if (lightbox) {
    lightbox.addEventListener('click', function (e) {
      if (e.target === lightbox) closeLightbox();
    });
  }
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

  // ---- Confirm Delete ----
  document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', function (e) {
      if (!confirm(this.getAttribute('data-confirm') || 'Are you sure?')) e.preventDefault();
    });
  });

  // ---- Alert Auto-dismiss ----
  document.querySelectorAll('.alert[data-auto-dismiss]').forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity 0.5s';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 500);
    }, parseInt(alert.getAttribute('data-auto-dismiss')) || 4000);
  });

  // ---- Active Nav Link ----
  const currentPath = window.location.pathname.split('/').pop();
  document.querySelectorAll('.nav-link, .admin-nav-link').forEach(link => {
    const href = link.getAttribute('href');
    if (href && href.includes(currentPath) && currentPath !== '') {
      link.classList.add('active');
    }
  });

  // ---- Smooth Scroll ----
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  // ---- Image Preview on Upload ----
  document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
    const previewId = input.getAttribute('data-preview');
    const preview = document.getElementById(previewId);
    if (preview) {
      input.addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
          reader.readAsDataURL(file);
        }
      });
    }
  });

  // ---- Tab System ----
  document.querySelectorAll('[data-tab-trigger]').forEach(trigger => {
    trigger.addEventListener('click', function () {
      const tabGroup = this.closest('[data-tab-group]');
      if (!tabGroup) return;
      const targetId = this.getAttribute('data-tab-trigger');
      tabGroup.querySelectorAll('[data-tab-trigger]').forEach(t => t.classList.remove('active'));
      tabGroup.querySelectorAll('[data-tab-content]').forEach(c => c.style.display = 'none');
      this.classList.add('active');
      const content = tabGroup.querySelector(`[data-tab-content="${targetId}"]`);
      if (content) content.style.display = '';
    });
  });

  // ---- Hero Parallax ----
  const hero = document.querySelector('.hero');
  if (hero) {
    window.addEventListener('scroll', function () {
      const scrolled = window.pageYOffset;
      const heroBg = hero.querySelector('.hero-bg');
      if (heroBg) heroBg.style.transform = `translateY(${scrolled * 0.3}px)`;
    }, { passive: true });
  }

  // ---- Number format live ----
  document.querySelectorAll('.format-number').forEach(el => {
    const n = parseInt(el.textContent.replace(/[^0-9]/g, ''));
    if (!isNaN(n)) el.textContent = n.toLocaleString();
  });

});
