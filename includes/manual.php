<?php
/**
 * SkySoft Weather - User Manual Modal
 */
?>
<div id="manual-modal" class="modal" role="dialog" aria-labelledby="manual-title" aria-hidden="true">
    <div class="modal-overlay" id="manual-overlay"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="manual-title" data-i18n="manual_title">User Manual</h2>
            <button type="button" class="modal-close" id="manual-close" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <section class="manual-section">
                <h3 data-i18n="manual_search_title">Searching for a City</h3>
                <p data-i18n="manual_search_text">Type a city name in the search box at the top of the page. As you type, suggestions will appear. Click on a city from the suggestions to view its weather.</p>
            </section>
            <section class="manual-section">
                <h3 data-i18n="manual_weather_title">Viewing Weather</h3>
                <p data-i18n="manual_weather_text">After selecting a city, you will see the current weather, hourly forecast for the next 24 hours, and a 7-day forecast. All data is provided in real-time from the Open-Meteo API.</p>
            </section>
            <section class="manual-section">
                <h3 data-i18n="manual_countries_title">Country A–Z Browser</h3>
                <p data-i18n="manual_countries_text">Click on the Countries tab to browse countries alphabetically. Click any letter (A–Z) to see countries starting with that letter. Click a country to search for its capital city weather.</p>
            </section>
            <section class="manual-section">
                <h3 data-i18n="manual_favorites_title">Using Favorites</h3>
                <p data-i18n="manual_favorites_text">When viewing a city's weather, click the heart icon to add it to your favorites. Click the Favorites tab to see all saved cities. Click a favorite to view its weather, or click the remove button to delete it.</p>
            </section>
            <section class="manual-section">
                <h3 data-i18n="manual_language_title">Changing Language</h3>
                <p data-i18n="manual_language_text">Use the language selector in the header to switch between English, Malay, Chinese, and Japanese. Your language preference is saved automatically.</p>
            </section>
        </div>
    </div>
</div>
