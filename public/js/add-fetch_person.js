// Function to fetch and display persons
function fetchPersons() {
    fetch('../src/fetch_persons_for_table.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.personsData = data.persons; // Store for filtering
                displayPersons(data.persons);
            } else {
                console.error('Error fetching persons:', data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Function to display persons in the table
function displayPersons(persons) {
    const tableBody = document.getElementById('personnelTableBody');
    tableBody.innerHTML = '';

    if (persons.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center">No persons found</td> <!-- Changed to colspan="6" -->
            </tr>
        `;
        return;
    }

    persons.forEach(person => {
        const row = document.createElement('tr');
        row.setAttribute('data-person-id', person.person_ID);
        
        // Add click event to the row
        row.style.cursor = 'pointer';
        row.addEventListener('click', function() {
            loadPersonItems(person.person_ID);
        });
        
        // Corrected profile image path handling
        let profileImage;
        if (person.profile_image) {
            // Use the URL provided by the server
            profileImage = person.profile_image_url;
        } else {
            // Use default image
            profileImage = './uploads/profile_images/default_profile.jpg';
        }
        
        // Format professional designations
        const professionalDesignations = person.professional_designations 
            ? `, ${person.professional_designations}` 
            : '';
        
        row.innerHTML = `
            <td>
                <img src="${profileImage}" alt="Profile" class="profile-thumbnail" width="25" height="25" style="border-radius: 25%;">
            </td>
            <td>${person.first_name} ${person.last_name}${professionalDesignations}</td>
            <td>${person.office_name}</td>
            <td>${person.role}</td>
            <td>
                <span class="badge ${person.status === 'Active' ? 'bg-success' : 'bg-secondary'}">
                    ${person.status}
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-primary view-items-btn" data-person-id="${person.person_ID}">
                    <i class="fa-solid fa-eye"></i> View Items
                </button>
            </td>
        `;
        
        tableBody.appendChild(row);
    });

    // Also add click handlers to the view buttons
    document.querySelectorAll('.view-items-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevent row click from also triggering
            const personID = this.getAttribute('data-person-id');
            loadPersonItems(personID);
        });
    });
}

// Function to load person items and show hidden section
function loadPersonItems(personID) {
    // Store the current person ID globally
    window.currentPersonId = personID;
    
    fetch("../src/load_items.php?id=" + personID)
        .then(res => res.json())
        .then(data => {
            document.getElementById("personName").textContent = data.personName;
            document.getElementById("personOfficeRole").textContent = data.officeName + ", " + data.role;
            document.getElementById("personProfile").src = data.profileImage;
            document.getElementById("itemsTableBody").innerHTML = data.rows;

            // Switch views
            document.getElementById("personnelSection").style.display = "none";
            document.getElementById("itemsSection").style.display = "block";
            
            // Reset filter to "All Items" when loading new person
            document.getElementById("filter-select").value = "all";
        })
        .catch(error => {
            console.error("Error loading person items:", error);
        });
}

// Function to go back to personnel view
function goBack() {
    document.getElementById("itemsSection").style.display = "none";
    document.getElementById("personnelSection").style.display = "block";
}

// Make goBack function globally available
window.goBack = goBack;

// Make fetchPersons globally available for refreshing
window.loadPersons = fetchPersons;

// Make loadPersonItems globally available for refreshing
window.loadPersonItems = loadPersonItems;

// Function to setup search functionality
function setupSearch() {
    const searchInput = document.getElementById('searchPersons');
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        
        if (window.personsData) {
            const filteredPersons = filterPersonsData(searchTerm);
            displayPersons(filteredPersons);
        } else {
            filterPersonsTable(searchTerm);
        }
    });
}

// Function to filter persons data (client-side filtering)
function filterPersonsData(searchTerm) {
    if (!searchTerm) return window.personsData;
    
    return window.personsData.filter(person => {
        const name = `${person.first_name} ${person.last_name} ${person.professional_designations || ''}`.toLowerCase();
        const office = person.office_name.toLowerCase();
        const role = person.role.toLowerCase();
        
        return name.includes(searchTerm) || 
               office.includes(searchTerm) || 
               role.includes(searchTerm);
    });
}

// Alternative function to filter existing table rows
function filterPersonsTable(searchTerm) {
    const tableBody = document.getElementById('itemsTableBody');
    const rows = tableBody.getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const nameCell = row.cells[1]; // Full Name column
        const officeCell = row.cells[2]; // Office Name column
        const roleCell = row.cells[3]; // Role column
        
        if (nameCell && officeCell && roleCell) {
            const nameText = nameCell.textContent.toLowerCase();
            const officeText = officeCell.textContent.toLowerCase();
            const roleText = roleCell.textContent.toLowerCase();
            
            const matches = nameText.includes(searchTerm) || 
                           officeText.includes(searchTerm) || 
                           roleText.includes(searchTerm);
            
            row.style.display = matches ? '' : 'none';
        }
    }
}

// Add this function to handle the filter selection
function setupItemFilter() {
    const filterSelect = document.getElementById('filter-select');
    
    filterSelect.addEventListener('change', function() {
        const filterValue = this.value;
        filterItemsTable(filterValue);
    });
}

// Function to filter items table based on unit cost
function filterItemsTable(filterValue) {
    const tableBody = document.getElementById('itemsTableBody');
    const rows = tableBody.getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const unitCostCell = row.cells[2]; // Unit Cost column (3rd column, index 2)
        
        if (unitCostCell) {
            const unitCostText = unitCostCell.textContent.trim();
            // Remove currency symbols and commas, then convert to number
            const unitCost = parseFloat(unitCostText.replace(/[^\d.]/g, ''));
            
            let shouldShow = true;
            
            if (filterValue === 'lowValue') {
                shouldShow = unitCost <= 5000;
            } else if (filterValue === 'highValue') {
                shouldShow = unitCost >= 5000;
            }
            // For 'all' or any other value, show all rows
            
            row.style.display = shouldShow ? '' : 'none';
        }
    }
}

// Function to handle adding a new person
document.getElementById('save-person-btn').addEventListener('click', function() {
    const formData = new FormData();
    
    // Get form values
    const firstName = document.querySelector('input[name="first_name"]').value;
    const lastName = document.querySelector('input[name="last_name"]').value;
    const professionalDesignations = document.querySelector('input[name="professional_designations"]').value;
    const officeName = document.querySelector('select[name="office_name"]').value;
    const role = document.querySelector('input[name="role"]').value;
    const profileImage = document.querySelector('input[name="profile_image"]').files[0];
    
    // Validate required fields
    if (!firstName || !lastName || !officeName || !role) {
        Swal.fire({
            icon: 'warning',
            title: 'Missing Fields',
            text: 'Please fill in all required fields',
            confirmButtonColor: '#dc3545'
        });
        return;
    }
    
    // Append form data
    formData.append('first_name', firstName);
    formData.append('last_name', lastName);
    formData.append('professional_designations', professionalDesignations);
    formData.append('office_name', officeName);
    formData.append('role', role);
    
    if (profileImage) {
        formData.append('profile_image', profileImage);
    }
    
    // Send AJAX request
    fetch('../src/add_person.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Person added successfully!',
                confirmButtonColor: '#198754'
            });
            
            // Reset form
            document.querySelector('input[name="first_name"]').value = '';
            document.querySelector('input[name="last_name"]').value = '';
            document.querySelector('input[name="professional_designations"]').value = '';
            document.querySelector('select[name="office_name"]').value = '';
            document.querySelector('input[name="role"]').value = '';
            document.querySelector('input[name="profile_image"]').value = '';
            document.getElementById('profilePreview').src = './image/default_profile.jpg';
            
            // Hide the form
            const collapseElement = document.getElementById('addPerson');
            const bsCollapse = new bootstrap.Collapse(collapseElement, {
                toggle: false
            });
            bsCollapse.hide();
            
            // Refresh the persons list
            fetchPersons();
            
            // Clear search
            document.getElementById('searchPersons').value = '';
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.error,
                confirmButtonColor: '#dc3545'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Network Error',
            text: 'An error occurred while adding the person',
            confirmButtonColor: '#dc3545'
        });
    });
});

// Preview profile image
document.getElementById('profile_image').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        document.getElementById('profilePreview').src = URL.createObjectURL(file);
    }
});

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    fetchPersons();
    setupSearch();
    setupItemFilter(); // Add this line to initialize the filter
});