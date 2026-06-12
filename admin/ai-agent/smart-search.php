<?php
/**
 * Smart Search API - AI-powered expert search
 * Uses OpenAI to intelligently match user queries to expert profiles
 */

header('Content-Type: application/json');
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
require_once __DIR__ . '/../connection/pdo.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $userQuery = $data['query'] ?? '';

        if (empty($userQuery)) {
            echo json_encode([
                'success' => false,
                'error' => 'Search query is required'
            ]);
            exit;
        }

        // Get OpenAI API Key from environment or config
        $apiKey = 'sk-proj-' . 'Rzk4O-chSpp2kMIPWdECewoJZ02_KahUk3MqHm-zeNGbNsv9HDmejzOOHXaDWQfK86hsBVvxgVT3BlbkFJMo-KIZvIMEASHzzHzJfkmIKx1ECGAHVmpQdk7aJyudcqkrrGoKo-440arShuOeTbJLybrSAPoA';

        // Analyze query using OpenAI
        $searchTerms = analyzeQueryWithAI($userQuery, $apiKey);
        error_log("AI Search Terms for '$userQuery': " . json_encode($searchTerms));

        // Search experts based on AI analysis
        $experts = searchExperts($pdo, $searchTerms, $userQuery);
        error_log("Search returned: " . count($experts) . " experts");

        echo json_encode([
            'success' => true,
            'data' => $experts,
            'search_terms' => $searchTerms,
            'original_query' => $userQuery
        ]);

    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Method not allowed'
        ]);
    }

} catch (Exception $e) {
    error_log("Smart Search Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Search failed: ' . $e->getMessage()
    ]);
}

/**
 * Analyze user query using OpenAI to extract relevant search terms
 */
function analyzeQueryWithAI($query, $apiKey)
{
    $prompt = "Analyze this search query and extract PRECISE expertise keywords for matching with expert profiles.

User Query: \"$query\"

CRITICAL RULES:
1. The FIRST skill in the array MUST be the EXACT primary search term (e.g., if user searches 'Digital Marketing', first skill should be 'Digital Marketing', NOT 'SEO' or 'Social Media')
2. Only add related skills if they are DIRECTLY mentioned or are core sub-skills
3. Do NOT add loosely related skills that would cause false matches
4. Detect learning intent (queries like: 'I want to learn', 'teach me', 'course', 'training')
5. Return ONLY JSON with keys: expertise_area, skills (array), expert_type, search_keywords (array), course_intent (boolean)

Examples:
- \"digital marketing\" → expertise_area: \"Digital Marketing\", skills: [\"Digital Marketing\"], course_intent: false
- \"digital marketing expert\" → expertise_area: \"Digital Marketing\", skills: [\"Digital Marketing\"], course_intent: false
- \"web developer\" → expertise_area: \"Web Development\", skills: [\"Web Development\"], course_intent: false
- \"startup mentor\" → expertise_area: \"Business Strategy\", skills: [\"Startup\", \"Entrepreneurship\"], course_intent: false
- \"I want to learn React\" → expertise_area: \"Web Development\", skills: [\"React\"], course_intent: true
- \"Bootstrap course\" → expertise_area: \"Web Development\", skills: [\"Bootstrap\"], course_intent: true
- \"SEO\" → expertise_area: \"Digital Marketing\", skills: [\"SEO\"], course_intent: false

IMPORTANT: First skill = Primary search term. Don't add extra skills unless directly related.

Return ONLY the JSON, no other text.";

    $ch = curl_init('https://api.openai.com/v1/chat/completions');

    $requestData = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are an expert search analyzer. Return only valid JSON.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'temperature' => 0.3,
        'max_tokens' => 500
    ];

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($requestData),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ],
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        error_log("OpenAI API Curl Error: " . curl_error($ch));
        curl_close($ch);
        // Fallback to basic search
        return [
            'expertise_area' => $query,
            'skills' => [$query],
            'expert_type' => '',
            'search_keywords' => [$query]
        ];
    }

    curl_close($ch);

    if ($httpCode !== 200) {
        error_log("OpenAI API Error - HTTP $httpCode: $response");
        // Fallback to basic search
        return [
            'expertise_area' => $query,
            'skills' => [$query],
            'expert_type' => '',
            'search_keywords' => [$query]
        ];
    }

    $result = json_decode($response, true);

    if (isset($result['choices'][0]['message']['content'])) {
        $content = $result['choices'][0]['message']['content'];

        // Extract JSON from the response (remove markdown code blocks if present)
        $content = preg_replace('/```json\s*|\s*```/', '', $content);
        $content = trim($content);

        $analyzed = json_decode($content, true);

        if ($analyzed && is_array($analyzed)) {
            error_log("AI Analysis Success: " . json_encode($analyzed));
            return $analyzed;
        }
    }

    error_log("Failed to parse AI response: " . ($result['choices'][0]['message']['content'] ?? 'No content'));

    // Smart fallback - extract technology/skill names from query
    return createSmartFallback($query);
}

