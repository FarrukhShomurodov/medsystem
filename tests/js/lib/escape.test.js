import { expect, it } from 'vitest';
import { escapeHtml } from '../../../resources/js/lib/escape';

it('escapes the characters that end text or an attribute in HTML', () => {
    expect(escapeHtml('<img src="x" onerror=\'a()\'> & co')).toBe('&lt;img src=&quot;x&quot; onerror=&#39;a()&#39;&gt; &amp; co');
});

it('prints null and undefined as nothing and other values as text', () => {
    expect(escapeHtml(null)).toBe('');
    expect(escapeHtml(undefined)).toBe('');
    expect(escapeHtml(0)).toBe('0');
    expect(escapeHtml('Каримова')).toBe('Каримова');
});
