 # Agency AEP Readiness Checklist: WordPress implementation

Prepared for `nccagent.com`. This package contains one main guide and seven interactive worksheet pages.

## Recommended page structure

Use the main guide as the parent page and create each worksheet as a child page.

| WordPress page title | Recommended slug |
|---|---|
| The Agency AEP Readiness Checklist | `/resources/guides/agency-aep-readiness-checklist/` |
| Producer Readiness Matrix | `/resources/guides/agency-aep-readiness-checklist/producer-readiness-matrix/` |
| Book of Business Segmentation Plan | `/resources/guides/agency-aep-readiness-checklist/book-of-business-segmentation-plan/` |
| AEP Demand and Capacity Plan | `/resources/guides/agency-aep-readiness-checklist/aep-demand-capacity-plan/` |
| AEP Systems Test Script | `/resources/guides/agency-aep-readiness-checklist/aep-systems-test-script/` |
| Compliance File Audit | `/resources/guides/agency-aep-readiness-checklist/compliance-file-audit/` |
| Sales Quality Scorecard | `/resources/guides/agency-aep-readiness-checklist/sales-quality-scorecard/` |
| AEP Responsibility Map | `/resources/guides/agency-aep-readiness-checklist/aep-responsibility-map/` |

If the site's existing Guides structure uses a different parent or permalink pattern, keep the worksheet slugs and substitute the actual published URLs in the link-update step below.

## Important technical note

Do not paste these complete files directly into a normal Gutenberg Custom HTML block. Each file contains a document head, page-level CSS, and JavaScript. WordPress or a security plugin may strip the scripts, and global theme styles may change the layouts.

Recommended implementation:

1. Use a child theme or a small site-specific plugin.
2. Create a full-width, no-sidebar page template for this campaign.
3. Move each file's CSS into a page-specific stylesheet and enqueue it only on its assigned page.
4. Move each file's JavaScript into a page-specific script and enqueue it in the footer with `defer`.
5. Put the markup inside the WordPress page template or a shortcode rendered by the site-specific plugin.
6. Keep the CSS scoped to the campaign wrapper. The source files currently include global selectors such as `body`, `button`, `input`, and `h1`, so the developer should verify there are no theme-header, footer, cookie-banner, or form-plugin conflicts.

An administrator with the `unfiltered_html` capability can use a code-oriented page builder or trusted code-block plugin, but the child-theme or site-plugin route is more durable and easier to version.

## Build sequence

1. Back up the site and deploy to staging first.
2. Upload `assets/NCCLogo-White.svg` and `assets/NCCLogo-FullColor.svg` to the Media Library. If SVG uploads are disabled, use the site's approved SVG workflow or convert the logos to transparent PNG files.
3. Create the eight pages in draft status using the titles and hierarchy above.
4. Assign the campaign template to all eight pages. Use a full-width content area with no sidebar.
5. Implement the main guide from `source-pages/2026-07-13_NCC_guide_agency-aep-readiness-checklist.html`.
6. Implement each worksheet from its matching file in `source-pages/`.
7. Replace the packaged logo paths with the final WordPress Media Library URLs.
8. Replace every file-relative worksheet link in the main guide with its final WordPress permalink.
9. Replace every `Return to the AEP readiness guide` link with the final main-guide permalink.
10. Confirm the final CTA destination. The source currently uses `https://nccagent.com/contact`; verify whether it should remain there or use the site's current contact URL.
11. Add the normal site privacy-policy link through the global footer or page template.
12. Keep all pages as drafts until compliance and content owners approve the final staging versions.

## Required link replacements

In the main guide, replace these file names with the corresponding published URLs:

- `2026-07-13_NCC_worksheet_producer-readiness-matrix.html`
- `2026-07-14_NCC_worksheet_book-of-business-segmentation-plan.html`
- `2026-07-14_NCC_worksheet_aep-demand-capacity-plan.html`
- `2026-07-14_NCC_worksheet_aep-systems-test-script.html`
- `2026-07-14_NCC_worksheet_compliance-file-audit.html`
- `2026-07-14_NCC_worksheet_sales-quality-scorecard.html`
- `2026-07-14_NCC_worksheet_aep-responsibility-map.html`

