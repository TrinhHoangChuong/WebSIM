// Admin Dashboard JavaScript
class AdminManager {
    constructor() {
        this.sims = [];
        this.users = [];
        this.orders = [];
        this.currentSection = 'dashboard';
        this.init();
    }

    init() {
        this.loadData();
        this.bindEvents();
        this.showDashboard();
    }

    loadData() {
        // Load SIMs
        const simsData = localStorage.getItem('sims');
        if (simsData) {
            this.sims = JSON.parse(simsData);
        } else {
            this.sims = this.getDefaultSims();
            this.saveData();
        }

        // Load users
        const usersData = localStorage.getItem('users');
        if (usersData) {
            this.users = JSON.parse(usersData);
        } else {
            this.users = this.getDefaultUsers();
            this.saveData();
        }

        // Load orders (mock data)
        this.orders = this.getMockOrders();
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

    getMockOrders() {
        return [
            {
                id: 1,
                customerId: 2,
                simId: 4,
                total: 150000,
                status: 'completed',
                dateCreated: '2024-01-20'
            }
        ];
    }

    saveData() {
        localStorage.setItem('sims', JSON.stringify(this.sims));
        localStorage.setItem('users', JSON.stringify(this.users));
    }

    bindEvents() {
        // Add SIM form
        const addSimForm = document.getElementById('addSimForm');
        if (addSimForm) {
            addSimForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.saveSim();
            });
        }
    }

    showDashboard() {
        this.hideAllSections();
        document.getElementById('dashboardSection').style.display = 'block';
        this.updateActiveNav('dashboard');
        this.loadDashboardData();
    }

    showSims() {
        this.hideAllSections();
        document.getElementById('simsSection').style.display = 'block';
        this.updateActiveNav('sims');
        this.loadSimsTable();
    }

    showCustomers() {
        this.hideAllSections();
        document.getElementById('customersSection').style.display = 'block';
        this.updateActiveNav('customers');
        this.loadCustomersTable();
    }

    showOrders() {
        this.hideAllSections();
        document.getElementById('ordersSection').style.display = 'block';
        this.updateActiveNav('orders');
    }

    showInventory() {
        this.hideAllSections();
        document.getElementById('inventorySection').style.display = 'block';
        this.updateActiveNav('inventory');
    }

    showReports() {
        this.hideAllSections();
        document.getElementById('reportsSection').style.display = 'block';
        this.updateActiveNav('reports');
    }

    showSettings() {
        this.hideAllSections();
        document.getElementById('settingsSection').style.display = 'block';
        this.updateActiveNav('settings');
    }

    hideAllSections() {
        const sections = ['dashboardSection', 'simsSection', 'customersSection', 'ordersSection', 'inventorySection', 'reportsSection', 'settingsSection'];
        sections.forEach(section => {
            const element = document.getElementById(section);
            if (element) {
                element.style.display = 'none';
            }
        });
    }

    updateActiveNav(section) {
        // Remove active class from all nav items
        document.querySelectorAll('.admin-sidebar .nav-link').forEach(link => {
            link.classList.remove('active');
        });

        // Add active class to current section
        const activeLink = document.querySelector(`[onclick="adminManager.show${section.charAt(0).toUpperCase() + section.slice(1)}()"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }
    }

    loadDashboardData() {
        // Update stats
        document.getElementById('totalSims').textContent = this.sims.length;
        document.getElementById('availableSims').textContent = this.sims.filter(sim => sim.status === 'available').length;
        document.getElementById('totalCustomers').textContent = this.users.filter(user => user.role === 'customer').length;
        document.getElementById('totalOrders').textContent = this.orders.length;

        // Load recent SIMs
        this.loadRecentSims();

        // Load alerts
        this.loadAlerts();

        // Load charts
        this.loadCharts();
    }

    loadRecentSims() {
        const recentSims = this.sims
            .sort((a, b) => new Date(b.dateAdded) - new Date(a.dateAdded))
            .slice(0, 5);

        const tbody = document.getElementById('recentSims');
        tbody.innerHTML = '';

        recentSims.forEach(sim => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${sim.number}</td>
                <td>${sim.type}</td>
                <td>${this.formatPrice(sim.price)}</td>
                <td><span class="badge ${this.getStatusBadgeClass(sim.status)}">${this.getStatusText(sim.status)}</span></td>
            `;
            tbody.appendChild(row);
        });
    }

    loadAlerts() {
        const alerts = [];
        
        // Check for low stock
        const availableSims = this.sims.filter(sim => sim.status === 'available');
        if (availableSims.length < 10) {
            alerts.push({
                type: 'warning',
                message: `Cảnh báo: Chỉ còn ${availableSims.length} SIM trong kho`
            });
        }

        // Check for expired SIMs
        const expiredSims = this.sims.filter(sim => {
            const expiryDate = new Date(sim.dateExpired);
            const today = new Date();
            return expiryDate < today && sim.status === 'available';
        });

        if (expiredSims.length > 0) {
            alerts.push({
                type: 'danger',
                message: `${expiredSims.length} SIM đã hết hạn cần xử lý`
            });
        }

        // Display alerts
        const alertsContainer = document.getElementById('alertsList');
        alertsContainer.innerHTML = '';

        if (alerts.length === 0) {
            alertsContainer.innerHTML = '<p class="text-muted">Không có cảnh báo nào</p>';
            return;
        }

        alerts.forEach(alert => {
            const alertElement = document.createElement('div');
            alertElement.className = `alert alert-${alert.type} alert-sm`;
            alertElement.innerHTML = `
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${alert.message}
            `;
            alertsContainer.appendChild(alertElement);
        });
    }

    loadCharts() {
        // Sales Chart
        const salesCtx = document.getElementById('salesChart');
        if (salesCtx) {
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
                    datasets: [{
                        label: 'Doanh thu (VNĐ)',
                        data: [1200000, 1900000, 3000000, 5000000, 2000000, 3000000, 4500000],
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Carrier Chart
        const carrierCtx = document.getElementById('carrierChart');
        if (carrierCtx) {
            const carrierData = this.getCarrierStats();
            new Chart(carrierCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(carrierData),
                    datasets: [{
                        data: Object.values(carrierData),
                        backgroundColor: [
                            '#FF6384',
                            '#36A2EB',
                            '#FFCE56',
                            '#4BC0C0',
                            '#9966FF'
                        ]
                    }]
                },
                options: {
                    responsive: true
                }
            });
        }
    }

    getCarrierStats() {
        const stats = {};
        this.sims.forEach(sim => {
            stats[sim.type] = (stats[sim.type] || 0) + 1;
        });
        return stats;
    }

    loadSimsTable() {
        const tbody = document.getElementById('simsTableBody');
        tbody.innerHTML = '';

        this.sims.forEach(sim => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${sim.id}</td>
                <td>${sim.number}</td>
                <td>${sim.type}</td>
                <td>${this.formatPrice(sim.price)}</td>
                <td><span class="badge ${this.getStatusBadgeClass(sim.status)}">${this.getStatusText(sim.status)}</span></td>
                <td>${this.formatDate(sim.dateAdded)}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="adminManager.editSim(${sim.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="adminManager.deleteSim(${sim.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    loadCustomersTable() {
        const tbody = document.getElementById('customersTableBody');
        tbody.innerHTML = '';

        const customers = this.users.filter(user => user.role === 'customer');
        customers.forEach(customer => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${customer.id}</td>
                <td>${customer.name}</td>
                <td>${customer.email}</td>
                <td>${customer.phone}</td>
                <td>${this.formatDate(customer.dateCreated)}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="adminManager.editCustomer(${customer.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="adminManager.deleteCustomer(${customer.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    addSim() {
        const modal = new bootstrap.Modal(document.getElementById('addSimModal'));
        modal.show();
    }

    saveSim() {
        const form = document.getElementById('addSimForm');
        const formData = new FormData(form);
        
        const newSim = {
            id: this.sims.length + 1,
            number: document.getElementById('simNumber').value,
            type: document.getElementById('simType').value,
            price: parseInt(document.getElementById('simPrice').value),
            originalPrice: parseInt(document.getElementById('originalPrice').value) || parseInt(document.getElementById('simPrice').value),
            status: document.getElementById('simStatus').value,
            description: document.getElementById('simDescription').value,
            image: 'https://via.placeholder.com/300x200?text=SIM+' + document.getElementById('simType').value,
            dateAdded: new Date().toISOString().split('T')[0],
            dateExpired: new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
            features: ['4G'],
            isFeatured: document.getElementById('isFeatured').checked,
            discount: 0
        };

        this.sims.push(newSim);
        this.saveData();
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('addSimModal'));
        modal.hide();
        
        this.showNotification('Đã thêm SIM thành công!', 'success');
        this.loadSimsTable();
    }

    editSim(simId) {
        const sim = this.sims.find(s => s.id === simId);
        if (sim) {
            // Fill form with sim data
            document.getElementById('simNumber').value = sim.number;
            document.getElementById('simType').value = sim.type;
            document.getElementById('simPrice').value = sim.price;
            document.getElementById('originalPrice').value = sim.originalPrice;
            document.getElementById('simStatus').value = sim.status;
            document.getElementById('simDescription').value = sim.description;
            document.getElementById('isFeatured').checked = sim.isFeatured;
            
            this.addSim();
        }
    }

    deleteSim(simId) {
        if (confirm('Bạn có chắc chắn muốn xóa SIM này?')) {
            this.sims = this.sims.filter(s => s.id !== simId);
            this.saveData();
            this.showNotification('Đã xóa SIM thành công!', 'success');
            this.loadSimsTable();
        }
    }

    editCustomer(customerId) {
        this.showNotification('Tính năng đang được phát triển!', 'info');
    }

    deleteCustomer(customerId) {
        if (confirm('Bạn có chắc chắn muốn xóa khách hàng này?')) {
            this.users = this.users.filter(u => u.id !== customerId);
            this.saveData();
            this.showNotification('Đã xóa khách hàng thành công!', 'success');
            this.loadCustomersTable();
        }
    }

    exportData() {
        this.showNotification('Tính năng đang được phát triển!', 'info');
    }

    viewProfile() {
        this.showNotification('Tính năng đang được phát triển!', 'info');
    }

    logout() {
        if (confirm('Bạn có chắc chắn muốn đăng xuất?')) {
            localStorage.removeItem('currentUser');
            window.location.href = 'index.html';
        }
    }

    getStatusBadgeClass(status) {
        const classes = {
            'available': 'bg-success',
            'sold': 'bg-primary',
            'expired': 'bg-danger'
        };
        return classes[status] || 'bg-secondary';
    }

    getStatusText(status) {
        const texts = {
            'available': 'Còn hàng',
            'sold': 'Đã bán',
            'expired': 'Hết hạn'
        };
        return texts[status] || status;
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

// Initialize admin manager
document.addEventListener('DOMContentLoaded', function() {
    window.adminManager = new AdminManager();
});
