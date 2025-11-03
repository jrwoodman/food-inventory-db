/**
 * USDA Nutrition Lookup Functionality
 * Handles nutrition information popup for foods and ingredients
 */

// Show nutrition modal for an item
function showNutritionInfo(itemName) {
    // Create modal if it doesn't exist
    let modal = document.getElementById('nutrition-modal');
    if (!modal) {
        modal = createNutritionModal();
        document.body.appendChild(modal);
    }
    
    // Show modal and loading state
    modal.style.display = 'flex';
    document.getElementById('nutrition-content').innerHTML = '<div style="text-align: center; padding: 2rem;"><div class="spinner"></div><p>Searching USDA database for "' + escapeHtml(itemName) + '"...</p></div>';
    
    // Fetch nutrition info from USDA
    fetch('index.php?action=get_nutrition_info&name=' + encodeURIComponent(itemName))
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                showError(data.error);
                return;
            }
            
            if (!data.results || data.results.length === 0) {
                showError('No nutrition data found for "' + escapeHtml(itemName) + '"');
                return;
            }
            
            displaySearchResults(data.results, data.using_demo_key);
        })
        .catch(error => {
            console.error('Error fetching nutrition data:', error);
            showError('Failed to fetch nutrition data. Please try again.');
        });
}

// Display search results for user to select
function displaySearchResults(results, usingDemoKey) {
    const content = document.getElementById('nutrition-content');
    let html = '';
    
    // Show API key warning if using demo key
    if (usingDemoKey) {
        html += `
            <div class="alert alert-warning" style="background: #fef3c7; border: 1px solid #fbbf24; color: #92400e; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                <strong>⚠️ Limited API Access</strong><br>
                <span style="font-size: 0.875rem;">You're using the DEMO_KEY with limited access (30 requests/hour, 50/day).</span><br>
                <a href="https://fdc.nal.usda.gov/api-key-signup.html" target="_blank" style="color: #92400e; text-decoration: underline;">Get your free API key</a> for 1,000 requests/hour.
            </div>
        `;
    }
    
    html += '<h3 style="margin-top: 0;">Select a matching food item:</h3><div class="nutrition-results">';
    
    results.forEach(result => {
        const description = result.description || 'Unknown';
        const brand = result.brandOwner ? ' - ' + result.brandOwner : '';
        const dataType = result.dataType || '';
        
        html += `
            <div class="nutrition-result-item" onclick="loadNutritionDetails(${result.fdcId})">
                <div class="nutrition-result-name">${escapeHtml(description)}${escapeHtml(brand)}</div>
                <div class="nutrition-result-type">${escapeHtml(dataType)}</div>
            </div>
        `;
    });
    
    html += '</div>';
    content.innerHTML = html;
}

// Load detailed nutrition information for selected food
function loadNutritionDetails(fdcId) {
    const content = document.getElementById('nutrition-content');
    content.innerHTML = '<div style="text-align: center; padding: 2rem;"><div class="spinner"></div><p>Loading nutrition details...</p></div>';
    
    fetch('index.php?action=get_nutrition_details&fdc_id=' + fdcId)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                showError(data.error);
                return;
            }
            
            displayNutritionFacts(data.nutrition, data.using_demo_key);
        })
        .catch(error => {
            console.error('Error fetching nutrition details:', error);
            showError('Failed to fetch nutrition details. Please try again.');
        });
}

