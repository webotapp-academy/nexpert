# Nexpert.ai

## Overview

Nexpert.ai is a global expert learning platform that connects learners with subject matter experts across various domains. The platform features two distinct user panels - a learner panel for students seeking knowledge and an expert panel for professionals offering their expertise. The system facilitates live sessions, structured learning workflows, payment processing, and comprehensive management tools for both user types.

## Recent Changes (September 25, 2025)

### Homepage Enhancement
- Updated from India-specific branding to international/global design
- Added professional stock images in hero section showcasing team collaboration, 1-on-1 mentoring, and skill development  
- Changed pricing from INR (Indian Rupees) to USD for international market appeal
- Enhanced trust indicators with improved statistics and professional styling
- All content now emphasizes "global" and "worldwide" reach instead of India-specific messaging

### Authentication System
- Added dummy login functionality for both learner and expert portals
- Demo credentials: learner@demo.com/demo123 redirects to learner dashboard
- Demo credentials: expert@demo.com/demo123 redirects to expert profile setup
- Implemented JavaScript-based authentication with proper form validation and user feedback
- Pre-filled demo credentials for easy testing with visible credential hints

## User Preferences

Preferred communication style: Simple, everyday language.

## System Architecture

### Frontend Architecture
- **Technology Stack**: Pure HTML/CSS with Tailwind CSS for styling and vanilla JavaScript for interactivity
- **Layout Pattern**: Multi-panel architecture with separate interfaces for learners and experts
- **Navigation**: Query parameter-based routing system (`?panel=learner&page=dashboard`) for seamless panel switching
- **Responsive Design**: Mobile-first approach using Tailwind's responsive utilities
- **UI Framework**: Tailwind CSS with custom color scheme (primary blue, accent orange, secondary navy)

### Authentication & User Management
- **Multi-Modal Authentication**: Email/password, Google OAuth, and OTP-based login for both user types
- **Role-Based Access**: Distinct authentication flows and dashboards for learners vs experts
- **Profile Management**: Comprehensive profile setup with KYC verification for experts
- **Session Management**: Browser-based session handling with demo credential support

### Booking & Session Management
- **Calendar Integration**: Interactive date/time picker for session scheduling
- **Real-time Availability**: Expert availability management with time slot booking
- **Session Execution**: Live session interface with resource sharing, note-taking, and assignment creation
- **Booking Lifecycle**: Complete flow from booking request to session completion with rescheduling capabilities

### Payment Processing
- **Payment Gateway**: Razorpay integration placeholder for Indian market payment processing
- **Pricing Models**: Support for per-session, package, and subscription-based pricing
- **Earnings Dashboard**: Revenue tracking, payout history, and financial analytics for experts
- **Transaction Management**: Checkout flow with success/failure handling

### Content & Learning Management
- **Workflow Builder**: AI-assisted template generation for structured learning paths
- **Assignment System**: Task creation, tracking, and progress monitoring
- **Resource Management**: File upload and sharing capabilities during sessions
- **Progress Tracking**: Learning journey visualization and milestone tracking

### Communication & Notifications
- **Notification System**: Real-time alerts for bookings, reminders, and updates
- **Messaging**: Direct communication channel between learners and experts
- **Review System**: Rating and feedback mechanism for quality assurance

## External Dependencies

### Styling & UI Components
- **Tailwind CSS**: Primary CSS framework loaded via CDN for rapid UI development
- **Chart.js**: Data visualization library for earnings and progress dashboards
- **Heroicons**: SVG icon library integrated through Tailwind

### Payment Integration
- **Razorpay**: Indian payment gateway for processing transactions and handling multiple payment methods
- **Payment Methods**: Support for cards, UPI, net banking, and digital wallets

### Authentication Services
- **Google OAuth**: Third-party authentication integration for simplified user onboarding
- **OTP Service**: SMS/Email verification system for secure authentication

### Future Integration Points
- **Video Calling**: WebRTC or third-party video service integration for live sessions
- **Cloud Storage**: File storage service for session recordings and shared resources
- **Email Service**: Transactional email system for notifications and communications
- **Analytics**: User behavior tracking and platform performance monitoring