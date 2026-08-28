# Nexpert Documentation Integration Map

**How the Daily Credibility Card Fits Into Your Complete System**

---

## Complete Document Hierarchy

Your Nexpert trust platform now has **5 interconnected technical guides**:

### Tier 1: Core Architecture
```
nexpert-trust-engine-analysis.md
├── Executive Summary
├── Trust Score Calculation Pipeline
├── EMA Smoothing Formula (α=0.3, decay)
├── 4-Dimension Model (Structure, Outcome, Boundary, Consistency)
├── Band Classification (Sovereign|Established|Verified|Emerging|Unverified)
├── Production Readiness Checklist
└── [Links to Tier 2 for implementation]
```

**Audience:** Product managers, architects, stakeholders  
**Reading time:** 25 minutes  
**Key takeaway:** Why the trust engine works, how scores are calculated

---

### Tier 2: Developer Implementation
```
nexpert-qa-script-reference.md
├── nexpert-ema-verify.php (verification without DB)
├── nexpert-seed.php (inject test data + aggregation)
├── Database Schema (trust_events, trust_state, trust_state_history)
├── Aggregation Trigger Strategies
├── Debugging & Performance Benchmarks
└── [Links to Tier 3 for API consumption]
```

**Audience:** Backend developers, QA engineers  
**Reading time:** 30 minutes  
**Key takeaway:** How to build, test, and deploy the scoring engine

---

### Tier 3: API & Event System (NEW)
```
nexpert-daily-credibility-card-implementation.md
├── Trigger Events (8 types: milestone, session, ranking, etc.)
├── Database Schema (credibility_card_events, credibility_card_templates)
├── Card Generation Engine (PHP cron logic)
├── API Endpoints (GET cards, POST generate, POST share-linkedin)
├── React Component (full-featured card UI)
├── LinkedIn Integration (OAuth, share text, image gen)
└── [Links to Tier 4 for visual specs]
```

**Audience:** Full-stack developers, product engineers  
**Reading time:** 40 minutes  
**Key takeaway:** How scores become shareable, user-facing events

---

### Tier 4: Design & UX Specifications (NEW)
```
nexpert-credibility-card-design-spec.md
├── Visual Hierarchy & Responsive Layouts
├── Component Breakdown (9 sections)
├── Color Palette & Typography
├── Trigger-Specific Variants (8 card types)
├── LinkedIn Share Preview Format
├── WCAG AA Accessibility Compliance
├── Animation & Interaction Specs
├── Testing Checklist
└── Figma/Developer Handoff Guidelines
```

**Audience:** Designers, frontend engineers, product  
**Reading time:** 20 minutes  
**Key takeaway:** How to design consistent, accessible, shareable experiences

---

### Tier 5: Integration & Monitoring (THIS DOCUMENT)
```
nexpert-documentation-integration-map.md
├── Complete Document Hierarchy
├── Data Flow: Backend → Frontend → Social
├── Endpoint Connection Diagrams
├── Deployment Checklist (all 5 documents)
├── Monitoring & Analytics Queries
├── FAQ for Cross-Team Questions
└── Future Roadmap
```

**Audience:** Tech leads, full-stack team leads, DevOps  
**Reading time:** 15 minutes  
**Key takeaway:** How all pieces work together as a system

---

## Data Flow: End-to-End

