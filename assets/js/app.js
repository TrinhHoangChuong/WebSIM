// Sim Thăng Long Clone JavaScript
const API_BASE = 'api/';

// Utility Functions
function formatPhone(num) {
    const d = num.replace(/\D/g, '');
    return d.length === 10 ? d.slice(0,3) + '.' + d.slice(3,6) + '.' + d.slice(6,10) : num;
}

function formatPrice(p) {
    return new Intl.NumberFormat('vi-VN').format(p) + '₫';
}

function getNetworkClass(network) {
    const classes = {
        'Mobifone': 'network-mobifone',
        'Viettel': 'network-viettel',
        'Vinaphone': 'network-vinaphone',
        'Vietnamobile': 'network-vietnamobile',
        'iTelecom': 'network-itelecom'
    };
    return classes[network] || 'network-mobifone';
}

// API Functions
async function fetchSims(params = {}) {
    try {
        const queryParams = new URLSearchParams();
        Object.keys(params).forEach(key => {
            if (params[key] !== null && params[key] !== '') {
                queryParams.append(key, params[key]);
            }
        });
        
        const response = await fetch(`${API_BASE}sims?${queryParams}`);
        const data = await response.json();
        
        if (data.success) {
            return data.data;
        } else {
            throw new Error(data.error || 'Failed to fetch SIMs');
        }
    } catch (error) {
        console.error('Error fetching SIMs:', error);
        throw error;
    }
}

async function searchSims(searchTerm) {
    try {
        const response = await fetch(`${API_BASE}search?q=${encodeURIComponent(searchTerm)}`);
        const data = await response.json();
        
        if (data.success) {
            return data.data.sims;
        } else {
            throw new Error(data.error || 'Search failed');
        }
    } catch (error) {
        console.error('Error searching SIMs:', error);
        throw error;
    }
}

