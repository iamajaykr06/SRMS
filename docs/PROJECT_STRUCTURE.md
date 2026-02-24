# SRMS - Student Result Management System

## Project Structure

```
SRMS/
├── .git/                           # Git version control
├── .gitignore                       # Git ignore file
├── .htaccess                        # Apache configuration
├── docs/                            # Documentation
│   ├── PROJECT_STRUCTURE.md           # This file
│   └── README.md                    # Main documentation
├── public/                           # Public web root
│   ├── index.php                    # Main entry point (router)
│   ├── assets/                      # Static assets
│   │   ├── jrulogo.jpg
│   │   └── jru-logo.png
│   ├── css/                         # Stylesheets (empty for now)
│   └── js/                          # JavaScript files
│       ├── api.js                    # API client
│       └── nav.js                    # Navigation component
├── src/                             # Source code
│   ├── config/                      # Configuration files
│   │   └── database.php              # Database connection
│   ├── controllers/                  # Application logic
│   │   ├── index.php                # Home controller
│   │   └── results.php              # Results API
│   ├── models/                       # Data models
│   │   └── schema.sql               # Database schema
│   ├── utils/                        # Utility functions
│   └── views/                        # HTML templates
│       ├── 404.html                 # 404 error page
│       ├── Contact.html              # Contact page
│       ├── Notice.html               # Notices page
│       ├── ResultPage.html           # Results page
│       ├── index.html               # Home page
│       └── navbar.html              # Navigation component
└── tests/                           # Test files (empty)
```

## Architecture

### MVC Pattern
- **Models**: Database schema and data structures
- **Views**: HTML templates and frontend
- **Controllers**: Business logic and API endpoints

### Public Directory Structure
- **Root**: `public/` is the web root for security
- **Router**: `public/index.php` handles all requests
- **Assets**: Static files (CSS, JS, images)

### Key Features
1. **Student Result Viewing**
   - Dropdown selection for session, course, semester
   - Form validation
   - API integration
   - Modal result display

2. **Navigation System**
   - Responsive navbar
   - Mobile-friendly menu
   - Active state indicators

3. **API Endpoints**
   - `/api/results` - Get student results
   - Proper error handling
   - JSON responses

## Development Setup

1. **Web Server**: Point to `public/` directory
2. **Database**: Run `src/models/schema.sql` to create tables
3. **URL Structure**: Clean URLs via `public/index.php` router

## Security Features
- Public directory isolation
- Input validation
- XSS protection headers
- SQL injection prevention
- Error handling

## Technologies Used
- **Backend**: PHP 8+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript ES6+
- **Styling**: Tailwind CSS
- **Icons**: SVG icons
- **Version Control**: Git
