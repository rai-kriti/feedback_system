# Power Supply Feedback System

A web-based feedback management system for power supply issues, featuring user authentication, feedback submission, and admin dashboard.

## Features

### User Features
- **User Registration & Login**: Secure authentication system
- **Profile Management**: Update personal details and profile picture
- **Feedback Submission**: Report power supply issues with details
- **Feedback History**: View all submitted feedbacks with status
- **Password Recovery**: Security question-based password reset

### Admin Features
- **Dashboard**: View all feedbacks with filtering options
- **Status Management**: Update feedback status (Pending/Resolved)
- **User Management**: View all registered users
- **Real-time Updates**: Dynamic feedback status changes

## Technologies Used

- **Frontend**: 
  - HTML5, CSS3, JavaScript
  - Tailwind CSS for styling
  - Lucide Icons
- **Backend**:
  - PHP
  - MySQL Database
- **Security**:
  - Prepared statements for SQL injection prevention
  - Password hashing
  - Session management

## Installation

### Prerequisites
- Web server (Apache/Nginx)
- PHP 7.4+
- MySQL 5.7+
- Composer (for optional dependencies)

### Setup Steps

1. Clone the repository:
   ```bash
   git clone https://github.com/Aman-Ranjan-003/feedback_system.git
   cd feedback_system