async function createOrder(orderData) {
    try {
        const response = await fetch(`${API_BASE}orders`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(orderData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            return data.data;
        } else {
            throw new Error(data.error || 'Order creation failed');
        }
    } catch (error) {
        console.error('Error creating order:', error);
        throw error;
    }
}

// SIM Card Rendering
function renderSimCard(sim) {
    return `
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="sim-card">
                <div class="network-badge ${getNetworkClass(sim.network)}">${sim.network.toUpperCase()}</div>
                <div class="sim-number">${formatPhone(sim.phone_number)}</div>
                <div class="sim-price">${formatPrice(sim.price)}</div>
                <div class="text-muted mb-3">${sim.sim_type || 'SIM thường'}</div>
                <button class="btn-buy" onclick="buySim(${sim.id})">
                    <i class="fas fa-shopping-cart me-1"></i>Mua ngay
                </button>
            </div>
        </div>
    `;
}

function renderSims(sims, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    if (sims.length === 0) {
        container.innerHTML = '<div class="col-12 text-center py-5"><h5>Không tìm thấy SIM nào</h5></div>';
        return;
    }
    
    container.innerHTML = sims.map(renderSimCard).join('');
}

// Pagination
function renderPagination(pagination, containerId, onPageChange) {
    const container = document.getElementById(containerId);
    if (!container || pagination.total_pages <= 1) {
        if (container) container.innerHTML = '';
        return;
    }
    
    let html = '<nav><ul class="pagination">';
    
    // Previous button
    if (pagination.current_page > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="${onPageChange}(${pagination.current_page - 1}); return false;">Trước</a></li>`;
    }
    
    // Page numbers
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);
    
    if (startPage > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="${onPageChange}(1); return false;">1</a></li>`;
        if (startPage > 2) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        if (i === pagination.current_page) {
            html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="${onPageChange}(${i}); return false;">${i}</a></li>`;
        }
    }
    
    if (endPage < pagination.total_pages) {
        if (endPage < pagination.total_pages - 1) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        html += `<li class="page-item"><a class="page-link" href="#" onclick="${onPageChange}(${pagination.total_pages}); return false;">${pagination.total_pages}</a></li>`;
    }
    
    // Next button
    if (pagination.current_page < pagination.total_pages) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="${onPageChange}(${pagination.current_page + 1}); return false;">Sau</a></li>`;
    }
    
    html += '</ul></nav>';
    container.innerHTML = html;
}

// Filter Functions
function filterByPrice(min, max) {
    const params = {
        min_price: min,
        max_price: max,
        page: 1,
        limit: 12
    };
    
    loadSimsWithParams(params);
}

function filterByNetwork(network) {
    const params = {
        network: network,
        page: 1,
        limit: 12
    };
    
    loadSimsWithParams(params);
}

function filterByType(type) {
    const params = {
        sim_type: type,
        page: 1,
        limit: 12
    };
    
    loadSimsWithParams(params);
}

// Loading Functions
async function loadSimsWithParams(params) {
    const spinner = document.getElementById('loadingSpinner');
    const results = document.getElementById('simResults');
    
    if (spinner) spinner.style.display = 'block';
    if (results) results.innerHTML = '';
    
    try {
        const data = await fetchSims(params);
        if (results) {
            renderSims(data.sims, 'simResults');
        }
        
        const pagination = document.getElementById('pagination');
        if (pagination) {
            renderPagination(data.pagination, 'pagination', 'loadSimsWithParams');
        }
    } catch (error) {
        if (results) {
            results.innerHTML = '<div class="col-12 text-center py-5"><h5>Lỗi tải dữ liệu</h5></div>';
        }
    } finally {
        if (spinner) spinner.style.display = 'none';
    }
}

// Buy SIM Function
function buySim(simId) {
    // Show order modal or redirect to order page
    const orderData = {
        sim_id: simId,
        customer_name: prompt('Nhập họ tên:') || '',
        customer_phone: prompt('Nhập số điện thoại:') || '',
        customer_email: prompt('Nhập email (tùy chọn):') || '',
        payment_method: 'cash',
        shipping_address: prompt('Nhập địa chỉ giao hàng:') || ''
    };
    
    if (!orderData.customer_name || !orderData.customer_phone) {
        alert('Vui lòng nhập đầy đủ thông tin!');
        return;
    }
    
    createOrder(orderData)
        .then(result => {
            alert(`Đặt hàng thành công! Mã đơn hàng: ${result.order_id}`);
        })
        .catch(error => {
            alert('Lỗi đặt hàng: ' + error.message);
        });
}

// Search Function
function searchSims() {
    const searchTerm = document.getElementById('searchInput').value;
    if (!searchTerm.trim()) {
        alert('Vui lòng nhập từ khóa tìm kiếm!');
        return;
    }
    
    const params = {
        q: searchTerm,
        page: 1,
        limit: 12
    };
    
    loadSimsWithParams(params);
}

// Modal Functions
function showLoginModal() {
    // Implement login modal
    const modal = new bootstrap.Modal(document.getElementById('loginModal'));
    modal.show();
}

function showRegisterModal() {
    // Implement register modal
    const modal = new bootstrap.Modal(document.getElementById('registerModal'));
    modal.show();
}

// Initialize functions
function initializePage() {
    // Add event listeners
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchSims();
            }
        });
    }
    
    // Load initial data
    loadSimsWithParams({ page: 1, limit: 12 });
}

// Export functions for global use
window.formatPhone = formatPhone;
window.formatPrice = formatPrice;
window.getNetworkClass = getNetworkClass;
window.renderSimCard = renderSimCard;
window.renderSims = renderSims;
window.filterByPrice = filterByPrice;
window.filterByNetwork = filterByNetwork;
window.filterByType = filterByType;
window.searchSims = searchSims;
window.buySim = buySim;
window.showLoginModal = showLoginModal;
window.showRegisterModal = showRegisterModal;
window.initializePage = initializePage;
