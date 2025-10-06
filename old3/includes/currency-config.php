<?php
// Centralized currency configuration for the entire application
// Default currency settings for Nexpert.ai platform

// Define currency constants (check if not already defined)
if (!defined('CURRENCY_CODE')) {
    define('CURRENCY_CODE', 'INR');
}
if (!defined('CURRENCY_SYMBOL')) {
    define('CURRENCY_SYMBOL', '₹');
}
if (!defined('CURRENCY_NAME')) {
    define('CURRENCY_NAME', 'Indian Rupee');
}

// Currency formatting function
function formatCurrency($amount) {
    return CURRENCY_SYMBOL . number_format($amount, 0);
}

// Currency formatting with decimals
function formatCurrencyWithDecimals($amount, $decimals = 2) {
    return CURRENCY_SYMBOL . number_format($amount, $decimals);
}