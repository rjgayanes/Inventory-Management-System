function showModal() {
let newPass = document.getElementById('newPassword').value;
let confirmPass = document.getElementById('confirmPassword').value;

if (!newPass || !confirmPass) {
    Swal.fire({
        icon: 'warning',
        title: 'Missing Passwords',
        text: 'Please fill out both password fields.',
        confirmButtonColor: '#dc3545'
    });
    return;
}

if (newPass !== confirmPass) {
    Swal.fire({
        icon: 'warning',
        title: 'Password Mismatch',
        text: 'Passwords do not match.',
        confirmButtonColor: '#dc3545'
    });
    return;
}

document.getElementById('passwordModal').style.display = 'flex';
}

function closeModal() {
document.getElementById('passwordModal').style.display = 'none';
}

function submitPassword() {
let currentPass = document.getElementById('currentPassword').value;
let newPass = document.getElementById('newPassword').value;

if (!currentPass) {
    Swal.fire({
        icon: 'warning',
        title: 'Current Password Required',
        text: 'Please enter your current password.',
        confirmButtonColor: '#dc3545'
    });
    return;
}

// Send data to PHP
let formData = new FormData();
formData.append('currentPassword', currentPass);
formData.append('newPassword', newPass);

fetch('../src/change_password.php', {
    method: 'POST',
    body: formData
})
.then(response => response.text())
.then(data => {
    if (data.includes("success")) {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: data,
            confirmButtonColor: '#198754'
        });
        closeModal();
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: data,
            confirmButtonColor: '#dc3545'
        });
    }
    }
})
.catch(error => console.error(error));
}