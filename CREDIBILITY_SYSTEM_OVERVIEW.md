# Nexpert Agentic Credibility Infrastructure: Technical Overview

This document provides a comprehensive technical overview of the transition from a traditional rating-based expert marketplace to an advanced, agentic, and event-driven credibility platform.

## 1. Core Philosophy
The new infrastructure moves away from subjective, easily manipulated star ratings. Instead, it tracks **objective behavioral signals** and analyzes them using AI Agents to determine an expert's true reliability and expertise level.

## 2. Technical Architecture
The system is built on a 3-layer event-driven architecture:

1.  **Event Emission Layer (PHP)**: Core APIs log structured events into the database whenever a significant expert action occurs.
2.  **Agent Processing Layer (LLM)**: An asynchronous processor uses GPT-4o-mini to categorize events into four key signals: **Structure, Outcome, Boundary, and Consistency**.
3.  **Aggregation Layer (EMA)**: A background engine calculates a dynamic "Trust Score" using Exponential Moving Average (EMA), ensuring that recent performance is weighted more heavily than old history.

## 3. Database Schema (`nexpert_ai` database)
We implemented four new tables to manage the lifecycle of a trust signal:

*   **`trust_events`**: Stores raw payloads of actions like `session_completed` or `kyc_verified`.
*   **`trust_signals`**: Stores AI-generated quality scores (0-100) and justifications for each event.
*   **`trust_state`**: Stores the expert's current "Trust Tier" (A, B, or C) and their scores in the four categories.
*   **`trust_state_history`**: Provides a temporal record of how an expert's credibility has evolved over time.

## 4. Implemented Event Hooks
We have instrumented the following APIs to emit trust events:

| Event Type | API Path | Significance |
|:---|:---|:---|
| `booking_created` | `learner/payment.php` | Tracks initial demand and expert interest. |
| `session_completed` | `expert/session-management.php` | Primary signal for reliability and summary quality. |
| `feedback_submitted` | `learner/reviews.php` | Captures learner satisfaction signals for AI analysis. |
| `kyc_verified` | `admin/experts.php` | Administrative trust and verification signal. |
| `expert_profile_updated` | `expert/profile.php` | Tracks professionalism and organizational structure. |

## 5. Key Infrastructure Files

### Core Helpers
- **`admin-panel/apis/connection/trust-helper.php`**: Unified interface for logging events from any PHP file.
- **`admin-panel/apis/connection/agent-helper.php`**: Facilitates LLM communication for signal extraction.

### Processing Engines
- **`admin-panel/apis/events/process.php`**: The "Brain" that processes pending events into structured signals.
- **`cron/update_trust_scores.php`**: The "Heart" that recomputes scores and tiers based on new signals.

## 6. User Interfaces

### Admin Console (`admin/admin-credibility.php`)
A centralized command center for Nexpert admins to:
- Monitor trust distribution across the platform.
- Trigger manual event processing.
- View detailed expert trust timelines.

### Expert Dashboard (`expert/expert-dashboard.php`)
A premium "Trust Insights" card that gives experts transparency into their AI-determined performance metrics.

### Public Profile (`learner/learner-expert-profile.php`)
A "Trust Tier" badge displayed to learners, providing instant credibility beyond simple numbers.

## 7. How to Test & Maintain

### Processing Events
To process pending events manually, visit:
`{BASE_URL}/admin-panel/apis/events/process.php` (Requires admin login).

### Recomputing Scores
To update the scores for all experts based on recent signals, run the cron script:
`php cron/update_trust_scores.php`

---
*Developed by Antigravity (Advanced Agentic Coding Team)*
