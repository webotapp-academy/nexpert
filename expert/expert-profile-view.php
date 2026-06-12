<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

// Central session + config
require_once dirname(__DIR__) . '/includes/session-config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/admin-panel/apis/connection/pdo.php';

// Get expert ID from URL or session
$expertId = $_GET['expert_id'] ?? $_SESSION['user_id'] ?? null;

if (!$expertId) {
    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

// Get expert profile data
$stmt = $pdo->prepare("
    SELECT 
        u.id as user_id,
        u.email,
        ep.full_name,
        ep.tagline,
        ep.bio_short,
        ep.bio_detailed,
        ep.profile_photo,
        ep.expertise_verticals,
        ep.industry_experience_years,
        ep.total_sessions,
        ep.rating_average,
        ep.total_reviews,
        ep.linkedin_url,
        ep.twitter_url,
        ep.portfolio_url
    FROM users u
    INNER JOIN expert_profiles ep ON u.id = ep.user_id
    WHERE u.id = ? AND u.role = 'expert' AND ep.verification_status = 'approved'
");
$stmt->execute([$expertId]);
$expert = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$expert) {
    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

// Get pricing
$stmt = $pdo->prepare("
    SELECT MIN(amount) as min_price, MAX(amount) as max_price
    FROM expert_pricing
    WHERE expert_id = ? AND is_active = 1
");
$stmt->execute([$expertId]);
$pricing = $stmt->fetch(PDO::FETCH_ASSOC);

// Get availability
$stmt = $pdo->prepare("
    SELECT day_of_week, start_time, end_time
    FROM expert_availability
    WHERE expert_id = (SELECT id FROM expert_profiles WHERE user_id = ?)
    AND is_active = 1
    ORDER BY day_of_week, start_time
");
$stmt->execute([$expertId]);
$availability = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent reviews
$stmt = $pdo->prepare("
    SELECT 
        r.rating,
        r.review_text,
        r.created_at,
        lp.full_name as learner_name,
        lp.profile_photo as learner_photo
    FROM reviews r
    LEFT JOIN learner_profiles lp ON r.learner_id = lp.user_id
    WHERE r.expert_id = ?
    ORDER BY r.created_at DESC
    LIMIT 5
");
$stmt->execute([$expertId]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get upcoming webinars
$stmt = $pdo->prepare("
    SELECT 
        id,
        title,
        description,
        webinar_date,
        webinar_time,
        duration_hours,
        price_inr,
        current_registrations,
        max_participants,
        status,
        is_active
    FROM webinars
    WHERE expert_id = ? 
    AND status = 'upcoming' 
    AND is_active = 1 
    AND webinar_date >= CURDATE()
    ORDER BY webinar_date ASC, webinar_time ASC
    LIMIT 6
");
$stmt->execute([$expertId]);
$webinars = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Debug: Check all webinars for this expert
$debug_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM webinars WHERE expert_id = ?");
$debug_stmt->execute([$expertId]);
$total_webinars = $debug_stmt->fetch(PDO::FETCH_ASSOC)['total'];
echo "<!-- Debug: Expert ID: $expertId, Total Webinars: $total_webinars, Upcoming Webinars: " . count($webinars) . " -->";

$page_title = $expert['full_name'] . " - Expert Profile";
$panel_type = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/navigation.php';

// Helper function for profile photo
function getProfilePhoto($photo) {
    if (empty($photo)) {
        return BASE_PATH . '/attached_assets/stock_images/diverse_professional_1d96e39f.jpg';
    }
    return BASE_PATH . '/' . ltrim($photo, '/');
}

// Days mapping
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
?>

<div class="bg-gradient-to-br from-blue-50 via-white to-purple-50 min-h-screen py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- DEBUG -->
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded mb-4">
            <strong>Debug:</strong> Expert ID = <?php echo $expertId; ?>, Total Webinars = <?php echo count($webinars); ?>
            <?php if (count($webinars) > 0): ?>
                <br>Webinar IDs: <?php echo implode(', ', array_column($webinars, 'id')); ?>
            <?php endif; ?>
        </div>
        
        <!-- Profile Header Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
            <!-- Banner Background -->
            <div class="h-32 bg-gradient-to-r from-primary to-blue-600"></div>
            
            <!-- Profile Content -->
            <div class="px-8 pb-8">
                <div class="flex flex-col md:flex-row items-start md:items-end gap-6 -mt-16">
                    <!-- Profile Photo -->
                    <div class="relative">
                        <div class="w-32 h-32 rounded-full border-4 border-white shadow-xl overflow-hidden bg-white">
                            <img src="<?php echo htmlspecialchars(getProfilePhoto($expert['profile_photo'])); ?>" 
                                 alt="<?php echo htmlspecialchars($expert['full_name']); ?>"
                                 class="w-full h-full object-cover">
                        </div>
                        <!-- Verification Badge -->
                        <div class="absolute bottom-0 right-0 bg-green-500 rounded-full p-2 border-4 border-white">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Profile Info -->
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($expert['full_name']); ?></h1>
                        <p class="text-xl text-gray-600 mb-4"><?php echo htmlspecialchars($expert['tagline']); ?></p>
                        
                        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                            <!-- Rating -->
                            <div class="flex items-center gap-1">
                                <span class="text-yellow-400 text-lg">★★★★★</span>
                                <span class="font-semibold"><?php echo number_format($expert['rating_average'], 1); ?></span>
                                <span>(<?php echo $expert['total_reviews']; ?> reviews)</span>
                            </div>
                            
                            <!-- Sessions -->
                            <div class="flex items-center gap-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                                <span><?php echo $expert['total_sessions']; ?>+ sessions completed</span>
                            </div>
                            
                            <!-- Experience -->
                            <?php if ($expert['industry_experience_years'] > 0): ?>
                            <div class="flex items-center gap-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <span><?php echo $expert['industry_experience_years']; ?> years experience</span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Pricing & CTA -->
                    <div class="text-center md:text-right">
                        <div class="mb-3">
                            <div class="text-sm text-gray-600">Starting from</div>
                            <div class="text-3xl font-bold text-primary">₹<?php echo number_format($pricing['min_price'] ?? 0); ?></div>
                            <div class="text-sm text-gray-500">per session</div>
                        </div>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'learner'): ?>
                        <a href="<?php echo BASE_PATH; ?>/index.php?panel=learner&page=booking&expert_id=<?php echo $expert['user_id']; ?>" 
                           class="inline-block bg-gradient-to-r from-primary to-blue-600 text-white px-6 py-3 rounded-lg hover:from-blue-600 hover:to-purple-600 transition-all duration-300 font-semibold shadow-lg">
                            Book a Session
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Social Links -->
                <div class="flex gap-4 mt-6">
                    <?php if (!empty($expert['linkedin_url'])): ?>
                    <a href="<?php echo htmlspecialchars($expert['linkedin_url']); ?>" target="_blank" class="text-gray-600 hover:text-blue-600 transition">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($expert['twitter_url'])): ?>
                    <a href="<?php echo htmlspecialchars($expert['twitter_url']); ?>" target="_blank" class="text-gray-600 hover:text-blue-400 transition">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($expert['portfolio_url'])): ?>
                    <a href="<?php echo htmlspecialchars($expert['portfolio_url']); ?>" target="_blank" class="text-gray-600 hover:text-purple-600 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-8">
                <!-- About Section -->
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">About Me</h2>
                    <p class="text-gray-700 leading-relaxed whitespace-pre-line"><?php echo htmlspecialchars($expert['bio_detailed'] ?: $expert['bio_short']); ?></p>
                </div>
                
                <!-- Expertise Areas -->
                <?php if (!empty($expert['expertise_verticals'])): ?>
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Expertise Areas</h2>
                    <div class="flex flex-wrap gap-3">
                        <?php 
                        $expertise = json_decode($expert['expertise_verticals'], true);
                        if (is_array($expertise)):
                            foreach ($expertise as $skill): ?>
                            <span class="px-4 py-2 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                                <?php echo htmlspecialchars($skill); ?>
                            </span>
                        <?php 
                            endforeach;
                        endif;
                        ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Reviews Section -->
                <?php if (count($reviews) > 0): ?>
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Recent Reviews</h2>
                    <div class="space-y-6">
                        <?php foreach ($reviews as $review): ?>
                        <div class="border-b border-gray-200 pb-6 last:border-0 last:pb-0">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-full bg-gray-200 overflow-hidden flex-shrink-0">
                                    <?php if (!empty($review['learner_photo'])): ?>
                                    <img src="<?php echo htmlspecialchars(getProfilePhoto($review['learner_photo'])); ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center bg-gray-300 text-gray-600">
                                        <?php echo strtoupper(substr($review['learner_name'] ?? 'L', 0, 1)); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-semibold text-gray-900"><?php echo htmlspecialchars($review['learner_name'] ?? 'Anonymous'); ?></h4>
                                        <span class="text-sm text-gray-500"><?php echo date('M j, Y', strtotime($review['created_at'])); ?></span>
                                    </div>
                                    <div class="text-yellow-400 mb-2">
                                        <?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                                    </div>
                                    <p class="text-gray-700"><?php echo htmlspecialchars($review['review_text']); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Availability Card -->
                <div class="bg-white rounded-xl shadow-lg p-6 sticky top-4">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Availability</h3>
                    <?php if (count($availability) > 0): ?>
                    <div class="space-y-3">
                        <?php 
                        $schedule = [];
                        foreach ($availability as $slot) {
                            $day = $days[$slot['day_of_week']];
                            if (!isset($schedule[$day])) {
                                $schedule[$day] = [];
                            }
                            $schedule[$day][] = substr($slot['start_time'], 0, 5) . ' - ' . substr($slot['end_time'], 0, 5);
                        }
                        
                        foreach ($schedule as $day => $times): ?>
                        <div class="flex justify-between items-start">
                            <span class="font-semibold text-gray-900"><?php echo $day; ?></span>
                            <div class="text-right text-gray-600 text-sm">
                                <?php echo implode('<br>', $times); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-gray-600 text-sm">Contact expert for availability</p>
                    <?php endif; ?>
                </div>
                
                <!-- Upcoming Webinars Card -->
                <?php if (count($webinars) > 0): ?>
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-gray-900">Upcoming Webinars</h3>
                        <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs font-semibold">
                            <?php echo count($webinars); ?>
                        </span>
                    </div>
                    <div class="space-y-4">
                        <?php foreach ($webinars as $webinar): ?>
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-all cursor-pointer group"
                             onclick="window.location.href='<?php echo BASE_PATH; ?>/index.php?panel=learner&page=webinar-details&id=<?php echo $webinar['id']; ?>'">
                            
                            <!-- Live Badge -->
                            <div class="mb-2">
                                <span class="inline-block px-2 py-1 bg-gradient-to-r from-purple-600 to-blue-600 text-white text-xs font-bold rounded uppercase">
                                    🎥 Live
                                </span>
                            </div>
                            
                            <!-- Title -->
                            <h4 class="font-bold text-gray-900 mb-2 group-hover:text-accent transition line-clamp-2">
                                <?php echo htmlspecialchars($webinar['title']); ?>
                            </h4>
                            
                            <!-- Date & Time -->
                            <div class="space-y-1 mb-3 text-xs text-gray-600">
                                <div class="flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span><?php echo date('M j, Y', strtotime($webinar['webinar_date'])); ?></span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span><?php echo date('g:i A', strtotime($webinar['webinar_time'])); ?> · <?php echo $webinar['duration_hours']; ?>h</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    <span class="font-semibold text-purple-600"><?php echo $webinar['current_registrations']; ?> registered</span>
                                </div>
                            </div>
                            
                            <!-- Price & View Button -->
                            <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                                <div class="text-right">
                                    <?php if ($webinar['price_inr'] > 0): ?>
                                    <span class="text-lg font-bold text-primary">₹<?php echo number_format($webinar['price_inr']); ?></span>
                                    <?php else: ?>
                                    <span class="text-sm font-bold text-green-600">FREE</span>
                                    <?php endif; ?>
                                </div>
                                <span class="text-accent font-semibold text-xs group-hover:underline">
                                    Details →
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>
