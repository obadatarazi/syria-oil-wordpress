# Syria Oil — WordPress Theme

Arabic-first (RTL), mobile-first WordPress theme for oil & gas news portals.
Built for Elementor and Elementor Pro Theme Builder.

**Publisher:** Obada Al Tarazi — [linkedin.com/in/obada-tarazi](https://www.linkedin.com/in/obada-tarazi)  
**Version:** 1.16.0

## Requirements

- WordPress 6.0+
- PHP 7.4+
- **Syria Oil Widgets** plugin (portable Elementor widgets — required for drag-and-drop blocks)
- Elementor (free) — editor + Global Site Settings bridge
- Elementor Pro — optional (Theme Builder)

## Quick Start (First-Time Installation)

1. Upload `syria-oil-theme.zip` via **Appearance → Themes → Upload Theme** and activate.
2. Upload and activate **`syria-oil-widgets.zip`**.
3. Install **Elementor** (free).
4. Open **Syria Oil → Setup** and click **Import Complete Demo** — one button imports pages, news, images, Side Ads, and Elementor layouts.
5. Optional: Open **Syria Oil → Theme Options** to customize branding, colors, and typography.

That's it! Your site is ready with full demo content.

## Brand colors & fonts

Set brand colors, fonts, logos, layout, oil prices, and widget defaults in **Syria Oil → Theme Options** (Codestar Framework panel) — **not** in Elementor → Site Settings → Global Colors. The theme prints one `:root` CSS block from `syria_oil_options` so classic templates and Elementor widgets stay in sync.

Primary logo and favicon live in Theme Options and sync to WordPress `custom_logo` / `site_icon` for SEO plugins.

## Phase 6 notes

- Options are stored in `syria_oil_options` (migrated once from old Customizer theme_mods).
- Per-page overrides: edit any Page/Post → Syria Oil — Page overrides sidebar.
- Oil & Gas Prices (EIA key + refresh) live under Theme Options (Settings submenu removed from the widgets plugin).

## Widget slug continuity

Existing pages using `syria_oil_featured_news_slider` and `syria_oil_article_card_grid` keep working — Elementor `get_name()` slugs are unchanged.

## Support

Obada Al Tarazi — [linkedin.com/in/obada-tarazi](https://www.linkedin.com/in/obada-tarazi)
