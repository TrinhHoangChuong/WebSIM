// Main Application JavaScript
class SIMStore {
    constructor() {
        this.sims = [];
        this.users = [];
        this.currentUser = null;
        this.cart = [];
        this.filters = {
            search: '',
            type: '',
            price: '',
            status: ''
        };
        this.sortBy = 'price-asc';
        this.viewMode = 'grid';
        this.currentPage = 1;
        this.itemsPerPage = 12;
        
        this.init();
    }

    init() {
        this.loadData();
        this.bindEvents();
        this.loadFeaturedSims();
    }

    loadData() {
        // Load SIMs data
        const simsData = localStorage.getItem('sims');
        if (simsData) {
            this.sims = JSON.parse(simsData);
        } else {
            this.sims = this.getDefaultSims();
            this.saveData();
        }

        // Load users data
        const usersData = localStorage.getItem('users');
        if (usersData) {
            this.users = JSON.parse(usersData);
        } else {
            this.users = this.getDefaultUsers();
            this.saveData();
        }

        // Load current user
        const currentUserData = localStorage.getItem('currentUser');
        if (currentUserData) {
            this.currentUser = JSON.parse(currentUserData);
        }
    }

    saveData() {
        localStorage.setItem('sims', JSON.stringify(this.sims));
        localStorage.setItem('users', JSON.stringify(this.users));
        if (this.currentUser) {
            localStorage.setItem('currentUser', JSON.stringify(this.currentUser));
        }
    }

    getDefaultSims() {
        return [
            {
                id: 1,
                number: '0987654321',
                type: 'Viettel',
                price: 500000,
                originalPrice: 600000,
                status: 'available',
                description: 'SIM số đẹp Viettel, dễ nhớ',
                image: 'https://via.placeholder.com/300x200?text=SIM+Viettel',
                dateAdded: '2024-01-15',
                dateExpired: '2025-01-15',
                features: ['4G', '5G', 'Không giới hạn data'],
                isFeatured: true
            },
            {
                id: 2,
                number: '0912345678',
                type: 'Vinaphone',
                price: 300000,
                originalPrice: 350000,
                status: 'available',
                description: 'SIM Vinaphone giá rẻ',
                image: 'https://via.placeholder.com/300x200?text=SIM+Vinaphone',
                dateAdded: '2024-01-10',
                dateExpired: '2025-01-10',
                features: ['4G', 'Gọi nội mạng miễn phí'],
                isFeatured: true
            },
            {
                id: 3,
                number: '0901234567',
                type: 'Mobifone',
                price: 800000,
                originalPrice: 1000000,
                status: 'available',
                description: 'SIM Mobifone cao cấp',
                image: 'https://via.placeholder.com/300x200?text=SIM+Mobifone',
                dateAdded: '2024-01-20',
                dateExpired: '2025-01-20',
                features: ['5G', 'Không giới hạn data', 'Gọi quốc tế'],
                isFeatured: false
            },
            {
                id: 4,
                number: '0923456789',
                type: 'Vietnamobile',
                price: 150000,
                originalPrice: 200000,
                status: 'sold',
                description: 'SIM Vietnamobile tiết kiệm',
                image: 'https://via.placeholder.com/300x200?text=SIM+Vietnamobile',
                dateAdded: '2024-01-05',
                dateExpired: '2025-01-05',
                features: ['4G', 'Data ưu đãi'],
                isFeatured: false
            },
            {
                id: 5,
                number: '0934567890',
                type: 'Gmobile',
                price: 250000,
                originalPrice: 300000,
                status: 'expired',
                description: 'SIM Gmobile đã hết hạn',
                image: 'https://via.placeholder.com/300x200?text=SIM+Gmobile',
                dateAdded: '2023-12-01',
                dateExpired: '2024-01-01',
                features: ['4G'],
                isFeatured: false
            }
        ];
    }

