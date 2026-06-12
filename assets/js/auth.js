/**
 * AuthPortal — Client-Side JavaScript
 * Handles: tab switching, password toggle, strength meter, form validation
 */

document.addEventListener('DOMContentLoaded', () => {

    /* =============================================
       TAB SWITCHING
       ============================================= */
    const tabs      = document.querySelectorAll('.tab');
    const panels    = document.querySelectorAll('.panel');
    const indicator = document.querySelector('.tab-indicator');

    function activateTab(tab) {
        tabs.forEach(t   => { t.classList.remove('active'); t.setAttribute('aria-selected', 'false'); });
        panels.forEach(p => p.classList.remove('active'));

        tab.classList.add('active');
        tab.setAttribute('aria-selected', 'true');

        const target = document.getElementById(tab.dataset.target);
        if (target) target.classList.add('active');

        // Move indicator
        const idx = [...tabs].indexOf(tab);
        if (indicator) {
            indicator.style.left = (idx * (100 / tabs.length)) + '%';
        }
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', () => activateTab(tab));
    });

    // If there was a register attempt (error on register), switch to register tab
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');
    if (activeTab === 'register') {
        const regTab = document.getElementById('tab-register');
        if (regTab) activateTab(regTab);
    }

    // Auto-switch to register tab if register form has values filled
    const regInputs = ['reg-fullname', 'reg-username', 'reg-email'];
    const hasRegValues = regInputs.some(id => {
        const el = document.getElementById(id);
        return el && el.value.trim() !== '';
    });
    if (hasRegValues) {
        const regTab = document.getElementById('tab-register');
        if (regTab) activateTab(regTab);
    }

    /* =============================================
       PASSWORD TOGGLE
       ============================================= */
    document.querySelectorAll('.toggle-pw').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.target);
            if (!input) return;
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';

            // Toggle icon
            const eyeOpen  = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
            const eyeClose = `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>`;
            const svg = btn.querySelector('svg');
            if (svg) svg.innerHTML = isPassword ? eyeClose : eyeOpen;

            btn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
        });
    });

    /* =============================================
       PASSWORD STRENGTH METER
       ============================================= */
    const pwInput      = document.getElementById('reg-password');
    const strengthFill = document.getElementById('strength-fill');
    const strengthLbl  = document.getElementById('strength-label');

    function getStrength(pw) {
        let score = 0;
        if (pw.length >= 6)  score++;
        if (pw.length >= 10) score++;
        if (/[A-Z]/.test(pw)) score++;
        if (/[0-9]/.test(pw)) score++;
        if (/[^A-Za-z0-9]/.test(pw)) score++;
        return score; // 0 – 5
    }

    const levels = [
        { pct: '0%',   clr: 'transparent', lbl: '' },
        { pct: '20%',  clr: '#f87171',     lbl: 'Weak' },
        { pct: '40%',  clr: '#fb923c',     lbl: 'Fair' },
        { pct: '60%',  clr: '#facc15',     lbl: 'Good' },
        { pct: '80%',  clr: '#34d399',     lbl: 'Strong' },
        { pct: '100%', clr: '#4ade80',     lbl: 'Excellent' },
    ];

    if (pwInput && strengthFill && strengthLbl) {
        pwInput.addEventListener('input', () => {
            const score = pwInput.value.length === 0 ? 0 : Math.max(1, getStrength(pwInput.value));
            const lvl   = levels[score];
            strengthFill.style.width      = lvl.pct;
            strengthFill.style.background = lvl.clr;
            strengthFill.style.boxShadow  = score > 0 ? `0 0 8px ${lvl.clr}60` : 'none';
            strengthLbl.textContent       = lvl.lbl;
            strengthLbl.style.color       = lvl.clr;
        });
    }

    /* =============================================
       FORM VALIDATION (client-side enhancement)
       ============================================= */
    function showInputError(input, msg) {
        input.style.borderColor = 'rgba(248, 113, 113, 0.6)';
        input.style.boxShadow   = '0 0 0 3px rgba(248, 113, 113, 0.15)';
        let hint = input.closest('.form-group').querySelector('.input-hint');
        if (!hint) {
            hint = document.createElement('span');
            hint.className = 'input-hint';
            hint.style.cssText = 'font-size:0.75rem;color:#f87171;margin-top:-0.25rem;';
            input.closest('.form-group').appendChild(hint);
        }
        hint.textContent = msg;
    }

    function clearInputError(input) {
        input.style.borderColor = '';
        input.style.boxShadow   = '';
        const hint = input.closest('.form-group')?.querySelector('.input-hint');
        if (hint) hint.remove();
    }

    // Login form
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            const id  = document.getElementById('login-identifier');
            const pw  = document.getElementById('login-password');
            let valid = true;

            [id, pw].forEach(clearInputError);

            if (!id.value.trim()) { showInputError(id, 'This field is required.'); valid = false; }
            if (!pw.value)        { showInputError(pw, 'This field is required.'); valid = false; }

            if (!valid) e.preventDefault();
            else {
                const btn = loginForm.querySelector('.btn-primary');
                if (btn) { btn.disabled = true; btn.querySelector('span').textContent = 'Signing in…'; }
            }
        });
    }

    // Register form
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            const fields = {
                'reg-fullname': 'Full name is required.',
                'reg-username': 'Username is required.',
                'reg-email'   : 'Email is required.',
                'reg-password': 'Password is required.',
                'reg-confirm' : 'Please confirm your password.',
            };

            let valid = true;

            Object.entries(fields).forEach(([id, msg]) => {
                const el = document.getElementById(id);
                clearInputError(el);
                if (!el.value.trim()) { showInputError(el, msg); valid = false; }
            });

            const emailEl = document.getElementById('reg-email');
            if (emailEl.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailEl.value)) {
                showInputError(emailEl, 'Enter a valid email address.');
                valid = false;
            }

            const pwEl  = document.getElementById('reg-password');
            const cfmEl = document.getElementById('reg-confirm');
            if (pwEl.value && pwEl.value.length < 6) {
                showInputError(pwEl, 'At least 6 characters required.');
                valid = false;
            }
            if (pwEl.value && cfmEl.value && pwEl.value !== cfmEl.value) {
                showInputError(cfmEl, 'Passwords do not match.');
                valid = false;
            }

            if (!valid) {
                e.preventDefault();
            } else {
                const btn = registerForm.querySelector('.btn-primary');
                if (btn) { btn.disabled = true; btn.querySelector('span').textContent = 'Creating account…'; }
            }
        });

        // Live confirm check
        const cfm = document.getElementById('reg-confirm');
        const pw2 = document.getElementById('reg-password');
        if (cfm && pw2) {
            cfm.addEventListener('input', () => {
                if (cfm.value && pw2.value !== cfm.value) {
                    showInputError(cfm, 'Passwords do not match.');
                } else {
                    clearInputError(cfm);
                }
            });
        }
    }

});
