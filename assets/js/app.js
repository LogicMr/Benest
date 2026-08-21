document.querySelector('[data-sidebar-toggle]')?.addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
document.querySelector('[data-sidebar-collapse]')?.addEventListener('click', () => { document.body.classList.toggle('sidebar-collapsed'); localStorage.setItem('benest-sidebar', document.body.classList.contains('sidebar-collapsed') ? '1' : '0'); });
if (localStorage.getItem('benest-sidebar') === '1') document.body.classList.add('sidebar-collapsed');
const themeToggles = document.querySelectorAll('[data-theme-toggle]');
const setTheme = (theme) => {
	document.body.dataset.theme = theme;
	localStorage.setItem('benest-theme', theme);
	themeToggles.forEach((toggle) => { toggle.innerHTML = `<i class="bi bi-${theme === 'dark' ? 'sun' : 'moon-stars'}"></i><span>${theme === 'dark' ? 'Light' : 'Dark'}</span>`; });
};
setTheme(localStorage.getItem('benest-theme') || 'light');
themeToggles.forEach((toggle) => toggle.addEventListener('click', () => setTheme(document.body.dataset.theme === 'dark' ? 'light' : 'dark')));
const confirmationModal = document.createElement('div');
confirmationModal.className = 'modal fade';
confirmationModal.id = 'benestConfirmModal';
confirmationModal.tabIndex = -1;
confirmationModal.innerHTML = `<div class="modal-dialog modal-dialog-centered"><div class="modal-content glass-modal"><div class="modal-header"><div><span class="section-kicker">Confirm action</span><h3 class="modal-title">Are you sure?</h3></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><div class="confirmation-icon"><i class="bi bi-exclamation-triangle"></i></div><p class="confirmation-message mb-0"></p></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-danger" data-confirm-submit>Continue</button></div></div></div>`;
document.body.appendChild(confirmationModal);
const confirmationInstance = window.bootstrap ? new bootstrap.Modal(confirmationModal) : null;
let confirmationForm = null;
document.querySelectorAll('[data-confirm]').forEach((form) => form.addEventListener('submit', (event) => {
	if (!confirmationInstance) {
		if (!window.confirm(form.dataset.confirm)) event.preventDefault();
		return;
	}
	event.preventDefault();
	confirmationForm = form;
	confirmationModal.querySelector('.confirmation-message').textContent = form.dataset.confirm;
	confirmationInstance.show();
}));
confirmationModal.querySelector('[data-confirm-submit]').addEventListener('click', () => {
	if (confirmationForm) confirmationForm.submit();
});
