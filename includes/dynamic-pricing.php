<?php
/**
 * Dynamic Pricing Helper Functions
 * Handles automatic price increases based on booking demand
 * Price increases every 3 bookings (4th booking triggers increase)
 */

/**
 * Calculate dynamic price for an expert based on booking count
 * Formula: current_price = base_price + (floor(booking_count / 3) * increment)
 * 
 * @param float $base_price The expert's original/base price
 * @param int $booking_count Total confirmed/completed bookings
 * @param float $increment_percentage Percentage increase per tier (default 10%)
 * @return float The calculated dynamic price
 */
function calculate_dynamic_price($base_price, $booking_count, $increment_percentage = 10) {
    if ($base_price <= 0) {
        return $base_price;
    }
    
    // Calculate which price tier (every 3 bookings = 1 tier)
    $tier = floor($booking_count / 3);
    
    // Calculate increment amount per tier
    $increment_per_tier = ($base_price * $increment_percentage) / 100;
    
    // Calculate final price
    $dynamic_price = $base_price + ($tier * $increment_per_tier);
    
    return round($dynamic_price, 2);
}

/**
 * Get learner-specific booking count for an expert
 * This counts how many times THIS learner has booked THIS expert
 * 
 * @param PDO $pdo Database connection
 * @param int $learner_id The learner's user ID
 * @param int $expert_id The expert's user ID
 * @return int Number of confirmed/completed bookings
 */
function get_learner_expert_booking_count($pdo, $learner_id, $expert_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM bookings 
            WHERE learner_id = ? 
            AND expert_id = ? 
            AND status IN ('confirmed', 'completed')
        ");
        $stmt->execute([$learner_id, $expert_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return intval($result['count'] ?? 0);
    } catch (PDOException $e) {
        error_log("Error getting learner-expert booking count: " . $e->getMessage());
        return 0;
    }
}

/**
 * Calculate learner-specific dynamic price
 * Price increases based on how many times THIS learner has booked THIS expert
 * 
 * @param PDO $pdo Database connection
 * @param int $learner_id The learner's user ID
 * @param int $expert_id The expert's user ID
 * @param float $base_price The expert's base price
 * @return array Pricing information
 */
function calculate_learner_dynamic_price($pdo, $learner_id, $expert_id, $base_price) {
    // Get how many times this learner has booked this expert
    $learner_booking_count = get_learner_expert_booking_count($pdo, $learner_id, $expert_id);
    
    // Calculate dynamic price based on learner's booking history
    $dynamic_price = calculate_dynamic_price($base_price, $learner_booking_count);
    $tier_info = get_price_tier_info($learner_booking_count);
    
    return [
        'base_price' => $base_price,
        'current_price' => $dynamic_price,
        'learner_booking_count' => $learner_booking_count,
        'tier' => $tier_info['tier'],
        'tier_label' => $tier_info['tier_label'],
        'is_near_increase' => $tier_info['is_near_increase'],
        'bookings_until_next' => $tier_info['bookings_until_next']
    ];
}

/**
 * Get price tier information for UI display
 * 
 * @param int $booking_count Total bookings
 * @return array Tier info with level, label, and bookings until next tier
 */
function get_price_tier_info($booking_count) {
    $tier = floor($booking_count / 3);
    $bookings_in_current_tier = $booking_count % 3;
    $bookings_until_next = 3 - $bookings_in_current_tier;
    
    $tier_labels = [
        0 => 'Standard',
        1 => 'Popular',
        2 => 'High Demand',
        3 => 'Premium',
        4 => 'Elite'
    ];
    
    $tier_label = $tier >= 5 ? 'Elite+' : ($tier_labels[$tier] ?? 'Standard');
    
    return [
        'tier' => $tier,
        'tier_label' => $tier_label,
        'bookings_in_tier' => $bookings_in_current_tier,
        'bookings_until_next' => $bookings_until_next,
        'is_near_increase' => $bookings_until_next <= 1
    ];
}

/**
 * Get badge HTML for price tier
 * 
 * @param int $tier The price tier level
 * @return string HTML for badge
 */
function get_tier_badge_html($tier) {
    $badges = [
        0 => '',
        1 => '<span class="badge bg-info ms-2">Popular</span>',
        2 => '<span class="badge bg-warning text-dark ms-2">High Demand</span>',
        3 => '<span class="badge bg-primary ms-2">Premium</span>',
        4 => '<span class="badge bg-danger ms-2">Elite</span>'
    ];
    
    if ($tier >= 5) {
        return '<span class="badge bg-danger ms-2">Elite+</span>';
    }
    
    return $badges[$tier] ?? '';
}

/**
 * Increment booking count for an expert (call when booking is confirmed/completed)
 * 
 * @param PDO $pdo Database connection
 * @param int $expert_id The expert's user ID
 * @return bool Success status
 */
function increment_expert_booking_count($pdo, $expert_id) {
    try {
        $stmt = $pdo->prepare("
            UPDATE expert_profiles 
            SET booking_count = booking_count + 1 
            WHERE user_id = ?
        ");
        return $stmt->execute([$expert_id]);
    } catch (PDOException $e) {
        error_log("Error incrementing booking count: " . $e->getMessage());
        return false;
    }
}

/**
 * Get dynamic pricing info for an expert
 * 
 * @param PDO $pdo Database connection
 * @param int $expert_id The expert's user ID
 * @return array|null Pricing info or null on error
 */
function get_expert_dynamic_pricing($pdo, $expert_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                base_price,
                booking_count
            FROM expert_profiles
            WHERE user_id = ?
        ");
        $stmt->execute([$expert_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $base_price = floatval($result['base_price']);
            $booking_count = intval($result['booking_count']);
            
            $current_price = calculate_dynamic_price($base_price, $booking_count);
            $tier_info = get_price_tier_info($booking_count);
            
            return [
                'base_price' => $base_price,
                'current_price' => $current_price,
                'booking_count' => $booking_count,
                'tier' => $tier_info['tier'],
                'tier_label' => $tier_info['tier_label'],
                'is_near_increase' => $tier_info['is_near_increase'],
                'bookings_until_next' => $tier_info['bookings_until_next']
            ];
        }
        
        return null;
    } catch (PDOException $e) {
        error_log("Error fetching dynamic pricing: " . $e->getMessage());
        return null;
    }
}
?>
