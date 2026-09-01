# GitHub Upload Guide

The project is ready for GitHub. Follow these steps to publish it.

## What is already prepared

- `LICENSE` — MIT License
- `includes/config.example.php` — database config template (no password)
- `includes/config.php` — **gitignored** (your local password stays private)
- `docs/screenshots/` — preview images for README
- Git repository initialized with initial commit on `main`

## Step 1 — Login to GitHub CLI

Open a terminal in this folder and run:

```bash
gh auth login
```

Choose:
1. **GitHub.com**
2. **HTTPS**
3. **Login with a web browser** (follow the code shown)

## Step 2 — Create repo and push

```bash
cd C:\xampp\htdocs\weather_forecast_system

gh repo create weather-forecast-system --public --source=. --remote=origin --push --description "SkySoft Weather - PHP weather forecast website"
```

Replace `weather-forecast-system` with your preferred repo name if needed.

## Alternative — Manual upload on GitHub website

1. Go to [https://github.com/new](https://github.com/new)
2. Repository name: `weather-forecast-system`
3. Set to **Public**, do **not** add README (already exists locally)
4. Click **Create repository**
5. Run in terminal:

```bash
cd C:\xampp\htdocs\weather_forecast_system
git remote add origin https://github.com/YOUR_USERNAME/weather-forecast-system.git
git push -u origin main
```

## After cloning (for others)

```bash
copy includes\config.example.php includes\config.php
```

Then edit `includes/config.php` with MySQL credentials.

## Optional — Replace screenshots

Take real screenshots and save as PNG in `docs/screenshots/`:
- `home.png`
- `weather.png`
- `countries.png`
- `favorites.png`

Update image paths in `README.md` from `.svg` to `.png`.
