// Enhanced search functionality for individual units with transfer capability
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById("searchInput");
    const resultsDiv = document.getElementById("results");
    const assignItemModal = new bootstrap.Modal(document.getElementById('assignItemModal'));
    let currentSelectedUnit = null;
    
    // Debounce function to limit API calls
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Function to search item units by barcode
    const searchItems = debounce(function(query) {
        if (query.length < 2) {
            resultsDiv.innerHTML = '';
            resultsDiv.style.display = 'none';
            return;
        }
        
        fetch(`../src/search_items.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.items.length > 0) {
                    displayResults(data.items);
                } else {
                    resultsDiv.innerHTML = '<div class="search-result-item">No items found</div>';
                    resultsDiv.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                resultsDiv.innerHTML = '<div class="search-result-item">Error searching items</div>';
                resultsDiv.style.display = 'block';
            });
    }, 300);
    
    // Display search results for individual units
    function displayResults(units) {
        resultsDiv.innerHTML = '';
        
        units.forEach(unit => {
            const resultItem = document.createElement('div');
            resultItem.className = 'search-result-item';
            
            let statusBadge = '';
            if (unit.status === 'Available') {
                statusBadge = '<span class="badge bg-success">Available</span>';
            } else if (unit.status === 'Assigned') {
                statusBadge = '<span class="badge bg-warning">Assigned - Click to Transfer</span>';
            } else if (unit.status === 'Unserviceable') {
                statusBadge = '<span class="badge bg-danger">Unserviceable</span>';
            }
            
            let html = `
                <div class="search-result-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong>${unit.item_name}</strong>
                        ${statusBadge}
                    </div>
                    <div>Barcode: ${unit.barcode}</div>
                    <div>Property #: ${unit.property_number || 'N/A'}</div>
                    <div>Description: ${unit.description || 'N/A'}</div>
            `;
            
            // Show current assignment if assigned
            if (unit.status === 'Assigned') {
                let assignedText = 'Currently assigned to: ';
                
                if (unit.assign_to_name && unit.assign_to_name.trim() !== '') {
                    assignedText += unit.assign_to_name;
                    
                    if (unit.office_name) {
                        assignedText += ` (${unit.office_name})`;
                    }
                    
                    if (unit.professional_designations) {
                        assignedText += `, ${unit.professional_designations}`;
                    }
                } else {
                    assignedText += 'Unknown person';
                }
                
                html += `<div>${assignedText}</div>`;
            }
            
            html += `</div>`;
            resultItem.innerHTML = html;
            
            // Make both available and assigned items clickable
            if (unit.status === 'Available' || unit.status === 'Assigned') {
                resultItem.addEventListener('click', () => {
                    selectUnit(unit);
                });
                resultItem.style.cursor = 'pointer';
            } else {
                resultItem.style.cursor = 'not-allowed';
                resultItem.style.opacity = '0.7';
            }
            
            resultsDiv.appendChild(resultItem);
        });
        
        resultsDiv.style.display = 'block';
    }

    // Handle unit selection from search results
    function selectUnit(unit) {
        currentSelectedUnit = unit;
        
        // Clear any previous current assignment alerts
        const existingAlerts = document.querySelectorAll('.alert-info');
        existingAlerts.forEach(alert => alert.remove());
        
        // Populate modal with unit details
        document.getElementById('modal-item-name').textContent = unit.item_name;
        document.getElementById('modal-fund-type').textContent = unit.fund_name || 'N/A';
        document.getElementById('modal-equipment-type').textContent = unit.type_name || 'N/A';
        document.getElementById('modal-classification').textContent = unit.item_classification || 'N/A';
        document.getElementById('modal-description').textContent = unit.description || 'N/A';
        document.getElementById('modal-property-number').textContent = unit.property_number || 'N/A';
        document.getElementById('modal-quantity').textContent = '1'; // Individual unit
        document.getElementById('modal-uom').textContent = unit.unit_of_measure;
        document.getElementById('modal-unit-cost').textContent = formatCurrency(unit.unit_cost);
        document.getElementById('modal-total-cost').textContent = formatCurrency(unit.unit_cost); // Single unit cost
        document.getElementById('modal-useful-life').textContent = unit.estimated_useful_life || 'N/A';
        
        // Update modal title based on status
        const modalTitle = document.getElementById('assignItemModalLabel');
        if (unit.status === 'Assigned') {
            modalTitle.textContent = 'Transfer Item to:';
            
            // Show current assignment information
            const currentAssignment = document.createElement('div');
            currentAssignment.className = 'alert alert-info';
            
            let assignmentText = '<strong>Currently assigned to:</strong> ';
            
            if (unit.assign_to_name && unit.assign_to_name.trim() !== '') {
                assignmentText += unit.assign_to_name;
                
                if (unit.office_name) {
                    assignmentText += ` (${unit.office_name})`;
                }
                
                if (unit.professional_designations) {
                    assignmentText += `, ${unit.professional_designations}`;
                }
            } else {
                assignmentText += 'Unknown person';
            }
            
            currentAssignment.innerHTML = assignmentText;
            
            // Insert at the beginning of modal body
            const modalBody = document.querySelector('.modal-body');
            modalBody.insertBefore(currentAssignment, modalBody.firstChild);
        } else {
            modalTitle.textContent = 'Assign Item to:';
        }
        
        // Set today's date as default for assignment date
        document.getElementById('assign-date').valueAsDate = new Date();
        
        // Fetch persons for assignment dropdown
        fetchPersons();
        
        // Show the modal
        assignItemModal.show();
        
        // Clear search and results
        searchInput.value = '';
        resultsDiv.innerHTML = '';
        resultsDiv.style.display = 'none';
    }
    
    // Format currency for display
    function formatCurrency(amount) {
        if (!amount) return '₱0.00';
        return '₱' + parseFloat(amount).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    
    // Fetch persons for assignment dropdown
    function fetchPersons() {
        fetch('../src/fetch_persons.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const assignToSelect = document.getElementById('assign-to');
                    assignToSelect.innerHTML = '<option value="" selected disabled>Select Person</option>';
                    
                    data.persons.forEach(person => {
                        const option = document.createElement('option');
                        option.value = person.person_ID;
                        
                        // Format the display text with available information
                        let displayText = `${person.first_name} ${person.last_name}`;
                        
                        if (person.professional_designations) {
                            displayText += `, ${person.professional_designations}`;
                        }
                        
                        if (person.office_name) {
                            displayText += ` (${person.office_name})`;
                        }
                        
                        option.textContent = displayText;
                        assignToSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching persons:', error);
            });
    }
    
    // Handle assignment confirmation
    document.getElementById('confirm-assignment').addEventListener('click', function() {
        if (!currentSelectedUnit || !currentSelectedUnit.unit_ID) {
            Swal.fire({
                icon: 'warning',
                title: 'No Item Selected',
                text: 'No valid item selected',
                confirmButtonColor: '#dc3545'
            });
            return;
        }
        
        const formData = new FormData(document.getElementById('assignmentForm'));
        formData.append('unit_ID', currentSelectedUnit.unit_ID);
        
        // Validate required fields
        if (!formData.get('assign_to')) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Assignment',
                text: 'Please select a person to assign the item to.',
                confirmButtonColor: '#dc3545'
            });
            return;
        }
        
        fetch('../src/assign_item.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Assignment response:', data); // Debug log
            
            if (data.success) {
                const action = data.action || 'assigned';
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: `Item ${action} successfully!`,
                    confirmButtonColor: '#198754'
                });
                assignItemModal.hide();
                
                // Clear the form and selection
                document.getElementById('assignmentForm').reset();
                currentSelectedUnit = null;
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Assignment Error',
                    text: data.error || 'Unknown error occurred',
                    confirmButtonColor: '#dc3545'
                });
            }
        })
        .catch(error => {
            console.error('Assignment error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Network Error',
                text: 'Network error occurred. Please check console for details.',
                confirmButtonColor: '#dc3545'
            });
        });
    });
    
    // Event listeners
    searchInput.addEventListener('input', function() {
        searchItems(this.value.trim());
    });
    
    // Hide results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
            resultsDiv.style.display = 'none';
        }
    });
    
    // Handle keyboard navigation
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            const firstResult = resultsDiv.querySelector('.search-result-item');
            if (firstResult) firstResult.focus();
        }
        
        // Close results on escape
        if (e.key === 'Escape') {
            resultsDiv.style.display = 'none';
            searchInput.value = '';
        }
    });
});