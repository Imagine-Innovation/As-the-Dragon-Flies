# As the Dragon Flies - Design System Specification

This document defines the official design system for the **As the Dragon Flies** platform. It acts as an exhaustive component and design token specification derived directly from the application's core visual architecture defined in `dragon-lite.css`.

---

## 1. Design Tokens & Variables

### Color Palette

The design system employs a rich color model defined in RGBA values to support translucency and overlay effects. Colors are grouped into functional blocks:

#### Cool Spectrum (Blue to Green)
- **Blue**: A primary active blue color (`rgba(0,123,255,1)`).
  - **Blue 25%**: Semi-translucent blue for focus states and light highlights (`rgba(0,123,255,0.25)`).
  - **Blue 50%**: Mid-translucent blue for disabled checked states (`rgba(0,123,255,0.5)`).
- **Light Blue**: Soft, high-brightness blue (`rgba(170,221,238,1)`), used primarily for active range indicators.
- **Cyan**: High-visibility informative cyan (`rgba(23,162,184,1)`).
  - **Cyan 50%**: Semi-translucent informative cyan for badge shadows and glows (`rgba(23,162,184,0.5)`).
- **Teal**: A secondary accent green-blue color (`rgba(32,201,151,1)`).
- **Green**: Semantic success green (`rgba(40,167,69,1)`).
  - **Green 50%**: Semi-translucent success green for highlights and secondary triggers (`rgba(40,167,69,0.5)`).
  - **Green 25%**: Soft translucent success green background helper (`rgba(40,167,69,0.25)`).
- **Light Green**: Soft pastel green background helper for success callouts (`rgba(204,238,204,1)`).

#### Warm Spectrum (Red to Yellow)
- **Red**: Semantic danger and aggressive alert red (`rgba(220,53,69,1)`).
  - **Red 25%**: Translucent red background highlight (`rgba(220,53,69,0.25)`).
  - **Red 50%**: Translucent red shadow/glow indicator (`rgba(220,53,69,0.5)`).
- **Orange**: Energetic secondary highlight and active anchor color (`rgba(253,126,20,1)`).
- **Yellow**: Radiant primary accent yellow (`rgba(255,193,7,1)`). Used extensively for primary links, text highlights, and borders.
  - **Yellow 50%**: Translucent yellow shadow/glow indicator (`rgba(255,193,7,0.5)`).
- **Light Yellow**: Soft pastel yellow for background warnings, markers, and input focus outlines (`rgba(255,238,187,1)`).

#### Purple Tones
- **Indigo**: Deep accent purple-blue (`rgba(102,16,242,1)`).
- **Purple**: Vibrant thematic purple (`rgba(111,66,193,1)`).

#### Grayscale & Base Alpha Scales
- **Black**: Deep black base (`rgba(0,0,0,1)`).
- **Dark Scales** (Translucent dark backgrounds):
  - **Dark 95%**: Near-opaque dark background for dropdowns and overlay elements (`rgba(0,0,0,0.95)`).
  - **Dark 75%**: High-contrast translucent dark overlay for button templates (`rgba(0,0,0,0.75)`).
  - **Dark 50%**: Moderate-contrast background overlay (`rgba(0,0,0,0.5)`).
  - **Dark 25%**: Low-contrast translucent block background used for tables and cards (`rgba(0,0,0,0.25)`).
  - **Dark 10%**: Thin translucent shading for headers, footers, and inner toolbars (`rgba(0,0,0,0.1)`).
  - **Dark 00%**: Fully transparent placeholder (`rgba(0,0,0,0)`).
- **Dark Gray**: Base background dark slate color (`rgba(56,51,51,1)`).
- **Dark Gray 50%**: Mid-translucent base background (`rgba(56,51,51,0.5)`).
- **Gray**: Standard structural gray (`rgba(108,117,125,1)`).
  - **Gray 50%**: Mid-translucent structural gray (`rgba(108,117,125,0.5)`).
- **Gray Alpha Step Scale**:
  - **Gray 1**: Extremely deep gray (`rgba(17,17,17,1)`).
  - **Gray 2**: Deep gray for high contrast text elements (`rgba(34,34,34,1)`).
  - **Gray 4**: Medium-deep gray (`rgba(68,68,68,1)`).
  - **Gray 6**: Medium gray (`rgba(102,102,102,1)`).
  - **Gray 8**: Medium-light gray (`rgba(136,136,136,1)`).
  - **Gray A**: Soft structural gray (`rgba(170,170,170,1)`).
  - **Gray C**: Soft border gray (`rgba(204,204,204,1)`).
  - **Gray E**: Very soft light-gray boundary lines (`rgba(238,238,238,1)`).
