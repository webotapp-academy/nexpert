<?php
// Define BASE_PATH
$BASE_PATH = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$BASE_PATH = $BASE_PATH ? $BASE_PATH : '/';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . $BASE_PATH . '/index.php?panel=expert&page=auth');
    exit;
}

$page_title = "Profile Setup - Nexpert.ai";
$panel_type = "expert";
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/navigation.php';
?>
    <div class="max-w-4xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Complete Your Expert Profile</h1>
            <p class="text-gray-600">Set up your profile to start connecting with learners worldwide</p>
        </div>

        <!-- Step Indicator -->
        <div class="mb-8">
            <div class="flex items-center justify-center">
                <!-- Step 1 -->
                <div id="step1Indicator" class="flex items-center">
                    <div class="w-10 h-10 bg-accent text-white rounded-full flex items-center justify-center text-sm font-semibold">
                        1
                    </div>
                    <span class="ml-2 text-sm text-accent font-medium hidden sm:inline">Profile Info</span>
                </div>
                <div id="line1" class="w-16 h-1 bg-gray-300 mx-2"></div>
                <!-- Step 2 -->
                <div id="step2Indicator" class="flex items-center">
                    <div class="w-10 h-10 bg-gray-300 text-white rounded-full flex items-center justify-center text-sm font-semibold">
                        2
                    </div>
                    <span class="ml-2 text-sm text-gray-500 font-medium hidden sm:inline">Pricing</span>
                </div>
                <div id="line2" class="w-16 h-1 bg-gray-300 mx-2"></div>
                <!-- Step 3 -->
                <div id="step3Indicator" class="flex items-center">
                    <div class="w-10 h-10 bg-gray-300 text-white rounded-full flex items-center justify-center text-sm font-semibold">
                        3
                    </div>
                    <span class="ml-2 text-sm text-gray-500 font-medium hidden sm:inline">Availability</span>
                </div>
            </div>
        </div>

        <!-- Step Content -->
        <div class="bg-white rounded-lg shadow-lg p-6 md:p-8">
            <!-- Step 1: Profile Information -->
            <div id="step1Content" class="step-content">
                <h2 class="text-2xl font-semibold text-gray-900 mb-6">Profile Information</h2>
                
                <!-- Profile Photo -->
                <div class="flex items-center space-x-6 mb-6">
                    <div id="profilePhotoPreview" class="w-24 h-24 rounded-full bg-gray-200 flex items-center justify-center text-gray-400">
                        <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>
                    <div>
                        <input type="file" id="profilePhotoInput" accept="image/*" class="hidden">
                        <button type="button" id="uploadPhotoBtn" class="bg-accent text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition">
                            Upload Photo
                        </button>
                        <p class="text-gray-600 text-sm mt-2">JPG, PNG up to 5MB</p>
                    </div>
                </div>

                <!-- Basic Info -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                        <input type="text" id="fullName" placeholder="Enter your full name" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Professional Title *</label>
                        <input type="text" id="tagline" placeholder="e.g., Senior UX Designer, Business Coach" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Years of Experience *</label>
                            <select id="experience" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                                <option value="">Select years</option>
                                <option value="2">1-2 years</option>
                                <option value="4">3-5 years</option>
                                <option value="7">5-8 years</option>
                                <option value="9">8-10 years</option>
                                <option value="10">10+ years</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Location *</label>
                            <input type="text" id="location" placeholder="City, Country" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Professional Bio *</label>
                        <textarea rows="4" id="bioText" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent" placeholder="Tell learners about your background, experience, and what makes you unique..."></textarea>
                        <p class="text-gray-500 text-sm mt-1">Minimum 100 characters</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Primary Expert Category *</label>
                        <select id="category" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                            <option value="">Select category</option>
                            <option value="coach">Coach</option>
                            <option value="mentor">Mentor</option>
                            <option value="consultant">Consultant</option>
                            <option value="trainer">Trainer</option>
                            <option value="freelancer">Freelancer</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Expertise Tags *</label>
                        <div id="expertiseTags" class="flex flex-wrap gap-2 mb-3"></div>
                        <input type="text" id="expertiseInput" placeholder="Add expertise tags (press Enter)" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                        <p class="text-gray-500 text-sm mt-1">Add skills, tools, or areas of expertise. Max 10 tags.</p>
                    </div>
                </div>
            </div>

            <!-- Step 2: Pricing Models -->
            <div id="step2Content" class="step-content hidden">
                <h2 class="text-2xl font-semibold text-gray-900 mb-6">Pricing Models</h2>
                
                <div class="space-y-6">
                    <!-- Per Session -->
                    <div class="border border-gray-200 rounded-lg p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="font-semibold text-gray-900">Per Session</h3>
                                <p class="text-gray-600 text-sm">One-time sessions</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="enablePerSession" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-accent/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent"></div>
                            </label>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">60 minutes</label>
                                <div class="flex">
                                    <span class="inline-flex items-center px-3 py-2 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500">₹</span>
                                    <input type="number" id="price60" placeholder="0" class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-accent">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">30 minutes</label>
                                <div class="flex">
                                    <span class="inline-flex items-center px-3 py-2 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500">₹</span>
                                    <input type="number" id="price30" placeholder="0" class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-accent">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Package Deal -->
                    <div class="border border-gray-200 rounded-lg p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="font-semibold text-gray-900">Package Deal</h3>
                                <p class="text-gray-600 text-sm">Multiple sessions with discount</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="enablePackage" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-accent/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent"></div>
                            </label>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">4 Sessions</label>
                                <div class="flex">
                                    <span class="inline-flex items-center px-3 py-2 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500">₹</span>
                                    <input type="number" id="package4" placeholder="0" class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-accent">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">8 Sessions</label>
                                <div class="flex">
                                    <span class="inline-flex items-center px-3 py-2 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500">₹</span>
                                    <input type="number" id="package8" placeholder="0" class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-accent">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">12 Sessions</label>
                                <div class="flex">
                                    <span class="inline-flex items-center px-3 py-2 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500">₹</span>
                                    <input type="number" id="package12" placeholder="0" class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-accent">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subscription -->
                    <div class="border border-gray-200 rounded-lg p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="font-semibold text-gray-900">Monthly Subscription</h3>
                                <p class="text-gray-600 text-sm">Ongoing mentorship</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="enableSubscription" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-accent/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent"></div>
                            </label>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Price</label>
                            <div class="flex">
                                <span class="inline-flex items-center px-3 py-2 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500">₹</span>
                                <input type="number" id="subscriptionPrice" placeholder="0" class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-accent">
                                <span class="inline-flex items-center px-3 py-2 rounded-r-lg border border-l-0 border-gray-300 bg-gray-50 text-gray-500">/month</span>
                            </div>
                            <p class="text-gray-500 text-sm mt-1">Includes 4 sessions + unlimited chat support</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Availability -->
            <div id="step3Content" class="step-content hidden">
                <h2 class="text-2xl font-semibold text-gray-900 mb-6">Availability Schedule</h2>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                    <select id="timezone" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                        <option>UTC (Coordinated Universal Time)</option>
                        <option>EST (Eastern Standard Time)</option>
                        <option>PST (Pacific Standard Time)</option>
                        <option>IST (India Standard Time)</option>
                        <option>GMT (Greenwich Mean Time)</option>
                    </select>
                </div>

                <div class="space-y-3">
                    <!-- Monday -->
                    <div class="flex items-center justify-between py-3 border-b border-gray-100">
                        <div class="flex items-center">
                            <input type="checkbox" id="monday" class="h-4 w-4 text-accent focus:ring-accent border-gray-300 rounded mr-3">
                            <label for="monday" class="font-medium text-gray-700">Monday</label>
                        </div>
                        <div class="flex items-center space-x-2">
                            <select id="mondayStart" class="px-3 py-1 border border-gray-300 rounded text-sm">
                                <option>9:00 AM</option>
                                <option>10:00 AM</option>
                                <option>11:00 AM</option>
                                <option>12:00 PM</option>
                                <option>1:00 PM</option>
                                <option>2:00 PM</option>
                                <option>3:00 PM</option>
                                <option>4:00 PM</option>
                                <option>5:00 PM</option>
                            </select>
                            <span class="text-gray-500">to</span>
                            <select id="mondayEnd" class="px-3 py-1 border border-gray-300 rounded text-sm">
                                <option>2:00 PM</option>
                                <option>3:00 PM</option>
                                <option>4:00 PM</option>
                                <option>5:00 PM</option>
                                <option>6:00 PM</option>
                                <option>7:00 PM</option>
                                <option>8:00 PM</option>
                                <option>9:00 PM</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tuesday -->
                    <div class="flex items-center justify-between py-3 border-b border-gray-100">
                        <div class="flex items-center">
                            <input type="checkbox" id="tuesday" class="h-4 w-4 text-accent focus:ring-accent border-gray-300 rounded mr-3">
                            <label for="tuesday" class="font-medium text-gray-700">Tuesday</label>
                        </div>
                        <div class="flex items-center space-x-2">
                            <select id="tuesdayStart" class="px-3 py-1 border border-gray-300 rounded text-sm">
                                <option>9:00 AM</option>
                                <option>10:00 AM</option>
                                <option>11:00 AM</option>
                                <option>12:00 PM</option>
                                <option>1:00 PM</option>
                                <option>2:00 PM</option>
                                <option>3:00 PM</option>
                                <option>4:00 PM</option>
                                <option>5:00 PM</option>
                            </select>
                            <span class="text-gray-500">to</span>
                            <select id="tuesdayEnd" class="px-3 py-1 border border-gray-300 rounded text-sm">
                                <option>2:00 PM</option>
                                <option>3:00 PM</option>
                                <option>4:00 PM</option>
                                <option>5:00 PM</option>
                                <option>6:00 PM</option>
                                <option>7:00 PM</option>
                                <option>8:00 PM</option>
                                <option>9:00 PM</option>
                            </select>
                        </div>
                    </div>

                    <!-- Wednesday -->
                    <div class="flex items-center justify-between py-3 border-b border-gray-100">
                        <div class="flex items-center">
                            <input type="checkbox" id="wednesday" class="h-4 w-4 text-accent focus:ring-accent border-gray-300 rounded mr-3">
                            <label for="wednesday" class="font-medium text-gray-700">Wednesday</label>
                        </div>
                        <div class="flex items-center space-x-2">
                            <select id="wednesdayStart" class="px-3 py-1 border border-gray-300 rounded text-sm">
                                <option>9:00 AM</option>
                                <option>10:00 AM</option>
                                <option>11:00 AM</option>
                                <option>12:00 PM</option>
                                <option>1:00 PM</option>
                                <option>2:00 PM</option>
                                <option>3:00 PM</option>
                                <option>4:00 PM</option>
                                <option>5:00 PM</option>
                            </select>
                            <span class="text-gray-500">to</span>
                            <select id="wednesdayEnd" class="px-3 py-1 border border-gray-300 rounded text-sm">
                                <option>2:00 PM</option>
                                <option>3:00 PM</option>
                                <option>4:00 PM</option>
                                <option>5:00 PM</option>
                                <option>6:00 PM</option>
                                <option>7:00 PM</option>
                                <option>8:00 PM</option>
                                <option>9:00 PM</option>
                            </select>
                        </div>
                    </div>

                    <!-- Thursday -->
                    <div class="flex items-center justify-between py-3 border-b border-gray-100">
                        <div class="flex items-center">
                            <input type="checkbox" id="thursday" class="h-4 w-4 text-accent focus:ring-accent border-gray-300 rounded mr-3">
                            <label for="thursday" class="font-medium text-gray-700">Thursday</label>
                        </div>
                        <div class="flex items-center space-x-2">
                            <select id="thursdayStart" class="px-3 py-1 border border-gray-300 rounded text-sm">
                                <option>9:00 AM</option>
                                <option>10:00 AM</option>
                                <option>11:00 AM</option>
                                <option>12:00 PM</option>
                                <option>1:00 PM</option>
                                <option>2:00 PM</option>
                                <option>3:00 PM</option>
                                <option>4:00 PM</option>
                                <option>5:00 PM</option>
                            </select>
                            <span class="text-gray-500">to</span>
                            <select id="thursdayEnd" class="px-3 py-1 border border-gray-300 rounded text-sm">
                                <option>2:00 PM</option>
                                <option>3:00 PM</option>
                                <option>4:00 PM</option>
                                <option>5:00 PM</option>
                                <option>6:00 PM</option>
                                <option>7:00 PM</option>
                                <option>8:00 PM</option>
                                <option>9:00 PM</option>
                            </select>
                        </div>
                    </div>

                    <!-- Friday -->
                    <div class="flex items-center justify-between py-3 border-b border-gray-100">
                        <div class="flex items-center">
                            <input type="checkbox" id="friday" class="h-4 w-4 text-accent focus:ring-accent border-gray-300 rounded mr-3">
                            <label for="friday" class="font-medium text-gray-700">Friday</label>
                        </div>
                        <div class="flex items-center space-x-2">
                            <select id="fridayStart" class="px-3 py-1 border border-gray-300 rounded text-sm">
                                <option>9:00 AM</option>
                                <option>10:00 AM</option>
                                <option>11:00 AM</option>
                                <option>12:00 PM</option>
                                <option>1:00 PM</option>
                                <option>2:00 PM</option>
                                <option>3:00 PM</option>
                                <option>4:00 PM</option>
                                <option>5:00 PM</option>
                            </select>
                            <span class="text-gray-500">to</span>
                            <select id="fridayEnd" class="px-3 py-1 border border-gray-300 rounded text-sm">
                                <option>2:00 PM</option>
                                <option>3:00 PM</option>
                                <option>4:00 PM</option>
                                <option>5:00 PM</option>
                                <option>6:00 PM</option>
                                <option>7:00 PM</option>
                                <option>8:00 PM</option>
                                <option>9:00 PM</option>
                            </select>
                        </div>
                    </div>

                    <!-- Saturday -->
                    <div class="flex items-center justify-between py-3 border-b border-gray-100">
                        <div class="flex items-center">
                            <input type="checkbox" id="saturday" class="h-4 w-4 text-accent focus:ring-accent border-gray-300 rounded mr-3">
                            <label for="saturday" class="font-medium text-gray-700">Saturday</label>
                        </div>
                        <div class="flex items-center space-x-2">
                            <select id="saturdayStart" class="px-3 py-1 border border-gray-300 rounded text-sm">
                                <option>9:00 AM</option>
                                <option>10:00 AM</option>
                                <option>11:00 AM</option>
                                <option>12:00 PM</option>
                                <option>1:00 PM</option>
                                <option>2:00 PM</option>
                                <option>3:00 PM</option>
                                <option>4:00 PM</option>
                                <option>5:00 PM</option>
                            </select>
                            <span class="text-gray-500">to</span>
                            <select id="saturdayEnd" class="px-3 py-1 border border-gray-300 rounded text-sm">
                                <option>2:00 PM</option>
                                <option>3:00 PM</option>
                                <option>4:00 PM</option>
                                <option>5:00 PM</option>
                                <option>6:00 PM</option>
                                <option>7:00 PM</option>
                                <option>8:00 PM</option>
                                <option>9:00 PM</option>
                            </select>
                        </div>
                    </div>

                    <!-- Sunday -->
                    <div class="flex items-center justify-between py-3">
                        <div class="flex items-center">
                            <input type="checkbox" id="sunday" class="h-4 w-4 text-accent focus:ring-accent border-gray-300 rounded mr-3">
                            <label for="sunday" class="font-medium text-gray-700">Sunday</label>
                        </div>
                        <div class="flex items-center space-x-2">
                            <select id="sundayStart" class="px-3 py-1 border border-gray-300 rounded text-sm">
                                <option>9:00 AM</option>
                                <option>10:00 AM</option>
                                <option>11:00 AM</option>
                                <option>12:00 PM</option>
                                <option>1:00 PM</option>
                                <option>2:00 PM</option>
                                <option>3:00 PM</option>
                                <option>4:00 PM</option>
                                <option>5:00 PM</option>
                            </select>
                            <span class="text-gray-500">to</span>
                            <select id="sundayEnd" class="px-3 py-1 border border-gray-300 rounded text-sm">
                                <option>2:00 PM</option>
                                <option>3:00 PM</option>
                                <option>4:00 PM</option>
                                <option>5:00 PM</option>
                                <option>6:00 PM</option>
                                <option>7:00 PM</option>
                                <option>8:00 PM</option>
                                <option>9:00 PM</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between mt-8 pt-6 border-t border-gray-200">
                <button id="prevBtn" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    Previous
                </button>
                <button id="nextBtn" class="px-6 py-3 bg-accent text-white rounded-lg hover:bg-yellow-600 transition">
                    Next Step
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 3;
        const expertiseTags = [];

        // Load basic info from registration
        const basicInfo = JSON.parse(sessionStorage.getItem('expertBasicInfo') || '{}');
        if (basicInfo.name) {
            document.getElementById('fullName').value = basicInfo.name;
        }

        // Step Navigation
        function showStep(step) {
            // Hide all steps
            document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
            
            // Show current step
            document.getElementById(`step${step}Content`).classList.remove('hidden');
            
            // Update indicators
            for (let i = 1; i <= totalSteps; i++) {
                const indicator = document.getElementById(`step${i}Indicator`).querySelector('div');
                const label = document.getElementById(`step${i}Indicator`).querySelector('span');
                const line = document.getElementById(`line${i}`);
                
                if (i < step) {
                    indicator.className = 'w-10 h-10 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-semibold';
                    indicator.textContent = '✓';
                    label.className = 'ml-2 text-sm text-green-600 font-medium hidden sm:inline';
                    if (line) line.className = 'w-16 h-1 bg-green-500 mx-2';
                } else if (i === step) {
                    indicator.className = 'w-10 h-10 bg-accent text-white rounded-full flex items-center justify-center text-sm font-semibold';
                    indicator.textContent = i;
                    label.className = 'ml-2 text-sm text-accent font-medium hidden sm:inline';
                } else {
                    indicator.className = 'w-10 h-10 bg-gray-300 text-white rounded-full flex items-center justify-center text-sm font-semibold';
                    indicator.textContent = i;
                    label.className = 'ml-2 text-sm text-gray-500 font-medium hidden sm:inline';
                    if (line) line.className = 'w-16 h-1 bg-gray-300 mx-2';
                }
            }
            
            // Update buttons
            document.getElementById('prevBtn').disabled = step === 1;
            document.getElementById('nextBtn').textContent = step === totalSteps ? 'Complete Profile' : 'Next Step';
        }

        // Previous Button
        document.getElementById('prevBtn').addEventListener('click', () => {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        });

        // Next Button
        document.getElementById('nextBtn').addEventListener('click', () => {
            if (currentStep < totalSteps) {
                currentStep++;
                showStep(currentStep);
            } else {
                // Save profile (placeholder)
                alert('Profile setup complete! Redirecting to dashboard...');
                window.location.href = `${window.BASE_PATH}/index.php?panel=expert&page=dashboard`;
            }
        });

        // Profile Photo Upload
        document.getElementById('uploadPhotoBtn').addEventListener('click', () => {
            document.getElementById('profilePhotoInput').click();
        });

        document.getElementById('profilePhotoInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('profilePhotoPreview');
                    preview.innerHTML = `<img src="${e.target.result}" class="w-24 h-24 rounded-full object-cover">`;
                };
                reader.readAsDataURL(file);
            }
        });

        // Expertise Tags
        document.getElementById('expertiseInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const tag = this.value.trim();
                if (tag && expertiseTags.length < 10 && !expertiseTags.includes(tag)) {
                    expertiseTags.push(tag);
                    updateExpertiseTags();
                    this.value = '';
                }
            }
        });

        function updateExpertiseTags() {
            const container = document.getElementById('expertiseTags');
            container.innerHTML = expertiseTags.map(tag => `
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-accent/10 text-accent">
                    ${tag}
                    <button onclick="removeTag('${tag}')" class="ml-2 focus:outline-none">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </span>
            `).join('');
        }

        function removeTag(tag) {
            const index = expertiseTags.indexOf(tag);
            if (index > -1) {
                expertiseTags.splice(index, 1);
                updateExpertiseTags();
            }
        }

        // Initialize
        showStep(1);
    </script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>
