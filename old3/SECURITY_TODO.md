# Security Implementation TODO

## Current Status
✅ **Expert Panel APIs - Functionally Complete**
- All 10 APIs built and tested
- Database integration working
- JavaScript wrapper layer created
- Ownership verification added to UPDATE/DELETE operations
- Error logging implemented
- Prepared statements used throughout

## ⚠️ Critical Security Requirements (Must Implement Before Production)

### 1. Authentication Middleware
**Priority: CRITICAL**
```php
// Create: admin-panel/apis/middleware/auth.php
// - Validate PHP session
// - Derive user_id and role from $_SESSION
// - Return 401 if not authenticated
// - Return 403 if role mismatch
```

### 2. Update All APIs to Use Session-Based Auth
**Current Issue:** APIs accept expert_id/user_id from client (IDOR vulnerability)

**Required Changes:**
- Remove `expert_id` from request parameters
- Get `expert_id` from `$_SESSION['user_id']` after auth middleware
- Verify `$_SESSION['role'] === 'expert'`

**Files to Update:**
- [ ] admin-panel/apis/expert/profile.php
- [ ] admin-panel/apis/expert/pricing.php
- [ ] admin-panel/apis/expert/availability.php
- [ ] admin-panel/apis/expert/bookings.php
- [ ] admin-panel/apis/expert/sessions.php
- [ ] admin-panel/apis/expert/earnings.php
- [ ] admin-panel/apis/expert/learners.php
- [ ] admin-panel/apis/expert/workflows.php
- [ ] admin-panel/apis/expert/dashboard.php
- [ ] admin-panel/apis/expert/verification.php

### 3. Additional Security Hardening

#### CSRF Protection
- Add CSRF token generation/validation
- Include token in all state-changing requests
- Validate token on POST/PUT/DELETE

#### Input Validation
- Validate booking status against whitelist
- Validate numeric ranges (amounts, durations)
- Sanitize file upload paths
- Validate datetime formats

#### Resource Authorization
- Sessions resource upload: Verify caller is booking's expert or learner
- Availability updates: Verify expert owns the availability slots
- All mutations: Double-check resource ownership

#### HTTP Status Codes
- 401 Unauthorized for missing/invalid auth
- 403 Forbidden for insufficient permissions
- 400 Bad Request for invalid input
- 500 Internal Server Error (logged only)

## Implementation Order

1. **Phase 1: Authentication System** (Required First)
   - Create session-based login system
   - Build auth middleware
   - Update JavaScript to handle sessions

2. **Phase 2: API Updates** (After Phase 1)
   - Update all expert APIs to use session-derived IDs
   - Remove client-supplied expert_id parameters
   - Add role verification

3. **Phase 3: Security Hardening** (Final)
   - CSRF protection
   - Input validation
   - Status whitelisting
   - Complete authorization checks

## Current Development Workaround
For development/testing purposes, APIs currently accept expert_id=1 from client.
**⚠️ DO NOT USE IN PRODUCTION ⚠️**