// Display nutrition facts label
function displayNutritionFacts(nutrition, usingDemoKey) {
    const content = document.getElementById('nutrition-content');
    
    let html = '';
    
    // Show API key warning if using demo key
    if (usingDemoKey) {
        html += `
            <div class="alert alert-warning" style="background: #fef3c7; border: 1px solid #fbbf24; color: #92400e; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                <strong>⚠️ Limited API Access</strong><br>
                <span style="font-size: 0.875rem;">You're using the DEMO_KEY with limited access (30 requests/hour, 50/day).</span><br>
                <a href="https://fdc.nal.usda.gov/api-key-signup.html" target="_blank" style="color: #92400e; text-decoration: underline;">Get your free API key</a> for 1,000 requests/hour.
            </div>
        `;
    }
    
    html += `
        <div class="nutrition-facts">
            <h2 class="nutrition-title">Nutrition Facts</h2>
            <div class="nutrition-food-name">${escapeHtml(nutrition.name)}</div>
    `;
    
    if (nutrition.brand) {
        html += `<div class="nutrition-brand">Brand: ${escapeHtml(nutrition.brand)}</div>`;
    }
    
    if (nutrition.serving_size && nutrition.serving_unit) {
        html += `<div class="nutrition-serving">Serving Size: ${nutrition.serving_size} ${escapeHtml(nutrition.serving_unit)}</div>`;
    }
    
    html += '<hr class="nutrition-divider">';
    
    // Main nutrients
    const nutrients = nutrition.nutrients || {};
    const mainNutrients = ['Energy', 'Total lipid (fat)', 'Carbohydrate, by difference', 'Protein'];
    const otherNutrients = ['Fiber, total dietary', 'Sugars, total including NLEA', 'Cholesterol', 'Fatty acids, total saturated', 'Sodium, Na'];
    const vitamins = ['Vitamin D (D2 + D3)', 'Calcium, Ca', 'Iron, Fe', 'Potassium, K'];
    
    // Display main nutrients
    mainNutrients.forEach(key => {
        if (nutrients[key]) {
            const label = key === 'Energy' ? 'Calories' : key.replace(', by difference', '').replace('Total lipid (fat)', 'Fat');
            html += `
                <div class="nutrition-row">
                    <span class="nutrition-label">${label}</span>
                    <span class="nutrition-value">${nutrients[key].value} ${nutrients[key].unit}</span>
                </div>
            `;
        }
    });
    
    html += '<hr class="nutrition-divider-thin">';
    
    // Other nutrients
    otherNutrients.forEach(key => {
        if (nutrients[key]) {
            const label = key.replace(', total dietary', '').replace(', total including NLEA', '').replace(', total saturated', ' (saturated)').replace(', Na', '');
            html += `
                <div class="nutrition-row nutrition-row-sub">
                    <span class="nutrition-label">${label}</span>
                    <span class="nutrition-value">${nutrients[key].value} ${nutrients[key].unit}</span>
                </div>
            `;
        }
    });
    
    html += '<hr class="nutrition-divider-thin">';
    
    // Vitamins and minerals
    vitamins.forEach(key => {
        if (nutrients[key]) {
            const label = key.replace(' (D2 + D3)', '').replace(', Ca', '').replace(', Fe', '').replace(', K', '');
            html += `
                <div class="nutrition-row nutrition-row-sub">
                    <span class="nutrition-label">${label}</span>
                    <span class="nutrition-value">${nutrients[key].value} ${nutrients[key].unit}</span>
                </div>
            `;
        }
    });
    
    html += '</div>';
    html += '<p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 1rem; text-align: center;">Data from USDA FoodData Central</p>';
    
    content.innerHTML = html;
}

// Show error message
function showError(message) {
    const content = document.getElementById('nutrition-content');
    content.innerHTML = `
        <div class="alert alert-error" style="margin: 2rem;">
            ${escapeHtml(message)}
        </div>
    `;
}

// Close nutrition modal
function closeNutritionModal() {
    const modal = document.getElementById('nutrition-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Create nutrition modal HTML
function createNutritionModal() {
    const modal = document.createElement('div');
    modal.id = 'nutrition-modal';
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content nutrition-modal-content">
            <span class="close" onclick="closeNutritionModal()">&times;</span>
            <div id="nutrition-content"></div>
        </div>
    `;
    
    // Close modal when clicking outside
    modal.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeNutritionModal();
        }
    });
    
    return modal;
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Add click handlers to item names
document.addEventListener('DOMContentLoaded', function() {
    // Add nutrition icon next to food and ingredient names
    addNutritionIcons();
});

function addNutritionIcons() {
    // Add icons to dashboard tables
    const foodRows = document.querySelectorAll('#foods-table tbody tr');
    const ingredientRows = document.querySelectorAll('#ingredients-table tbody tr');
    
    addIconsToRows(foodRows);
    addIconsToRows(ingredientRows);
}

function addIconsToRows(rows) {
    const linkMode = window.NUTRITION_LINK_MODE || 'icon';
    
    rows.forEach(row => {
        const nameCell = row.querySelector('td:first-child');
        if (nameCell) {
            const itemName = nameCell.textContent.trim();
            if (itemName && itemName !== 'No food items found.' && itemName !== 'No ingredients found.') {
                
                if (linkMode === 'name') {
                    // Make the entire name cell clickable
                    nameCell.style.cursor = 'pointer';
                    nameCell.style.color = 'var(--accent-primary)';
                    nameCell.title = 'Click to view nutrition information';
                    nameCell.onclick = function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        showNutritionInfo(itemName);
                    };
                    nameCell.onmouseover = function() {
                        this.style.textDecoration = 'underline';
                    };
                    nameCell.onmouseout = function() {
                        this.style.textDecoration = 'none';
                    };
                } else {
                    // Default: add icon next to name
                    const icon = document.createElement('span');
                    icon.className = 'nutrition-icon';
                    icon.innerHTML = ' 🥗';
                    icon.title = 'View nutrition information';
                    icon.style.cursor = 'pointer';
                    icon.style.marginLeft = '0.5rem';
                    icon.onclick = function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        showNutritionInfo(itemName);
                    };
                    nameCell.appendChild(icon);
                }
            }
        }
    });
}
