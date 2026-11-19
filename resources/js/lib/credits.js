// Daily credits: the server decides; the browser asks before each charged
// operation. If the API is unreachable (e.g. the static preview), operations
// are allowed: limits are a convenience, not security (Pro features are
// protected server-side).
const api = () => document.querySelector('meta[name="yameen-api"]')?.content ?? '/api';

/** SHA-256 of normalized text, hex. Only this fingerprint is sent, never the text. */
export async function textHash(text) {
    const norm = (text ?? '').normalize('NFC').replace(/\s+/g, ' ').trim();
    if (!norm || !globalThis.crypto?.subtle) return null;
    const buf = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(norm));
    return [...new Uint8Array(buf)].map((b) => b.toString(16).padStart(2, '0')).join('');
}

async function request(path, body) {
    try {
        const res = await fetch(api() + path, {
            method: body ? 'POST' : 'GET',
            headers: { Accept: 'application/json', ...(body ? { 'Content-Type': 'application/json' } : {}) },
            credentials: 'same-origin',
            body: body ? JSON.stringify(body) : undefined,
        });
        if (!res.ok) return null;
        return await res.json();
    } catch {
        return null;
    }
}

export function createCreditsStore() {
    return {
        loaded: false,
        available: false, // false when there is no API (static preview)
        remaining: null,
        limit: null,
        pro: false,
        signedIn: false,
        resetsAt: null,
        blocked: false,
        reason: null, // 'limit' | 'pro'
        apply(status) {
            if (!status) return;
            this.available = true;
            this.loaded = true;
            this.remaining = status.remaining;
            this.limit = status.limit;
            this.pro = status.pro;
            this.signedIn = status.signed_in;
            this.resetsAt = status.resets_at;
        },
        async refresh() {
            const status = await request('/credits');
            if (status) this.apply(status);
            else this.loaded = true;
        },
        /** Ask to run an action. Returns true when allowed. */
        async consume(action, text = '') {
            const status = await request('/credits/consume', { action, text_hash: await textHash(text) });
            if (!status) return true; // no API: allow
            this.apply(status);
            this.blocked = !status.ok;
            this.reason = status.ok ? null : status.reason;
            return status.ok;
        },
        get msUntilReset() {
            return this.resetsAt ? Math.max(0, new Date(this.resetsAt) - Date.now()) : 0;
        },
    };
}
