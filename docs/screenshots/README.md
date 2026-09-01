# Screenshots

Real screenshots captured from the running SkySoft Weather application.

| File | Page |
|------|------|
| `home.png` | Welcome / home screen |
| `search.png` | City search with suggestions |
| `weather.png` | Current weather, hourly & 7-day forecast |
| `countries.png` | Country A–Z browser with flags |
| `favorites.png` | Favorite cities |
| `history.png` | Recent search history |

## Re-capture screenshots

Requires Node.js and a running XAMPP server:

```bash
npm install playwright --no-save
npx playwright install chromium
node scripts/capture-screenshots.mjs
```

Screenshots are saved to this folder at **1280×800** resolution.