```
┌──────────────────────────────────────────────────────────────────┐
│                     TRUST ENGINE BACKEND (Tier 1-2)              │
└──────────────────────────────────────────────────────────────────┘
                               ↓
            trust_events table (INSERT trust event)
                    │session_completed
                    │outcome_achieved
                    │repeat_booking
                    └─ metadata: {duration_min, rating, evidence}
                               ↓
          Cron: update_trust_scores.php (daily)
                    │EMA aggregation (α=0.3)
                    │Temporal decay (>90 days → ×0.6)
                    │Dimension scoring (Structure, Outcome, Boundary, Consistency)
                    └─ Band classification (Sovereign|Established|Verified|Emerging)
                               ↓
            trust_state table (UPDATE overall_score, band_name)
            trust_state_history table (APPEND historical record)
                               ↓
          Cron: generate_credibility_cards.php (daily 12 AM UTC) ← [Tier 3]
                    │Compare today vs yesterday
                    │Evaluate against 8 trigger types
                    │Load template (credibility_card_templates)
                    │Render card JSON
                    └─ Insert into credibility_card_events
                               ↓
          credibility_card_events table (NEW)
                    │trigger_type: 'milestone_crossed'
                    │card_data: { header, profile, metrics, achievements, cta }
                    │shared_to_linkedin: FALSE (initially)
                    └─ Queue for expert notification
                               ↓

┌──────────────────────────────────────────────────────────────────┐
│                     FRONTEND LAYER (Tier 4)                       │
└──────────────────────────────────────────────────────────────────┘
                               ↓
            API: GET /api/v2/experts/:id/credibility-cards
                    │Fetch from credibility_card_events
                    │Fetch template for rendering
                    └─ Return card_data JSON
                               ↓
          React Component: <CredibilityCard /> (Tier 4 design spec)
                    │Render header (logo, title, badge)
                    │Render profile (photo, name, title)
                    │Render score comparison (847 → 862)
                    │Render achievements list
                    │Render expertise tags
                    │Render CTA & share button
                    └─ Handle interactions (click, share, etc.)
                               ↓

┌──────────────────────────────────────────────────────────────────┐
│                   SOCIAL SHARING LAYER (Tier 3)                   │
└──────────────────────────────────────────────────────────────────┘
                               ↓
        User clicks "Share to LinkedIn" button
                    │Fetch LinkedIn OAuth token
                    │Generate share text (interpolate template)
                    │Render card image (1200×628px PNG)
                    │POST to LinkedIn API (/v2/shares)
                    └─ Update shared_to_linkedin = TRUE
                               ↓
        LinkedIn Feed Preview
                    │Link card image
                    │Display share text
                    │Track impressions & engagement
                    └─ Nexpert expert profile link in CTA
                               ↓
        LinkedIn Analytics (Future)
                    │impressions tracked via LinkedIn API
                    │engagement_rate calculated
                    │Click-through to nexpertapp.com tracked
                    └─ Insights dashboard for experts
```

---

## API Connection Map

### How the Trust Engine Feeds the Card System

**Database Connections:**

```
┌─────────────────────────────────────────────┐
│  TRUST ENGINE TABLES                        │
│  (Populated by nexpert-seed.php)            │
├─────────────────────────────────────────────┤
│  trust_events                               │
│  ├─ id, expert_id, event_type, metadata... │
│  └─ source: 'api' | 'qa_seed'              │
│                                             │
│  trust_state                                │
│  ├─ expert_id (PK)                         │
│  ├─ overall_score (0-100)                  │
│  ├─ band_name (Verified|Established|...)   │
│  ├─ confidence_score (0.0-1.0)             │
│  └─ structure|outcome|boundary|consistency │
│                                             │
│  trust_state_history                        │
│  ├─ id, expert_id, overall_score, band... │
│  └─ created_at (timestamp)                 │
└─────────────────────────────────────────────┘
                    ↓ (feeds)
┌─────────────────────────────────────────────┐
│  CARD SYSTEM TABLES                         │
│  (NEW - Tier 3)                             │
├─────────────────────────────────────────────┤
│  credibility_card_events                    │
│  ├─ id, expert_id, trigger_type            │
│  ├─ trigger_condition (JSON)               │
│  ├─ card_data (JSON)                       │
│  ├─ score_before, score_after              │
│  └─ shared_to_linkedin, impressions        │
│                                             │
│  credibility_card_templates                 │
│  ├─ id, trigger_type (PK)                  │
│  ├─ title_template, body_json              │
│  └─ variant: 'a', 'b', 'control'          │
└─────────────────────────────────────────────┘
                    ↓ (consumed by)
┌─────────────────────────────────────────────┐
│  API LAYER                                  │
│  (Tier 3 endpoints)                         │
├─────────────────────────────────────────────┤
│  GET /api/v2/experts/:id/credibility-cards │
│  POST /api/v2/credibility-cards/generate   │
│  POST /api/v2/credibility-cards/:id/share  │
└─────────────────────────────────────────────┘
                    ↓ (consumed by)
┌─────────────────────────────────────────────┐
│  FRONTEND COMPONENTS                        │
│  (Tier 4 design spec)                       │
├─────────────────────────────────────────────┤
│  <CredibilityCard />                        │
│  ├─ Renders card_data to UI                │
│  ├─ Handles share interactions              │
│  └─ Calls API on share click               │
└─────────────────────────────────────────────┘
```

