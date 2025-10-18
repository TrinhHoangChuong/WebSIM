// Authentication JavaScript
class AuthManager {
    constructor() {
        this.currentUser = null;
        this.init();
    }

    init() {
        this.loadCurrentUser();
        this.bindAuthEvents();
        this.updateAuthUI();
    }

    loadCurrentUser() {
        const userData = localStorage.getItem('currentUser');
        if (userData) {
            this.currentUser = JSON.parse(userData);
        }
    }

    bindAuthEvents() {
        // Login form
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleLogin();
            });
        }

        // Register form
        const registerForm = document.getElementById('registerForm');
        if (registerForm) {
            registerForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleRegister();
            });
        }

        // Contact form
        const contactForm = document.getElementById('contactForm');
        if (contactForm) {
            contactForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleContactForm();
            });
        }
    }

    handleLogin() {
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;
        const rememberMe = document.getElementById('rememberMe').checked;

        if (!email || !password) {
            this.showAlert('Vui lòng điền đầy đủ thông tin!', 'danger');
            return;
        }

        // Simulate login process
        if (this.validateLogin(email, password)) {
            this.currentUser = {
                id: 1,
                name: 'Nguyễn Văn A',
                email: email,
                role: 'customer'
            };

            if (rememberMe) {
                localStorage.setItem('currentUser', JSON.stringify(this.currentUser));
            }

            this.showAlert('Đăng nhập thành công!', 'success');
            this.updateAuthUI();
            this.closeModal('loginModal');
        } else {
            this.showAlert('Email hoặc mật khẩu không đúng!', 'danger');
        }
    }

    handleRegister() {
        const name = document.getElementById('registerName').value;
        const email = document.getElementById('registerEmail').value;
        const phone = document.getElementById('registerPhone').value;
        const password = document.getElementById('registerPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        if (!name || !email || !phone || !password || !confirmPassword) {
            this.showAlert('Vui lòng điền đầy đủ thông tin!', 'danger');
            return;
        }

        if (password !== confirmPassword) {
            this.showAlert('Mật khẩu xác nhận không khớp!', 'danger');
            return;
        }

        if (password.length < 6) {
            this.showAlert('Mật khẩu phải có ít nhất 6 ký tự!', 'danger');
            return;
        }

        if (!this.validateEmail(email)) {
            this.showAlert('Email không hợp lệ!', 'danger');
            return;
        }

        if (!this.validatePhone(phone)) {
            this.showAlert('Số điện thoại không hợp lệ!', 'danger');
            return;
        }

        // Simulate register process
        this.currentUser = {
            id: Date.now(),
            name: name,
            email: email,
            phone: phone,
            role: 'customer'
        };

        localStorage.setItem('currentUser', JSON.stringify(this.currentUser));
        this.showAlert('Đăng ký thành công!', 'success');
        this.updateAuthUI();
        this.closeModal('registerModal');
    }

    handleContactForm() {
        const name = document.getElementById('contactName').value;
        const email = document.getElementById('contactEmail').value;
        const phone = document.getElementById('contactPhone').value;
        const subject = document.getElementById('contactSubject').value;
        const message = document.getElementById('contactMessage').value;
        const agreeTerms = document.getElementById('agreeTerms').checked;

        if (!name || !email || !subject || !message) {
            this.showAlert('Vui lòng điền đầy đủ thông tin bắt buộc!', 'danger');
            return;
        }

        if (!agreeTerms) {
            this.showAlert('Vui lòng đồng ý với điều khoản sử dụng!', 'danger');
            return;
        }

        if (!this.validateEmail(email)) {
            this.showAlert('Email không hợp lệ!', 'danger');
            return;
        }

        if (phone && !this.validatePhone(phone)) {
            this.showAlert('Số điện thoại không hợp lệ!', 'danger');
            return;
        }

        // Simulate form submission
        this.showAlert('Gửi yêu cầu hỗ trợ thành công! Chúng tôi sẽ liên hệ lại trong thời gian sớm nhất.', 'success');
        document.getElementById('contactForm').reset();
    }

    validateLogin(email, password) {
        // Simple validation for demo
        const validUsers = [
            { email: 'admin@simstore.com', password: 'admin123' },
            { email: 'user@example.com', password: 'user123' },
            { email: 'test@test.com', password: '123456' }
        ];

        return validUsers.some(user => user.email === email && user.password === password);
    }

    validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    validatePhone(phone) {
        const phoneRegex = /^[0-9]{10,11}$/;
        return phoneRegex.test(phone.replace(/\s/g, ''));
    }

    updateAuthUI() {
        const authButtons = document.querySelector('.btn-group');
        if (!authButtons) return;

        if (this.currentUser) {
            authButtons.innerHTML = `
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i>${this.currentUser.name}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="authManager.viewProfile()">
                            <i class="fas fa-user me-2"></i>Thông tin cá nhân
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="authManager.viewOrders()">
                            <i class="fas fa-shopping-bag me-2"></i>Đơn hàng
                        </a></li>
                        ${this.currentUser.role === 'admin' ? `
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="admin.html">
                                <i class="fas fa-cog me-2"></i>Quản trị
                            </a></li>
                        ` : ''}
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="authManager.logout()">
                            <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                        </a></li>
                    </ul>
                </div>
            `;
        } else {
            authButtons.innerHTML = `
                <button class="btn btn-outline-light" onclick="showLoginModal()">
                    <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập
                </button>
                <button class="btn btn-light" onclick="showRegisterModal()">
                    <i class="fas fa-user-plus me-1"></i>Đăng ký
                </button>
            `;
        }
    }

    viewProfile() {
        if (this.currentUser) {
            this.showAlert('Tính năng đang được phát triển!', 'info');
        }
    }

    viewOrders() {
        if (this.currentUser) {
            this.showAlert('Tính năng đang được phát triển!', 'info');
        }
    }

    logout() {
        this.currentUser = null;
        localStorage.removeItem('currentUser');
        this.updateAuthUI();
        this.showAlert('Đã đăng xuất!', 'info');
    }

    closeModal(modalId) {
        const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
        if (modal) {
            modal.hide();
        }
    }

    showAlert(message, type = 'info') {
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());

        // Create new alert
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alert.style.cssText = 'top: 100px; right: 20px; z-index: 9999; min-width: 300px;';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(alert);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 5000);
    }

    // OTP functionality
    sendOTP(phone) {
        // Simulate OTP sending
        this.showAlert('Mã OTP đã được gửi đến số điện thoại của bạn!', 'success');
        return '123456'; // Demo OTP
    }

    verifyOTP(otp, sentOTP) {
        return otp === sentOTP;
    }

    // Password reset
    resetPassword(email) {
        if (!this.validateEmail(email)) {
            this.showAlert('Email không hợp lệ!', 'danger');
            return false;
        }

        this.showAlert('Hướng dẫn đặt lại mật khẩu đã được gửi đến email của bạn!', 'success');
        return true;
    }
}

// Initialize auth manager
document.addEventListener('DOMContentLoaded', function() {
    window.authManager = new AuthManager();
});
