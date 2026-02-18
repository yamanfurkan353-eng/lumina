/**
 * HOTEL MASTER LITE v1.0
 * Main Application JavaScript
 * Chart.js, FullCalendar, PDF Export & Dark Mode Tema
 */

// ========================================
// GLOBAL APP STATE
// ========================================
const app = {
    user: null,
    isAuthenticated: false,
    csrfToken: null,
    baseURL: '/api',
    
    // Initialize app
    async init() {
        console.log('🏨 Hotel Master Lite v1.0 başlatılıyor...');
        
        // Check authentication
        await this.checkAuth();
        
        if (!this.isAuthenticated) {
            // Redirect to login if not authenticated
            if (!window.location.pathname.includes('login') && !window.location.pathname.includes('setup')) {
                window.location.href = '/login.html';
            }
            return;
        }
        
        // Initialize app UI
        this.setupTheme();
        this.setupNavigation();
        this.setupEventListeners();
        
        console.log('✅ Uygulama başlatıldı');
    },
    
    async checkAuth() {
        try {
            const response = await fetch(`${this.baseURL}/auth/user`);
            
            if (response.ok) {
                const data = await response.json();
                this.user = data.data.user;
                this.csrfToken = data.data.csrf_token;
                this.isAuthenticated = true;
                document.body.classList.add('authenticated');
            }
        } catch (error) {
            console.error('Auth kontrol hatası:', error);
            this.isAuthenticated = false;
        }
    },
    
    // Setup dark mode theme
    setupTheme() {
        // Add theme toggle button
        if (!document.querySelector('.theme-toggle')) {
            const toggleBtn = document.createElement('button');
            toggleBtn.className = 'theme-toggle';
            toggleBtn.innerHTML = '🌙';
            toggleBtn.title = 'Tema Değiştir';
            toggleBtn.addEventListener('click', () => this.toggleTheme());
            document.body.appendChild(toggleBtn);
        }
        
        // Load saved theme preference
        const savedTheme = localStorage.getItem('theme-mode');
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
            const toggleBtn = document.querySelector('.theme-toggle');
            if (toggleBtn) toggleBtn.innerHTML = '☀️';
        }
        
        // Or use system preference
        if (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.body.classList.add('dark-mode');
        }
    },
    
    // Toggle dark/light theme
    toggleTheme() {
        const isDark = document.body.classList.toggle('dark-mode');
        localStorage.setItem('theme-mode', isDark ? 'dark' : 'light');
        
        const toggleBtn = document.querySelector('.theme-toggle');
        if (toggleBtn) {
            toggleBtn.innerHTML = isDark ? '☀️' : '🌙';
        }
    },
    
    setupNavigation() {
        const userNameEl = document.getElementById('userName');
        const logoutBtn = document.getElementById('logoutBtn');
        
        if (userNameEl && this.user) {
            userNameEl.textContent = `👤 ${this.user.name}`;
        }
        
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => this.logout());
        }
    },
    
    setupEventListeners() {
        // Setup any global event listeners here
    },
    
    async logout() {
        try {
            await fetch(`${this.baseURL}/auth/logout`, {method: 'POST'});
            window.location.href = '/login.html';
        } catch (error) {
            console.error('Logout hatası:', error);
        }
    },
    
    formatCurrency(amount) {
        return new Intl.NumberFormat('tr-TR', {
            style: 'currency',
            currency: 'TRY',
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        }).format(amount);
    },
    
    formatDate(dateStr) {
        return new Date(dateStr).toLocaleDateString('tr-TR', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric'
        });
    }
};

// ========================================
// CHART.JS HELPER
// ========================================
const ChartHelper = {
    // Wait for Chart.js library to load
    waitForChartJS(callback, attempts = 0) {
        if (typeof Chart !== 'undefined') {
            callback();
        } else if (attempts < 20) {
            setTimeout(() => this.waitForChartJS(callback, attempts + 1), 100);
        }
    },
    
    // Revenue line chart
    createRevenueChart(elementId, data) {
        this.waitForChartJS(() => {
            const ctx = document.getElementById(elementId);
            if (!ctx) return;
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels || [],
                    datasets: [{
                        label: 'Aylık Gelir (₺)',
                        data: data.values || [],
                        borderColor: '#C4886C',
                        backgroundColor: 'rgba(196, 136, 108, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        pointBackgroundColor: '#C4886C',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: true,
                            labels: {
                                color: '#6B7280',
                                font: { size: 12, weight: 'bold' },
                                usePointStyle: true,
                                padding: 15
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(229, 231, 235, 0.3)'
                            },
                            ticks: {
                                color: '#6B7280',
                                callback: function(value) {
                                    return '₺' + value.toLocaleString('tr-TR');
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#6B7280'
                            }
                        }
                    }
                }
            });
        });
    },
    
    // Occupancy pie chart
    createOccupancyChart(elementId, occupied, available) {
        this.waitForChartJS(() => {
            const ctx = document.getElementById(elementId);
            if (!ctx) return;
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Dolu Odalar', 'Müsait Odalar'],
                    datasets: [{
                        data: [occupied, available],
                        backgroundColor: [
                            '#C4886C',
                            '#E8D5C4'
                        ],
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#6B7280',
                                font: { size: 12 },
                                padding: 20,
                                usePointStyle: true
                            }
                        }
                    }
                }
            });
        });
    }
};

