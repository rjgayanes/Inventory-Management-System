// Profile Modal functionality - Wait for DOM to load
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('editProfileModal');
    const editBtn = document.getElementById('editProfileBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const profileInput = document.getElementById('profile_image');
    const profilePreview = document.getElementById('profilePreview');

    // Debug: Check if elements exist
    console.log('Modal:', modal);
    console.log('Edit Button:', editBtn);
    console.log('Cancel Button:', cancelBtn);
    console.log('Profile Input:', profileInput);
    console.log('Profile Preview:', profilePreview);

    if (!editBtn) {
        console.error('Edit button not found!');
        return;
    }

    if (!modal) {
        console.error('Modal not found!');
        return;
    }

    // Function to show profile modal with auto-population
    function showProfileModal() {
        // Auto-populate form with current user data from the displayed profile
        const profileDetails = document.querySelector('.profile-details');
        
        // Extract data from the displayed profile information
        const usernameText = profileDetails.querySelector('.uname p')?.textContent || '';
        const emailText = profileDetails.querySelector('.email p')?.textContent || '';
        const nameText = profileDetails.querySelector('.name p')?.textContent || '';
        const roleText = profileDetails.querySelector('.role p')?.textContent || '';
        
        // Parse the name (format: "First Last, Designation")
        const nameParts = nameText.split(',');
        const fullName = nameParts[0].trim();
        const nameArray = fullName.split(' ');
        const firstName = nameArray[0] || '';
        const lastName = nameArray.slice(1).join(' ') || '';
        const professionalDesignation = nameParts[1] ? nameParts[1].trim() : '';
        
        // Populate form fields
        document.getElementById('user_name').value = usernameText;
        document.getElementById('user_email').value = emailText;
        document.getElementById('first_name').value = firstName;
        document.getElementById('last_name').value = lastName;
        document.getElementById('professional_designation').value = professionalDesignation;
        
        // Set profile image from the displayed image
        const currentProfileImg = document.querySelector('.profile-image img');
        if (currentProfileImg) {
            document.getElementById('profilePreview').src = currentProfileImg.src;
        }
        
        // Show modal
        modal.style.display = 'flex';
    }

    // Function to hide profile modal
    function hideProfileModal() {
        modal.style.display = 'none';
    }

    // Open modal
    editBtn.onclick = (e) => {
        e.preventDefault();
        console.log('Edit button clicked'); // Debug log
        showProfileModal();
    };

    // Alternative event listener in case onclick doesn't work
    editBtn.addEventListener('click', (e) => {
        e.preventDefault();
        console.log('Edit button clicked via addEventListener'); // Debug log
        showProfileModal();
    });

    // Close modal with cancel button
    if (cancelBtn) {
        cancelBtn.onclick = () => {
            hideProfileModal();
        };
    }

    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            hideProfileModal();
        }
    });

    // Preview uploaded image
    if (profileInput) {
        profileInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                profilePreview.src = URL.createObjectURL(file);
            }
        });
    }

    // Handle form submission
    const form = document.getElementById('editProfileForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../src/update_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Profile updated successfully!',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        hideProfileModal();
                        // Reload the page to show updated information
                        window.location.reload();
                    });
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
                    text: 'An error occurred while updating the profile',
                    confirmButtonColor: '#dc3545'
                });
            });
        });
    }
});