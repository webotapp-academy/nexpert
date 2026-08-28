<?php
/**
 * Expert Application Page — Task 4.2
 * Route: index.php?panel=expert&page=apply
 * 3-Step onboarding form for new experts with duplicate email checks and admin alert.
 */
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';
require_once dirname(__DIR__) . '/includes/session-config.php';
require_once dirname(__DIR__) . '/admin-panel/apis/connection/pdo.php';

$success = false;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName   = trim($_POST['full_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $title      = trim($_POST['professional_title'] ?? '');
    $category   = trim($_POST['category'] ?? 'Leadership');
    $experience = (int)($_POST['experience_years'] ?? 0);
    $linkedin   = trim($_POST['linkedin_url'] ?? '');
    $hourlyRate = (float)($_POST['hourly_rate'] ?? 1500);
    $bio        = trim($_POST['bio'] ?? '');

    if (!$fullName || !$email || !$password) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check for duplicate email
        $dupCheck = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $dupCheck->execute([$email]);
        if ($dupCheck->fetch()) {
            $error = 'An account with this email address already exists. Please log in instead.';
        } else {
            try {
                $pdo->beginTransaction();

                // 1. Insert into users table
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $userStmt = $pdo->prepare("
                    INSERT INTO users (email, password, password_hash, role, status, created_at)
                    VALUES (?, ?, ?, 'expert', 'active', NOW())
                ");
                $userStmt->execute([$email, $hashedPassword, $hashedPassword]);
                $userId = (int)$pdo->lastInsertId();

                // Map category to allowed database enum ('coach','mentor','consultant','trainer','freelancer')
                $categoryMap = [
                    'AI & ML' => 'consultant',
                    'Leadership' => 'coach',
                    'Product & Strategy' => 'mentor',
                    'Data Science' => 'consultant',
                    'Career Growth' => 'coach',
                    'Software Engineering' => 'mentor'
                ];
                $dbCategory = $categoryMap[$category] ?? 'mentor';
                $verticalsJson = json_encode([$category]);

                // 2. Insert into expert_profiles table
                $profileStmt = $pdo->prepare("
                    INSERT INTO expert_profiles (
                        user_id, full_name, tagline, category, expertise_verticals, experience_years,
                        bio_short, bio_full, base_price, credentials, timezone, verification_status, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'UTC', 'pending', NOW())
                ");
                $profileStmt->execute([
                    $userId, $fullName, $title, $dbCategory, $verticalsJson, $experience,
                    $bio, $bio, $hourlyRate, $linkedin
                ]);

                // 3. Emit expert_profile_updated trust event
                $eventStmt = $pdo->prepare("
                    INSERT INTO trust_events (event_type, expert_id, payload, status, created_at)
                    VALUES ('expert_profile_updated', ?, ?, 'pending', NOW())
                ");
                $eventStmt->execute([
                    $userId,
                    json_encode(['action' => 'initial_application', 'name' => $fullName, 'category' => $category])
                ]);

                $pdo->commit();

                // Send notification email to admin
                @mail(
                    'admin@nexpertapp.com',
                    "New Expert Application: {$fullName}",
                    "A new expert has applied to join Nexpert.\n\nName: {$fullName}\nEmail: {$email}\nCategory: {$category}\nExperience: {$experience} years\nLinkedIn: {$linkedin}\n\nReview in Admin Panel: https://nexpertapp.com/index.php?panel=admin&page=kyc-verification",
                    "From: noreply@nexpertapp.com"
                );

                $success = true;
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $error = 'Registration failed: ' . $e->getMessage();
            }
        }
    }
}

$page_title = "Apply as an Expert — Nexpert.ai";
$panel_type = "expert";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navigation.php';
?>
<script>document.body.className="bg-[#080B10] min-h-screen text-white antialiased";</script>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center mb-10">
        <div class="inline-flex items-center gap-2 bg-[#00D4AA]/10 border border-[#00D4AA]/25 text-[#00D4AA] px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest mb-4">
            Founding Expert Cohort
        </div>
        <h1 class="text-4xl font-extrabold text-white mb-3">Apply as a Nexpert Practitioner</h1>
        <p class="text-gray-400 text-sm max-w-lg mx-auto">Join a verified network where your credibility is backed by evidence, not superficial ratings.</p>
    </div>

    <?php if ($success): ?>
    <div class="bg-[#0d131f] border border-emerald-900/60 rounded-2xl p-8 text-center shadow-2xl">
        <div class="w-16 h-16 bg-emerald-500/20 text-emerald-400 rounded-full flex items-center justify-center mx-auto mb-4 border border-emerald-500/40">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h2 class="text-2xl font-bold text-white mb-2">Application Received!</h2>
        <p class="text-gray-300 text-sm max-w-md mx-auto leading-relaxed mb-6">
            Thank you for applying. Our verification team reviews applications within 48 hours. Upon approval, your baseline Trust Score will be computed automatically.
        </p>
        <a href="index.php?panel=expert&page=auth" class="bg-[#00D4AA] text-[#080B10] font-bold px-6 py-3 rounded-xl text-sm hover:bg-[#00bda0] transition inline-block">
            Proceed to Expert Login →
        </a>
    </div>
    <?php else: ?>

    <div class="bg-[#0d131f] border border-gray-800 rounded-2xl p-8 shadow-xl">
        <?php if ($error): ?>
        <div class="p-4 bg-rose-950/50 border border-rose-800 text-rose-300 text-xs rounded-xl mb-6 flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <!-- Step 1: Basic Info -->
            <div>
                <h3 class="text-xs font-bold text-[#00D4AA] uppercase tracking-wider mb-4 pb-2 border-b border-gray-800">
                    Step 1 — Personal & Account Information
                </h3>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Full Name *</label>
                        <input type="text" name="full_name" required placeholder="e.g. Priya Sharma" class="w-full bg-[#080B10] border border-gray-800 text-white rounded-xl p-3 text-sm focus:outline-none focus:border-[#00D4AA]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Email Address *</label>
                        <input type="email" name="email" required placeholder="priya@domain.com" class="w-full bg-[#080B10] border border-gray-800 text-white rounded-xl p-3 text-sm focus:outline-none focus:border-[#00D4AA]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Create Password *</label>
                        <input type="password" name="password" required minlength="6" placeholder="••••••••" class="w-full bg-[#080B10] border border-gray-800 text-white rounded-xl p-3 text-sm focus:outline-none focus:border-[#00D4AA]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Professional Title / Headline *</label>
                        <input type="text" name="professional_title" required placeholder="e.g. Staff AI Engineer @ Tech" class="w-full bg-[#080B10] border border-gray-800 text-white rounded-xl p-3 text-sm focus:outline-none focus:border-[#00D4AA]">
                    </div>
                </div>
            </div>

            <!-- Step 2: Credibility Signals -->
            <div>
                <h3 class="text-xs font-bold text-[#00D4AA] uppercase tracking-wider mb-4 pb-2 border-b border-gray-800">
                    Step 2 — Credibility Signals & Domain
                </h3>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Primary Domain Category *</label>
                        <select name="category" class="w-full bg-[#080B10] border border-gray-800 text-white rounded-xl p-3 text-sm focus:outline-none focus:border-[#00D4AA]">
                            <option value="AI & ML">AI & Machine Learning</option>
                            <option value="Leadership">Executive & Tech Leadership</option>
                            <option value="Product & Strategy">Product Management & Strategy</option>
                            <option value="Data Science">Data Science & Analytics</option>
                            <option value="Career Growth">Career Growth & Coaching</option>
                            <option value="Software Engineering">Software Engineering</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Years of Practical Experience *</label>
                        <input type="number" name="experience_years" min="1" max="50" value="5" class="w-full bg-[#080B10] border border-gray-800 text-white rounded-xl p-3 text-sm focus:outline-none focus:border-[#00D4AA]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">LinkedIn Profile URL *</label>
                        <input type="url" name="linkedin_url" required placeholder="https://linkedin.com/in/username" class="w-full bg-[#080B10] border border-gray-800 text-white rounded-xl p-3 text-sm focus:outline-none focus:border-[#00D4AA]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Hourly Consultation Rate (₹) *</label>
                        <input type="number" name="hourly_rate" min="500" step="100" value="2000" class="w-full bg-[#080B10] border border-gray-800 text-white rounded-xl p-3 text-sm focus:outline-none focus:border-[#00D4AA]">
                    </div>
                </div>
            </div>

            <!-- Step 3: Bio & Review -->
            <div>
                <h3 class="text-xs font-bold text-[#00D4AA] uppercase tracking-wider mb-4 pb-2 border-b border-gray-800">
                    Step 3 — Professional Bio & Submission
                </h3>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Short Professional Summary *</label>
                    <textarea name="bio" required rows="3" placeholder="Briefly describe your background, areas of mentorship expertise, and how you help practitioners succeed..." class="w-full bg-[#080B10] border border-gray-800 text-white rounded-xl p-3 text-sm focus:outline-none focus:border-[#00D4AA]"></textarea>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-between">
                <span class="text-xs text-gray-500">Already registered? <a href="index.php?panel=expert&page=auth" class="text-[#00D4AA] hover:underline">Log in</a></span>
                <button type="submit" class="bg-[#00D4AA] hover:bg-[#00bda0] text-[#080B10] font-bold px-8 py-3.5 rounded-xl text-sm transition shadow-lg">
                    Submit Expert Application →
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
