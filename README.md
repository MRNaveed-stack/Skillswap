# SkillSwap - Peer-to-Peer Skill Exchange Platform

[![Laravel Version](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.5-blue.svg)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-18-blue.svg)](https://postgresql.org)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

## 📌 Overview

SkillSwap is a comprehensive peer-to-peer mentorship platform connecting learners with skilled mentors across various domains. Users can earn credits by teaching skills and spend credits to learn from others, creating a self-sustaining knowledge economy.

**Live Demo:** [https://sswap.duckdns.org](https://sswap.duckdns.org)

## ✨ Key Features

### For Learners
- Browse mentors by skills and categories
- Request personalized mentoring sessions
- Schedule sessions based on mentor availability
- Leave reviews and ratings after sessions
- Track learning progress

### For Mentors
- Showcase skills with experience levels
- Set hourly credit rates
- Manage availability slots
- Accept or decline session requests
- Earn credits through successful sessions
- Build reputation through learner reviews

### Platform Features
- **Wallet System:** Earn and spend credits
- **Real-time Notifications:** Stay updated on session status
- **Session Management:** Schedule, track, and complete sessions
- **Review System:** Build trust through verified reviews

## 🧠 Algorithmic Intelligence

The platform implements several classical algorithms for intelligent matching and optimization:

### 1. Mentor Matching Algorithm
- **Approach:** Weighted scoring with Top-K selection
- **Complexity:** O(n log k) using SplPriorityQueue
- **Factors:** Rating (35%), Experience (25%), Skill Relevance (20%), Availability (10%), Response Rate (10%)

### 2. Schedule Optimization
- **Approach:** Greedy interval scheduling
- **Complexity:** O(n log n)
- **Purpose:** Maximize non-overlapping sessions

### 3. Reputation Ranking
- **Approach:** PageRank-inspired trust propagation
- **Complexity:** O(n × iterations)
- **Purpose:** Calculate mentor trust scores from review graphs

### 4. Mentor Recommendation
- **Approach:** Weighted collaborative filtering
- **Purpose:** Personalize mentor suggestions based on search history

## 🏗️ Architecture
SkillSwap/
├── app/
│ ├── Http/
│ │ └── Controllers/ # Request handlers
│ ├── Models/ # Eloquent models
│ └── Services/ # Business logic & algorithms
├── database/
│ ├── migrations/ # Schema definitions
│ └── seeders/ # Test data
├── resources/
│ └── views/ # Blade templates
└── routes/
└── web.php # Route definitions

text

## 🛠️ Technology Stack

| Layer | Technology |
|-------|------------|
| **Backend Framework** | Laravel 12.x |
| **Language** | PHP 8.5 |
| **Database** | PostgreSQL 18 |
| **Web Server** | Nginx |
| **Frontend** | Blade, Bootstrap 5, JavaScript |
| **Deployment** | AWS EC2 (Ubuntu 26.04) |

## 📊 Database Schema

| Table | Purpose |
|-------|---------|
| `users` | User accounts & authentication |
| `profiles` | User profiles & bios |
| `skills` | Available skills catalog |
| `skill_categories` | Skill categorization |
| `user_skills` | Mentor skill listings |
| `session_requests` | Mentorship requests |
| `mentoring_sessions` | Confirmed sessions |
| `reviews` | Session feedback & ratings |
| `availability_slots` | Mentor availability |
| `wallets` | Credit balances |
| `notifications` | System notifications |

## 🚀 Installation

### Prerequisites
- PHP 8.5+
- PostgreSQL 18+
- Composer
- Nginx

### Local Setup

```bash
# Clone repository
git clone https://github.com/MRNaveed-stack/Skillswap.git
cd Skillswap

# Install dependencies
composer install

# Configure environment
cp .env.example .env
php artisan key:generate

# Set up database
createdb skillswap
php artisan migrate --seed

# Start development server
php artisan serve
🔧 Configuration
Environment Variables (.env)
env
APP_NAME=SkillSwap
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sswap.duckdns.org

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=skillswap
DB_USERNAME=skillswap_user
DB_PASSWORD=your_password

SESSION_DRIVER=file
📡 API Endpoints
Method	Endpoint	Description
GET	/mentors	Browse all mentors
GET	/skills	Browse skills
POST	/register	User registration
POST	/login	User authentication
GET	/profile	View profile
POST	/profile/edit	Update profile
GET	/requests	View session requests
POST	/requests	Create session request
PUT	/requests/{id}	Accept/reject request
GET	/sessions	View scheduled sessions
PUT	/sessions/{id}	Complete/cancel session
🔄 Core Workflows
Mentorship Request Flow
text
Learner → Search Mentors → Select Skill → Request Session
                    ↓
              Mentor Receives Request
                    ↓
              Accept / Reject
           ↓                    ↓
      Session Scheduled      Credits Refunded
           ↓
      Complete Session
           ↓
      Review & Rating
Credit Economy
text
Mentor: Earn credits by teaching
Learner: Spend credits to learn
Platform: 10 starting credits for new users
📈 Performance Metrics
Algorithm	Time Complexity	Space Complexity
Mentor Matching	O(n log k)	O(k)
Schedule Optimization	O(n log n)	O(n)
Reputation Ranking	O(n × 5)	O(n)
Recommendations	O(m × n)	O(n)
🧪 Testing
bash
# Run test suite
php artisan test

# Run specific test
php artisan test --filter=MentorMatchingServiceTest
🚢 Deployment
The application is deployed on AWS EC2 (Ubuntu 26.04) with:

Nginx web server

PHP 8.5-FPM

PostgreSQL 18

SSL via Let's Encrypt (DuckDNS domain)

Deployment URL: https://sswap.duckdns.org

🤝 Contributing
Fork the repository

Create a feature branch (git checkout -b feature/amazing-feature)

Commit changes (git commit -m 'Add amazing feature')

Push to branch (git push origin feature/amazing-feature)

Open a Pull Request

📄 License
This project is licensed under the MIT License - see the LICENSE file for details.

👤 Author
Muhammad Naveed Qasim

GitHub: @MRNaveed-stack

🙏 Acknowledgments
Laravel Community for the excellent framework

PostgreSQL for robust database management

AWS for reliable cloud infrastructure

📧 Contact
For questions or support, please open an issue on GitHub or contact the maintainer directly.

Built with ❤️ for the knowledge sharing community
