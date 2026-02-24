# SRMS - Student Result Management System

Jharkhand Rai University Student Result Management System - A comprehensive web-based system for managing student examination results and notices.

## Features

- **Student Result Management**: View and manage examination results
- **Notice Board**: Display university notices and announcements
- **Modern UI**: Beautiful glassmorphism design with Tailwind CSS
- **RESTful API**: Complete PHP backend with MySQL database
- **Security**: CSRF protection, input validation, and secure authentication
- **Responsive Design**: Works seamlessly on desktop and mobile devices

## Technology Stack

### Frontend
- HTML5
- Tailwind CSS
- Vanilla JavaScript
- Glassmorphism UI Design

### Backend
- PHP 7.4+
- MySQL 8.0+
- RESTful API Architecture
- PDO for Database Operations

### Security Features
- CSRF Token Protection
- Input Validation & Sanitization
- SQL Injection Prevention
- Rate Limiting
- Secure Password Hashing

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 8.0 or higher
- Web Server (Apache/Nginx)
- Composer (optional)

### Step 1: Clone the Repository
```bash
git clone <repository-url>
cd SRMS
```

### Step 2: Run the Installation Script
Open your web browser and navigate to:
```
http://localhost/SRMS/setup/install.php
```

Follow the installation wizard to:
1. Check system requirements
2. Configure database connection
3. Create database tables
4. Insert sample data

### Step 3: Configure Web Server
Point your web server document root to the `public` directory.

#### Apache Configuration
```apache
<VirtualHost *:80>
    DocumentRoot /path/to/SRMS/public
    ServerName your-domain.com
    
    <Directory /path/to/SRMS/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/SRMS/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Step 4: Secure Installation
After successful installation, delete the setup directory:
```bash
rm -rf setup/
```

## Database Schema

The system uses the following main tables:

- **students**: Student information and enrollment details
- **subjects**: Subject catalog with course mapping
- **examinations**: Exam schedules and sessions
- **results**: Student marks and grades
- **notices**: University notices and announcements
- **admin_users**: System administrators

## API Endpoints

### Results API
- `GET /api/results.php` - Get student results
- `POST /api/results.php` - Add/update results

### Notices API
- `GET /api/notices.php` - Get all notices
- `GET /api/notices.php?id={id}` - Get specific notice
- `POST /api/notices.php` - Create new notice
- `PUT /api/notices.php?id={id}` - Update notice
- `DELETE /api/notices.php?id={id}` - Delete notice

### Students API
- `GET /api/students.php` - Get all students
- `GET /api/students.php?id={id}` - Get specific student
- `POST /api/students.php` - Add new student
- `PUT /api/students.php?id={id}` - Update student
- `DELETE /api/students.php?id={id}` - Delete student

## Default Credentials

After installation, you can login with:
- **Username**: admin
- **Password**: admin123

⚠️ **Important**: Change the default password after first login.

## Project Structure

```
SRMS/
├── api/                    # API endpoints
│   ├── results.php         # Results management API
│   ├── notices.php         # Notice management API
│   └── students.php       # Student management API
├── config/                # Configuration files
│   └── database.php       # Database connection
├── database/              # Database files
│   └── schema.sql         # Database schema
├── public/                # Public web root
│   └── index.php          # Entry point
├── setup/                 # Installation files
│   └── install.php        # Installation wizard
├── utils/                 # Utility classes
│   └── security.php       # Security utilities
├── js/                    # JavaScript files
│   └── api.js            # API client library
├── assets/               # Static assets
│   ├── jrulogo.jpg       # University logo
│   └── jru-logo.png      # Alternative logo
├── index.html            # Homepage
├── ResultPage.html       # Results page
├── Notice.html           # Notices page
├── Contact.html          # Contact page
├── navbar.html           # Navigation component
└── nav.js               # Navigation JavaScript
```

## Usage

### For Students
1. Visit the result page
2. Select examination session, course, and semester
3. Enter your roll number
4. View your results instantly

### For Administrators
1. Login to admin panel
2. Add/manage student records
3. Upload examination results
4. Publish notices and announcements
5. Generate reports

## Security Considerations

- All database queries use prepared statements
- Input validation and sanitization
- CSRF protection on all forms
- Rate limiting on API endpoints
- Secure session management
- File upload validation
- SQL injection prevention

## Development

### Adding New Features
1. Create new API endpoints in the `api/` directory
2. Follow the existing code structure and patterns
3. Implement proper validation and security measures
4. Update the frontend JavaScript accordingly

### Database Changes
1. Modify the `database/schema.sql` file
2. Create migration scripts for existing installations
3. Update the relevant API endpoints

## Support

For technical support and queries:
- Email: support@jru.edu.in
- Phone: +91 651-234-5678

## License

This project is proprietary to Jharkhand Rai University.

## Contributing

This is an internal project. Please contact the development team for contribution guidelines.

---

**Jharkhand Rai University**  
*Empowering Education Through Technology*
