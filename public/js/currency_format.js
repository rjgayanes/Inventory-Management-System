// Format currency inputs with commas
function formatCurrencyInput(input) {
    // Remove non-digit characters
    let value = input.value.replace(/[^\d.]/g, '');
    
    // Format with commas
    if (value) {
        let parts = value.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        input.value = parts.join('.');
    }
}

// Add event listeners to currency inputs
document.getElementById('unit_cost').addEventListener('blur', function() {
    formatCurrencyInput(this);
    calculateTotalCost();
});

document.getElementById('unit_total_cost').addEventListener('blur', function() {
    formatCurrencyInput(this);
});

// Parse formatted currency back to number for calculations
function parseCurrencyValue(formattedValue) {
    return parseFloat(formattedValue.replace(/,/g, ''));
}

// Update your calculateTotalCost function
function calculateTotalCost() {
    const quantity = parseFloat(document.getElementById('unit_quantity').value) || 0;
    const unitCost = parseCurrencyValue(document.getElementById('unit_cost').value) || 0;
    const totalCost = quantity * unitCost;
    
    // Format the total cost with commas
    document.getElementById('unit_total_cost').value = totalCost.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}