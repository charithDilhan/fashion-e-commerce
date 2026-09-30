/* ─── Header UI ─── */
const menuButton    = document.querySelector('[data-menu-toggle]');
const mainNav       = document.querySelector('[data-main-nav]');
const profileButton = document.querySelector('[data-profile-toggle]');
const profileMenu   = document.querySelector('[data-profile-menu]');

menuButton?.addEventListener('click', () => {
  mainNav?.classList.toggle('open');
  if (!mainNav?.classList.contains('open')) closeAllMenus();
});

profileButton?.addEventListener('click', (e) => {
  e.stopPropagation();
  profileMenu?.classList.toggle('open');
});
document.addEventListener('click', () => profileMenu?.classList.remove('open'));
profileMenu?.addEventListener('click', (e) => e.stopPropagation());

setTimeout(() => document.querySelectorAll('.toast').forEach((t) => t.remove()), 5500);

/* ─────────────────────────────────────────────────────────
   DROPDOWN MENUS — Inline Style controlled (bypasses cache)
   ───────────────────────────────────────────────────────── */

function isMobile() { return window.innerWidth <= 980; }

/* Apply hidden styles directly to a menu element */
function hideMenu(menu) {
  if (!menu) return;
  menu.style.cssText = 'display:none!important;';
}

/* Apply visible styles directly — flex column guaranteed */
function showMenu(menu) {
  if (!menu) return;
  menu.style.cssText = [
    'display:flex!important',
    'flex-direction:column!important',
    'position:absolute',
    'top:calc(100% + 8px)',
    'left:50%',
    'transform:translateX(-50%)',
    'background:#fff',
    'border:1px solid rgba(0,0,0,0.09)',
    'border-radius:16px',
    'padding:6px',
    'min-width:200px',
    'box-shadow:0 8px 30px rgba(0,0,0,0.13),0 2px 8px rgba(0,0,0,0.06)',
    'z-index:1000',
    'gap:2px',
  ].join(';');
}

/* Apply mobile-visible styles */
function showMenuMobile(menu) {
  if (!menu) return;
  menu.style.cssText = [
    'display:flex!important',
    'flex-direction:column!important',
    'position:static',
    'transform:none',
    'background:transparent',
    'box-shadow:none',
    'border:none',
    'border-left:3px solid #e8e3dc',
    'border-radius:0',
    'padding:4px 0 8px 14px',
    'margin:4px 0 6px 0',
    'min-width:unset',
    'gap:0',
    'z-index:auto',
  ].join(';');
}

/* ── Initialise: hide all menus immediately on page load ── */
const dropdownItems = document.querySelectorAll('.nav-item.dropdown');
dropdownItems.forEach((item) => {
  const menu = item.querySelector('.dropdown-menu');
  hideMenu(menu);
});

/* ── Style each menu link ── */
document.querySelectorAll('.dropdown-menu a').forEach((a) => {
  a.style.cssText = [
    'display:block',
    'padding:10px 16px',
    'color:#2d2d2d',
    'font-size:0.875rem',
    'font-weight:500',
    'border-radius:10px',
    'white-space:nowrap',
    'text-decoration:none',
    'transition:background 0.15s,color 0.15s',
    'line-height:1.4',
  ].join(';');

  a.addEventListener('mouseenter', () => {
    a.style.background = '#f7f4ef';
    a.style.color = '#e59a2f';
  });
  a.addEventListener('mouseleave', () => {
    a.style.background = '';
    a.style.color = '#2d2d2d';
  });
});

/* ── Desktop: mouseenter / mouseleave ── */
dropdownItems.forEach((item) => {
  const menu   = item.querySelector('.dropdown-menu');
  const toggle = item.querySelector('.dropdown-toggle');
  let timer    = null;

  item.addEventListener('mouseenter', () => {
    if (isMobile()) return;
    clearTimeout(timer);
    // Close all others
    dropdownItems.forEach((other) => {
      if (other !== item) hideMenu(other.querySelector('.dropdown-menu'));
    });
    showMenu(menu);
  });

  item.addEventListener('mouseleave', () => {
    if (isMobile()) return;
    timer = setTimeout(() => hideMenu(menu), 130);
  });

  /* Mobile: click toggle */
  toggle?.addEventListener('click', (e) => {
    if (!isMobile()) return;
    e.preventDefault();
    const visible = menu && menu.style.display !== 'none' && menu.style.display !== '';
    // Hide all
    dropdownItems.forEach((other) => hideMenu(other.querySelector('.dropdown-menu')));
    // Reset arrows
    document.querySelectorAll('.dropdown-toggle').forEach((t) => {
      t.style.removeProperty('color');
    });
    if (!visible) {
      showMenuMobile(menu);
      if (toggle) toggle.style.color = '#e59a2f';
    }
  });
});

/* Click outside closes all */
document.addEventListener('click', (e) => {
  if (!e.target.closest('.nav-item.dropdown')) {
    dropdownItems.forEach((item) => hideMenu(item.querySelector('.dropdown-menu')));
  }
});

