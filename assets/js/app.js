/**
 * SkySoft Weather - Main Application
 */

(function () {
    'use strict';

    const API_BASE = 'api/';
    let searchTimeout = null;
    let currentCity = null;
    let favorites = [];
    let dbAvailable = true;
    let lastWeatherData = null;

    // DOM Elements
    const searchInput = document.getElementById('search-input');
    const searchClear = document.getElementById('search-clear');
    const searchSuggestions = document.getElementById('search-suggestions');
    const weatherWelcome = document.getElementById('weather-welcome');
    const weatherLoading = document.getElementById('weather-loading');
    const weatherError = document.getElementById('weather-error');
    const weatherDisplay = document.getElementById('weather-display');
    const errorMessage = document.getElementById('error-message');
    const btnFavorite = document.getElementById('btn-favorite');
    const letterNav = document.getElementById('letter-nav');
    const countriesList = document.getElementById('countries-list');
    const favoritesList = document.getElementById('favorites-list');
    const historyList = document.getElementById('history-list');

    // Weather icon SVGs
    const WEATHER_ICONS = {
        sun: '<svg viewBox="0 0 64 64"><circle cx="32" cy="32" r="14" fill="#FFD93D"/><g stroke="#FFD93D" stroke-width="2.5" stroke-linecap="round"><line x1="32" y1="6" x2="32" y2="14"/><line x1="32" y1="50" x2="32" y2="58"/><line x1="6" y1="32" x2="14" y2="32"/><line x1="50" y1="32" x2="58" y2="32"/><line x1="13.6" y1="13.6" x2="19.2" y2="19.2"/><line x1="44.8" y1="44.8" x2="50.4" y2="50.4"/><line x1="13.6" y1="50.4" x2="19.2" y2="44.8"/><line x1="44.8" y1="19.2" x2="50.4" y2="13.6"/></g></svg>',
        moon: '<svg viewBox="0 0 64 64"><path d="M44 12c-12 2-20 12-20 24s8 22 20 24c-16-4-26-18-26-34S28 16 44 12z" fill="#C4B5FD"/></svg>',
        'partly-cloudy': '<svg viewBox="0 0 64 64"><circle cx="24" cy="24" r="10" fill="#FFD93D"/><path d="M18 42h30a10 10 0 0 0 0-20 12 12 0 0 0-23.5 3.5A8 8 0 0 0 18 42z" fill="#E2E8F0"/></svg>',
        'partly-cloudy-night': '<svg viewBox="0 0 64 64"><path d="M36 14c-8 1-14 8-14 16a14 14 0 0 0 14 16c-10-2-17-10-17-20s7-18 17-20z" fill="#C4B5FD"/><path d="M18 42h30a10 10 0 0 0 0-20 12 12 0 0 0-23.5 3.5A8 8 0 0 0 18 42z" fill="#94A3B8"/></svg>',
        cloud: '<svg viewBox="0 0 64 64"><path d="M16 44h34a12 12 0 0 0 0-24 14 14 0 0 0-27.3 4A10 10 0 0 0 16 44z" fill="#CBD5E1"/></svg>',
        fog: '<svg viewBox="0 0 64 64"><path d="M12 28h40M8 36h48M12 44h40" stroke="#94A3B8" stroke-width="3" stroke-linecap="round"/></svg>',
        drizzle: '<svg viewBox="0 0 64 64"><path d="M16 30h32a10 10 0 0 0 0-20 12 12 0 0 0-23.5 3.5A8 8 0 0 0 16 30z" fill="#CBD5E1"/><g fill="#60A5FA"><circle cx="22" cy="42" r="2"/><circle cx="32" cy="46" r="2"/><circle cx="42" cy="42" r="2"/></g></svg>',
        rain: '<svg viewBox="0 0 64 64"><path d="M16 28h32a10 10 0 0 0 0-20 12 12 0 0 0-23.5 3.5A8 8 0 0 0 16 28z" fill="#94A3B8"/><g stroke="#3B82F6" stroke-width="2.5" stroke-linecap="round"><line x1="22" y1="38" x2="20" y2="48"/><line x1="32" y1="38" x2="30" y2="50"/><line x1="42" y1="38" x2="40" y2="48"/></g></svg>',
        'rain-showers': '<svg viewBox="0 0 64 64"><path d="M12 26h36a10 10 0 0 0 0-20 12 12 0 0 0-23.5 3.5A8 8 0 0 0 12 26z" fill="#94A3B8"/><path d="M44 18h8a6 6 0 0 0 0-12" fill="none" stroke="#CBD5E1" stroke-width="2"/><g stroke="#3B82F6" stroke-width="2.5" stroke-linecap="round"><line x1="20" y1="36" x2="18" y2="46"/><line x1="30" y1="36" x2="28" y2="48"/><line x1="40" y1="36" x2="38" y2="46"/></g></svg>',
        snow: '<svg viewBox="0 0 64 64"><path d="M16 28h32a10 10 0 0 0 0-20 12 12 0 0 0-23.5 3.5A8 8 0 0 0 16 28z" fill="#E2E8F0"/><g fill="#93C5FD"><circle cx="22" cy="40" r="2.5"/><circle cx="32" cy="44" r="2.5"/><circle cx="42" cy="40" r="2.5"/><circle cx="27" cy="50" r="2"/><circle cx="37" cy="50" r="2"/></g></svg>',
        thunderstorm: '<svg viewBox="0 0 64 64"><path d="M16 28h32a10 10 0 0 0 0-20 12 12 0 0 0-23.5 3.5A8 8 0 0 0 16 28z" fill="#64748B"/><polygon points="30,34 24,46 30,46 26,56 38,42 32,42 36,34" fill="#FBBF24"/></svg>',
    };

    function getIcon(iconName) {
        return WEATHER_ICONS[iconName] || WEATHER_ICONS.cloud;
    }

    function showElement(el) {
        if (el) el.classList.remove('hidden');
    }

    function hideElement(el) {
        if (el) el.classList.add('hidden');
    }

    function showWeatherState(state) {
        hideElement(weatherWelcome);
        hideElement(weatherLoading);
        hideElement(weatherError);
        hideElement(weatherDisplay);

        switch (state) {
            case 'welcome': showElement(weatherWelcome); break;
            case 'loading': showElement(weatherLoading); break;
            case 'error': showElement(weatherError); break;
            case 'display': showElement(weatherDisplay); break;
        }
    }

    // Tab Navigation
    function initTabs() {
        document.querySelectorAll('.nav-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                const target = tab.dataset.tab;
                document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
                tab.classList.add('active');
                document.getElementById('tab-' + target).classList.add('active');

                if (target === 'favorites') loadFavorites();
                if (target === 'history') loadHistory();
                if (target === 'countries' && !letterNav.hasChildNodes()) initCountries();
            });
        });
    }

    // Search
    function initSearch() {
        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim();
            searchClear.classList.toggle('hidden', query.length === 0);

            clearTimeout(searchTimeout);
            if (query.length < 2) {
                hideSuggestions();
                return;
            }

            searchTimeout = setTimeout(() => searchCities(query), 300);
        });

        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') hideSuggestions();
        });

        searchClear.addEventListener('click', () => {
            searchInput.value = '';
            searchClear.classList.add('hidden');
            hideSuggestions();
            searchInput.focus();
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.search-container')) {
                hideSuggestions();
            }
        });
    }

    function hideSuggestions() {
        searchSuggestions.classList.add('hidden');
        searchSuggestions.innerHTML = '';
    }

    async function searchCities(query) {
        try {
            const res = await fetch(API_BASE + 'search.php?q=' + encodeURIComponent(query));
            const data = await res.json();

            if (!data.success) {
                return;
            }

            renderSuggestions(data.results);
        } catch (err) {
            console.error('Search error:', err);
        }
    }

    function renderSuggestions(results) {
        searchSuggestions.innerHTML = '';

        if (!results || results.length === 0) {
            hideSuggestions();
            return;
        }

        results.forEach((city, index) => {
            const li = document.createElement('li');
            li.className = 'suggestion-item animate-in';
            li.style.animationDelay = (index * 0.04) + 's';
            li.setAttribute('role', 'option');
            const admin = city.admin1 ? `, ${city.admin1}` : '';
            li.innerHTML = `
                <div class="suggestion-left">
                    ${renderFlag(city.country_code, city.country, 'country-flag suggestion-flag')}
                    <span class="suggestion-name">${escapeHtml(city.name)}${escapeHtml(admin)}</span>
                </div>
                <span class="suggestion-country">${escapeHtml(city.country)}</span>`;
            li.addEventListener('click', () => selectCity(city));
            searchSuggestions.appendChild(li);
        });

        searchSuggestions.classList.remove('hidden');
    }

    function selectCity(city) {
        hideSuggestions();
        searchInput.value = city.name;

        currentCity = {
            name: city.name,
            country: city.country,
            country_code: city.country_code,
            latitude: city.latitude,
            longitude: city.longitude,
            timezone: city.timezone || 'UTC',
        };

        switchToWeatherTab();
        loadWeather(currentCity);
    }

    function switchToWeatherTab() {
        document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        document.querySelector('[data-tab="weather"]').classList.add('active');
        document.getElementById('tab-weather').classList.add('active');
    }

    async function loadWeather(city) {
        showWeatherState('loading');

        const params = new URLSearchParams({
            lat: city.latitude,
            lon: city.longitude,
            timezone: city.timezone || 'auto',
            city: city.name,
            country: city.country,
            country_code: city.country_code || '',
        });

        try {
            const res = await fetch(API_BASE + 'weather.php?' + params);
            const data = await res.json();

            if (!data.success || !data.data) {
                errorMessage.textContent = data.message || t('error_weather');
                if (typeof SkyTheme !== 'undefined') SkyTheme.resetTheme();
                showWeatherState('error');
                return;
            }

            renderWeather(data.data);
            showWeatherState('display');
            if (typeof SkyTheme !== 'undefined') {
                SkyTheme.applyWeatherTheme(data.data);
            }
            saveToHistory(city);
            updateFavoriteButton();
        } catch (err) {
            console.error('Weather error:', err);
            errorMessage.textContent = t('error_weather');
            if (typeof SkyTheme !== 'undefined') SkyTheme.resetTheme();
            showWeatherState('error');
        }
    }

    function renderWeather(data) {
        lastWeatherData = data;
        document.getElementById('weather-city').textContent = data.city || '';

        const flagEl = document.getElementById('weather-flag');
        const countryTextEl = document.getElementById('weather-country-text');
        if (flagEl) {
            flagEl.innerHTML = renderFlagLarge(data.country_code, data.country);
        }
        if (countryTextEl) {
            countryTextEl.textContent = data.country || '';
        }
        document.getElementById('weather-datetime').textContent =
            (data.local_date || '') + ' · ' + (data.local_time || '');
        document.getElementById('weather-temp').textContent = Math.round(data.current.temperature);
        document.getElementById('weather-feels').textContent = Math.round(data.current.feels_like);
        document.getElementById('weather-condition').textContent = tWeather(data.current.condition);
        document.getElementById('weather-humidity').textContent = data.current.humidity + '%';
        document.getElementById('weather-wind').textContent =
            data.current.wind_speed + ' ' + t('wind_unit') + ' ' + data.current.wind_direction;
        document.getElementById('weather-pressure').textContent =
            data.current.pressure + ' ' + t('pressure_unit');
        document.getElementById('weather-sunrise').textContent = data.current.sunrise || '—';
        document.getElementById('weather-sunset').textContent = data.current.sunset || '—';

        const iconEl = document.getElementById('weather-icon');
        iconEl.innerHTML = getIcon(data.current.icon);

        // Hourly
        const hourlyList = document.getElementById('hourly-list');
        hourlyList.innerHTML = '';
        (data.hourly || []).forEach((h, index) => {
            const item = document.createElement('div');
            item.className = 'hourly-item animate-in';
            item.style.animationDelay = (index * 0.04) + 's';
            item.innerHTML = `
                <span class="hourly-time">${escapeHtml(h.time)}</span>
                <div class="hourly-icon">${getIcon(h.icon)}</div>
                <span class="hourly-temp">${Math.round(h.temperature)}°</span>
                <span class="hourly-condition">${escapeHtml(tWeather(h.condition))}</span>
            `;
            hourlyList.appendChild(item);
        });

        // Daily
        const dailyList = document.getElementById('daily-list');
        dailyList.innerHTML = '';
        (data.daily || []).forEach((d, index) => {
            const item = document.createElement('div');
            item.className = 'daily-item animate-in';
            item.style.animationDelay = (index * 0.06) + 's';
            item.innerHTML = `
                <span class="daily-date">${escapeHtml(d.display_date)}</span>
                <div class="daily-icon">${getIcon(d.icon)}</div>
                <span class="daily-condition">${escapeHtml(tWeather(d.condition))}</span>
                <span class="daily-temps">
                    <span class="temp-high">${Math.round(d.temp_max)}°</span>
                    <span class="temp-low">${Math.round(d.temp_min)}°</span>
                </span>
            `;
            dailyList.appendChild(item);
        });

        currentCity = {
            name: data.city,
            country: data.country,
            country_code: data.country_code,
            latitude: data.latitude,
            longitude: data.longitude,
            timezone: data.timezone,
        };
    }

    // Favorites
    function initFavorites() {
        btnFavorite.addEventListener('click', toggleFavorite);
    }

    function isFavorite(city) {
        if (!city) return false;
        return favorites.some(f =>
            f.city === city.name &&
            f.country_code === (city.country_code || '') &&
            parseFloat(f.latitude) === parseFloat(city.latitude) &&
            parseFloat(f.longitude) === parseFloat(city.longitude)
        );
    }

    function updateFavoriteButton() {
        if (!currentCity) return;
        const active = isFavorite(currentCity);
        btnFavorite.classList.toggle('active', active);
        btnFavorite.setAttribute('aria-label', active ? t('remove_favorite') : t('add_favorite'));
    }

    async function toggleFavorite() {
        if (!currentCity || !dbAvailable) return;

        const active = isFavorite(currentCity);
        const action = active ? 'remove_favorite' : 'add_favorite';

        try {
            const res = await fetch(API_BASE + 'history.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: action,
                    city: currentCity.name,
                    country: currentCity.country,
                    country_code: currentCity.country_code || '',
                    latitude: currentCity.latitude,
                    longitude: currentCity.longitude,
                }),
            });
            const data = await res.json();
            if (data.success) {
                await loadFavorites();
                updateFavoriteButton();
            }
        } catch (err) {
            console.error('Favorite toggle error:', err);
        }
    }

    async function loadFavorites() {
        try {
            const res = await fetch(API_BASE + 'history.php?action=favorites');
            const data = await res.json();
            dbAvailable = data.available !== false;
            favorites = data.items || [];
            renderFavorites();
        } catch (err) {
            console.error('Load favorites error:', err);
        }
    }

    function renderFavorites() {
        favoritesList.innerHTML = '';

        if (!dbAvailable) {
            favoritesList.innerHTML = `<p class="hint-text">${escapeHtml(t('db_unavailable'))}</p>`;
            return;
        }

        if (favorites.length === 0) {
            favoritesList.innerHTML = `<p class="hint-text" data-i18n="favorites_empty">${escapeHtml(t('favorites_empty'))}</p>`;
            return;
        }

        favorites.forEach((fav, index) => {
            const item = document.createElement('div');
            item.className = 'list-item animate-in';
            item.style.animationDelay = (index * 0.05) + 's';
            item.innerHTML = `
                ${renderFlag(fav.country_code, fav.country, 'country-flag list-flag')}
                <div class="list-item-info">
                    <span class="list-item-name">${escapeHtml(fav.city)}</span>
                    <span class="list-item-sub">${escapeHtml(fav.country)}</span>
                </div>
                <button type="button" class="btn-remove" data-id="${fav.id}">${escapeHtml(t('btn_remove'))}</button>
            `;

            item.querySelector('.list-item-info').addEventListener('click', () => {
                selectCity({
                    name: fav.city,
                    country: fav.country,
                    country_code: fav.country_code,
                    latitude: parseFloat(fav.latitude),
                    longitude: parseFloat(fav.longitude),
                    timezone: 'auto',
                });
            });

            item.querySelector('.btn-remove').addEventListener('click', async (e) => {
                e.stopPropagation();
                await removeFavorite(fav);
            });

            favoritesList.appendChild(item);
        });
    }

    async function removeFavorite(fav) {
        try {
            const res = await fetch(API_BASE + 'history.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'remove_favorite',
                    city: fav.city,
                    country: fav.country,
                    country_code: fav.country_code,
                    latitude: parseFloat(fav.latitude),
                    longitude: parseFloat(fav.longitude),
                }),
            });
            const data = await res.json();
            if (data.success) {
                await loadFavorites();
                updateFavoriteButton();
            }
        } catch (err) {
            console.error('Remove favorite error:', err);
        }
    }

    // History
    async function saveToHistory(city) {
        if (!dbAvailable) return;
        try {
            await fetch(API_BASE + 'history.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'add_history',
                    city: city.name,
                    country: city.country,
                    country_code: city.country_code || '',
                    latitude: city.latitude,
                    longitude: city.longitude,
                }),
            });
        } catch (err) {
            console.error('Save history error:', err);
        }
    }

    async function loadHistory() {
        try {
            const res = await fetch(API_BASE + 'history.php?action=history');
            const data = await res.json();
            dbAvailable = data.available !== false;
            renderHistory(data.items || []);
        } catch (err) {
            console.error('Load history error:', err);
        }
    }

    function renderHistory(items) {
        historyList.innerHTML = '';

        if (!dbAvailable) {
            historyList.innerHTML = `<p class="hint-text">${escapeHtml(t('db_unavailable'))}</p>`;
            return;
        }

        if (items.length === 0) {
            historyList.innerHTML = `<p class="hint-text" data-i18n="history_empty">${escapeHtml(t('history_empty'))}</p>`;
            return;
        }

        items.forEach((item, index) => {
            const el = document.createElement('div');
            el.className = 'list-item clickable animate-in';
            el.style.animationDelay = (index * 0.05) + 's';
            const date = item.searched_at ? formatDate(item.searched_at) : '';
            el.innerHTML = `
                ${renderFlag(item.country_code, item.country, 'country-flag list-flag')}
                <div class="list-item-info">
                    <span class="list-item-name">${escapeHtml(item.city)}</span>
                    <span class="list-item-sub">${escapeHtml(item.country)}${date ? ' · ' + date : ''}</span>
                </div>
            `;
            el.addEventListener('click', () => {
                selectCity({
                    name: item.city,
                    country: item.country,
                    country_code: item.country_code,
                    latitude: parseFloat(item.latitude),
                    longitude: parseFloat(item.longitude),
                    timezone: 'auto',
                });
            });
            historyList.appendChild(el);
        });
    }

    // Countries A-Z
    function initCountries() {
        const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');
        letterNav.innerHTML = '';

        letters.forEach(letter => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'letter-btn';
            btn.textContent = letter;
            btn.addEventListener('click', () => loadCountriesByLetter(letter, btn));
            letterNav.appendChild(btn);
        });
    }

    async function loadCountriesByLetter(letter, btn) {
        document.querySelectorAll('.letter-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');

        countriesList.innerHTML = '<div class="spinner small"></div>';

        try {
            const res = await fetch(API_BASE + 'countries.php?letter=' + letter);
            const data = await res.json();

            if (!data.success || !data.countries || data.countries.length === 0) {
                countriesList.innerHTML = `<p class="hint-text">${escapeHtml(t('countries_none'))}</p>`;
                return;
            }

            countriesList.innerHTML = '';
            data.countries.forEach((country, index) => {
                const item = document.createElement('div');
                item.className = 'country-item clickable animate-in';
                item.style.animationDelay = (index * 0.03) + 's';
                item.innerHTML = `
                    ${renderFlag(country.code, country.name, 'country-flag country-item-flag')}
                    <div class="country-item-text">
                        <span class="country-name">${escapeHtml(country.name)}</span>
                        <span class="country-capital">${escapeHtml(t('capital'))}: ${escapeHtml(country.capital)}</span>
                    </div>
                `;
                item.addEventListener('click', () => searchCountryCapital(country));
                countriesList.appendChild(item);
            });
        } catch (err) {
            console.error('Countries error:', err);
            countriesList.innerHTML = `<p class="hint-text">${escapeHtml(t('error_weather'))}</p>`;
        }
    }

    async function searchCountryCapital(country) {
        searchInput.value = country.capital;
        try {
            const res = await fetch(API_BASE + 'search.php?q=' + encodeURIComponent(country.capital));
            const data = await res.json();
            if (data.success && data.results && data.results.length > 0) {
                const match = data.results.find(r =>
                    r.country_code === country.code || r.country === country.name
                ) || data.results[0];
                selectCity(match);
            }
        } catch (err) {
            console.error('Capital search error:', err);
        }
    }

    // Utilities
    function escapeHtml(str) {
        if (str == null) return '';
        const div = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    }

    function formatDate(dateStr) {
        try {
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return '';
            return d.toLocaleDateString(currentLang, {
                month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit',
            });
        } catch (e) {
            return '';
        }
    }

    // Language change callback
    window.onLanguageChange = function () {
        if (lastWeatherData && weatherDisplay && !weatherDisplay.classList.contains('hidden')) {
            renderWeather(lastWeatherData);
        }
        loadFavorites();
        loadHistory();
    };

    // Init
    document.addEventListener('DOMContentLoaded', () => {
        initTabs();
        initSearch();
        initFavorites();
        loadFavorites();
        showWeatherState('welcome');
        if (typeof SkyTheme !== 'undefined') {
            SkyTheme.resetTheme();
        }
    });
})();
