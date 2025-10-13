// Food & Ingredient Inventory Application
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the application
    initializeApp();
    
    // Initialize theme toggle
    initializeTheme();
});

function initializeApp() {
    // Set up form validations
    setupFormValidations();
    
    // Set up table interactions
    setupTableInteractions();
    
    // Set up date defaults
    setupDateDefaults();
    
    // Auto-hide alerts after 5 seconds
    autoHideAlerts();
    
    // Set up search functionality if search inputs exist
    if (document.querySelectorAll('.search-input').length > 0) {
        setupSearch();
    }
}

function setupFormValidations() {
    const forms = document.querySelectorAll('.add-form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            isValid = false;
        } else {
            clearFieldError(field);
        }
    });
    
    // Validate quantity is positive
    const quantityField = form.querySelector('[name="quantity"]');
    if (quantityField && parseFloat(quantityField.value) <= 0) {
        showFieldError(quantityField, 'Quantity must be greater than 0');
        isValid = false;
    }
    
    // Validate cost is not negative
    const costField = form.querySelector('[name="cost_per_unit"]');
    if (costField && costField.value && parseFloat(costField.value) < 0) {
        showFieldError(costField, 'Cost cannot be negative');
        isValid = false;
    }
    
    // Validate expiry date is not in the past (but allow it for existing items)
    const expiryField = form.querySelector('[name="expiry_date"]');
    if (expiryField && expiryField.value) {
        const expiryDate = new Date(expiryField.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (expiryDate < today) {
            const confirmed = confirm('The expiry date is in the past. Do you want to continue?');
            if (!confirmed) {
                isValid = false;
            }
        }
    }
    
    return isValid;
}

function showFieldError(field, message) {
    clearFieldError(field);
    
    const errorElement = document.createElement('div');
    errorElement.className = 'field-error';
    errorElement.textContent = message;
    errorElement.style.color = '#e74c3c';
    errorElement.style.fontSize = '12px';
    errorElement.style.marginTop = '5px';
    
    field.style.borderColor = '#e74c3c';
    field.parentNode.appendChild(errorElement);
}

function clearFieldError(field) {
    field.style.borderColor = '';
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}

function setupTableInteractions() {
    // Add sorting capability to tables
    const tables = document.querySelectorAll('.inventory-table');
    
    tables.forEach(table => {
        const headers = table.querySelectorAll('th');
        headers.forEach((header, index) => {
            if (index < headers.length - 1) { // Don't make Actions column sortable
                header.style.cursor = 'pointer';
                header.title = 'Click to sort';
                header.addEventListener('click', () => sortTable(table, index));
            }
        });
    });
}

function sortTable(table, columnIndex) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    // Skip if no data rows
    if (rows.length === 0 || rows[0].cells[0].textContent.includes('No')) return;
    
    const isNumeric = isColumnNumeric(rows, columnIndex);
    const isDate = isColumnDate(rows, columnIndex);
    
    rows.sort((a, b) => {
        let aVal = a.cells[columnIndex].textContent.trim();
        let bVal = b.cells[columnIndex].textContent.trim();
        
        if (isNumeric) {
            aVal = parseFloat(aVal.replace(/[^0-9.-]/g, '')) || 0;
            bVal = parseFloat(bVal.replace(/[^0-9.-]/g, '')) || 0;
            return aVal - bVal;
        } else if (isDate) {
            aVal = new Date(aVal === 'N/A' ? '1900-01-01' : aVal);
            bVal = new Date(bVal === 'N/A' ? '1900-01-01' : bVal);
            return aVal - bVal;
        } else {
            return aVal.localeCompare(bVal);
        }
    });
    
    // Re-append sorted rows
    rows.forEach(row => tbody.appendChild(row));
}

function isColumnNumeric(rows, columnIndex) {
    const sample = rows.slice(0, 3);
    return sample.every(row => {
        const text = row.cells[columnIndex].textContent.trim();
        return /^[\d\s,.$-]+$/.test(text) && text !== 'N/A';
    });
}

function isColumnDate(rows, columnIndex) {
    const sample = rows.slice(0, 3);
    return sample.every(row => {
        const text = row.cells[columnIndex].textContent.trim();
        return text === 'N/A' || /^[A-Za-z]{3}\s\d{1,2},\s\d{4}$/.test(text);
    });
}

function setupDateDefaults() {
    // Set purchase date to today by default
    const purchaseDateFields = document.querySelectorAll('[name="purchase_date"]');
    purchaseDateFields.forEach(field => {
        if (!field.value) {
            field.value = new Date().toISOString().split('T')[0];
        }
    });
}

function autoHideAlerts() {
    const alerts = document.querySelectorAll('.alert-success');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 500);
        }, 5000);
    });
}

// Utility functions for API calls (if needed for future enhancements)
function fetchData(endpoint) {
    return fetch(endpoint)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            return null;
        });
}

function postData(endpoint, data) {
    return fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .catch(error => {
        console.error('Error posting data:', error);
        return null;
    });
}

// Search functionality (for future enhancement)
function setupSearch() {
    const searchInputs = document.querySelectorAll('.search-input');
    
    searchInputs.forEach(input => {
        input.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const table = this.closest('.card').querySelector('.inventory-table');
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    });
}

// Theme Management Functions
function initializeTheme() {
    // Check for saved theme preference or default to 'light'
    const savedTheme = localStorage.getItem('theme') || 'light';
    
    // Apply the theme
    applyTheme(savedTheme);
    
    // Set up theme toggle event listener
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        // Set initial state
        themeToggle.checked = savedTheme === 'dark';
        
        // Add event listener
        themeToggle.addEventListener('change', function() {
            const newTheme = this.checked ? 'dark' : 'light';
            applyTheme(newTheme);
            localStorage.setItem('theme', newTheme);
            
            // Add smooth transition for theme change
            document.documentElement.style.transition = 'background-color 0.3s ease, color 0.3s ease';
            setTimeout(() => {
                document.documentElement.style.transition = '';
            }, 300);
        });
    }
    
    // Listen for system theme changes
    if (window.matchMedia) {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        mediaQuery.addListener(handleSystemThemeChange);
    }
}

