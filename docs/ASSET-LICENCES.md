# Asset licences

Acquisition date for all third-party assets: **23 October 2026**.

## Brand

| Asset | Source | Notes |
| --- | --- | --- |
| `resources/brand-source/zytecs-research-logo-original.png` | Supplied by the business owner ("Zytec Research Digital Logo.png", 1536 × 1024 px, RGB PNG, no transparency, off-white background) | Kept unmodified as the master file. |
| `resources/brand-source/zytecs-research-logo-transparent.png`, `public/images/brand/logo-480/960.webp/.png` | Derived from the original | Background removed by un-blending against white; whitespace trimmed; resized. No recolouring, redrawing or distortion. |
| `public/images/brand/mark-192.png`, `mark-512.png`, `public/favicon.ico`, `favicon-32.png`, `apple-touch-icon.png` | Derived from the original | The "Z" mark cropped from the logo, with the adjoining "y" stroke masked out. |
| `public/images/brand/og-default.jpg` | Derived from the original | Logo centred on white at 1200 × 630 for social sharing. |

**Owner check:** the supplied artwork reads **"Zytec RESEARCH"** while the business name is **"Zytecs Research"**. The site uses "Zytecs Research" in all text and the supplied logo as-is. Please confirm this is intended, or supply an updated logo. An older 2021 "Zytec" logo (`zytec logo.zip`, AI + PNG) was found next to the project and was not used.

## Photography (Unsplash)

All photographs were downloaded through each photo's official Unsplash download endpoint (which only serves images under the standard Unsplash License; "Unsplash+" images require a paid login and were not used), resized to 800 and 1400 px wide and converted to WebP. Files live in `public/images/photos/`.

**Unsplash License:** free to use for commercial and non-commercial purposes, no permission needed, attribution appreciated but not required; you may not sell the photos without significant modification or use them to build a competing image service. Licence text: https://unsplash.com/license

| Local file | Unsplash photo | Photographer | Used on | Alt text |
| --- | --- | --- | --- | --- |
| `library-study-desk-*.webp` | https://unsplash.com/photos/klbApl9mxr0 | Zoshua Colah | Home hero | A student working on a laptop at a long wooden desk in a university library reading room |
| `reading-room-rotunda-*.webp` | https://unsplash.com/photos/8CASOM38lQc | Lilian Do Khac | About | A circular university reading room lined with bookshelves under tall arched windows |
| `students-discussing-laptop-*.webp` | https://unsplash.com/photos/2FDdgn0-W_o | Chidera Faustina Okeke | How it works; register page (decorative) | Two students discussing work on a laptop at a white table |
| `desk-books-notes-*.webp` | https://unsplash.com/photos/HNjWq8WPyoY | Yen Vu | Services; verify-email page (decorative) | A study desk with open books, sticky notes and handwritten research notes |
| `analytics-on-laptop-*.webp` | https://unsplash.com/photos/JKUTrJ4vK00 | Luke Chesser | Home (portfolio section) | A laptop screen showing analytics charts and histograms |
| `notebook-and-pen-*.webp` | https://unsplash.com/photos/n9AaeihA9HI | Clay Banks | Sign-in pages (decorative) | An open notebook and pen on a wooden desk |
| `library-hall-tables-*.webp` | https://unsplash.com/photos/dONCzml7nw4 | Zoshua Colah | 404 page (decorative) | — (decorative, empty alt) |

Attribution is not required, but crediting photographers is good practice; this table serves as the record. The Suzzallo Library photographs (Zoshua Colah) show a real university building; they are used only as generic library imagery and the site does not name or imply any connection with that institution.

## Icons

Lucide icons (SVG files copied from `lucide-static` 1.61.0 into `resources/icons/`). Licence: ISC — https://lucide.dev/license. The licence header is preserved in each source SVG.

## Libraries

| Library | Use | Licence |
| --- | --- | --- |
| Dompdf 3.1 | PDF quotations, receipts and invoices | LGPL-2.1 (used unmodified as a Composer dependency) |
| DejaVu Sans (bundled with Dompdf) | Font in PDF documents | Bitstream Vera / DejaVu licence (free) |

## Fonts

Self-hosted at build time by `laravel-vite-plugin` (downloaded from Bunny Fonts, served from this site — no third-party font requests from visitors):

| Font | Licence |
| --- | --- |
| Newsreader (Production Type) | SIL Open Font License 1.1 |
| IBM Plex Sans, IBM Plex Mono (IBM) | SIL Open Font License 1.1 |

## Not used

No university logos, competitor material, stock "badge" graphics or AI-generated imagery are used anywhere on the site.