In every worksheet, replace `2026-07-13_NCC_guide_agency-aep-readiness-checklist.html` with the published main-guide URL.

## Functional requirements

All seven worksheets must retain:

- browser-local saving through `localStorage`
- add and remove row controls where present
- calculated totals and status indicators
- CSV export
- clear/reset confirmation
- print styling and the Print Worksheet control
- leadership review or sign-off fields where present

Each worksheet uses a distinct storage key, so the pages can share the same domain without overwriting one another. Data stays in the user's current browser and device. It is not submitted to WordPress or NCC.

Do not add form submission, user accounts, database synchronization, or analytics capture for worksheet field values without a separate privacy, security, and compliance review. The worksheets instruct users not to enter beneficiary PII, PHI, credentials, access tokens, or other sensitive information.

## WordPress and security checks

- Confirm the Content Security Policy permits the packaged inline behavior or, preferably, the enqueued local scripts and styles.
- Exclude the eight pages from aggressive JavaScript delay or combination until all workbook controls pass testing.
- Do not cache HTML that contains user-entered worksheet values. Current values live only in `localStorage`, not in the HTML response.
- Confirm any cookie-consent tool, accessibility overlay, optimization plugin, and page-builder CSS does not cover controls or alter table scrolling.
- Keep scripts first-party. The package does not require a third-party JavaScript library.
- Verify SVG files are served with the correct MIME type and are allowed by the site's security policy.

## Analytics recommendation

Track page views and non-sensitive button events only:

- main guide viewed
- worksheet opened
- CSV export clicked
- print clicked
- Schedule AEP Review clicked

Do not send field names, field values, producer names, NPNs, notes, or worksheet contents to GA4, the data layer, session replay, heatmaps, or other analytics tools. Mask form fields in any replay or behavioral analytics platform.

## Staging acceptance checklist

- [ ] Main guide loads without horizontal scrolling at 320, 768, 1024, and 1440 pixels.
- [ ] All seven worksheet links open the correct WordPress pages.
- [ ] Every worksheet returns to the main guide.
- [ ] Both logos load over HTTPS with no mixed-content warning.
- [ ] Add-row, remove-row, totals, radio buttons, and checkboxes work.
- [ ] Refreshing a worksheet restores its locally saved entries.
- [ ] Clearing a worksheet affects only that worksheet.
- [ ] CSV export opens correctly in Excel or Google Sheets.
- [ ] Print preview is readable and omits screen-only controls.
- [ ] Keyboard focus is visible and controls are reachable without a mouse.
- [ ] Theme header, footer, menus, cookie tools, and overlays do not collide with campaign content.
- [ ] Mobile table areas scroll horizontally without moving the full page.
- [ ] Page titles, meta descriptions, canonical URLs, and social-share images are set.
- [ ] Worksheet pages are excluded from the XML sitemap if the content owner does not want them indexed separately.
- [ ] No worksheet field values appear in analytics, logs, or session replay.
- [ ] Final contact CTA points to the approved destination.
- [ ] Human compliance approval is documented before publication.

## SEO and publishing recommendation

Index the main guide. For the worksheet child pages, choose one approach intentionally:

- Index them if each worksheet should be discoverable as a standalone agent resource.
- Set them to `noindex, follow` if the main guide should be the primary search result and the worksheets are supporting tools.

Use the packaged hero image as the main guide's featured or social-share image if it still matches the final staging design. Add meaningful alt text in WordPress rather than relying on the file name.

## Compliance status

This package is implementation-ready, not approved for publication. The guide contains date-sensitive Medicare and CMS operational guidance, plus a `50+ carriers` claim that the source itself flags for Heather Burley's sign-off. The Compliance File Audit also requires human compliance review. Confirm all dates, citations, carrier counts, contact information, and required disclosures against current guidance immediately before launch.