    getDefaultUsers() {
        return [
            {
                id: 1,
                name: 'Admin',
                email: 'admin@simstore.com',
                phone: '0123456789',
                password: 'admin123',
                role: 'admin',
                dateCreated: '2024-01-01'
            },
            {
                id: 2,
                name: 'Nguyễn Văn A',
                email: 'user@example.com',
                phone: '0987654321',
                password: 'user123',
                role: 'customer',
                dateCreated: '2024-01-15'
            }
        ];
    }

    bindEvents() {
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.filters.search = e.target.value;
                this.filterSims();
            });
        }

        // Filter functionality
        const filterInputs = ['filterType', 'filterPrice', 'filterStatus'];
        filterInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                input.addEventListener('change', (e) => {
                    this.filters[inputId.replace('filter', '').toLowerCase()] = e.target.value;
                    this.filterSims();
                });
            }
        });

        // Sort functionality
        const sortSelect = document.getElementById('sortBy');
        if (sortSelect) {
            sortSelect.addEventListener('change', (e) => {
                this.sortBy = e.target.value;
                this.filterSims();
            });
        }
    }

    loadFeaturedSims() {
        const featuredSims = this.sims.filter(sim => sim.isFeatured && sim.status === 'available');
        const container = document.getElementById('featuredSims');
        
        if (container) {
            container.innerHTML = '';
            featuredSims.forEach(sim => {
                const simCard = this.createSimCard(sim);
                container.appendChild(simCard);
            });
        }
    }

    createSimCard(sim) {
        const col = document.createElement('div');
        col.className = 'col-lg-4 col-md-6 mb-4';
        
        const statusClass = this.getStatusClass(sim.status);
        const statusText = this.getStatusText(sim.status);
        
        col.innerHTML = `
            <div class="sim-card">
                <div class="card-body">
                    <div class="sim-number">${sim.number}</div>
                    <div class="sim-type">${sim.type}</div>
                    <div class="sim-price">${this.formatPrice(sim.price)}</div>
                    <div class="sim-status ${statusClass}">${statusText}</div>
                    <p class="text-muted small">${sim.description}</p>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary btn-sm" onclick="simStore.viewSimDetail(${sim.id})">
                            <i class="fas fa-eye me-1"></i>Xem chi tiết
                        </button>
                        ${sim.status === 'available' ? `
                            <button class="btn btn-success btn-sm" onclick="simStore.addToCart(${sim.id})">
                                <i class="fas fa-cart-plus me-1"></i>Mua ngay
                            </button>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
        
        return col;
    }

    getStatusClass(status) {
        const statusClasses = {
            'available': 'status-available',
            'sold': 'status-sold',
            'expired': 'status-expired'
        };
        return statusClasses[status] || '';
    }

    getStatusText(status) {
        const statusTexts = {
            'available': 'Còn hàng',
            'sold': 'Đã bán',
            'expired': 'Hết hạn'
        };
        return statusTexts[status] || status;
    }

    formatPrice(price) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(price);
    }

    filterSims() {
        let filteredSims = [...this.sims];

        // Apply search filter
        if (this.filters.search) {
            const searchTerm = this.filters.search.toLowerCase();
            filteredSims = filteredSims.filter(sim => 
                sim.number.toLowerCase().includes(searchTerm) ||
                sim.type.toLowerCase().includes(searchTerm) ||
                sim.description.toLowerCase().includes(searchTerm)
            );
        }

        // Apply type filter
        if (this.filters.type) {
            filteredSims = filteredSims.filter(sim => sim.type === this.filters.type);
        }

        // Apply price filter
        if (this.filters.price) {
            const [min, max] = this.filters.price.split('-').map(Number);
            filteredSims = filteredSims.filter(sim => {
                if (max === 999999999) {
                    return sim.price >= min;
                }
                return sim.price >= min && sim.price <= max;
            });
        }

        // Apply status filter
        if (this.filters.status) {
            filteredSims = filteredSims.filter(sim => sim.status === this.filters.status);
        }

        // Apply sorting
        filteredSims.sort((a, b) => {
            switch (this.sortBy) {
                case 'price-asc':
                    return a.price - b.price;
                case 'price-desc':
                    return b.price - a.price;
                case 'number-asc':
                    return a.number.localeCompare(b.number);
                case 'number-desc':
                    return b.number.localeCompare(a.number);
                case 'date-desc':
                    return new Date(b.dateAdded) - new Date(a.dateAdded);
                case 'date-asc':
                    return new Date(a.dateAdded) - new Date(b.dateAdded);
                default:
                    return 0;
            }
        });

        this.displaySims(filteredSims);
        this.updateResultsCount(filteredSims.length);
    }

    displaySims(sims) {
        const container = document.getElementById('simsGrid');
        if (!container) return;

        container.innerHTML = '';

        if (sims.length === 0) {
            this.showNoResults();
            return;
        }

        sims.forEach(sim => {
            const simCard = this.createSimCard(sim);
            container.appendChild(simCard);
        });
    }

    showNoResults() {
        const noResults = document.getElementById('noResults');
        if (noResults) {
            noResults.style.display = 'block';
        }
    }

    updateResultsCount(count) {
        const resultsCount = document.getElementById('resultsCount');
        if (resultsCount) {
            resultsCount.textContent = count;
        }
    }

    viewSimDetail(simId) {
        const sim = this.sims.find(s => s.id === simId);
        if (sim) {
            // Redirect to SIM detail page
            window.location.href = `sim-detail.html?id=${simId}`;
        }
    }

    addToCart(simId) {
        const sim = this.sims.find(s => s.id === simId);
        if (sim && sim.status === 'available') {
            this.cart.push(sim);
            this.showNotification('Đã thêm SIM vào giỏ hàng', 'success');
        }
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 100px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        // Auto remove after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 3000);
    }

    // Authentication methods
    login(email, password) {
        const user = this.users.find(u => u.email === email && u.password === password);
        if (user) {
            this.currentUser = user;
            this.saveData();
            this.showNotification('Đăng nhập thành công!', 'success');
            return true;
        }
        this.showNotification('Email hoặc mật khẩu không đúng!', 'danger');
        return false;
    }

    register(userData) {
        const existingUser = this.users.find(u => u.email === userData.email);
        if (existingUser) {
            this.showNotification('Email đã được sử dụng!', 'danger');
            return false;
        }

        const newUser = {
            id: this.users.length + 1,
            ...userData,
            role: 'customer',
            dateCreated: new Date().toISOString().split('T')[0]
        };

        this.users.push(newUser);
        this.saveData();
        this.showNotification('Đăng ký thành công!', 'success');
        return true;
    }

    logout() {
        this.currentUser = null;
        localStorage.removeItem('currentUser');
        this.showNotification('Đã đăng xuất!', 'info');
    }
}

// Global functions
function showLoginModal() {
    const modal = new bootstrap.Modal(document.getElementById('loginModal'));
    modal.show();
}

function showRegisterModal() {
    const modal = new bootstrap.Modal(document.getElementById('registerModal'));
    modal.show();
}

function applyFilters() {
    if (window.simStore) {
        window.simStore.filterSims();
    }
}

function clearFilters() {
    // Reset filter inputs
    document.getElementById('searchSim').value = '';
    document.getElementById('filterType').value = '';
    document.getElementById('filterPrice').value = '';
    document.getElementById('filterStatus').value = '';
    
    // Reset filters object
    if (window.simStore) {
        window.simStore.filters = {
            search: '',
            type: '',
            price: '',
            status: ''
        };
        window.simStore.filterSims();
    }
}

function setViewMode(mode) {
    if (window.simStore) {
        window.simStore.viewMode = mode;
        // Update view mode buttons
        document.querySelectorAll('.btn-group .btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');
    }
}

// Initialize application
document.addEventListener('DOMContentLoaded', function() {
    window.simStore = new SIMStore();
});