---

## Deployment Checklist (All Tiers)

### Phase 1: Core Trust Engine (Tier 1-2) ✅ DONE
- [x] EMA formula implemented & verified
- [x] Band classification logic working
- [x] Seeding scripts (nexpert-seed.php, nexpert-ema-verify.php)
- [x] Database schema for trust_events, trust_state, trust_state_history
- [x] Cron job: update_trust_scores.php (daily)
- [x] QA testing with 2 test experts (IDs 126-127)
- [x] Math validation within ±5 point tolerance
- [x] Production documentation (nexpert-trust-engine-analysis.md)

### Phase 2: Card Generation System (Tier 3) ⏳ READY
- [ ] Create credibility_card_events table
- [ ] Create credibility_card_templates table
- [ ] Implement generate_credibility_cards.php cron
- [ ] Implement 8 trigger type logic functions
- [ ] Set up API endpoints (3 endpoints)
- [ ] Implement LinkedIn OAuth token retrieval
- [ ] Test card generation with live data
- [ ] Deploy card generation cron (production)

### Phase 3: Frontend Components (Tier 4) ⏳ READY
- [ ] Implement <CredibilityCard /> React component
- [ ] Test card on mobile (360px), tablet (768px), desktop (1200px)
- [ ] Verify WCAG AA accessibility (4.5:1 contrast, 44px targets)
- [ ] Implement animations (entry, counter, button ripple)
- [ ] Test with Figma designs
- [ ] QA on all browsers (Chrome, Safari, Firefox)
- [ ] Optimize card image rendering (1200×628px)

### Phase 4: LinkedIn Integration (Tier 3) ⏳ READY
- [ ] Set up LinkedIn OAuth app (developer.linkedin.com)
- [ ] Implement token retrieval & refresh logic
- [ ] Test share endpoint with test account
- [ ] Implement share text generation (8 templates)
- [ ] Implement card image generation
- [ ] Test full share flow (click → LinkedIn → back)
- [ ] Implement analytics tracking (impressions, engagement)

### Phase 5: Monitoring & Iteration (Tier 5)
- [ ] Set up analytics dashboard for card metrics
- [ ] Monitor card generation rates by trigger type
- [ ] Track LinkedIn engagement by card type
- [ ] A/B test different card templates
- [ ] Gather expert feedback on card UX
- [ ] Iterate on design based on data
- [ ] Document learnings in internal wiki

---

## Cross-Team FAQ

### Q: I'm a backend developer. Where do I start?
**A:** Start with Tier 2 (nexpert-qa-script-reference.md). You'll understand how to deploy and test the scoring engine. Then move to Tier 3 (Implementation) to build the card generation cron and API endpoints.

### Q: I'm a frontend developer. What do I need to know?
**A:** Read Tier 4 (Design Spec) first to understand the visual requirements. Then read Tier 3 (Implementation) to understand the data structure. Finally, implement the React component and API integration using the provided code.

### Q: I'm a QA engineer. How do I test this?
**A:** Use the QA scripts in Tier 2 (nexpert-seed.php, nexpert-ema-verify.php) to inject test data. Then use the checklists in Tier 4 (Testing Checklist) and Tier 3 (API test flows) to verify card generation and sharing.

### Q: I'm a designer. What do I need to deliver?
**A:** Create Figma components matching Tier 4 (Design Spec). Export design tokens (colors, spacing, typography). Provide 8 card variants (one for each trigger type). Hand off Figma link and design tokens JSON to developers.

### Q: How does the card system scale with thousands of experts?
**A:** The card generation runs once per day (12 AM UTC) as a cron job, checking trust_state_history for score changes. Not every expert generates a card daily—only those with meaningful changes. On 10,000 experts, ~500-1000 cards/day. Database indexes on (expert_id, generated_at) keep queries fast (<100ms).

### Q: Can an expert manually trigger a card?
**A:** Yes. The API endpoint `POST /api/v2/credibility-cards/generate?force=true` allows on-demand generation (bypassing the cron schedule). Use this for edge cases or if an expert wants to re-share an old card.

### Q: What if an expert's score drops?
**A:** Cards are only generated for *improvements* (score_after > score_before). If a score drops, no card is generated. The philosophy is: cards celebrate achievements, not declines.