// ========================================
// FULLCALENDAR HELPER
// ========================================
const CalendarHelper = {
    // Wait for FullCalendar library to load
    waitForCalendar(callback, attempts = 0) {
        if (typeof FullCalendar !== 'undefined') {
            callback();
        } else if (attempts < 20) {
            setTimeout(() => this.waitForCalendar(callback, attempts + 1), 100);
        }
    },
    
    // Initialize interactive calendar
    initCalendar(elementId, events = [], onEventClick = null) {
        this.waitForCalendar(() => {
            const calendarEl = document.getElementById(elementId);
            if (!calendarEl) return;
            
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                locale: 'tr',
                events: events,
                eventClick: function(info) {
                    if (onEventClick) {
                        onEventClick(info.event);
                    }
                },
                eventColor: '#C4886C',
                eventTextColor: '#fff',
                buttonText: {
                    today: 'Bugün',
                    month: 'Ay',
                    week: 'Hafta',
                    day: 'Gün'
                }
            });
            
            calendar.render();
            return calendar;
        });
    }
};

// ========================================
// PDF & EXPORT HELPER
// ========================================
const ExportHelper = {
    // Generate PDF invoice
    async generateInvoicePDF(reservationId) {
        try {
            UIHelper.showLoader('PDF oluşturuluyor...');
            const response = await fetch(`${app.baseURL}/export/reservation/${reservationId}/pdf`);
            
            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = `Fatura-${reservationId}.pdf`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(url);
                UIHelper.hideLoader();
                UIHelper.showMessage('PDF indirildi', 'success');
            } else {
                UIHelper.hideLoader();
                UIHelper.showMessage('PDF oluşturulamadı', 'error');
            }
        } catch (error) {
            UIHelper.hideLoader();
            console.error('PDF hatası:', error);
            UIHelper.showMessage('PDF oluşturma hatası', 'error');
        }
    },
    
    // Print element
    printElement(elementId) {
        const element = document.getElementById(elementId);
        if (!element) return;
        
        const printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Yazdır</title>
                <link rel="stylesheet" href="/css/style.css">
            </head>
            <body>
                ${element.innerHTML}
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }
};

// ========================================
// API HELPER
// ========================================
const APIHelper = {
    // Make API call
    async call(endpoint, method = 'GET', data = null) {
        try {
            const options = {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };
            
            if (data) {
                options.body = JSON.stringify(data);
            }
            
            const response = await fetch(app.baseURL + endpoint, options);
            return await response.json();
        } catch (error) {
            console.error('API hatası:', error);
            return { status: 'error', message: error.message };
        }
    },
    
    get(endpoint) { return this.call(endpoint, 'GET'); },
    post(endpoint, data) { return this.call(endpoint, 'POST', data); },
    put(endpoint, data) { return this.call(endpoint, 'PUT', data); },
    delete(endpoint) { return this.call(endpoint, 'DELETE'); }
};

// ========================================
// UI HELPER
// ========================================
const UIHelper = {
    // Show message notification
    showMessage(message, type = 'info') {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `<p>${message}</p>`;
        alert.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
            animation: slideIn 0.3s ease-out;
        `;
        
        document.body.appendChild(alert);
        setTimeout(() => alert.remove(), 4000);
    },
    
    // Show loader
    showLoader(text = 'Yükleniyor...') {
        const loader = document.createElement('div');
        loader.className = 'loader';
        loader.innerHTML = `
            <div style="text-align: center;">
                <span style="display: inline-block; font-size: 32px; animation: spin 1s linear infinite;">⏳</span>
                <p>${text}</p>
            </div>
        `;
        loader.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9998;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 30px;
            border-radius: 12px;
        `;
        
        document.body.appendChild(loader);
        return loader;
    },
    
    // Hide loader
    hideLoader() {
        const loader = document.querySelector('.loader');
        if (loader) loader.remove();
    },
    
    // Show empty state
    showEmptyState(container, icon = '📭', title = 'Veri bulunamadı', message = '') {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon" style="font-size: 64px; margin-bottom: 20px;">${icon}</div>
                <h3 style="color: #6B7280; margin-bottom: 10px;">${title}</h3>
                ${message ? `<p style="color: #9CA3AF;">${message}</p>` : ''}
            </div>
        `;
    }
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => app.init());
