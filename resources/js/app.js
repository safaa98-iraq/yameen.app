import Alpine from 'alpinejs';
import { readSharedText, writeSharedText } from './lib/shared-text';
import { registerTools } from './tools';
import { registerFonts } from './fonts';
import { createCreditsStore } from './lib/credits';

// Shared text across tools, persisted per browser session.
Alpine.store('text', {
    value: readSharedText(),
    set(value) {
        this.value = value;
        writeSharedText(value);
    },
});

Alpine.store('credits', createCreditsStore());
document.addEventListener('alpine:initialized', () => Alpine.store('credits').refresh());

// Auto-growing textarea: <textarea x-data x-autogrow>
Alpine.directive('autogrow', (el) => {
    const resize = () => {
        el.style.height = 'auto';
        el.style.height = `${el.scrollHeight + 2}px`;
    };
    el.addEventListener('input', resize);
    queueMicrotask(resize);
});

// Ctrl/Cmd + Enter triggers the page's primary action: <button data-primary-action>
document.addEventListener('keydown', (event) => {
    if (event.key !== 'Enter' || !(event.ctrlKey || event.metaKey)) return;
    const target = document.querySelector('[data-primary-action]');
    if (target) {
        event.preventDefault();
        target.click();
    }
});

registerTools(Alpine);
registerFonts(Alpine);

window.Alpine = Alpine;
Alpine.start();