### Q: How do we prevent spam/abuse of the share button?
**A:** Rate limiting: Each expert can share max 1 card per hour. The share button is disabled after clicking (state managed in React). Future: add CAPTCHA or email verification on first share.

### Q: Can we track ROI on LinkedIn shares?
**A:** Yes. The credibility_card_events table stores impressions and engagement_rate (from LinkedIn API). Build a dashboard querying this data:
```sql
SELECT 
  AVG(impressions) as avg_impressions,
  ROUND(AVG(engagement_rate), 4) as avg_engagement,
  COUNT(*) as total_shares
FROM credibility_card_events
WHERE shared_to_linkedin = 1 AND shared_at > DATE_SUB(NOW(), INTERVAL 30 DAY);
```

### Q: Should we notify experts when they're eligible for a card?
**A:** Optional. Send push notification: "🎯 You just crossed 850 credibility points! Share your achievement." Email is less effective for real-time updates. In-app toast is best.

---

## Document Updates & Maintenance

### Version Control

Each document should include:
```
---
version: 1.0
date: 2026-08-25
author: Lekha Bhan
last_updated: 2026-08-25
status: [Draft|Ready|Production|Deprecated]
---
```

### When to Update Tier Documents

| Event | Update | Documents |
|-------|--------|-----------|
| **Formula change** (α value, decay formula) | Major | Tier 1 + Tier 2 (test data) |
| **Band thresholds change** | Major | Tier 1 + Tier 3 (trigger logic) |
| **New trigger type added** | Minor | Tier 3 + Tier 4 (new card variant) |
| **Design refresh** | Minor | Tier 4 only |
| **API endpoint changes** | Major | Tier 3 + Tier 2 (integration tests) |
| **LinkedIn API updates** | Major | Tier 3 (OAuth, share endpoint) |

---

## Monitoring & Analytics Queries

### Card Generation Health

```sql
-- Cards generated today
SELECT 
  trigger_type, 
  COUNT(*) as count,
  AVG(point_gain) as avg_gain
FROM credibility_card_events
WHERE generated_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
GROUP BY trigger_type
ORDER BY count DESC;

-- Share rate by card type
SELECT 
  trigger_type,
  COUNT(*) as generated,
  SUM(shared_to_linkedin) as shared,
  ROUND(SUM(shared_to_linkedin) / COUNT(*) * 100, 1) as share_rate
FROM credibility_card_events
WHERE generated_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY trigger_type;

-- LinkedIn impact (engagement tracking)
SELECT 
  expert_id,
  COUNT(*) as cards_shared,
  ROUND(AVG(impressions), 0) as avg_impressions,
  ROUND(AVG(engagement_rate), 4) as avg_engagement,
  SUM(impressions) as total_impressions
FROM credibility_card_events
WHERE shared_to_linkedin = 1 AND shared_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY expert_id
ORDER BY total_impressions DESC
LIMIT 20;
```

---

## Future Roadmap

### Q4 2026 (Next Quarter)
- Implement A/B testing framework for card templates
- Add learner-centric outcome cards ("Your learner got certified!")
- Integrate learner satisfaction ratings into card generation

### Q1 2027
- Predictive milestone notifications ("5 points to next tier")
- Animated score counter in card UI
- Integration with Nexpert email digests

### Q2 2027
- Expert tier rewards (badges, featured profile at Established/Sovereign)
- Comparative benchmark cards ("You're top 10%, here's how to reach top 5%")
- LinkedIn live-posting via Nexpert dashboard (no manual copy-paste)

### Q3 2027
- ML-powered optimal share timing (detect when expert's network is most active)
- Multi-language card support (card templates + share text in 5+ languages)
- Video card variant (animated 15-second video for LinkedIn)

---

## Summary

Your Nexpert trust platform now has **5 interconnected technical guides** that work together:

1. **Tier 1: Architecture** — Why the system works
2. **Tier 2: Implementation** — How to build it
3. **Tier 3: API & Events** — How scores become shareable moments
4. **Tier 4: Design** — How moments look and feel
5. **Tier 5: Integration** — How it all fits together (this document)

**Key Insight:** The Daily Credibility Card isn't a feature bolted onto the trust engine. It's the **output** of the trust engine—making expert achievement visible, measurable, and worth sharing.

---

**Maintained by:** Lekha Bhan  
**Last Updated:** August 25, 2026  
**Platform:** Nexpert.ai v2  
**Status:** Complete & Production-Ready
