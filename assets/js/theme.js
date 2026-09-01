/**
 * SkySoft Weather - Dynamic Theme & Background Effects
 */

const SkyTheme = (function () {
    const THEMES = [
        'theme-default', 'theme-clear', 'theme-cloudy', 'theme-rain',
        'theme-snow', 'theme-storm', 'theme-night', 'theme-fog',
    ];
    let lightningTimer = null;
    let parallaxBound = false;

    function init() {
        createSkyBackground();
        setTheme('theme-default');
        bindParallax();
    }

    function createSkyBackground() {
        if (document.querySelector('.sky-bg')) return;

        const sky = document.createElement('div');
        sky.className = 'sky-bg';
        sky.setAttribute('aria-hidden', 'true');
        sky.innerHTML = `
            <div class="sky-gradient"></div>
            <div class="sky-aurora"></div>
            <div class="sky-mesh"></div>
            <div class="light-rays"></div>
            <div class="celestial sun" id="sky-sun" aria-hidden="true"></div>
            <div class="celestial moon" id="sky-moon" aria-hidden="true"></div>
            <div class="fog-layer fog-1"></div>
            <div class="fog-layer fog-2"></div>
            <div class="fog-layer fog-3"></div>
            <div class="cloud cloud-1"></div>
            <div class="cloud cloud-2"></div>
            <div class="cloud cloud-3"></div>
            <div class="cloud cloud-4"></div>
            <div class="cloud cloud-5"></div>
            <div id="stars-container" class="stars-container"></div>
            <div id="weather-particles" class="weather-particles"></div>
            <div class="lightning-flash" id="lightning-flash"></div>
            <div class="sky-vignette"></div>
        `;
        document.body.insertBefore(sky, document.body.firstChild);
        createStars(90);
    }

    function createStars(count) {
        const container = document.getElementById('stars-container');
        if (!container) return;

        container.innerHTML = '';
        for (let i = 0; i < count; i++) {
            const star = document.createElement('span');
            star.className = 'star';
            const size = Math.random() > 0.85 ? 3 : Math.random() > 0.5 ? 2 : 1;
            star.style.cssText = `
                left: ${Math.random() * 100}%;
                top: ${Math.random() * 65}%;
                width: ${size}px;
                height: ${size}px;
                animation-delay: ${Math.random() * 5}s;
                animation-duration: ${2 + Math.random() * 4}s;
            `;
            container.appendChild(star);
        }

        for (let i = 0; i < 3; i++) {
            const shooting = document.createElement('span');
            shooting.className = 'shooting-star';
            shooting.style.cssText = `
                top: ${10 + Math.random() * 30}%;
                left: ${Math.random() * 60}%;
                animation-delay: ${3 + i * 7 + Math.random() * 5}s;
            `;
            container.appendChild(shooting);
        }
    }

    function setTheme(themeClass) {
        const theme = themeClass || 'theme-default';
        THEMES.forEach(t => document.body.classList.remove(t));
        document.body.classList.add(theme);
        updateParticles(theme);
        updateCelestial(theme);
        updateLightning(theme);
    }

    function themeFromWeather(data) {
        if (!data || !data.current) return 'theme-default';

        const condition = data.current.condition || '';
        const icon = data.current.icon || '';
        const isNight = icon === 'moon' || icon === 'partly-cloudy-night';

        if (isNight) return 'theme-night';

        switch (condition) {
            case 'clear': return 'theme-clear';
            case 'partly_cloudy': return 'theme-cloudy';
            case 'fog': return 'theme-fog';
            case 'drizzle':
            case 'rain':
            case 'rain_showers': return 'theme-rain';
            case 'snow':
            case 'snow_showers': return 'theme-snow';
            case 'thunderstorm': return 'theme-storm';
            default: return 'theme-cloudy';
        }
    }

    function applyWeatherTheme(data) {
        setTheme(themeFromWeather(data));
    }

    function resetTheme() {
        stopLightning();
        setTheme('theme-default');
    }

    function updateCelestial(theme) {
        const sun = document.getElementById('sky-sun');
        const moon = document.getElementById('sky-moon');
        if (!sun || !moon) return;

        sun.classList.toggle('visible', theme === 'theme-clear' || theme === 'theme-default');
        moon.classList.toggle('visible', theme === 'theme-night');
    }

    function updateParticles(theme) {
        clearParticles();
        const container = document.getElementById('weather-particles');
        if (!container) return;

        if (theme === 'theme-rain') {
            spawnParticles(container, 'rain', 55);
        } else if (theme === 'theme-storm') {
            spawnParticles(container, 'rain', 80);
            spawnParticles(container, 'mist', 12);
        } else if (theme === 'theme-snow') {
            spawnParticles(container, 'snow', 50);
        } else if (theme === 'theme-clear') {
            spawnParticles(container, 'sparkle', 18);
        } else if (theme === 'theme-fog') {
            spawnParticles(container, 'mist', 20);
        }
    }

    function spawnParticles(container, type, count) {
        container.className = 'weather-particles active particles-' + type;

        for (let i = 0; i < count; i++) {
            const p = document.createElement('span');
            p.className = 'particle';

            if (type === 'rain') {
                p.style.left = Math.random() * 100 + '%';
                p.style.animationDuration = (0.4 + Math.random() * 0.8) + 's';
                p.style.animationDelay = Math.random() * 2 + 's';
                p.style.opacity = (0.2 + Math.random() * 0.6).toFixed(2);
                p.style.height = (14 + Math.random() * 16) + 'px';
            } else if (type === 'snow') {
                p.style.left = Math.random() * 100 + '%';
                p.style.animationDuration = (3 + Math.random() * 5) + 's';
                p.style.animationDelay = Math.random() * 4 + 's';
                const size = 2 + Math.random() * 6;
                p.style.width = size + 'px';
                p.style.height = size + 'px';
                p.style.opacity = (0.4 + Math.random() * 0.6).toFixed(2);
            } else if (type === 'mist') {
                p.style.left = Math.random() * 100 + '%';
                p.style.top = (20 + Math.random() * 60) + '%';
                p.style.animationDuration = (8 + Math.random() * 12) + 's';
                p.style.animationDelay = Math.random() * 6 + 's';
                p.style.width = (60 + Math.random() * 120) + 'px';
                p.style.height = (30 + Math.random() * 50) + 'px';
            } else if (type === 'sparkle') {
                p.style.left = Math.random() * 100 + '%';
                p.style.top = Math.random() * 80 + '%';
                p.style.animationDuration = (2 + Math.random() * 3) + 's';
                p.style.animationDelay = Math.random() * 4 + 's';
                const size = 2 + Math.random() * 3;
                p.style.width = size + 'px';
                p.style.height = size + 'px';
            }

            container.appendChild(p);
        }
    }

    function clearParticles() {
        const container = document.getElementById('weather-particles');
        if (container) {
            container.innerHTML = '';
            container.className = 'weather-particles';
        }
    }

    function updateLightning(theme) {
        stopLightning();
        if (theme !== 'theme-storm') return;

        const flash = () => {
            const el = document.getElementById('lightning-flash');
            if (!el || !document.body.classList.contains('theme-storm')) return;
            el.classList.add('active');
            setTimeout(() => el.classList.remove('active'), 120 + Math.random() * 180);
            lightningTimer = setTimeout(flash, 2000 + Math.random() * 6000);
        };

        lightningTimer = setTimeout(flash, 1500 + Math.random() * 3000);
    }

    function stopLightning() {
        if (lightningTimer) {
            clearTimeout(lightningTimer);
            lightningTimer = null;
        }
        const el = document.getElementById('lightning-flash');
        if (el) el.classList.remove('active');
    }

    function bindParallax() {
        if (parallaxBound || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        parallaxBound = true;

        let ticking = false;
        document.addEventListener('mousemove', (e) => {
            if (ticking) return;
            ticking = true;
            requestAnimationFrame(() => {
                const x = (e.clientX / window.innerWidth - 0.5) * 2;
                const y = (e.clientY / window.innerHeight - 0.5) * 2;
                const sky = document.querySelector('.sky-bg');
                if (sky) {
                    sky.style.setProperty('--px', x.toFixed(3));
                    sky.style.setProperty('--py', y.toFixed(3));
                }
                ticking = false;
            });
        });
    }

    return {
        init,
        setTheme,
        applyWeatherTheme,
        resetTheme,
    };
})();

document.addEventListener('DOMContentLoaded', () => SkyTheme.init());
