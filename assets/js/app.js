// Food & Ingredient Inventory Application
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the application
    initializeApp();
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

// Export functions for potential use in other scripts
window.InventoryApp = {
    fetchData,
    postData,
    setupSearch,
    sortTable
};