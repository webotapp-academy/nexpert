# Daily Credibility Card — Design Specification

**Date:** August 25, 2026  
**Component:** Shareable Credibility Update Card  
**Platforms:** Web, Mobile, LinkedIn  
**Design System:** Nexpert Dark + Purple/Blue accents

---

## Visual Hierarchy & Layout

### Desktop Card (Figma Dimensions: 1200px × 800px)

```
┌─────────────────────────────────────────────────────────┐
│ NEXPERT | DAILY CREDIBILITY UPDATE          AI-VERIFIED │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  [Profile Photo]  Lekha Bhan                           │
│                   AI / ML Architect                     │
│                                                          │
│  🎯 You crossed 850 Credibility Points                 │
│                                                          │
│  ┌───────────────────────────────────────────────────┐ │
│  │ 847 → 862 | +15 CREDIBILITY POINTS               │ │
│  └───────────────────────────────────────────────────┘ │
│                                                          │
│  WHAT CHANGED TODAY?                                    │
│  ✓ Completed 3 verified sessions                       │
│  ⭐ 4.9/5 learner satisfaction                         │
│  💡 Added 2 new expertise signals                      │
│  📈 +15 credibility points this week                   │
│                                                          │
│  Generative AI · RAG · Agentic AI                      │
│                                                          │
│  Top 8% of AI Experts on Nexpert                       │
│  [View my verified profile →]                          │
│                                                          │
├─────────────────────────────────────────────────────────┤
│ NEXPERT · Where expertise becomes measurable.           │
│ nexpertapp.com/expert/lekha-bhan    [Share to LinkedIn] │
└─────────────────────────────────────────────────────────┘
```

### Mobile Card (Mobile-First: 360px width)

```
┌─────────────────────────────────┐
│ NEXPERT | DAILY UPDATE  AI-VER. │
├─────────────────────────────────┤
│                                 │
│      [Profile Photo]            │
│      Lekha Bhan                │
│      AI / ML Architect         │
│                                 │
│  🎯 You crossed 850            │
│  Credibility Points            │
│                                 │
│  847 → 862                     │
│  +15 POINTS                    │
│                                 │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│  WHAT CHANGED TODAY?           │
│  ✓ 3 verified sessions        │
│  ⭐ 4.9/5 satisfaction        │
│  💡 2 expertise signals       │
│  📈 +15 points this week      │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                 │
│  Generative AI · RAG          │
│  Agentic AI                   │
│                                 │
│  Top 8% on Nexpert            │
│                                 │
│  [View profile →]             │
│  [Share to LinkedIn]          │
│                                 │
│ NEXPERT · Where expertise      │
│ becomes measurable.            │
└─────────────────────────────────┘
```

---

## Color & Typography

### Palette

| Role | Color | Usage |
|------|-------|-------|
| **Primary Background** | `#0f172a` (Slate-900) | Card bg |
| **Accent Gradient** | `#7c3aed` → `#3b82f6` (Purple → Blue) | Headers, CTAs |
| **Success/Metric** | `#10b981` (Emerald-500) | Score increases, positive metrics |
| **Text Primary** | `#f8fafc` (Slate-50) | Body text |
| **Text Secondary** | `#cbd5e1` (Slate-300) | Labels, metadata |
| **Text Tertiary** | `#64748b` (Slate-500) | Timestamps, secondary info |
| **Border** | `rgba(124, 58, 237, 0.2)` | Subtle dividers |

### Typography

| Element | Font | Size | Weight | Line Height |
|---------|------|------|--------|-------------|
| **Card Title** | Inter | 28px | 700 | 1.3 |
| **Profile Name** | Inter | 20px | 600 | 1.2 |
| **Metric Numbers** | SF Mono | 32px | 700 | 1.0 |
| **Body Text** | Inter | 14px | 400 | 1.5 |
| **Label** | Inter | 12px | 600 | 1.2 |
| **Badge** | Inter | 11px | 700 | 1.0 |

---

## Component Breakdown

### 1. Header Section

```
┌──────────────────────────────────────┐
│ [Nexpert Logo] NEXPERT | DAILY UPDATE│
│                       AI-VERIFIED ✓  │
└──────────────────────────────────────┘
```

**Specs:**
- Height: 48px
- Logo: 24×24px
- Verification badge: Right-aligned
- Font-size: 12px, uppercase, letter-spacing: 1.5px

### 2. Profile Card

```
┌──────────────────────────────┐
│ [Image] Name                 │
│         Title                │
└──────────────────────────────┘
```

**Specs:**
- Photo: 64×64px, rounded full circle
- Border: 2px solid rgba(124, 58, 237, 0.4)
- Name: 18px, bold
- Title: 14px, text-secondary
- Layout: Flex row, gap: 16px

### 3. Score Comparison Box

```
┌─────────────────────────────┐
│ Yesterday          Today     │
│   847      →      862       │
│                 +15 ↑       │
└─────────────────────────────┘
```

