# Nexpert Trust System Analysis: Client PDF Guide vs. Current Codebase

This document details the analysis of the project's current Trust/Credibility implementation compared against the client's newly provided PDF guide **(`nexpert-founder-guide-v3.pdf`)** and the previous specification **(`requirement1.txt`)**.

---

## 1. Executive Summary

- **Current Implementation Status**: The codebase already contains a fully developed **Agentic Credibility Infrastructure** based on the earlier specification (`requirement1.txt`). It uses a PHP-native GPT-4o-mini AI integration (`agent-helper.php`) and Exponential Moving Average (EMA) scoring (`trust-aggregator.php`).
- **The Client's PDF Guide**: The client has separately written a guide advocating for a **"No-Agent Version"** (deterministic PHP/SQL calculations with no LLM calls) with a different database schema (`eci_signals`, `eci_scores`, `ALTER experts`) and 5 distinct bands (Sovereign, Established, Verified, Emerging, Unverified).
- **Core Tally**: The client is **correct** that her specific "ECI" database schema, formulas, and UI elements (like SVG rings or specific wording changes) are not implemented. However, she is **incorrect** in claiming that *no* trust infrastructure exists, as a more complex AI-driven version is already fully integrated.

---

## 2. Right vs. Wrong Tally of Client's Statements

Below is a detailed verification of the claims made by the client in the PDF guide:

| PDF Location | Client's Claim | Codebase Reality Check | Verdict |
| :--- | :--- | :--- | :--- |
| **Page 1 (Section 01)** | *"The entire MVP2 differentiation does not exist yet in any file."* | **A trust system is already implemented.** Tables (`trust_events`, `trust_signals`, `trust_state`, `trust_state_history`) and backend files (`agent-helper.php`, `process.php`, `update_trust_scores.php`) exist and are operational. | ❌ **Wrong** |
| **Page 2 (Section 01)** | *"3 new database tables (eci_signals, eci_scores, ALTER experts)"* | These specific tables and columns do not exist. The codebase uses `trust_state` and `trust_signals` instead. | ❌ **Wrong (Not Done)** |
| **Page 2 (Section 01)** | *"1 new PHP file: includes/eci_engine.php"* | No `eci_engine.php` file exists. The scoring logic is currently in `admin-panel/apis/connection/trust-aggregator.php` using EMA. | ❌ **Wrong (Not Done)** |
| **Page 2 / Page 14** | *Update navigation.php: change "Expert Login" to "Apply as Expert"* | The codebase still has "Expert Login" buttons (e.g. lines 104, 141 in `includes/navigation.php`). |  **Right (Not Done)** |
| **Page 2 / Page 14** | *Update home.php: replace fake learner count on landing page* | Line 130 of `home.php` still reads `"2,000+ learners already matched with 100+ verified experts"`. |  **Right (Not Done)** |
| **Page 10 (Prompt 4)** | *IDOR vulnerability in `admin-panel/apis/expert/`* | Files like `sessions.php` (lines 14, 112) read `expert_id` directly from input payloads without verifying it against `$_SESSION['user_id']`. |  **Right (Vulnerable)** |

---

## 3. Done vs. Not Done according to the PDF Specifications

Here is a breakdown of what has been implemented versus what remains pending based on the client's PDF specifications:

### 🗄️ Database & Schema
- [x] **Event/Signal Logging**: Done via `trust_events` and `trust_signals` (rather than PDF's `eci_signals`).
- [ ] **ECI Specific Tables**: Pending. `eci_signals`, `eci_scores`, and the `experts` table alterations (`eci_score`, `eci_band`, `eci_band_color`, `eci_last_computed`) do not exist.

### ⚙️ Scoring Engine & Calculations
- [x] **Calculations & Aggregation**: Done using Exponential Moving Average (EMA) and 4 categories (Structure, Outcome, Boundary, Consistency).
- [ ] **Deterministic Formula (No-Agent)**: Pending. The PDF requires a deterministic count-based formula (`c_pred`, `c_cons`, `c_depth`, `c_acct`, `c_long`) without any OpenAI/LLM calls. The current codebase calls GPT-4o-mini in `agent-helper.php`.
- [ ] **Bands & Tiers**: The codebase implements 3 tiers (`A`, `B`, `C`), whereas the PDF expects 5 bands (`Sovereign`, `Established`, `Verified`, `Emerging`, `Unverified`) with specific hex colors.

### 🔌 API & Event Hooks
- [x] **Event Emitting**: Done. Actions like KYC approval, feedback submission, and session updates emit events.
- [ ] **Direct ECI Hooks**: Pending. Existing hooks call `TrustHelper::logEvent` (saving to `trust_events`), not the PDF's `recordSignal` (saving directly to `eci_signals`).

### 🖥️ User Interfaces
- [x] **Admin Page**: Done. `admin/admin-credibility.php` exists.
- [ ] **Admin Trust Engine Page (`admin-trust-engine.php`)**: Pending. The specific tables and actions (like "Recompute All" button) specified in Prompt 6 do not match the current admin page structure.
- [x] **Expert Dashboard**: Done. A "Trust Insights" card is present.
- [ ] **Expert Dashboard SVG Ring**: Pending. The dashboard lacks the specific animated SVG ring showing the 0-100 score inside it and the +Score tiles described in Prompt 2.
- [x] **Learner Browse Page**: Done. Shows trust tiers and percentages.
- [ ] **Learner Browse Page Min Trust Filters**: Pending. Lacks the dropdown filters (All, 60+, 75+, 90+) and JavaScript replacements described in Prompt 5.

---

## 4. Recommendations for Next Steps

1. **Address the Security Vulnerability (IDOR)**: Update all files under `admin-panel/apis/expert/` to enforce that `$expert_id` is retrieved securely from the active session (`$_SESSION['user_id']`) instead of the request parameters.
2. **Update Static text on Landing & Navigation Pages**: Edit `home.php` and `includes/navigation.php` to clean up the fake numbers and change the buttons as requested.
3. **Decide on the AI vs. Non-AI Engine**: Align with the client on whether to keep the advanced GPT-4o-mini system currently implemented, or downgrade it to the simpler, deterministic, PHP/SQL-only scoring system (`eci_engine.php`) requested in the PDF.
