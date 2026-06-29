# MCV Network — Static Site

Static HTML marketing site built from `sitemap.html` (structure) and `mcv_brand_kit.html` (design language).

## Preview

Internal links are root-relative (`/assets/...`, `/advertisers/`), so open via a server — **not** `file://`:

```bash
node serve.mjs       # → http://localhost:8080
```

## How it's organized

| Path | Purpose |
|------|---------|
| `assets/css/mcv.css` | Design system — tokens (colors, type, spacing, radius) + all components |
| `assets/js/mcv.js` | Shared nav + footer (injected so there's no duplication across pages) |
| `build.mjs` | **Source of truth for content.** A generator that emits every `index.html` |
| `serve.mjs` | Minimal local preview server |
| `<section>/<page>/index.html` | Generated pages, mirroring the sitemap URLs |

Reference originals kept as-is: `sitemap.html`, `mcv_brand_kit.html`, `mcv_homepage.html`.

## Regenerate / edit content

Edit the `pages` data in `build.mjs`, then:

```bash
node build.mjs       # regenerates all 48 pages
```

The generator provides block renderers (`cards`, `tiles`, `metrics`, `featureRows`,
`table`, `pricing`, `prose`, `hero`, `stats`, `cta`, …) so new pages stay on-brand
automatically. Active nav state comes from `<body data-nav="...">`.

## Pages (48)

Home · login · signup · advertisers (+platform, formats, targeting, pricing,
case-studies, get-started) · publishers (+5) · commerce (+3) · technology (×4) ·
solutions (×6, healthcare is the rich vertical) · resources (×6) · company (×6,
contact has a form) · legal (×5).

> Notes: charts use Highcharts via CDN; Font Awesome + Google Fonts also via CDN
> (needs internet). The `ads.mcv.network` SPA and `docs.mcv.network` are separate
> subdomains and not part of this marketing build.