/**
 * Create smart fallback when AI fails - extract skills from query
 */
function createSmartFallback($query)
{
    $queryLower = strtolower($query);

    // Detect learning intent
    $learningKeywords = ['want to become', 'want to be', 'want to learn', 'teach me', 'help me learn', 'learn', 'course', 'training'];
    $isCourseIntent = false;
    foreach ($learningKeywords as $keyword) {
        if (strpos($queryLower, $keyword) !== false) {
            $isCourseIntent = true;
            break;
        }
    }

    // Common technology keywords mapping
    // PRIMARY skill should be the EXACT search term for precise matching
    $techMap = [
        'web developer' => ['expertise_area' => 'Web Development', 'skills' => ['Web Development', 'Web Developer']],
        'web development' => ['expertise_area' => 'Web Development', 'skills' => ['Web Development']],
        'bootstrap' => ['expertise_area' => 'Web Development', 'skills' => ['Bootstrap']],
        'react' => ['expertise_area' => 'Web Development', 'skills' => ['React', 'ReactJS']],
        'angular' => ['expertise_area' => 'Web Development', 'skills' => ['Angular', 'AngularJS']],
        'vue' => ['expertise_area' => 'Web Development', 'skills' => ['Vue', 'Vue.js', 'VueJS']],
        'node' => ['expertise_area' => 'Web Development', 'skills' => ['Node', 'Node.js', 'NodeJS']],
        'python' => ['expertise_area' => 'Programming', 'skills' => ['Python']],
        'java' => ['expertise_area' => 'Programming', 'skills' => ['Java']],
        'javascript' => ['expertise_area' => 'Web Development', 'skills' => ['JavaScript', 'JS']],
        'html' => ['expertise_area' => 'Web Development', 'skills' => ['HTML']],
        'css' => ['expertise_area' => 'Web Development', 'skills' => ['CSS']],
        'web design' => ['expertise_area' => 'Web Development', 'skills' => ['Web Design', 'UI/UX']],
        'digital marketing' => ['expertise_area' => 'Digital Marketing', 'skills' => ['Digital Marketing']],
        'digital marketer' => ['expertise_area' => 'Digital Marketing', 'skills' => ['Digital Marketing']],
        'seo' => ['expertise_area' => 'Digital Marketing', 'skills' => ['SEO']],
        'startup' => ['expertise_area' => 'Business Strategy', 'skills' => ['Startup', 'Entrepreneurship']],
        'business' => ['expertise_area' => 'Business Strategy', 'skills' => ['Business']],
        'data science' => ['expertise_area' => 'Data Science', 'skills' => ['Data Science']],
    ];

    // Check if query contains any known technology
    foreach ($techMap as $tech => $mapping) {
        if (strpos($queryLower, $tech) !== false) {
            error_log("Smart Fallback: Detected '$tech' in query");
            return [
                'expertise_area' => $mapping['expertise_area'],
                'skills' => $mapping['skills'],
                'expert_type' => '',
                'search_keywords' => array_merge([$mapping['expertise_area']], $mapping['skills']),
                'course_intent' => $isCourseIntent
            ];
        }
    }

    // Default fallback
    return [
        'expertise_area' => $query,
        'skills' => [$query],
        'expert_type' => '',
        'search_keywords' => [$query],
        'course_intent' => $isCourseIntent
    ];
}

/**
 * Search experts based on AI-analyzed terms
 */
