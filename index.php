<?php
require_once __DIR__ . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkySoft Weather</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://flagcdn.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="theme-default">
    <div class="app-wrapper">
        <!-- Header -->
        <header class="header">
            <div class="header-inner">
                <div class="logo">
                    <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5"/>
                        <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                    </svg>
                    <span class="logo-text">SkySoft Weather</span>
                </div>
                <div class="header-actions">
                    <select id="lang-select" class="lang-select" aria-label="Language">
                        <option value="en">English</option>
                        <option value="ms">Bahasa Melayu</option>
                        <option value="zh">中文</option>
                        <option value="ja">日本語</option>
                    </select>
                    <button type="button" id="btn-manual" class="btn-icon" title="User Manual" data-i18n-title="btn_manual">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                    </button>
                </div>
            </div>
        </header>

        <!-- Search -->
        <section class="search-section">
            <div class="search-container">
                <div class="search-box">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input type="text" id="search-input" class="search-input"
                           placeholder="Search for a city..." data-i18n-placeholder="search_placeholder"
                           autocomplete="off" aria-label="Search city">
                    <button type="button" id="search-clear" class="search-clear hidden" aria-label="Clear">&times;</button>
                </div>
                <ul id="search-suggestions" class="search-suggestions hidden" role="listbox"></ul>
            </div>
        </section>

        <!-- Navigation Tabs -->
        <nav class="nav-tabs">
            <button type="button" class="nav-tab active" data-tab="weather" data-i18n="tab_weather">Weather</button>
            <button type="button" class="nav-tab" data-tab="countries" data-i18n="tab_countries">Countries</button>
            <button type="button" class="nav-tab" data-tab="favorites" data-i18n="tab_favorites">Favorites</button>
            <button type="button" class="nav-tab" data-tab="history" data-i18n="tab_history">History</button>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Weather Tab -->
            <div id="tab-weather" class="tab-panel active">
                <div id="weather-welcome" class="welcome-screen">
                    <div class="welcome-icon">
                        <svg viewBox="0 0 64 64" fill="none">
                            <circle cx="32" cy="32" r="16" fill="#FFD93D"/>
                            <path d="M32 4v6M32 54v6M4 32h6M54 32h6M10.3 10.3l4.2 4.2M49.5 49.5l4.2 4.2M10.3 53.7l4.2-4.2M49.5 14.5l4.2-4.2" stroke="#FFD93D" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <h2 data-i18n="welcome_title">Welcome to SkySoft Weather</h2>
                    <p data-i18n="welcome_text">Search for a city to see current weather, hourly and 7-day forecasts.</p>
                </div>

                <div id="weather-loading" class="loading-screen hidden">
                    <div class="spinner"></div>
                    <p data-i18n="loading">Loading weather data...</p>
                </div>

                <div id="weather-error" class="error-screen hidden">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <p id="error-message" data-i18n="error_weather">Unable to load weather information. Please try again.</p>
                </div>

                <div id="weather-display" class="weather-display hidden">
                    <!-- Current Weather -->
                    <section class="card current-weather">
                        <div class="current-header">
                            <div class="location-info">
                                <h1 id="weather-city" class="city-name"></h1>
                                <p id="weather-country" class="country-name">
                                    <span id="weather-flag"></span>
                                    <span id="weather-country-text"></span>
                                </p>
                                <p id="weather-datetime" class="local-datetime"></p>
                            </div>
                            <button type="button" id="btn-favorite" class="btn-favorite" aria-label="Add to favorites">
                                <svg class="heart-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                </svg>
                            </button>
                        </div>
                        <div class="current-body">
                            <div class="current-main">
                                <div id="weather-icon" class="weather-icon-lg"></div>
                                <div class="temp-block">
                                    <span id="weather-temp" class="temperature"></span>
                                    <span class="temp-unit">°C</span>
                                </div>
                                <p id="weather-condition" class="condition-text"></p>
                                <p class="feels-like"><span data-i18n="feels_like">Feels like</span> <span id="weather-feels"></span>°C</p>
                            </div>
                            <div class="current-details">
                                <div class="detail-item">
                                    <span class="detail-label" data-i18n="humidity">Humidity</span>
                                    <span id="weather-humidity" class="detail-value"></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label" data-i18n="wind">Wind</span>
                                    <span id="weather-wind" class="detail-value"></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label" data-i18n="pressure">Pressure</span>
                                    <span id="weather-pressure" class="detail-value"></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label" data-i18n="sunrise">Sunrise</span>
                                    <span id="weather-sunrise" class="detail-value"></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label" data-i18n="sunset">Sunset</span>
                                    <span id="weather-sunset" class="detail-value"></span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Hourly Forecast -->
                    <section class="card hourly-forecast">
                        <h2 class="section-title" data-i18n="hourly_forecast">Hourly Forecast</h2>
                        <div id="hourly-scroll" class="hourly-scroll">
                            <div id="hourly-list" class="hourly-list"></div>
                        </div>
                    </section>

                    <!-- Daily Forecast -->
                    <section class="card daily-forecast">
                        <h2 class="section-title" data-i18n="daily_forecast">7-Day Forecast</h2>
                        <div id="daily-list" class="daily-list"></div>
                    </section>
                </div>
            </div>

            <!-- Countries Tab -->
            <div id="tab-countries" class="tab-panel">
                <div class="card countries-browser">
                    <h2 class="section-title" data-i18n="countries_title">Browse Countries A–Z</h2>
                    <div id="letter-nav" class="letter-nav"></div>
                    <div id="countries-list" class="countries-list">
                        <p class="hint-text" data-i18n="countries_hint">Click a letter above to browse countries.</p>
                    </div>
                </div>
            </div>

            <!-- Favorites Tab -->
            <div id="tab-favorites" class="tab-panel">
                <div class="card">
                    <h2 class="section-title" data-i18n="favorites_title">Favorite Cities</h2>
                    <div id="favorites-list" class="item-list">
                        <p class="hint-text" data-i18n="favorites_empty">No favorite cities yet. Search for a city and click the heart icon to add it.</p>
                    </div>
                </div>
            </div>

            <!-- History Tab -->
            <div id="tab-history" class="tab-panel">
                <div class="card">
                    <h2 class="section-title" data-i18n="history_title">Recent Searches</h2>
                    <div id="history-list" class="item-list">
                        <p class="hint-text" data-i18n="history_empty">No search history yet. Search for a city to get started.</p>
                    </div>
                </div>
            </div>
        </main>

        <?php include __DIR__ . '/includes/footer.php'; ?>
    </div>
</body>
</html>