function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    
    // Update meta theme-color for mobile browsers
    const metaThemeColor = document.querySelector('meta[name="theme-color"]');
    if (metaThemeColor) {
        metaThemeColor.setAttribute('content', theme === 'dark' ? '#121212' : '#ffffff');
    }
    
    // Update favicon if different versions exist
    updateFavicon(theme);
}

function handleSystemThemeChange(e) {
    // Only apply system theme if no manual preference is saved
    if (!localStorage.getItem('theme')) {
        const systemTheme = e.matches ? 'dark' : 'light';
        applyTheme(systemTheme);
        
        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
            themeToggle.checked = systemTheme === 'dark';
        }
    }
}

function updateFavicon(theme) {
    const favicon = document.querySelector('link[rel="icon"]');
    if (favicon) {
        // You can add different favicons for light/dark themes here
        // favicon.href = theme === 'dark' ? '/favicon-dark.ico' : '/favicon-light.ico';
    }
}

function getSystemTheme() {
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        return 'dark';
    }
    return 'light';
}

// Enhanced form validation with theme-aware styling
function showFieldError(field, message) {
    clearFieldError(field);
    
    const errorElement = document.createElement('div');
    errorElement.className = 'field-error';
    errorElement.textContent = message;
    errorElement.style.color = 'var(--color-danger)';
    errorElement.style.fontSize = '12px';
    errorElement.style.marginTop = '5px';
    errorElement.style.fontWeight = '500';
    
    field.style.borderColor = 'var(--color-danger)';
    field.style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.1)';
    field.parentNode.appendChild(errorElement);
}

function clearFieldError(field) {
    field.style.borderColor = '';
    field.style.boxShadow = '';
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}

// Enhanced alert auto-hide with better animations
function autoHideAlerts() {
    const alerts = document.querySelectorAll('.alert-success, .alert-error, .alert-warning');
    alerts.forEach(alert => {
        // Add close button
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '&times;';
        closeBtn.className = 'alert-close';
        closeBtn.style.cssText = `
            background: none;
            border: none;
            font-size: 20px;
            font-weight: bold;
            color: currentColor;
            cursor: pointer;
            opacity: 0.7;
            margin-left: auto;
            padding: 0;
            line-height: 1;
        `;
        
        closeBtn.addEventListener('click', () => {
            hideAlert(alert);
        });
        
        alert.appendChild(closeBtn);
        
        // Auto-hide after 7 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                hideAlert(alert);
            }
        }, 7000);
    });
}

function hideAlert(alert) {
    alert.style.transition = 'all 0.3s ease';
    alert.style.opacity = '0';
    alert.style.transform = 'translateY(-20px)';
    alert.style.maxHeight = '0';
    alert.style.marginBottom = '0';
    alert.style.paddingTop = '0';
    alert.style.paddingBottom = '0';
    
    setTimeout(() => {
        if (alert.parentNode) {
            alert.parentNode.removeChild(alert);
        }
    }, 300);
}

// Enhanced table sorting with theme-aware indicators
function sortTable(table, columnIndex) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const header = table.querySelectorAll('th')[columnIndex];
    
    // Skip if no data rows
    if (rows.length === 0 || rows[0].cells[0].textContent.includes('No')) return;
    
    // Check current sort direction
    const currentDirection = header.getAttribute('data-sort-direction') || 'asc';
    const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
    
    // Clear all sort indicators
    table.querySelectorAll('th').forEach(th => {
        th.removeAttribute('data-sort-direction');
        th.style.position = 'relative';
        const indicator = th.querySelector('.sort-indicator');
        if (indicator) indicator.remove();
    });
    
    // Add sort indicator
    const indicator = document.createElement('span');
    indicator.className = 'sort-indicator';
    indicator.innerHTML = newDirection === 'asc' ? ' ↑' : ' ↓';
    indicator.style.cssText = `
        color: var(--color-primary);
        font-weight: bold;
        margin-left: 4px;
    `;
    header.appendChild(indicator);
    header.setAttribute('data-sort-direction', newDirection);
    
    const isNumeric = isColumnNumeric(rows, columnIndex);
    const isDate = isColumnDate(rows, columnIndex);
    
    rows.sort((a, b) => {
        let aVal = a.cells[columnIndex].textContent.trim();
        let bVal = b.cells[columnIndex].textContent.trim();
        
        let comparison = 0;
        
        if (isNumeric) {
            aVal = parseFloat(aVal.replace(/[^0-9.-]/g, '')) || 0;
            bVal = parseFloat(bVal.replace(/[^0-9.-]/g, '')) || 0;
            comparison = aVal - bVal;
        } else if (isDate) {
            aVal = new Date(aVal === 'N/A' ? '1900-01-01' : aVal);
            bVal = new Date(bVal === 'N/A' ? '1900-01-01' : bVal);
            comparison = aVal - bVal;
        } else {
            comparison = aVal.localeCompare(bVal);
        }
        
        return newDirection === 'desc' ? -comparison : comparison;
    });
    
    // Re-append sorted rows with animation
    rows.forEach((row, index) => {
        setTimeout(() => {
            tbody.appendChild(row);
        }, index * 10);
    });
}

// Export functions for potential use in other scripts
window.InventoryApp = {
    fetchData,
    postData,
    setupSearch,
    sortTable,
    applyTheme,
    initializeTheme
};
