<?php
session_start();
require_once __DIR__ . '/../../apis/connection/pdo.php';
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../apis/connection/trust-helper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
    // Temporary: Set test session for debugging
    $_SESSION['user_id'] = 20; // Test expert ID
    $_SESSION['role'] = 'expert';
    error_log("Session not found in expert API, using test session");
}

$expertId = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Debug logging
error_log("Expert Session Management API called - Expert ID: $expertId, Action: $action");
error_log("POST data: " . print_r($_POST, true));

try {
    switch ($action) {
        case 'update_summary':
            $bookingId = $_POST['booking_id'] ?? null;
            $summary = $_POST['summary'] ?? '';

            error_log("Update summary - Booking ID: $bookingId, Summary length: " . strlen($summary));

            if (!$bookingId) {
                throw new Exception('Booking ID is required');
            }

            // Verify booking belongs to expert and get booking details
            $stmt = $pdo->prepare("
                SELECT b.*, 
                       l.email as learner_email,
                       lp.full_name as learner_full_name,
                       e.email as expert_email,
                       ep.full_name as expert_full_name
                FROM bookings b
                JOIN users l ON b.learner_id = l.id
                LEFT JOIN learner_profiles lp ON l.id = lp.user_id
                JOIN users e ON b.expert_id = e.id
                LEFT JOIN expert_profiles ep ON e.id = ep.user_id
                WHERE b.id = ? AND b.expert_id = ?
            ");
            $stmt->execute([$bookingId, $expertId]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                throw new Exception('Booking not found or unauthorized');
            }

            // Update summary, set status to completed, and set review pending flag
            $stmt = $pdo->prepare("UPDATE bookings SET session_summary = ?, status = 'completed', review_pending = 1, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$summary, $bookingId]);

            // Log trust event
            TrustHelper::logEvent($pdo, 'session_completed', $expertId, $booking['learner_id'], [
                'booking_id' => $bookingId,
                'summary_length' => strlen($summary)
            ]);

            // Send email to learner with AI-generated insights
            try {
                error_log("Attempting to send session summary email for booking ID: " . $bookingId);
                sendSessionSummaryEmail($pdo, $booking, $summary);
                error_log("Session summary email sent successfully for booking ID: " . $bookingId);
            } catch (Exception $e) {
                // Log email error but don't fail the summary update
                error_log("Failed to send session summary email for booking ID " . $bookingId . ": " . $e->getMessage());
            }

            echo json_encode(['success' => true, 'message' => 'Summary updated successfully']);
            break;

        case 'clear_review_flag':
            $bookingId = $_POST['booking_id'] ?? null;

            if (!$bookingId) {
                throw new Exception('Booking ID is required');
            }

            // Clear the review pending flag
            $stmt = $pdo->prepare("UPDATE bookings SET review_pending = 0, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$bookingId]);

            echo json_encode(['success' => true, 'message' => 'Review flag cleared']);
            break;

        case 'enhance_summary_ai':
            $summary = $_POST['summary'] ?? '';

            if (empty($summary)) {
                throw new Exception('Summary text is required');
            }

            // Load environment variables from .env
            require_once __DIR__ . '/../connection/env-loader.php';

            // Check if OPENAI_API_KEY exists - check .env first
            $apiKey = $_ENV['OPENAI_API_KEY'] ?? $_SERVER['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY') ?? null;

            if (!$apiKey) {
                throw new Exception('OpenAI API key not configured. Please add OPENAI_API_KEY to your .env file.');
            }

            // Call OpenAI API
            $ch = curl_init('https://api.openai.com/v1/chat/completions');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $apiKey
                ],
                CURLOPT_POSTFIELDS => json_encode([
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are an expert educational session summarizer. Enhance the given session summary by making it more professional, clear, and comprehensive. Keep the same key points but improve the language, structure, and clarity. Limit to 300 words.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $summary
                        ]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 500
                ])
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                $error = json_decode($response, true);
                throw new Exception('OpenAI API error: ' . ($error['error']['message'] ?? 'Unknown error'));
            }

            $result = json_decode($response, true);
            $enhancedSummary = $result['choices'][0]['message']['content'] ?? '';

            echo json_encode([
                'success' => true,
                'enhanced_summary' => $enhancedSummary
            ]);
            break;

        case 'add_task':
            $bookingId = $_POST['booking_id'] ?? null;
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';

            if (!$bookingId || !$title) {
                throw new Exception('Booking ID and task title are required');
            }

            // Verify booking belongs to expert
            $stmt = $pdo->prepare("SELECT session_tasks FROM bookings WHERE id = ? AND expert_id = ?");
            $stmt->execute([$bookingId, $expertId]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                throw new Exception('Booking not found or unauthorized');
            }

            // Get existing tasks
            $tasks = !empty($booking['session_tasks']) ? json_decode($booking['session_tasks'], true) : [];
            if (!is_array($tasks))
                $tasks = [];

            // Add new task
            $tasks[] = [
                'id' => uniqid(),
                'title' => $title,
                'description' => $description,
                'completed' => false,
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Update database
            $stmt = $pdo->prepare("UPDATE bookings SET session_tasks = ? WHERE id = ?");
            $stmt->execute([json_encode($tasks), $bookingId]);

            echo json_encode(['success' => true, 'tasks' => $tasks]);
            break;

        case 'toggle_task':
            $bookingId = $_POST['booking_id'] ?? null;
            $taskId = $_POST['task_id'] ?? null;

            if (!$bookingId || !$taskId) {
                throw new Exception('Booking ID and task ID are required');
            }

            // Get booking tasks
            $stmt = $pdo->prepare("SELECT session_tasks FROM bookings WHERE id = ? AND expert_id = ?");
            $stmt->execute([$bookingId, $expertId]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                throw new Exception('Booking not found or unauthorized');
            }

            $tasks = !empty($booking['session_tasks']) ? json_decode($booking['session_tasks'], true) : [];

            // Toggle task completion
            foreach ($tasks as &$task) {
                if ($task['id'] === $taskId) {
                    $task['completed'] = !($task['completed'] ?? false);
                    break;
                }
            }

            // Update database
            $stmt = $pdo->prepare("UPDATE bookings SET session_tasks = ? WHERE id = ?");
            $stmt->execute([json_encode($tasks), $bookingId]);

            echo json_encode(['success' => true, 'tasks' => $tasks]);
            break;

        case 'delete_task':
            $bookingId = $_POST['booking_id'] ?? null;
            $taskId = $_POST['task_id'] ?? null;

            if (!$bookingId || !$taskId) {
                throw new Exception('Booking ID and task ID are required');
            }

            // Get booking tasks
            $stmt = $pdo->prepare("SELECT session_tasks FROM bookings WHERE id = ? AND expert_id = ?");
            $stmt->execute([$bookingId, $expertId]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                throw new Exception('Booking not found or unauthorized');
            }

            $tasks = !empty($booking['session_tasks']) ? json_decode($booking['session_tasks'], true) : [];

            // Remove task
            $tasks = array_values(array_filter($tasks, function ($task) use ($taskId) {
                return $task['id'] !== $taskId;
            }));

            // Update database
            $stmt = $pdo->prepare("UPDATE bookings SET session_tasks = ? WHERE id = ?");
            $stmt->execute([json_encode($tasks), $bookingId]);

            echo json_encode(['success' => true, 'tasks' => $tasks]);
            break;

        case 'add_resource':
            error_log("=== ADD RESOURCE START ===");
            error_log("POST data: " . json_encode($_POST));
            error_log("FILES data: " . json_encode($_FILES));

            $bookingId = $_POST['booking_id'] ?? null;
            $title = $_POST['title'] ?? '';
            $url = $_POST['url'] ?? '';
            $type = $_POST['type'] ?? 'link';

            error_log("Parsed - bookingId: $bookingId, title: $title, url: $url, type: $type");

            if (!$bookingId || !$title) {
                error_log("ERROR: Missing required fields");
                throw new Exception('Booking ID and resource title are required');
            }

            // Verify booking belongs to expert
            error_log("Verifying booking for expert_id: $expertId");
            $stmt = $pdo->prepare("SELECT session_resources FROM bookings WHERE id = ? AND expert_id = ?");
            $stmt->execute([$bookingId, $expertId]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                error_log("ERROR: Booking not found - bookingId: $bookingId, expertId: $expertId");
                throw new Exception('Booking not found or unauthorized');
            }

            error_log("Booking found, existing resources: " . ($booking['session_resources'] ?? 'null'));

            // Handle file upload if present
            $filePath = null;
            $fileName = null;

            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                error_log("Processing file upload");
                $uploadDir = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/uploads/session_resources/';
                error_log("Upload directory: $uploadDir");

                // Create directory if it doesn't exist
                if (!is_dir($uploadDir)) {
                    error_log("Creating directory: $uploadDir");
                    mkdir($uploadDir, 0755, true);
                }

                $allowedTypes = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'jpg', 'jpeg', 'png', 'gif', 'zip'];
                $maxSize = 10 * 1024 * 1024; // 10MB

                $fileInfo = pathinfo($_FILES['file']['name']);
                $extension = strtolower($fileInfo['extension']);

                if (!in_array($extension, $allowedTypes)) {
                    throw new Exception('Invalid file type. Allowed: ' . implode(', ', $allowedTypes));
                }

                if ($_FILES['file']['size'] > $maxSize) {
                    throw new Exception('File too large. Maximum size: 10MB');
                }

                // Generate unique filename
                $fileName = $_FILES['file']['name'];
                $uniqueName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
                $filePath = '/uploads/session_resources/' . $uniqueName;

                error_log("Moving file from " . $_FILES['file']['tmp_name'] . " to " . $uploadDir . $uniqueName);

                if (!move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $uniqueName)) {
                    error_log("ERROR: Failed to move uploaded file");
                    throw new Exception('Failed to upload file');
                }

                error_log("File uploaded successfully: $filePath");
                $type = 'file';
                $url = $filePath; // Store relative path
            } else if (isset($_FILES['file'])) {
                error_log("File upload error: " . $_FILES['file']['error']);
            }

            // Get existing resources
            $resources = !empty($booking['session_resources']) ? json_decode($booking['session_resources'], true) : [];
            if (!is_array($resources))
                $resources = [];

            error_log("Existing resources count: " . count($resources));

            // Add new resource
            $newResource = [
                'id' => uniqid(),
                'title' => $title,
                'url' => $url,
                'type' => $type,
                'filename' => $fileName,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $resources[] = $newResource;

            error_log("New resource added: " . json_encode($newResource));
            error_log("Total resources now: " . count($resources));

            // Update database
            $stmt = $pdo->prepare("UPDATE bookings SET session_resources = ? WHERE id = ?");
            $success = $stmt->execute([json_encode($resources), $bookingId]);

            error_log("Database update result: " . ($success ? 'success' : 'failed'));
            error_log("=== ADD RESOURCE END ===");

            echo json_encode(['success' => true, 'resources' => $resources]);
            break;

        case 'delete_resource':
            $bookingId = $_POST['booking_id'] ?? null;
            $resourceId = $_POST['resource_id'] ?? null;

            if (!$bookingId || !$resourceId) {
                throw new Exception('Booking ID and resource ID are required');
            }

            // Get booking resources
            $stmt = $pdo->prepare("SELECT session_resources FROM bookings WHERE id = ? AND expert_id = ?");
            $stmt->execute([$bookingId, $expertId]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                throw new Exception('Booking not found or unauthorized');
            }

            $resources = !empty($booking['session_resources']) ? json_decode($booking['session_resources'], true) : [];

            // Find and delete file if it exists
            $resourceToDelete = null;
            foreach ($resources as $resource) {
                if ($resource['id'] === $resourceId) {
                    $resourceToDelete = $resource;
                    break;
                }
            }

            if ($resourceToDelete && $resourceToDelete['type'] === 'file' && !empty($resourceToDelete['url'])) {
                $filePath = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . $resourceToDelete['url'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // Remove resource from array
            $resources = array_values(array_filter($resources, function ($resource) use ($resourceId) {
                return $resource['id'] !== $resourceId;
            }));

            // Update database
            $stmt = $pdo->prepare("UPDATE bookings SET session_resources = ? WHERE id = ?");
            $stmt->execute([json_encode($resources), $bookingId]);

            echo json_encode(['success' => true, 'resources' => $resources]);
            break;

        case 'edit_resource':
            $bookingId = $_POST['booking_id'] ?? null;
            $resourceId = $_POST['resource_id'] ?? null;
            $title = $_POST['title'] ?? '';
            $url = $_POST['url'] ?? '';

            if (!$bookingId || !$resourceId || !$title) {
                throw new Exception('Booking ID, resource ID, and title are required');
            }

            // Verify booking belongs to expert
            $stmt = $pdo->prepare("SELECT session_resources FROM bookings WHERE id = ? AND expert_id = ?");
            $stmt->execute([$bookingId, $expertId]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                throw new Exception('Booking not found or unauthorized');
            }

            $resources = !empty($booking['session_resources']) ? json_decode($booking['session_resources'], true) : [];

            // Find and update resource
            $resourceFound = false;
            foreach ($resources as &$resource) {
                if ($resource['id'] === $resourceId) {
                    $resourceFound = true;
                    $oldType = $resource['type'] ?? 'link';

                    // Handle file upload replacement
                    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/uploads/session_resources/';

                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }

                        $allowedTypes = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'jpg', 'jpeg', 'png', 'gif', 'zip'];
                        $maxSize = 10 * 1024 * 1024;

                        $fileInfo = pathinfo($_FILES['file']['name']);
                        $extension = strtolower($fileInfo['extension']);

                        if (!in_array($extension, $allowedTypes)) {
                            throw new Exception('Invalid file type');
                        }

                        if ($_FILES['file']['size'] > $maxSize) {
                            throw new Exception('File too large. Maximum size: 10MB');
                        }

                        // Delete old file if it was a file type
                        if ($oldType === 'file' && !empty($resource['url'])) {
                            $oldFilePath = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . $resource['url'];
                            if (file_exists($oldFilePath)) {
                                unlink($oldFilePath);
                            }
                        }

                        // Upload new file
                        $fileName = $_FILES['file']['name'];
                        $uniqueName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
                        $filePath = '/uploads/session_resources/' . $uniqueName;

                        if (!move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $uniqueName)) {
                            throw new Exception('Failed to upload file');
                        }

                        $resource['type'] = 'file';
                        $resource['url'] = $filePath;
                        $resource['filename'] = $fileName;
                    } else {
                        // Just updating title/url for link type
                        if ($oldType === 'link' || empty($_POST['keep_current_file'])) {
                            $resource['url'] = $url;
                        }
                    }

                    $resource['title'] = $title;
                    break;
                }
            }

            if (!$resourceFound) {
                throw new Exception('Resource not found');
            }

            // Update database
            $stmt = $pdo->prepare("UPDATE bookings SET session_resources = ? WHERE id = ?");
            $stmt->execute([json_encode($resources), $bookingId]);

            echo json_encode(['success' => true, 'resources' => $resources]);
            break;

        case 'upscale_booking':
            $bookingId = $_POST['booking_id'] ?? null;
            $programId = $_POST['program_id'] ?? null;

            if (!$bookingId || !$programId) {
                throw new Exception('Booking ID and Program ID are required');
            }

            // Verify booking belongs to expert
            $stmt = $pdo->prepare("SELECT id, learner_id FROM bookings WHERE id = ? AND expert_id = ?");
            $stmt->execute([$bookingId, $expertId]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                throw new Exception('Booking not found or unauthorized');
            }

            // Verify program belongs to expert
            $stmt = $pdo->prepare("SELECT id FROM workflows WHERE id = ? AND expert_id = ? AND is_active = 1");
            $stmt->execute([$programId, $expertId]);
            if (!$stmt->fetch()) {
                throw new Exception('Program not found or unauthorized');
            }

            // Update booking with upscale program
            $stmt = $pdo->prepare("UPDATE bookings SET upsell_workflow_id = ? WHERE id = ?");
            $stmt->execute([$programId, $bookingId]);

            // Create learner progress entry (including expert_id for foreign key constraint)
            $stmt = $pdo->prepare("
                INSERT INTO learner_progress (learner_id, workflow_id, expert_id, progress_percentage, created_at)
                VALUES (?, ?, ?, 0, NOW())
                ON DUPLICATE KEY UPDATE workflow_id = workflow_id
            ");
            $stmt->execute([$booking['learner_id'], $programId, $expertId]);

            echo json_encode(['success' => true, 'message' => 'Session upscaled to program successfully']);
            break;

        case 'generate_learner_insights':
            $bookingId = $_POST['booking_id'] ?? null;

            if (!$bookingId) {
                throw new Exception('Booking ID is required');
            }

            // Get booking with learner profile data
            $stmt = $pdo->prepare("
                SELECT 
                    b.session_datetime,
                    b.duration_minutes,
                    lp.full_name,
                    lp.goals,
                    lp.challenges,
                    lp.education,
                    lp.profession
                FROM bookings b
                JOIN users u ON b.learner_id = u.id
                JOIN learner_profiles lp ON u.id = lp.user_id
                WHERE b.id = ? AND b.expert_id = ?
            ");
            $stmt->execute([$bookingId, $expertId]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                throw new Exception('Booking not found or unauthorized');
            }

            // Check if learner has provided profile data
            if (
                empty($booking['goals']) && empty($booking['challenges']) &&
                empty($booking['education']) && empty($booking['profession'])
            ) {
                throw new Exception('Learner has not provided profile information yet');
            }

            // Load environment variables
            require_once __DIR__ . '/../connection/env-loader.php';

            // Check if OPENAI_API_KEY exists
            $apiKey = $_ENV['OPENAI_API_KEY'] ?? $_SERVER['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY') ?? null;

            if (!$apiKey) {
                throw new Exception('OpenAI API key not configured. Please add OPENAI_API_KEY to your .env file.');
            }

            // Build learner profile summary
            $profileSummary = "Learner: " . $booking['full_name'] . "\n\n";
            if (!empty($booking['profession'])) {
                $profileSummary .= "Profession: " . $booking['profession'] . "\n";
            }
            if (!empty($booking['education'])) {
                $profileSummary .= "Education: " . $booking['education'] . "\n";
            }
            if (!empty($booking['goals'])) {
                $profileSummary .= "\nGoals:\n" . $booking['goals'] . "\n";
            }
            if (!empty($booking['challenges'])) {
                $profileSummary .= "\nChallenges:\n" . $booking['challenges'] . "\n";
            }

            // Create AI prompt
            $prompt = "Based on the following learner profile, provide expert coaching insights for an upcoming {$booking['duration_minutes']}-minute session:\n\n{$profileSummary}\n\nPlease provide:\n1. Learner Overview (2-3 sentences summarizing the learner's background and current situation)\n2. Session Goals Summary (2-3 sentences about specific goals for this session)\n3. Recommended Approach (provide exactly 4-5 SHORT bullet points, each point should be 5-10 words only, start each with a dash -)\n\nFormat your response as JSON with keys: overview, session_goals, recommended_approach";

            // Call OpenAI API
            $ch = curl_init('https://api.openai.com/v1/chat/completions');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $apiKey
                ],
                CURLOPT_POSTFIELDS => json_encode([
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are an expert educational coach assistant helping experts prepare for coaching sessions. Provide concise, actionable insights.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 800
                ])
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                throw new Exception('OpenAI API error: ' . $response);
            }

            $result = json_decode($response, true);
            $aiResponse = $result['choices'][0]['message']['content'] ?? '';

            // Try to parse JSON response
            $aiResponse = trim($aiResponse);
            if (strpos($aiResponse, '{') !== false) {
                $jsonStart = strpos($aiResponse, '{');
                $jsonEnd = strrpos($aiResponse, '}') + 1;
                $jsonStr = substr($aiResponse, $jsonStart, $jsonEnd - $jsonStart);
                $insights = json_decode($jsonStr, true);
            } else {
                $insights = null;
            }

            // Fallback if JSON parsing fails
            if (!$insights || !isset($insights['overview'])) {
                $insights = [
                    'overview' => $aiResponse,
                    'session_goals' => 'Focus on addressing the learner\'s stated goals and challenges.',
                    'recommended_approach' => 'Adapt your teaching style based on the learner\'s background and needs.'
                ];
            }

            // Save insights to database for future use
            $insightsJson = json_encode($insights);
            $stmt = $pdo->prepare("UPDATE bookings SET ai_insights = ? WHERE id = ?");
            $stmt->execute([$insightsJson, $bookingId]);

            echo json_encode([
                'success' => true,
                'insights' => $insights
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    error_log("=== EXCEPTION CAUGHT ===");
    error_log("Action: " . ($action ?? 'unknown'));
    error_log("Error: " . $e->getMessage());
    error_log("File: " . $e->getFile() . " Line: " . $e->getLine());
    error_log("Stack trace: " . $e->getTraceAsString());

    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// Function to send session summary email to learner with AI insights
function sendSessionSummaryEmail($pdo, $booking, $summary)
{
    // Load EmailHelper
    require_once __DIR__ . '/../connection/email-helper.php';

    // Load environment variables
    $envFile = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0)
                continue;
            if (strpos($line, '=') === false)
                continue;
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!empty($key)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }

    $apiKey = $_ENV['OPENAI_API_KEY'] ?? $_SERVER['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY') ?? null;

    if (!$apiKey) {
        throw new Exception('OpenAI API key not configured');
    }

    $expertName = $booking['expert_full_name'] ?? $booking['expert_email'] ?? 'Your Expert';
    $learnerName = $booking['learner_full_name'] ?? $booking['learner_email'] ?? 'there';
    $sessionTopic = $booking['session_topic'] ?? 'General Session';
    $duration = $booking['duration_minutes'] ?? 60;
    $sessionDate = date('F j, Y', strtotime($booking['session_datetime']));
    $status = ucfirst($booking['status'] ?? 'completed');

    // Get actual resources uploaded by expert
    $expertResources = [];
    if (!empty($booking['session_resources'])) {
        $resourcesData = json_decode($booking['session_resources'], true);
        if (is_array($resourcesData)) {
            $expertResources = $resourcesData;
        }
    }

    // Generate AI insights from the session summary
    $prompt = "Based on this session summary, create a structured email content for the learner.\n\n";
    $prompt .= "Session Summary:\n$summary\n\n";
    $prompt .= "Generate the following in a clear, professional format:\n\n";
    $prompt .= "1. **Key Insights** (2-3 main takeaways from the session)\n";
    $prompt .= "2. **Roadmap** (Brief learning path or next steps - 3-4 points)\n";
    $prompt .= "3. **Action Items** (Specific tasks the learner should complete - 3-5 items)\n";
    $prompt .= "4. **Next Milestone** (What achievement to aim for next)\n";
    $prompt .= "5. **Suggested Next Session** (Topic/focus for next meeting)\n";
    $prompt .= "6. **Resources** (2-3 helpful resources, can be generic like 'Documentation', 'Video tutorials', 'Practice exercises')\n\n";
    $prompt .= "Format as JSON with keys: key_insights (array), roadmap (array), action_items (array), next_milestone (string), next_session (string), resources (array)";

    // Call OpenAI API
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert educational coach assistant. Create structured, actionable insights from session summaries.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 1000
        ])
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception('Failed to generate AI insights for email');
    }

    $result = json_decode($response, true);
    $aiResponse = $result['choices'][0]['message']['content'] ?? '';

    // Parse JSON from AI response
    preg_match('/\{[\s\S]*\}/', $aiResponse, $matches);
    $insights = null;
    if (!empty($matches)) {
        $insights = json_decode($matches[0], true);
    }

    // Fallback if parsing fails
    if (!$insights) {
        $insights = [
            'key_insights' => ['Session completed successfully', 'Progress discussed', 'Next steps identified'],
            'roadmap' => ['Continue learning', 'Practice regularly', 'Apply concepts'],
            'action_items' => ['Review session notes', 'Complete assigned tasks', 'Prepare for next session'],
            'next_milestone' => 'Achieve proficiency in discussed topics',
            'next_session' => 'Advanced concepts and Q&A',
            'resources' => ['Documentation', 'Video tutorials', 'Practice exercises']
        ];
    }

    // Create email HTML
    $viewSessionUrl = BASE_PATH . '/index.php?panel=learner&page=booking-details&booking_id=' . $booking['id'];

    $emailHTML = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
            .session-info { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #667eea; }
            .section { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
            .section-title { color: #667eea; font-size: 18px; font-weight: bold; margin-bottom: 15px; }
            .list-item { padding: 8px 0; border-bottom: 1px solid #e9ecef; }
            .list-item:last-child { border-bottom: none; }
            .cta-button { display: inline-block; background: #667eea; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 20px 0; font-weight: bold; }
            .footer { text-align: center; padding: 20px; color: #6c757d; font-size: 14px; }
            .emoji { font-size: 20px; margin-right: 8px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🎓 Session Summary & Insights</h1>
                <p>AI has generated your personalized insights</p>
            </div>
            
            <div class="content">
                <div class="session-info">
                    <h2 style="margin-top:0; color: #667eea;">Session Details</h2>
                    <p><strong>Call With:</strong> ' . htmlspecialchars($expertName) . '</p>
                    <p><strong>Topic:</strong> ' . htmlspecialchars($sessionTopic) . '</p>
                    <p><strong>Date:</strong> ' . htmlspecialchars($sessionDate) . '</p>
                    <p><strong>Status:</strong> ' . htmlspecialchars($status) . '</p>
                    <p><strong>Duration:</strong> ' . $duration . ' min</p>
                </div>
                
                <div class="section">
                    <div class="section-title"><span class="emoji">🎯</span>Key Insights</div>';

    foreach ($insights['key_insights'] as $insight) {
        $emailHTML .= '<div class="list-item">• ' . htmlspecialchars($insight) . '</div>';
    }

    $emailHTML .= '
                </div>
                
                <div class="section">
                    <div class="section-title"><span class="emoji">📘</span>Roadmap</div>';

    foreach ($insights['roadmap'] as $step) {
        $emailHTML .= '<div class="list-item">→ ' . htmlspecialchars($step) . '</div>';
    }

    $emailHTML .= '
                </div>
                
                <div class="section">
                    <div class="section-title"><span class="emoji">📋</span>Action Items</div>';

    foreach ($insights['action_items'] as $action) {
        $emailHTML .= '<div class="list-item">☐ ' . htmlspecialchars($action) . '</div>';
    }

    $emailHTML .= '
                </div>
                
                <div class="section">
                    <div class="section-title"><span class="emoji">🔥</span>Next Milestone</div>
                    <p>' . htmlspecialchars($insights['next_milestone']) . '</p>
                </div>
                
                <div class="section">
                    <div class="section-title"><span class="emoji">📅</span>Suggested Next Session</div>
                    <p>' . htmlspecialchars($insights['next_session']) . '</p>
                </div>
                
                <div class="section">
                    <div class="section-title"><span class="emoji">🔗</span>Resources</div>';

    // Show expert's uploaded resources if available
    if (count($expertResources) > 0) {
        foreach ($expertResources as $resource) {
            $resourceTitle = htmlspecialchars($resource['title'] ?? 'Resource');
            $resourceUrl = $resource['url'] ?? '';
            $resourceType = $resource['type'] ?? 'link';

            if ($resourceType === 'file' && !empty($resourceUrl)) {
                // For file resources, create download link
                $fullUrl = 'https://' . $_SERVER['HTTP_HOST'] . BASE_PATH . $resourceUrl;
                $emailHTML .= '<div class="list-item">📄 <a href="' . htmlspecialchars($fullUrl) . '" style="color: #667eea; text-decoration: none;">' . $resourceTitle . '</a> <span style="font-size: 12px; color: #6c757d;">(Download)</span></div>';
            } elseif (!empty($resourceUrl)) {
                // For URL resources, create link
                $emailHTML .= '<div class="list-item">� <a href="' . htmlspecialchars($resourceUrl) . '" style="color: #667eea; text-decoration: none;" target="_blank">' . $resourceTitle . '</a></div>';
            } else {
                $emailHTML .= '<div class="list-item">📚 ' . $resourceTitle . '</div>';
            }
        }
    } else {
        // Show message if no resources uploaded by expert
        $emailHTML .= '<div class="list-item" style="color: #6c757d; font-style: italic;">No resources uploaded by expert for this session.</div>';
    }

    $emailHTML .= '
                </div>
                
                <div style="text-align: center;">
                    <a href="' . htmlspecialchars($viewSessionUrl) . '" class="cta-button">View Complete Session Details</a>
                </div>
                
                <div style="background: #e3f2fd; padding: 15px; border-radius: 8px; margin-top: 20px;">
                    <p style="margin: 0; color: #1976d2;"><strong>💡 Pro Tip:</strong> Review these insights regularly and track your progress. Book your next session to maintain momentum!</p>
                </div>
            </div>
            
            <div class="footer">
                <p>This email was automatically generated by Nexpert.ai</p>
                <p>Keep learning, keep growing! 🚀</p>
            </div>
        </div>
    </body>
    </html>';

    // Send email using PHPMailer
    $learnerEmail = $booking['learner_email'];
    $subject = "🎓 Session Summary: $sessionTopic with $expertName";

    $emailHelper = new EmailHelper();
    $result = $emailHelper->sendEmail($learnerEmail, $subject, $emailHTML, $learnerName);

    if (!$result['success']) {
        error_log("Failed to send session summary email: " . ($result['error'] ?? 'Unknown error'));
        throw new Exception('Failed to send email: ' . ($result['error'] ?? 'Unknown error'));
    }

    error_log("Session summary email sent successfully to: " . $learnerEmail);
}
