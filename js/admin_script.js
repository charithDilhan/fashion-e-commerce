const adminMenuToggle = document.querySelector('[data-admin-menu-toggle]');
const adminSidebar = document.querySelector('[data-admin-sidebar]');
adminMenuToggle?.addEventListener('click', () => adminSidebar?.classList.toggle('open'));
setTimeout(() => document.querySelectorAll('.toast').forEach((toast) => toast.remove()), 5500);
