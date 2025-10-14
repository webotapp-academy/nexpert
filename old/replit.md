# Nexpert.ai

## Overview
Nexpert.ai is a global expert learning platform connecting learners with subject matter experts. It features distinct learner and expert panels, facilitating live sessions, structured learning, payment processing, and comprehensive management tools. The platform aims to provide a worldwide reach for knowledge exchange.

## Recent Changes (October 2025)
- **Global Path Configuration (Oct 6)**: Implemented automatic base path detection that works across all environments (Replit, XAMPP, shared hosting). The `includes/config.php` file automatically detects the application root and sets `BASE_PATH` and `BASE_URL` constants. All absolute paths in PHP, HTML, and JavaScript files updated to use these variables, ensuring the project works when exported to XAMPP subdirectories without manual path adjustments. JavaScript files use `BASE_PATH` for all API calls and redirects.
- **My Programs Feature**: Fully functional program management system for experts with database integration. Experts can create structured learning programs with milestones, assignments, and resources. Uses workflows, workflow_steps, and assignments tables. API endpoint at `/admin-panel/apis/expert/programs.php` handles GET/POST/DELETE operations. Real-time stats display total programs, active learners, assignments, and completion rates.
- **Homepage Dynamic Experts**: "Meet Our Top Experts" section now displays real experts from database using AJAX, showing latest 6 experts with profile photos, ratings, skills, and hourly rates
- **Homepage Animations**: Added animated gradient orbs and floating icons to hero section and footer for modern, engaging UI
- **AI-Style Loader**: Browse-experts page features animated AI loader with rotating rings, pulsing center, and orbiting dots during expert loading
- **Learner Panel Implementation**: Complete learner authentication, dashboard, profile, and browse-experts functionality with database integration
- **Timezone Configuration**: Default timezone set to IST (Asia/Kolkata) across entire platform (PHP and MySQL)
- **Security Enhancements**: XSS prevention with HTML escaping, secure session management, login gates on protected pages
- **Expert Registration Flow**: Registration now redirects to settings page for streamlined onboarding
- **Settings Page Database Integration**: All forms (profile, bank details, image uploads) now save data to database with real-time validation
- **Profile Photo Upload**: Secure image upload with server-side MIME validation using finfo

## User Preferences
Preferred communication style: Simple, everyday language.

## System Architecture

### Frontend Architecture
The frontend uses pure HTML/CSS with Tailwind CSS for styling and vanilla JavaScript for interactivity. It employs a multi-panel layout for learners and experts, with query parameter-based routing. The design is mobile-first, utilizing Tailwind's responsive utilities and a custom color scheme (primary blue, accent orange, secondary navy).

### Backend Architecture
The backend is powered by PHP 8.2.23 and connects to a remote Hostinger MySQL database via a secure PDO connection. Database credentials are managed through Replit environment variables. The API structure is organized within the `admin-panel/apis/` directory, featuring distinct endpoints for admin, expert, and learner functionalities.

**Path Configuration**: The project uses an automatic path detection system (`includes/config.php`) that works universally across different hosting environments. It traverses up from the executing script to find the application root (identified by `index.php` and `includes/config.php`), then calculates `BASE_PATH` and `BASE_URL` relative to the document root. This ensures all asset paths, API calls, and redirects work correctly whether the project is in the root directory (Replit) or a subdirectory (XAMPP/htdocs/myproject).

### Authentication & User Management
The platform supports multi-modal authentication including email/password, Google OAuth, and OTP-based login. It implements role-based access with distinct flows for learners and experts, comprehensive expert profile setup with KYC verification, and browser-based session management with CSRF protection and secure cookie configuration.

### Booking & Session Management
Features include an interactive date/time picker for scheduling, real-time expert availability management, and a live session interface for resource sharing and note-taking. The system manages the complete booking lifecycle from request to completion.

### Payment Processing
The platform is designed to integrate with payment gateways like Razorpay, supporting per-session, package, and subscription pricing models. It includes an earnings dashboard for experts and robust transaction management.

### Content & Learning Management
Key features include an AI-assisted workflow builder for structured learning paths, an assignment system for task tracking, resource management for file sharing, and progress tracking for learners.

### Communication & Notifications
The system provides real-time notifications for bookings and updates, direct messaging between users, and a review system for feedback.

## External Dependencies

### Styling & UI Components
- **Tailwind CSS**: For rapid UI development.
- **Chart.js**: For data visualization in dashboards.
- **Heroicons**: For SVG icons.

### Payment Integration
- **Razorpay**: For processing transactions (cards, UPI, net banking, digital wallets).

### Authentication Services
- **Google OAuth**: For simplified user onboarding.
- **OTP Service**: For secure authentication via SMS/Email verification.

### Database
- **Hostinger MySQL**: Remote database for data persistence.

### Future Integration Points
- **Video Calling**: WebRTC or third-party service.
- **Cloud Storage**: For session recordings and shared resources.
- **Email Service**: For transactional emails and notifications.
- **Analytics**: For user behavior and platform performance monitoring.