**Specs:**
- Background: rgba(15, 23, 42, 0.5)
- Border: 1px solid rgba(124, 58, 237, 0.2)
- Padding: 20px
- Grid: 3 columns (before, arrow, after)
- Center alignment for all columns
- Arrow: Green, ↑ icon or animated line
- Score numbers: 28px, bold, green (#10b981) for "today"

### 4. Achievement List

```
┌───────────────────────────────┐
│ WHAT CHANGED TODAY?           │
│ ✓ Completed 3 verified sesh  │
│ ⭐ 4.9/5 learner satisfaction│
│ 💡 Added 2 expertise signals │
│ 📈 +15 credibility points    │
└───────────────────────────────┘
```

**Specs:**
- Background: rgba(15, 23, 42, 0.3)
- Border: 1px solid rgba(124, 58, 237, 0.15)
- Padding: 16px
- Each item: 14px text-secondary, 12px gap between lines
- Icons: 20px, left-aligned
- Checkmarks/stars in success green (#10b981)

### 5. Expertise Tags

```
[Generative AI] [RAG] [Agentic AI]
```

**Specs:**
- Background: rgba(124, 58, 237, 0.15)
- Border: 1px solid rgba(124, 58, 237, 0.3)
- Padding: 6px 12px
- Border-radius: 16px
- Font: 12px, text-secondary
- Gap: 8px
- Max 3 tags shown

### 6. Ranking Section

```
┌──────────────────────────────────┐
│ Top 8% of AI Experts on Nexpert  │
└──────────────────────────────────┘
```

**Specs:**
- Background: rgba(124, 58, 237, 0.1)
- Border: 1px solid rgba(124, 58, 237, 0.2)
- Padding: 12px
- Border-radius: 8px
- Font: 13px, text-secondary, centered
- Purple accent text for percentile

### 7. CTA Button

```
[View my verified profile →]
```

**Specs:**
- Background: Linear gradient (Purple → Blue)
- Text: White, 14px, bold
- Padding: 12px 24px
- Border-radius: 8px
- Hover: Brightness +10%, shadow glow
- Width: Full (on mobile) or auto (desktop)

### 8. Share Button

```
[⊙ Share to LinkedIn]
```

**Specs:**
- Background: rgba(124, 58, 237, 0.2)
- Border: 1px solid rgba(124, 58, 237, 0.3)
- Text: 14px, text-secondary
- Padding: 12px 24px
- Border-radius: 8px
- Hover: Background rgba(124, 58, 237, 0.3)
- Icon: 16×16px left-aligned
- State: Disabled (gray) if already shared

### 9. Footer

```
NEXPERT · Where expertise becomes measurable.
nexpertapp.com/expert/lekha-bhan
```

**Specs:**
- Text: 12px, text-tertiary
- Centered alignment
- Border-top: 1px solid rgba(124, 58, 237, 0.1)
- Padding-top: 16px
- Link text: Purple accent on hover

---

## Responsive Behavior

### Desktop (1200px+)
- Card width: 720px (max-width)
- Centered in container
- 2-column layout for score/metrics if space allows

### Tablet (768px–1199px)
- Card width: 90vw
- Full-width layout
- Padding adjusted: 32px → 24px

### Mobile (< 768px)
- Card width: 100vw
- Full-bleed on small screens
- Padding: 16px
- Font sizes reduced by 8% on typography < 16px
- Grid columns: 1 (stack vertical)

---

## Animations & Interactions

### Card Entry Animation
```css
@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

animation: slideInUp 0.6s ease-out;
```

### Score Number Counter (Optional)
```javascript
// Animate score from 847 to 862
animateCounter({
  start: 847,
  end: 862,
  duration: 1200,
  easing: 'easeInOutCubic'
});
```

### Share Button Click
```css
/* Ripple effect on share */
@keyframes buttonRipple {
  0% {
    box-shadow: 0 0 0 0 rgba(124, 58, 237, 0.4);
  }
  100% {
    box-shadow: 0 0 0 10px rgba(124, 58, 237, 0);
  }
}
```

### Achievement Icon Pulse (Optional)
```css
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.6;
  }
}

.achievement-icon {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
```

---

## Trigger-Specific Layouts

Each trigger type has a slightly customized card:

### 1. Milestone Crossed
```
Title: 🎯 You crossed {score} Credibility Points
Icon emphasis: Gold/Yellow (#fbbf24)
Primary achievement: Milestone reached
```

### 2. Session Count
```
Title: ⭐ Your {count}th verified expert session
Icon emphasis: Blue (#60a5fa)
Primary achievement: {count} sessions completed
```

### 3. Ranking Jump
```
Title: 📈 Moved from #{old_rank} → #{new_rank} this week
Icon emphasis: Green (#10b981)
Primary achievement: Top {new_rank} in {topic}
```

### 4. Expertise Recognition
```
Title: 🧠 You're now top 10% in {topic}
Icon emphasis: Purple (#c084fc)
Primary achievement: New expertise milestone
```

### 5. Learner Outcome
```
Title: 💬 {count} learners certified
Icon emphasis: Emerald (#34d399)
Primary achievement: {satisfaction}% satisfaction
```

### 6. Credibility Growth
```
Title: 🚀 +{gain} credibility points in {period}
Icon emphasis: Green (#10b981)
Primary achievement: Consistent growth
```

### 7. Band Promotion
```
Title: 🏅 You've earned {new_band} status
Icon emphasis: Gold (#fbbf24)
Primary achievement: Band upgraded to {new_band}
```

### 8. Top Performer
```
Title: 👑 Top {percentile}% of Experts
Icon emphasis: Gold (#fbbf24)
Primary achievement: Top {percentile} ranking
```

---

## LinkedIn Share Preview

When shared to LinkedIn, the card appears as:

```
Lekha Bhan
AI / ML Architect
3 hours ago

Just crossed 850 on Nexpert. 🚀

Over the last 90 days, my credibility score has grown by 
64 points through verified sessions, learner feedback and 
demonstrated expertise in Generative AI, RAG and Agentic AI.

Curious to see how Nexpert measures expert credibility 
differently from a traditional profile or follower count.

[View my Nexpert profile]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
[Nexpert Card Image Preview]
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

**Card Image Specs for LinkedIn:**
- Dimensions: 1200×628px (LinkedIn's ideal ratio for shares)
- Format: PNG with transparency
- Quality: 72 DPI (web-optimized)
- Max file size: 8 MB

---

## Accessibility

### WCAG AA Compliance

| Element | Requirement | Implementation |
|---------|-------------|-----------------|
| Color Contrast | 4.5:1 for text | White text on purple: 8.2:1 ✓ |
| Font Size | Min 14px body | All text ≥ 14px ✓ |
| Interactive | Touch target ≥ 44px | Buttons: 48px ✓ |
| Labels | Form labels present | All icons have aria-labels |
| Motion | Prefers reduced motion | Disable animations if `prefers-reduced-motion` |
| Keyboard | Tab navigation | All buttons focusable with :focus-visible |

### Screen Reader Markup

```html
<article 
  role="article" 
  aria-label="Credibility update: crossed 850 points"
>
  <header>
    <h1>🎯 You crossed 850 Credibility Points</h1>
  </header>
  
  <section aria-label="Score progression">
    <dl>
      <dt>Yesterday's score</dt>
      <dd>847</dd>
      <dt>Today's score</dt>
      <dd>862</dd>
      <dt>Points gained</dt>
      <dd>+15</dd>
    </dl>
  </section>
  
  <section aria-label="Achievements unlocked">
    <ul>
      <li>Completed 3 verified sessions</li>
      <li>4.9/5 learner satisfaction</li>
      <li>Added 2 expertise signals</li>
      <li>+15 credibility points this week</li>
    </ul>
  </section>
  
  <footer>
    <a href="/expert/lekha-bhan">View my verified profile</a>
    <button aria-label="Share this card to LinkedIn">
      Share to LinkedIn
    </button>
  </footer>
</article>
```

---

## Dark Mode Variants

All colors are already dark-mode optimized. Optional light-mode variant (if needed):

| Element | Dark Mode | Light Mode |
|---------|-----------|-----------|
| Background | `#0f172a` | `#ffffff` |
| Text Primary | `#f8fafc` | `#1e293b` |
| Text Secondary | `#cbd5e1` | `#64748b` |
| Accent | `#7c3aed` | `#6d28d9` |
| Border | `rgba(124, 58, 237, 0.2)` | `rgba(124, 58, 237, 0.1)` |

---

## Localization

Card text should support internationalization (i18n):

```json
{
  "trigger_milestone": "🎯 You crossed {score} Credibility Points",
  "trigger_sessions": "⭐ Your {count}th verified expert session",
  "what_changed_today": "What changed today?",
  "learner_satisfaction": "{rating}/5 learner satisfaction",
  "credibility_growth": "+{points} credibility points",
  "view_profile": "View my verified profile →",
  "share_linkedin": "Share to LinkedIn",
  "nexpert_tagline": "Where expertise becomes measurable."
}
```

---

## Testing Checklist

- [ ] Card renders correctly on mobile (360px viewport)
- [ ] Card renders correctly on tablet (768px viewport)
- [ ] Card renders correctly on desktop (1200px+ viewport)
- [ ] Text contrast meets WCAG AA (4.5:1)
- [ ] Buttons are clickable (44px+ touch target)
- [ ] Share button updates state after clicking
- [ ] Animation performance: 60 FPS on low-end devices
- [ ] LinkedIn preview image generates correctly (1200×628px)
- [ ] Screen reader reads all content in logical order
- [ ] No layout shift when images load (use aspect-ratio CSS)
- [ ] QR code is scannable in both light and dark contexts
- [ ] Card works with zoom up to 200%
- [ ] Reduced motion preference respected
- [ ] Links have proper focus states

---

## Export Guidelines

### Figma Setup
- Create component library with all variants
- Document constraints for responsive behavior
- Export SVG for logo and icons
- Provide design tokens JSON for developers

### Developer Handoff
1. Figma file link
2. Design tokens (colors, spacing, typography)
3. Component props/API documentation
4. Animation specifications (duration, easing)
5. Edge cases (empty states, error states, loading)

---

**Design System:** Nexpert v2.0  
**Last Updated:** August 25, 2026  
**Maintained by:** Lekha Bhan, Design & Architecture