- **Light Scales** (Translucent white overlays):
  - **Light 05%**: Faint layout highlight overlay (`rgba(255,255,255,0.05)`).
  - **Light 10%**: Subtle visual layout highlight/hover overlay (`rgba(255,255,255,0.1)`).
  - **Light 25%**: Low-opacity visual separation lines and checkboxes (`rgba(255,255,255,0.25)`).
  - **Light 50%**: Moderate-opacity soft border lines, labels, and disabled states (`rgba(255,255,255,0.5)`).
  - **Light 75%**: Highly visible soft light text color (`rgba(255,255,255,0.75)`).
  - **Light 85%**: Primary soft-white body text color (`rgba(255,255,255,0.85)`).
- **Light Gray**: Standard soft off-white background (`rgba(248,249,250,1)`).
  - **Light Gray 50%**: Soft off-white translucent state trigger (`rgba(248,249,250,0.5)`).
- **White**: Pure canvas white (`rgba(255,255,255,1)`).

### Bootstrap Mapping

The framework maps standard Bootstrap semantic properties to these design tokens:
- **Primary Accent**: Mapped to Blue.
- **Secondary Accent**: Mapped to Gray.
- **Success Accent**: Mapped to Green.
- **Success Background**: Mapped to Light Green.
- **Info Accent**: Mapped to Cyan.
- **Warning Accent**: Mapped to Yellow.
- **Warning Background**: Mapped to Light Yellow.
- **Danger Accent**: Mapped to Red.
- **Light Theme Accent**: Mapped to Light Gray.
- **Dark Theme Accent**: Mapped to Dark Gray.

### Responsive Breakpoints

Visual elements respond dynamically to these viewport width thresholds:
- **Extra Small (xs)**: Base layout starts at `0`.
- **Small (sm)**: Threshold is `576px`. Handles mobile landscapes and tight modal centering.
- **Medium (md)**: Threshold is `768px`. Standard tablet boundary and layout adjustments.
- **Large (lg)**: Threshold is `992px`. Tablet landscape and dual-column grid conversions.
- **Extra Large (xl)**: Threshold is `1200px`. Standard desktop viewport grid mapping.
- **Extra Extra Large (xxl)**: Threshold is `1600px`. Wide-screen alignment rules.
- **Extreme High Definition (3xl)**: Viewports equal to or exceeding `1981px`.

### Base Typography Metrics

- **Sans-Serif Font Family**: Standard functional sans-serif typeface.
- **Monospace Font Family**: Code and specific data readouts map to a stack containing SFMono-Regular, Menlo, Monaco, Consolas, Liberation Mono, and Courier New.
- **Global HTML Font Size**: Initialized at `0.9rem`.
- **Global Body Typography**: Font size maps to `0.95rem` at a medium weight (`400`) and a leading line height of `1.5`.

---

## 2. Typography & Text Styles

### Decorated Typography
- **First-Letter Illumination**: Selected paragraphs feature a prominent capital initial styled in the artistic, medieval-inspired **Berenika** sans-serif font family, rendered at exactly double standard height (`2em`).
- **Decorated Headers**: Design sections utilize the **Berenika** font family for thematic textual displays.
- **Standard Un-decorating**: Global overrides are present to strictly omit default link decorations and underlines across specified items.

### RPG Custom Script Stylings
- **Dwarvish Script**: Applied to narrative blocks depicting underground races, runic texts, or ancient stone carvings. Styled in the historic, blocky **Glagolitsa** sans-serif family, utilizing a specialized oblique tilt.
- **Scroll Script**: Applied to hand-written messages, magical notes, or old parchment transcriptions. Styled in the ornate gothic **Bastarda** serif font family, calibrated to a precise size-adjust ratio of `0.8` to preserve clarity.

---

## 3. Layout, Structure & Grids

### Containers
- **Standard Container**: Configured to dynamically stretch to a comfortable maximum of `95%` of the screen width, applying standard horizontal cushions.
- **Fluid Container**: Spans the full width of the screen, preserving consistent horizontal cushions.

### Page Header & Core Structure
- **Global Page Header**: Affixed at the top of the viewport using a fixed layout position. Measures precisely `72px` in height, relying on a Dark 25% background color supported by a deep, dark shadow boundary. It provides standard right-hand cushions and applies smooth background color and shadow transitions.
- **Tavern/Site Logo**: Nested inside the header, aligned vertically in a flexbox layout. Features a standardized left-hand cushion and displays high-contrast white text sized at `1.3rem` at a normal font weight.
- **Main Layout Shell**: Establishes a relative reference positioning system to contain the dynamic view elements correctly.

