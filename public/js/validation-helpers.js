/**
 * Global Validation Helper Functions
 * Provides reusable validation and input manipulation utilities
 */

/**
 * Prevents wheel/scroll from changing numeric input values
 * Fixes unwanted increments when scrolling over number inputs
 * 
 * @param {HTMLElement} element - The number input element
 */
function preventNumberWheelChange(element) {
    if (!element) return;
    
    element.addEventListener('wheel', function(e) {
        e.preventDefault();
        return false;
    }, { passive: false });
}

/**
 * Applies wheel-scroll prevention to all number inputs in a selector
 * 
 * @param {string} selector - CSS selector for inputs (default: 'input[type="number"]')
 */
function applyWheelPreventionToAll(selector = 'input[type="number"]') {
    const inputs = document.querySelectorAll(selector);
    inputs.forEach(input => {
        preventNumberWheelChange(input);
    });
}

/**
 * Prevents decimal input for a number field (integers only)
 * Strips any decimal point before it's entered
 * 
 * @param {HTMLElement} element - The input element
 */
function enforceIntegerOnly(element) {
    if (!element) return;
    
    element.addEventListener('keypress', function(e) {
        // Allow: backspace, delete, tab, escape, enter
        if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
            // Allow: Ctrl+C, Ctrl+V, Ctrl+X, Ctrl+A
            (e.keyCode === 65 && e.ctrlKey === true) ||
            (e.keyCode === 67 && e.ctrlKey === true) ||
            (e.keyCode === 86 && e.ctrlKey === true) ||
            (e.keyCode === 88 && e.ctrlKey === true)) {
            return;
        }
        
        // Prevent decimal point and other non-numeric characters
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
            return false;
        }
    });
    
    // Clean non-numeric on input
    element.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^\d]/g, '');
    });
    
    // Prevent wheel increment
    preventNumberWheelChange(element);
    
    // Prevent arrow keys from incrementing
    element.addEventListener('keydown', function(e) {
        if (e.keyCode === 38 || e.keyCode === 40) { // Up/Down arrows
            e.preventDefault();
            return false;
        }
    });
}

/**
 * Validates a numeric range (min/max)
 * 
 * @param {number|string} value - The value to validate
 * @param {number} min - Minimum allowed value
 * @param {number} max - Maximum allowed value
 * @returns {boolean} True if valid, false otherwise
 */
function validateNumericRange(value, min = 0, max = 999999.99) {
    const num = parseFloat(value);
    return !isNaN(num) && num >= min && num <= max;
}

/**
 * Validates Pakistani phone number format
 * Accepts: 03001234567, 0300-1234567, +923001234567, +92-300-1234567
 * 
 * @param {string} phone - The phone number to validate
 * @returns {boolean} True if valid Pakistani phone, false otherwise
 */
function validatePakistaniPhone(phone) {
    if (!phone) return false;
    
    // Remove common formatting characters
    const cleaned = phone.replace(/[\s\-\.\(\)]/g, '');
    
    const patterns = [
        /^03\d{9}$/,         // 03001234567
        /^\+923\d{9}$/,      // +923001234567
        /^0\d{10}$/,         // 0XXXXXXXXXX
    ];
    
    return patterns.some(pattern => pattern.test(cleaned));
}

/**
 * Validates Pakistani CNIC format
 * Format: XXXXX-XXXXXXX-X (e.g., 12345-1234567-1)
 * 
 * @param {string} cnic - The CNIC number to validate
 * @returns {boolean} True if valid format, false otherwise
 */
function validatePakistaniCNIC(cnic) {
    if (!cnic) return false;
    return /^\d{5}-\d{7}-\d{1}$/.test(cnic.trim());
}

/**
 * Validates amount/decimal field
 * Ensures numeric value within range
 * 
 * @param {number|string} amount - The amount to validate
 * @param {number} max - Maximum allowed amount
 * @param {number} min - Minimum allowed amount
 * @returns {boolean} True if valid, false otherwise
 */
function validateAmount(amount, max = 999999.99, min = 0) {
    return validateNumericRange(amount, min, max);
}

/**
 * Validates date range (from_date <= to_date)
 * 
 * @param {string} fromDate - Start date (YYYY-MM-DD format)
 * @param {string} toDate - End date (YYYY-MM-DD format)
 * @returns {boolean} True if fromDate <= toDate, false otherwise
 */
function validateDateRange(fromDate, toDate) {
    if (!fromDate || !toDate) return true; // Allow empty dates
    
    const from = new Date(fromDate);
    const to = new Date(toDate);
    
    return from <= to;
}

/**
 * Formats currency display (removes .00 for whole numbers)
 * 
 * @param {number|string} amount - The amount to format
 * @param {string} currency - Currency symbol (default: 'Rs.')
 * @returns {string} Formatted amount (e.g., "Rs. 12,350")
 */
function formatCurrency(amount, currency = 'Rs.') {
    const num = parseFloat(amount);
    if (isNaN(num)) return `${currency} 0`;
    
    const whole = Math.round(num);
    return `${currency} ${whole.toLocaleString('en-PK')}`;
}

/**
 * Initialize all validation helpers on page load
 * Call this in your main layout or use DOMContentLoaded event
 */
function initializeValidationHelpers() {
    // Apply wheel prevention to all number inputs
    applyWheelPreventionToAll('input[type="number"]');
    
    // Apply integer enforcement to paid amount fields
    document.querySelectorAll('.integer-only').forEach(element => {
        enforceIntegerOnly(element);
    });
}

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeValidationHelpers);
} else {
    initializeValidationHelpers();
}
