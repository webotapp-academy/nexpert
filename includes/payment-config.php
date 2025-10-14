<?php
// DIRECT RAZORPAY KEYS (User requested restore)
// WARNING: Live keys are hardcoded here. This is NOT recommended for production security.
// Suggestion: Move these to environment variables or an untracked secrets file before deploying.
// Provided live keys:
//   RAZORPAY_KEY_ID: rzp_live_0mfcCl30ISENa5
//   RAZORPAY_KEY_SECRET: aRbOPceozHCL9DXz307zZwXG

if (!defined('RAZORPAY_KEY_ID')) {
    define('RAZORPAY_KEY_ID', 'rzp_live_0mfcCl30ISENa5');
}
if (!defined('RAZORPAY_KEY_SECRET')) {
    define('RAZORPAY_KEY_SECRET', 'aRbOPceozHCL9DXz307zZwXG');
}
if (!defined('PLATFORM_CURRENCY')) {
    define('PLATFORM_CURRENCY', 'INR');
}

// SECURITY NOTE: Remove hardcoded keys before committing to any public repo.
?>