/**
 * QR code for setting up an authenticator app (two-factor authentication).
 *
 * Separate small entry (dist/twofa.js) so the backend login page can use it
 * without loading the whole backend bundle. Renders every element with a
 * data-otpauth attribute as an inline SVG - generated locally in the
 * browser, the secret never leaves the page.
 */
import qrcode from 'qrcode-generator';

function renderQrCodes(root = document) {
    root.querySelectorAll('[data-otpauth]').forEach((el) => {
        if (el.dataset.qrRendered === '1') {
            return;
        }
        const qr = qrcode(0, 'M'); // type 0 = smallest version that fits
        qr.addData(el.dataset.otpauth);
        qr.make();
        el.innerHTML = qr.createSvgTag({ cellSize: 4, margin: 4, scalable: true });
        el.dataset.qrRendered = '1';
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => renderQrCodes());
} else {
    renderQrCodes();
}

// the 2FA card in the personal settings is replaced via HTMX
document.addEventListener('htmx:afterSettle', (event) => renderQrCodes(event.target));
