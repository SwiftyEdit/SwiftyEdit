
/*
 * Privacy blur toggle for the ACP header.
 * Blurs every element carrying the "se-privacy-blur" class (e.g. customer
 * names, addresses, order totals) so admins can take a support screenshot
 * without exposing personal data. State persists across page loads via
 * localStorage, mirroring the dark-mode toggle in color-mode.js.
 */

const getStoredPrivacyBlur = () => localStorage.getItem("backendPrivacyBlur");
const setStoredPrivacyBlur = state => localStorage.setItem("backendPrivacyBlur", state);

const applyPrivacyBlur = enabled => {
    document.documentElement.setAttribute('data-privacy-blur', enabled ? 'on' : 'off');
};

applyPrivacyBlur(getStoredPrivacyBlur() === 'on');

function togglePrivacyBlur() {
    const enabled = getStoredPrivacyBlur() === 'on';
    applyPrivacyBlur(!enabled);
    setStoredPrivacyBlur(!enabled ? 'on' : 'off');
}

window.togglePrivacyBlur = togglePrivacyBlur;
