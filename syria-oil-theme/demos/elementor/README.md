# Elementor templates & kit

Shipped layouts for Home and portal pages live under `pages/*.json`.
`homepage.json` mirrors `pages/home.json`.

## Auto-seed (recommended)

After portal/starter import, the theme writes `_elementor_data` onto matching
WordPress pages so **Edit with Elementor** opens the demo instead of a blank
canvas.

- Runs automatically after ministry-portal install and OCDI after-import.
- Force re-apply anytime: **Syria Oil → Setup → Re-apply Elementor demos**.
- Requires **Elementor** + **Syria Oil Widgets** for the widgets to render.

## Elementor Pro Theme Builder

With **Elementor Pro** active, the theme can seed:

| File | Type | Conditions |
|------|------|------------|
| `theme-builder/single-post.json` | Single Post | All Posts |
| `theme-builder/archive.json` | Archive | Post archives |

Apply via **Syria Oil → Setup → Apply Single Post + Archive Theme Builder templates**,
or it runs automatically with portal import / OCDI when Pro is present.

Then open any news post (“اقرأ المزيد”) — Pro’s Single template renders
(Post Title, Featured Image, Content, navigation, related cards). Edit under
**Templates → Theme Builder**.

Classic PHP `single.php` / `archive.php` remain the fallback when no Theme
Builder template matches.

## Kit / Site Settings

Prefer day-to-day brand colors in **Syria Oil → Theme Options** (not Elementor
Global Colors). Front-end CSS reads `syria_oil_options` only.

## Optional OCDI companion files

- `demos/widgets.wie`
- `demos/customizer.dat`
- `demos/preview.png`
- `demos/elementor/kit.json`
