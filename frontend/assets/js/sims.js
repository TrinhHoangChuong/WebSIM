// SIM Management JavaScript
class SIMManager {
    constructor() {
        this.sims = [];
        this.filteredSims = [];
        this.currentPage = 1;
        this.itemsPerPage = 12;
        this.init();
    }

    init() {
        this.loadSims();
        this.bindEvents();
        this.displaySims();
    }

    loadSims() {
        // Load SIMs from localStorage or use default data
        const simsData = localStorage.getItem('sims');
        if (simsData) {
            this.sims = JSON.parse(simsData);
        } else {
            this.sims = this.getDefaultSims();
            this.saveSims();
        }
        this.filteredSims = [...this.sims];
    }

    saveSims() {
        localStorage.setItem('sims', JSON.stringify(this.sims));
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
                isFeatured: true,
                discount: 16.67
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
                isFeatured: true,
                discount: 14.29
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
                isFeatured: false,
                discount: 20
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
                isFeatured: false,
                discount: 25
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
                isFeatured: false,
                discount: 16.67
            },
            {
                id: 6,
                number: '0945678901',
                type: 'Viettel',
                price: 1200000,
                originalPrice: 1500000,
                status: 'available',
                description: 'SIM Viettel VIP',
                image: 'https://via.placeholder.com/300x200?text=SIM+Viettel+VIP',
                dateAdded: '2024-01-25',
                dateExpired: '2025-01-25',
                features: ['5G', 'Không giới hạn data', 'Gọi quốc tế', 'SMS miễn phí'],
                isFeatured: true,
                discount: 20
            },
            {
                id: 7,
                number: '0956789012',
                type: 'Vinaphone',
                price: 450000,
                originalPrice: 500000,
                status: 'available',
                description: 'SIM Vinaphone trung cấp',
                image: 'https://via.placeholder.com/300x200?text=SIM+Vinaphone+Mid',
                dateAdded: '2024-01-18',
                dateExpired: '2025-01-18',
                features: ['4G', 'Data ưu đãi', 'Gọi nội mạng'],
                isFeatured: false,
                discount: 10
            },
            {
                id: 8,
                number: '0967890123',
                type: 'Mobifone',
                price: 200000,
                originalPrice: 250000,
                status: 'available',
                description: 'SIM Mobifone cơ bản',
                image: 'https://via.placeholder.com/300x200?text=SIM+Mobifone+Basic',
                dateAdded: '2024-01-12',
                dateExpired: '2025-01-12',
                features: ['4G', 'Data cơ bản'],
                isFeatured: false,
                discount: 20
            }
        ];
    }

    bindEvents() {
        // Search functionality
        const searchInput = document.getElementById('searchSim');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.filterSims();
            });
        }

        // Filter functionality
        const filterInputs = ['filterType', 'filterPrice', 'filterStatus'];
        filterInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                input.addEventListener('change', (e) => {
                    this.filterSims();
                });
            }
        });

        // Sort functionality
        const sortSelect = document.getElementById('sortBy');
        if (sortSelect) {
            sortSelect.addEventListener('change', (e) => {
                this.sortSims();
            });
        }
    }

    filterSims() {
        const searchTerm = document.getElementById('searchSim')?.value.toLowerCase() || '';
        const typeFilter = document.getElementById('filterType')?.value || '';
        const priceFilter = document.getElementById('filterPrice')?.value || '';
        const statusFilter = document.getElementById('filterStatus')?.value || '';

        this.filteredSims = this.sims.filter(sim => {
            // Search filter
            const matchesSearch = !searchTerm || 
                sim.number.toLowerCase().includes(searchTerm) ||
                sim.type.toLowerCase().includes(searchTerm) ||
                sim.description.toLowerCase().includes(searchTerm);

            // Type filter
            const matchesType = !typeFilter || sim.type === typeFilter;

            // Price filter
            let matchesPrice = true;
            if (priceFilter) {
                const [min, max] = priceFilter.split('-').map(Number);
                if (max === 999999999) {
                    matchesPrice = sim.price >= min;
                } else {
                    matchesPrice = sim.price >= min && sim.price <= max;
                }
            }

            // Status filter
            const matchesStatus = !statusFilter || sim.status === statusFilter;

            return matchesSearch && matchesType && matchesPrice && matchesStatus;
        });

        this.currentPage = 1;
        this.displaySims();
        this.updateResultsCount();
    }

    sortSims() {
        const sortBy = document.getElementById('sortBy')?.value || 'price-asc';

        this.filteredSims.sort((a, b) => {
            switch (sortBy) {
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

        this.displaySims();
    }

    displaySims() {
        const container = document.getElementById('simsGrid');
        if (!container) return;

        container.innerHTML = '';

        if (this.filteredSims.length === 0) {
            this.showNoResults();
            return;
        }

        // Pagination
        const startIndex = (this.currentPage - 1) * this.itemsPerPage;
        const endIndex = startIndex + this.itemsPerPage;
        const paginatedSims = this.filteredSims.slice(startIndex, endIndex);

        paginatedSims.forEach(sim => {
            const simCard = this.createSimCard(sim);
            container.appendChild(simCard);
        });

        this.updatePagination();
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
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="sim-price">${this.formatPrice(sim.price)}</div>
                        ${sim.originalPrice > sim.price ? `
                            <div class="text-muted small">
                                <del>${this.formatPrice(sim.originalPrice)}</del>
                            </div>
                        ` : ''}
                    </div>
                    ${sim.discount ? `
                        <div class="badge bg-danger mb-2">-${sim.discount}%</div>
                    ` : ''}
                    <div class="sim-status ${statusClass}">${statusText}</div>
                    <p class="text-muted small">${sim.description}</p>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary btn-sm" onclick="simManager.viewSimDetail(${sim.id})">
                            <i class="fas fa-eye me-1"></i>Xem chi tiết
                        </button>
                        ${sim.status === 'available' ? `
                            <button class="btn btn-success btn-sm" onclick="simManager.addToCart(${sim.id})">
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
            // Add to cart logic here
            this.showNotification('Đã thêm SIM vào giỏ hàng', 'success');
        } else {
            this.showNotification('SIM không khả dụng', 'danger');
        }
    }

    showNoResults() {
        const noResults = document.getElementById('noResults');
        if (noResults) {
            noResults.style.display = 'block';
        }
    }

    updateResultsCount() {
        const resultsCount = document.getElementById('resultsCount');
        if (resultsCount) {
            resultsCount.textContent = this.filteredSims.length;
        }
    }

    updatePagination() {
        const pagination = document.getElementById('pagination');
        if (!pagination) return;

        const totalPages = Math.ceil(this.filteredSims.length / this.itemsPerPage);
        
        if (totalPages <= 1) {
            pagination.innerHTML = '';
            return;
        }

        let paginationHTML = '';

        // Previous button
        paginationHTML += `
            <li class="page-item ${this.currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="simManager.goToPage(${this.currentPage - 1})">Trước</a>
            </li>
        `;

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= this.currentPage - 2 && i <= this.currentPage + 2)) {
                paginationHTML += `
                    <li class="page-item ${i === this.currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="simManager.goToPage(${i})">${i}</a>
                    </li>
                `;
            } else if (i === this.currentPage - 3 || i === this.currentPage + 3) {
                paginationHTML += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        // Next button
        paginationHTML += `
            <li class="page-item ${this.currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="simManager.goToPage(${this.currentPage + 1})">Sau</a>
            </li>
        `;

        pagination.innerHTML = paginationHTML;
    }

    goToPage(page) {
        const totalPages = Math.ceil(this.filteredSims.length / this.itemsPerPage);
        if (page >= 1 && page <= totalPages) {
            this.currentPage = page;
            this.displaySims();
        }
    }

    showNotification(message, type = 'info') {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(notification => notification.remove());

        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show notification position-fixed`;
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
}

// Initialize SIM manager
document.addEventListener('DOMContentLoaded', function() {
    window.simManager = new SIMManager();
});
