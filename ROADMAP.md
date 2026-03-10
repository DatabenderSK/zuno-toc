# Zuno TOC – Roadmap

Planned features and improvements for future releases.

## Priority 1 – Core UX

### Scroll spy (active heading highlight)
- Highlight the currently visible heading in TOC using `IntersectionObserver`
- Active class `.zuno-toc__link--active` with accent color indicator
- Smooth transitions between active states

### Scroll offset
- Configurable offset (px) for fixed headers/admin bar
- Global setting + per-block override
- Applied to both smooth scroll and scroll spy calculations

### Sticky sidebar TOC
- Option to display TOC as sticky sidebar (position: sticky)
- Auto-hide on mobile, show in sidebar on desktop
- Configurable max-height with scroll

### Auto-insert position
- Currently: before first H2 only
- Add options: after first paragraph, before content, after content, custom position

## Priority 2 – Display options

### Flat list mode
- Option to render all headings at the same level (no indentation)
- Useful for simple posts with only H2 headings

### Collapsible sub-headings
- Click H2 to expand/collapse its H3/H4 children
- Per-heading toggle, not just the entire TOC

### Progress bar
- Reading progress indicator at the top of the page
- Or integrated into the TOC itself (percentage per section)

### Reading time estimate
- "5 min read" displayed next to the TOC title
- Calculated from word count (configurable WPM)

### Back-to-top button
- Floating button that scrolls back to the TOC or page top
- Appears after scrolling past the TOC

## Priority 3 – Compatibility

### Shortcode support
- `[zuno_toc]` shortcode for use in classic editor or page builders
- Same attributes as block (style, color, collapsed, etc.)

### Classic Editor support
- Auto-insert works, but no editor preview in Classic Editor
- Consider a simple meta box for Classic Editor users

### Mobile responsiveness
- Collapsible mobile drawer (full-width)
- Touch-friendly tap targets (min 44px)
- Responsive font sizes

## Priority 4 – SEO & Advanced

### SEO sitelinks optimization
- Structured data markup for TOC items
- `<nav>` with proper `aria-label` (already done)
- Schema.org Article + hasPart markup for Google sitelinks

### AI summaries per section
- Optional AI-generated summary for each heading section
- Displayed as tooltip or expandable preview in TOC

### Multi-language support
- Translatable UI strings (toggle text, title)
- RTL support for Arabic/Hebrew sites
- `.pot` file generation for WordPress translations

## Priority 5 – Ecosystem

### Zuno Gallery
- Second plugin in the Zuno brand family
- Shared admin bar menu, shared block category
- Image gallery with lightbox, masonry, carousel

### Zuno brand infrastructure
- Shared "Zuno" admin bar dropdown across all Zuno plugins
- Unified settings page grouping
- Consistent green brand color (#5ba462)

---

*Last updated: 2026-03-10*
