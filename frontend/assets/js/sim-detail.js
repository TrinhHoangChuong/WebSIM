// SIM Detail JavaScript
class SIMDetailManager {
    constructor() {
        this.sim = null;
        this.sims = [];
        this.init();
    }

    init() {
        this.loadSims();
        this.loadSimDetail();
    }

    loadSims() {
        const simsData = localStorage.getItem('sims');
        if (simsData) {
            this.sims = JSON.parse(simsData);
        } else {
            // Load default data if no data exists
            this.sims = this.getDefaultSims();
            localStorage.setItem('sims', JSON.stringify(this.sims));
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
            }
        ];
    }

    loadSimDetail() {
        const urlParams = new URLSearchParams(window.location.search);
        const simId = parseInt(urlParams.get('id'));

        if (!simId) {
            this.showError();
            return;
        }

        this.sim = this.sims.find(sim => sim.id === simId);

        if (!this.sim) {
            this.showError();
            return;
        }

        this.displaySimDetail();
        this.loadRelatedSims();
    }

    displaySimDetail() {
        // Hide loading spinner
        document.getElementById('loadingSpinner').style.display = 'none';
        document.getElementById('simDetail').style.display = 'block';

        // Update breadcrumb
        document.getElementById('breadcrumbSim').textContent = this.sim.number;

        // Update SIM image
        document.getElementById('simImage').src = this.sim.image;
        document.getElementById('simImage').alt = `SIM ${this.sim.number}`;

        // Update SIM number
        document.getElementById('simNumber').textContent = this.sim.number;

        // Update SIM type
        document.getElementById('simType').textContent = this.sim.type;

        // Update price
        document.getElementById('simPrice').textContent = this.formatPrice(this.sim.price);
        
        if (this.sim.originalPrice > this.sim.price) {
            document.getElementById('originalPrice').style.display = 'block';
            document.getElementById('originalPrice').innerHTML = `<del>${this.formatPrice(this.sim.originalPrice)}</del>`;
            
            if (this.sim.discount) {
                document.getElementById('discountBadge').style.display = 'block';
                document.getElementById('discountBadge').innerHTML = `<span class="badge bg-danger fs-6">-${this.sim.discount}%</span>`;
            }
        }

        // Update status
        const statusElement = document.getElementById('simStatus');
        statusElement.textContent = this.getStatusText(this.sim.status);
        statusElement.className = `sim-status ${this.getStatusClass(this.sim.status)}`;

        // Update description
        document.getElementById('simDescription').textContent = this.sim.description;

        // Update features
        const featuresContainer = document.getElementById('simFeatures');
        featuresContainer.innerHTML = '';
        this.sim.features.forEach(feature => {
            const badge = document.createElement('span');
            badge.className = 'badge bg-info me-2 mb-2';
            badge.textContent = feature;
            featuresContainer.appendChild(badge);
        });

        // Update dates
        document.getElementById('dateAdded').textContent = this.formatDate(this.sim.dateAdded);
        document.getElementById('dateExpired').textContent = this.formatDate(this.sim.dateExpired);

        // Update specifications
        document.getElementById('specCarrier').textContent = this.sim.type;
        document.getElementById('specType').textContent = 'Nano SIM';
        document.getElementById('specFrequency').textContent = this.sim.features.includes('5G') ? '4G/5G' : '4G';
        document.getElementById('specBand').textContent = this.sim.features.includes('5G') ? 'LTE, 5G NR' : 'LTE';

        // Update action buttons
        this.updateActionButtons();
    }

    updateActionButtons() {
        const buyNowBtn = document.getElementById('buyNowBtn');
        const addToCartBtn = document.getElementById('addToCartBtn');

        if (this.sim.status === 'available') {
            buyNowBtn.style.display = 'block';
            addToCartBtn.style.display = 'block';
            buyNowBtn.disabled = false;
            addToCartBtn.disabled = false;
        } else {
            buyNowBtn.style.display = 'none';
            addToCartBtn.style.display = 'none';
        }
    }

    loadRelatedSims() {
        const relatedSims = this.sims
            .filter(sim => sim.id !== this.sim.id && sim.type === this.sim.type)
            .slice(0, 3);

        const container = document.getElementById('relatedSims');
        container.innerHTML = '';

        if (relatedSims.length === 0) {
            container.innerHTML = '<p class="text-muted">Không có SIM liên quan</p>';
            return;
        }

        relatedSims.forEach(sim => {
            const col = document.createElement('div');
            col.className = 'col-md-4 mb-3';
            
            col.innerHTML = `
                <div class="sim-card">
                    <div class="card-body">
                        <div class="sim-number">${sim.number}</div>
                        <div class="sim-type">${sim.type}</div>
                        <div class="sim-price">${this.formatPrice(sim.price)}</div>
                        <div class="sim-status ${this.getStatusClass(sim.status)}">${this.getStatusText(sim.status)}</div>
                        <p class="text-muted small">${sim.description}</p>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary btn-sm" onclick="simDetailManager.viewSimDetail(${sim.id})">
                                <i class="fas fa-eye me-1"></i>Xem chi tiết
                            </button>
                            ${sim.status === 'available' ? `
                                <button class="btn btn-success btn-sm" onclick="simDetailManager.addToCart(${sim.id})">
                                    <i class="fas fa-cart-plus me-1"></i>Mua ngay
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
            
            container.appendChild(col);
        });
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

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN');
    }

    showError() {
        document.getElementById('loadingSpinner').style.display = 'none';
        document.getElementById('errorMessage').style.display = 'block';
    }

    viewSimDetail(simId) {
        window.location.href = `sim-detail.html?id=${simId}`;
    }

    addToCart(simId) {
        const sim = this.sims.find(s => s.id === simId);
        if (sim && sim.status === 'available') {
            this.showNotification('Đã thêm SIM vào giỏ hàng', 'success');
        } else {
            this.showNotification('SIM không khả dụng', 'danger');
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

// Global functions
function buyNow() {
    if (window.simDetailManager && window.simDetailManager.sim) {
        if (window.simDetailManager.sim.status === 'available') {
            // Redirect to checkout or show purchase modal
            window.simDetailManager.showNotification('Chuyển đến trang thanh toán...', 'info');
            // window.location.href = `checkout.html?simId=${window.simDetailManager.sim.id}`;
        } else {
            window.simDetailManager.showNotification('SIM không khả dụng', 'danger');
        }
    }
}

function addToCart() {
    if (window.simDetailManager && window.simDetailManager.sim) {
        window.simDetailManager.addToCart(window.simDetailManager.sim.id);
    }
}

function shareSim() {
    if (navigator.share) {
        navigator.share({
            title: `SIM ${window.simDetailManager.sim.number}`,
            text: `Xem SIM ${window.simDetailManager.sim.number} tại SIM Store`,
            url: window.location.href
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(window.location.href).then(() => {
            window.simDetailManager.showNotification('Đã sao chép liên kết!', 'success');
        });
    }
}

// Initialize SIM detail manager
document.addEventListener('DOMContentLoaded', function() {
    window.simDetailManager = new SIMDetailManager();
});