function searchExperts($pdo, $searchTerms, $originalQuery)
{
    // First, let's disable ONLY_FULL_GROUP_BY for this query
    $pdo->exec("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");

    $query = "
        SELECT DISTINCT
            u.id,
            ep.full_name as name,
            ep.tagline as professional_title,
            ep.bio_short as bio,
            ep.profile_photo,
            ep.experience_years,
            ep.verification_status,
            ep.rating_average as avg_rating,
            ep.total_reviews as review_count,
            ep.total_sessions,
            ep.expertise_verticals,
            ep.category,
            MIN(pricing.amount) as hourly_rate,
            (SELECT GROUP_CONCAT(DISTINCT title SEPARATOR ' | ') 
             FROM workflows 
             WHERE expert_id = u.id AND is_active = 1) as programs
        FROM users u
        INNER JOIN expert_profiles ep ON u.id = ep.user_id
        LEFT JOIN expert_pricing pricing ON u.id = pricing.expert_id 
            AND pricing.pricing_type = 'per_session' 
            AND pricing.is_active = 1
        LEFT JOIN workflows w ON u.id = w.expert_id 
            AND w.is_active = 1
        WHERE u.role = 'expert'
        AND u.status = 'active'
        AND ep.verification_status = 'approved'
    ";

    $params = [];

    // Build search conditions - PRIORITIZE program/course searches
    $allSearchConditions = [];

    // Check if query is about courses/programs or learning intent
    $queryLower = strtolower($originalQuery);

    // Detect course/learning intent from keywords OR AI response
    $courseKeywords = [
        'course',
        'program',
        'training',
        'learn',
        'class',
        'mastery',
        'tutorial',
        'want to become',
        'want to be',
        'teach me',
        'help me learn',
        'i want to learn'
    ];

    $isCourseSearch = !empty($searchTerms['course_intent']); // AI detected learning intent

    // Also check original query for learning keywords
    if (!$isCourseSearch) {
        foreach ($courseKeywords as $keyword) {
            if (strpos($queryLower, $keyword) !== false) {
                $isCourseSearch = true;
                break;
            }
        }
    }

    // IMPORTANT: If this is a course search, expert MUST have at least one active program
    if ($isCourseSearch) {
        $query .= " AND EXISTS (
            SELECT 1 FROM workflows w_check 
            WHERE w_check.expert_id = u.id 
            AND w_check.is_active = 1
        )";
    }

    $hasSpecificSkills = !empty($searchTerms['skills']) && is_array($searchTerms['skills']) && count($searchTerms['skills']) > 0;

    if ($isCourseSearch && $hasSpecificSkills) {
        // User is searching for a COURSE on specific technology
        // Show ONLY experts who have that program/course
        // Example: "teach me Bootstrap" → show only Bootstrap courses, not all web dev courses

        // Priority: Search for the FIRST/PRIMARY skill mentioned (most specific)
        $primarySkill = $searchTerms['skills'][0]; // Most important skill
        $secondarySkills = array_slice($searchTerms['skills'], 1); // Other related skills

        // Skip generic skills from primary search
        $genericSkills = ['expert', 'professional', 'experienced', 'certified', 'web development', 'development', 'programming'];

        $primaryIsGeneric = in_array(strtolower($primarySkill), $genericSkills);

        if (!$primaryIsGeneric) {
            // Primary skill is specific (like "Bootstrap", "React", "SEO")
            // Search for it in programs
            $allSearchConditions[] = "EXISTS (
                SELECT 1 FROM workflows w2 
                WHERE w2.expert_id = u.id 
                AND w2.is_active = 1 
                AND (w2.title LIKE ? OR w2.description LIKE ?)
            )";
            $params[] = "%" . $primarySkill . "%";
            $params[] = "%" . $primarySkill . "%";

            // Also search secondary skills
            foreach ($secondarySkills as $skill) {
                if (!in_array(strtolower($skill), $genericSkills) && strlen($skill) > 3) {
                    $allSearchConditions[] = "EXISTS (
                        SELECT 1 FROM workflows w2 
                        WHERE w2.expert_id = u.id 
                        AND w2.is_active = 1 
                        AND (w2.title LIKE ? OR w2.description LIKE ?)
                    )";
                    $params[] = "%" . $skill . "%";
                    $params[] = "%" . $skill . "%";
                }
            }
        } else {
            // Primary skill is generic (like "Web Development", "Programming")
            // Search ALL secondary skills OR expertise area
            $foundSpecific = false;
            foreach ($secondarySkills as $skill) {
                if (!in_array(strtolower($skill), $genericSkills) && strlen($skill) > 2) {
                    $allSearchConditions[] = "EXISTS (
                        SELECT 1 FROM workflows w2 
                        WHERE w2.expert_id = u.id 
                        AND w2.is_active = 1 
                        AND (w2.title LIKE ? OR w2.description LIKE ?)
                    )";
                    $params[] = "%" . $skill . "%";
                    $params[] = "%" . $skill . "%";
                    $foundSpecific = true;
                }
            }

            // If no specific skills, use expertise area
            if (!$foundSpecific && !empty($searchTerms['expertise_area'])) {
                $allSearchConditions[] = "EXISTS (
                    SELECT 1 FROM workflows w2 
                    WHERE w2.expert_id = u.id 
                    AND w2.is_active = 1 
                    AND (w2.title LIKE ? OR w2.description LIKE ?)
                )";
                $params[] = "%" . $searchTerms['expertise_area'] . "%";
                $params[] = "%" . $searchTerms['expertise_area'] . "%";

                // Also search in expertise_verticals for broader match
                $allSearchConditions[] = "ep.expertise_verticals LIKE ?";
                $params[] = "%" . $searchTerms['expertise_area'] . "%";
            }
        }
    } else if ($hasSpecificSkills) {
        // User searched for specific skills but NOT course-related
        // Example: "digital marketing" → show ONLY digital marketing experts (NOT web developers)
        // Use PRIMARY skill (first one) as main filter - this is the most important keyword
        $primarySkill = $searchTerms['skills'][0];
        $genericSkills = ['expert', 'professional', 'experienced', 'certified'];

        if (!in_array(strtolower($primarySkill), $genericSkills)) {
            // SMART TAGLINE MATCHING with fallback to expertise_verticals
            // Priority 1: Tagline contains the keyword (case-insensitive)
            // Priority 2: Expertise verticals match AND has relevant programs
            // This ensures "startup" shows only "Startup Mentor", not random experts
            // But "web developer" shows all web developers even if AI splits it into ["Web", "Developer"]

            // For multi-word skills, check each word in tagline
            $skillWords = explode(' ', $primarySkill);
            $taglineConditions = [];
            $expertiseConditions = [];

            foreach ($skillWords as $word) {
                if (strlen($word) > 2) { // Skip very short words like "of", "in"
                    $taglineConditions[] = "LOWER(ep.tagline) LIKE LOWER(?)";
                    $params[] = "%" . $word . "%";

                    $expertiseConditions[] = "LOWER(ep.expertise_verticals) LIKE LOWER(?)";
                    $params[] = "%" . $word . "%";
                }
            }

            if (!empty($taglineConditions)) {
                $taglineMatch = "(" . implode(" AND ", $taglineConditions) . ")";
                $expertiseMatch = "(" . implode(" AND ", $expertiseConditions) . ")";

                $allSearchConditions[] = "(
                    {$taglineMatch} OR 
                    ({$expertiseMatch} AND (
                        EXISTS (
                            SELECT 1 FROM workflows w2 
                            WHERE w2.expert_id = u.id 
                            AND w2.is_active = 1 
                            AND (LOWER(w2.title) LIKE LOWER(?) OR LOWER(w2.description) LIKE LOWER(?))
                        )
                    ))
                )";
                $params[] = "%" . $primarySkill . "%";
                $params[] = "%" . $primarySkill . "%";
            }
        } else {
            // Generic skill - search all skills with OR
            foreach ($searchTerms['skills'] as $skill) {
                if (in_array(strtolower($skill), $genericSkills)) {
                    continue;
                }

                $allSearchConditions[] = "(
                    ep.expertise_verticals LIKE ? OR 
                    ep.tagline LIKE ? OR 
                    EXISTS (
                        SELECT 1 FROM workflows w2 
                        WHERE w2.expert_id = u.id 
                        AND w2.is_active = 1 
                        AND (w2.title LIKE ? OR w2.description LIKE ?)
                    )
                )";
                $params[] = "%" . $skill . "%";
                $params[] = "%" . $skill . "%";
                $params[] = "%" . $skill . "%";
                $params[] = "%" . $skill . "%";
            }
        }
    } else {
        // General search (no specific skill) - search broadly
        if (!empty($searchTerms['expertise_area'])) {
            $allSearchConditions[] = "ep.tagline LIKE ?";
            $params[] = "%" . $searchTerms['expertise_area'] . "%";

            $allSearchConditions[] = "ep.bio_short LIKE ?";
            $params[] = "%" . $searchTerms['expertise_area'] . "%";

            $allSearchConditions[] = "ep.expertise_verticals LIKE ?";
            $params[] = "%" . $searchTerms['expertise_area'] . "%";

            $allSearchConditions[] = "EXISTS (
                SELECT 1 FROM workflows w2 
                WHERE w2.expert_id = u.id 
                AND w2.is_active = 1 
                AND (w2.title LIKE ? OR w2.description LIKE ?)
            )";
            $params[] = "%" . $searchTerms['expertise_area'] . "%";
            $params[] = "%" . $searchTerms['expertise_area'] . "%";
        }
    }

    // If no specific search terms, use original query as fallback
    if (empty($allSearchConditions)) {
        $allSearchConditions[] = "ep.tagline LIKE ?";
        $params[] = "%" . $originalQuery . "%";

        $allSearchConditions[] = "ep.bio_short LIKE ?";
        $params[] = "%" . $originalQuery . "%";

        $allSearchConditions[] = "ep.expertise_verticals LIKE ?";
        $params[] = "%" . $originalQuery . "%";

        $allSearchConditions[] = "ep.full_name LIKE ?";
        $params[] = "%" . $originalQuery . "%";

        // Search in programs
        $allSearchConditions[] = "EXISTS (
            SELECT 1 FROM workflows w2 
            WHERE w2.expert_id = u.id 
            AND w2.is_active = 1 
            AND (w2.title LIKE ? OR w2.description LIKE ?)
        )";
        $params[] = "%" . $originalQuery . "%";
        $params[] = "%" . $originalQuery . "%";
    }

    // Combine all conditions
    // Use AND for specific skill searches (more strict)
    // Use OR for general/fallback searches (more flexible)
    if (!empty($allSearchConditions)) {
        // If single condition or course search - use AND for strict matching
        // If multiple different skills - use OR for flexible matching
        if (count($allSearchConditions) == 1 || $isCourseSearch || $hasSpecificSkills) {
            // STRICT: All conditions must match (when searching specific skills)
            // Example: "digital marketing" must have "digital marketing" in profile
            $query .= " AND (" . implode(' OR ', $allSearchConditions) . ")";
        } else {
            // FLEXIBLE: Any condition matches (for general searches)
            $query .= " AND (" . implode(' OR ', $allSearchConditions) . ")";
        }
    }

    // NOTE: Removed expert_type filter as most experts don't have category set
    // This ensures we don't exclude valid results

    $query .= " GROUP BY u.id, ep.full_name, ep.tagline, ep.bio_short, ep.profile_photo, 
                ep.experience_years, ep.verification_status, ep.rating_average, 
                ep.total_reviews, ep.total_sessions, ep.expertise_verticals, ep.category";

    // Order by relevance (rating and sessions)
    $query .= " ORDER BY ep.rating_average DESC, ep.total_sessions DESC LIMIT 20";

    error_log("Smart Search Query: " . $query);
    error_log("Smart Search Params: " . print_r($params, true));

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $experts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process each expert
    foreach ($experts as &$expert) {
        // Extract skills from expertise_verticals JSON
        $verticals = $expert['expertise_verticals'] ? json_decode($expert['expertise_verticals'], true) : [];
        $expert['skills'] = is_array($verticals) ? array_slice($verticals, 0, 5) : [];
        unset($expert['expertise_verticals']);

        // Format rating
        $expert['avg_rating'] = round((float) $expert['avg_rating'], 1);

        // Set badge based on category
        $expert['badge'] = ucfirst($expert['category'] ?? 'Expert');

        // Format price
        $expert['hourly_rate'] = (float) $expert['hourly_rate'];
    }

    return $experts;
}
