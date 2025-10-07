//commpute total cost based on quantity and unit cost
document.addEventListener("DOMContentLoaded", function() {
const qty = document.getElementById("unit_quantity");
const cost = document.getElementById("unit_cost");
const uom = document.getElementById("unit_of_measure");
const total = document.getElementById("unit_total_cost");

function calculateTotal() {
    const q = parseFloat(qty.value) || 0;
    const c = parseFloat(cost.value) || 0;
    const selectedUOM = uom.value.trim().toLowerCase();

    // UOMs where quantity should be ignored (total cost = unit cost)
    const nonMultiplyingUOMs = [ "sq. m", "sq. ft", "sq. yd",
            "m³", "ft³", "yd³", "kg", "g", "MT", "lb", "L", "mL", "gal",
            "m", "cm", "mm", "in", "ft", "yd"];

    if (!c) {
        total.value = ""; // reset if cost is empty
        return;
    }

    if (nonMultiplyingUOMs.includes(selectedUOM)) {
        total.value = c.toFixed(2);
    } else {
        if (!q) {
            total.value = ""; // reset if quantity is empty
            return;
        }
        total.value = (q * c).toFixed(2);
    }
}

qty.addEventListener("input", calculateTotal);
cost.addEventListener("input", calculateTotal);
uom.addEventListener("change", calculateTotal);
});