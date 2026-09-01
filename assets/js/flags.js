/**
 * SkySoft Weather - Country Flag Icons
 */

function getFlagUrl(code, size) {
    if (!code || typeof code !== 'string') return null;
    const normalized = code.toLowerCase().trim();
    if (!/^[a-z]{2}$/.test(normalized)) return null;
    return 'https://flagcdn.com/' + (size || 'w40') + '/' + normalized + '.png';
}

function flagAlt(code, name) {
    return (name || code || 'Country').trim();
}

function renderFlag(code, name, className) {
    const cls = className || 'country-flag';
    const alt = flagAlt(code, name);
    const url = getFlagUrl(code, 'w40');

    if (!url) {
        return '<span class="' + cls + ' country-flag-fallback" role="img" aria-label="' + escapeFlagText(alt) + '">🌍</span>';
    }

    return '<img class="' + cls + '" src="' + url + '" alt="' + escapeFlagText(alt) + ' flag" loading="lazy" width="32" height="24">';
}

function renderFlagLarge(code, name) {
    const url = getFlagUrl(code, 'w80');
    const alt = flagAlt(code, name);

    if (!url) {
        return '<span class="country-flag country-flag-lg country-flag-fallback" role="img" aria-label="' + escapeFlagText(alt) + '">🌍</span>';
    }

    return '<img class="country-flag country-flag-lg" src="' + url + '" alt="' + escapeFlagText(alt) + ' flag" loading="lazy" width="48" height="36">';
}

function escapeFlagText(str) {
    if (str == null) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
