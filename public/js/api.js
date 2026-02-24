/**
 * SRMS API Client
 * Jharkhand Rai University Student Result Management System
 */

class SRMSAPI {
    constructor() {
        this.baseURL = window.location.origin + '/api/results';
    }

    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const config = {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        };

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || `HTTP error! status: ${response.status}`);
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    // Results API
    async getResults(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/results.php?${queryString}`);
    }

    async addResult(resultData) {
        return this.request('/results.php', {
            method: 'POST',
            body: JSON.stringify(resultData)
        });
    }

    // Notices API
    async getNotices(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/notices.php?${queryString}`);
    }

    async getNoticeById(id) {
        return this.request(`/notices.php?id=${id}`);
    }

    async addNotice(noticeData) {
        return this.request('/notices.php', {
            method: 'POST',
            body: JSON.stringify(noticeData)
        });
    }

    async updateNotice(id, noticeData) {
        return this.request(`/notices.php?id=${id}`, {
            method: 'PUT',
            body: JSON.stringify(noticeData)
        });
    }

    async deleteNotice(id) {
        return this.request(`/notices.php?id=${id}`, {
            method: 'DELETE'
        });
    }

    // Students API
    async getStudents(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/students.php?${queryString}`);
    }

    async getStudentById(id) {
        return this.request(`/students.php?id=${id}`);
    }

    async addStudent(studentData) {
        return this.request('/students.php', {
            method: 'POST',
            body: JSON.stringify(studentData)
        });
    }

    async updateStudent(id, studentData) {
        return this.request(`/students.php?id=${id}`, {
            method: 'PUT',
            body: JSON.stringify(studentData)
        });
    }

    async deleteStudent(id) {
        return this.request(`/students.php?id=${id}`, {
            method: 'DELETE'
        });
    }
}

// Utility functions
function showMessage(message, type = 'info') {
    const messageDiv = document.getElementById('formMessage');
    if (!messageDiv) return;

    messageDiv.textContent = message;
    messageDiv.className = 'mb-6 p-4 rounded-xl text-sm font-medium';
    
    switch (type) {
        case 'success':
            messageDiv.classList.add('bg-green-500/20', 'border', 'border-green-500/50', 'text-green-300');
            break;
        case 'error':
            messageDiv.classList.add('bg-red-500/20', 'border', 'border-red-500/50', 'text-red-300');
            break;
        case 'warning':
            messageDiv.classList.add('bg-yellow-500/20', 'border', 'border-yellow-500/50', 'text-yellow-300');
            break;
        default:
            messageDiv.classList.add('bg-blue-500/20', 'border', 'border-blue-500/50', 'text-blue-300');
    }
    
    messageDiv.classList.remove('hidden');
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        messageDiv.classList.add('hidden');
    }, 5000);
}

function formatGrade(marks) {
    if (marks >= 90) return { grade: 'O', color: 'text-green-400' };
    if (marks >= 80) return { grade: 'A+', color: 'text-green-300' };
    if (marks >= 70) return { grade: 'A', color: 'text-blue-400' };
    if (marks >= 60) return { grade: 'B+', color: 'text-blue-300' };
    if (marks >= 50) return { grade: 'B', color: 'text-yellow-400' };
    if (marks >= 40) return { grade: 'C', color: 'text-yellow-300' };
    return { grade: 'F', color: 'text-red-400' };
}

function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

function validateForm(formData) {
    const errors = [];
    
    if (!formData.roll_number) {
        errors.push('Roll number is required');
    } else if (!/^[A-Z]{3}\d{4}$/.test(formData.roll_number)) {
        errors.push('Invalid roll number format (e.g., JRU2021)');
    }
    
    if (!formData.name) {
        errors.push('Name is required');
    }
    
    if (formData.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
        errors.push('Invalid email format');
    }
    
    if (formData.phone && !/^[6-9]\d{9}$/.test(formData.phone)) {
        errors.push('Invalid phone number format');
    }
    
    return errors;
}

// Initialize API client
const api = new SRMSAPI();

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { SRMSAPI, api, showMessage, formatGrade, formatDate, validateForm };
}