### Toolbars & Content Sections
- **Content Title Sections**: Composed as flexible rows that align title headings with optional action buttons. Applies an uppercase transformation to standard headers, rendering them at `1.15rem` with a line-height multiplier of `140%`. Subheadings are sized at `1rem` in a muted light gray.
- **Action Toolbars**: Designed with a fixed height of `4.5rem` to house functional buttons.
  - **Inner Toolbars**: Standardized with a Dark 10% shading color.
  - **Outer Toolbars**: Colored in Dark 25%, complete with bottom shadow boundaries.
- **Footer Section**: Centered layout aligned with ample vertical cushions. Incorporates simple bullet-separated links utilizing soft light-gray colors.

### Wide Desktop Column System (Grid Extensions)
- **Grid Column Allocations**:
  - Desktop Breakpoint (xl): Implements custom widths for narrow columns (16.66%), three-column clusters (33.33%), mid-size blocks (41.66% and 58.33%), and standard wide sections (66.66%).
  - Wide Desktop Breakpoint (xxl): Provides precise quarter-screen (25%), third-screen (33.33%), half-screen (50%), and standard three-quarter column (75%) configurations.
- **Ultra-Wide HD Breakpoint (3xl)**: Extends standard 12-column grid systems beyond 1980px. Columns scale from single-width segments (8.33%) to full-width containers (100%), alongside specialized flex-basis sizing guidelines.
- **Row Grid Split Variations**: Provides structured multi-column splits across xl, xxl, and 3xl screens, supporting complex card dashboards.

### Responsive Grid Auto-Flow (Vertical Columns)
- **Base Grid Setup**: Initializes as a single-column, vertically-aligned block that handles long grids. It uses a CSS grid model designed to auto-flow content sequentially inside a grid of up to 18 rows.
- **Dual-Column Layout (lg)**: Spits the vertical layout into two columns with a repeating row count of 9.
- **Triple-Column Layout (xxl)**: Spits the vertical layout into three columns with a repeating row count of 6.

---

## 4. Core Components

### Tables
- **Table Data Text**: Styled in high-contrast light-gray.
- **Structural Lines**: Separated by top border lines utilizing Light 10% translucent gray. Row cells apply comfortable internal cushions.
- **Header Definitions**: Styled at a heavier weight (`600`), using a bottom border line multiplier.
- **Dark Theme Tables**: Renders data text in pure white on top of a low-opacity Dark 25% base color.
- **Striped Tables**: Automatically adds subtle Light 10% translucent gray backgrounds to odd rows.
- **Hover Transitions**: Highlight row cells by shifting background colors to Light 10% and increasing text contrast when hovered.

### Forms & Input Fields

#### Standard Input Controls
- **Layout Fields**: Styled with a rounded-corner radius of zero, bordered with a thin Light 25% line. Establishes a custom height, subtle focus shadow glow, and renders placeholder text in Light 50%.
- **Disabled State**: Converts to a transparent background with standard visibility.
- **Focused State**: Shifts the border to a bright Light 50% color, applying a soft glowing focus shadow.
- **Required Indicator**: Automatically appends a prominent, colored asterisk to associated form labels using the semantic Danger color.

#### Specialized Inputs & Custom Switches
- **Custom Selection Containers (Select2 Integration)**:
  - Multi-select containers utilize transparent backgrounds, soft light gray choices, and subtle border color transitions.
  - Single-select containers utilize transparent backgrounds with a specialized custom SVG caret positioning background image.
  - Dropdown options are housed in Dark 95% translucent overlays, utilizing subtle fade-in animations.
- **Custom Checkboxes & Radio Buttons**:
  - Operates using absolute positioning layers to hide default checkboxes under custom overlays.
  - Checked Checkboxes: Uses a customized SVG checkbox checkmark overlay.
  - Checked Radio Buttons: Uses a custom SVG white circle center icon over a rounded boundary outline.
- **Custom Range Sliders**:
  - Spans the full width of the container. Uses cross-browser styling (WebKit, Mozilla, and Microsoft Edge engines).
  - Uses highly visible slider tracks and circular, responsive slider thumbs colored in semantic blue that shift to light-blue when pressed.

### Cards

#### Card Layout Structure
- **Card Container**: Structured in a flex column layout using a Dark 25% background. Uses a borderless profile supported by a subtle bottom shadow.
- **Card Body**: Applies comfortable internal cushions, setting secondary text elements to a soft Light 75% shade.
- **Title & Subtitle Elements**: Titles are sized to `1.25rem` with a line-height multiplier of `140%`. Subtitles are formatted to `1rem` and positioned with a negative margin to tuck underneath titles.
- **Header & Footer Areas**: Shaded in Dark 10% with transparent border lines.
- **Responsive Overflow Scroll**: Custom scrollable card bodies apply rounded-corner vertical scrolls.

