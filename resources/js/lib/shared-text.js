// Text the user typed, shared across every tool page for the browser session.
const KEY = 'yameen:text';

export function readSharedText() {
    try {
        return sessionStorage.getItem(KEY) ?? '';
    } catch {
        return '';
    }
}

export function writeSharedText(value) {
    try {
        if (value) {
            sessionStorage.setItem(KEY, value);
        } else {
            sessionStorage.removeItem(KEY);
        }
    } catch {
        // Storage can be unavailable (private mode, blocked site data). The tool still works.
    }
}