---

## 5. RPG Custom & App-Specific Elements

### Action Items
- **Actions Container**: Uses a relative flex layout to position functional buttons.
- **Action Icons (.actions__item)**: Designed as small circular icons measuring exactly `35px` by `35px`. Utilizes a Dark 75% background to stand out on top of map images, rendering icons in a soft light color. Hovering shifts the background to Light 10% and elevates icon contrast to pure white.

### Character Avatars & Image Elements
- **Circular Avatars**: Scaled to exactly `80px` by `80px` in a circle frame. Outlined with a prominent Yellow border, applying an object-fit crop.
- **Avatar Magnification**: Hovering over a character avatar triggers a rapid scale transition, magnifying the image to exactly triple its base size (`scale(3)`) at a high z-index to overlay adjacent cards.
- **Thumbnails**: Standard list-view thumbnails are styled at `50px` by `50px`. Hovering triggers a scale transition that magnifies the image to five times its base size (`scale(5)`).

### Interactive RPG Special Blocks

#### The Custom Scroll Block (.scroll)
- Used to render official proclamations, quests, dungeon scrolls, and secret maps.
- Applies an exhaustive hand-crafted paper parchment texture background image.
- Formatted with a generous horizontal and vertical padding of `8%` to center content away from scroll edges.
- Text is rendered in high-contrast Dark 75% gray to mimic aged calligraphy ink, utilizing a comfortable line-height of `1.6`.

#### Standard Rich Text Editor Integration
- Focus states apply a warning light-yellow border overlay supported by soft blue glows. It ensures clean spacing of headings inside active edit screens.

#### Custom Toggle Switches
- Composed of a flexbox container with standard dimensions (`36px` wide by `20px` high).
- Uses a completely transparent checkbox input block over a customized sliding toggle switch background.
- Sliding Behavior: Checked checkboxes trigger smooth sliding animations, shifting the toggle knob horizontally and turning the slider color to semantic danger red.
- Disabled switches apply a translucent opacity filter to indicate locked states.

#### Interactive RPG Image Cards
- Formatted as a square-aspect container with a custom overlay transition.
- Employs a linear gradient overlay at the base (ranging from solid black at `0%` to transparent at `100%`) to guarantee high-contrast readability for white title overlays over multi-colored images.
- Hovering triggers subtle card translations and bottom shadows to provide depth.

---

## 6. Animations

The design system incorporates lightweight, optimized keyframe animations to guide player interaction:

- **Blink**: A toggle loop that transitions opacity between standard visibility (`1`) and complete transparency (`0`) to represent active cursors, target alerts, or immediate threats.
- **Color Cycling**: Smoothly cycles svg stroke outlines between semantic colors: Red, Blue, Green, and Yellow.
- **Dash Path (SVG Progress)**: Gradually animates SVG path lines from fully masked to fully visible.
- **Rotation**: Standard continuous clockwise circle rotation used for spinner overlays.

---

## 7. Utilities & Helpers

### Background Utilities
- **Danger**: Red background.
- **Dark**: Dark Gray background.
- **Info**: Cyan background.
- **Primary**: Blue background.
- **Purple**: Thematic purple background.
- **Secondary**: Medium Gray background.
- **Success**: Green background.
- **Transparent**: Background color is omitted.
- **Warning**: Yellow background.

### Border Utilities
- **Standard Border**: Soft gray-e outline.
- **Border-0**: Strict border erasure.
- **Border Bottom**: Bottom-only gray-e line.
- **Border Secondary**: Medium-gray outline helper.
- **Border Warning**: Radiant yellow border highlight.

### Structural & Alignment Helpers
- **Clearfix**: Implements standard pseudo-element block erasures to contain floating layout cards.
- **Pill Shape**: Standardizes border-radius multipliers to render pill-shaped badges.
- **Visibility Helpers**: Provides standard invisible/visible overrides.
- **Absolute/Fixed Layouts**: Utility overlays for positioning content grids.

---

## 8. Print Media Overrides

To support players who print character sheets, campaign summaries, or spell lists, the design system implements critical overrides for paper media:

- **Drop Shadow Erasure**: Drops all text shadows, box shadows, and glow animations to preserve printer toner.
- **Contrast Conversions**: Forces backgrounds to high-contrast white and typography to deep black.
- **Structural Outlines**: Replaces decorative colors with simple thin black border rules.
- **Element Hiding**: Completely hides header navigation bars, player controls, action menus, and interactive buttons.
- **Page breaks**: Instructs printers to strictly avoid splitting image cards, tables, and lists across separate pages.
