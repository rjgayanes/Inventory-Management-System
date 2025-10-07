document.getElementById('openModal3').addEventListener('click', function() {
// Show modal only if fields are filled
const question = document.getElementById('question').value;
const answer = document.getElementById('answer').value;
if (question && answer) {
    document.getElementById('passwordModal3').style.display = 'flex';
} else {
    Swal.fire({
        icon: 'warning',
        title: 'Missing Information',
        text: 'Please select a question and provide an answer.',
        confirmButtonColor: '#dc3545'
    });
}
});

// Cancel modal
document.getElementById('cancelModal3').addEventListener('click', function() {
document.getElementById('passwordModal3').style.display = 'none';
});

// Confirm save
document.getElementById('confirmSave3').addEventListener('click', function() {
const question_ID = document.getElementById('question').value;
const answer = document.getElementById('answer').value;
const current_password = document.getElementById('current_password').value;

if (!current_password) {
    Swal.fire({
        icon: 'warning',
        title: 'Password Required',
        text: 'Please enter your current password.',
        confirmButtonColor: '#dc3545'
    });
    return;
}

// Send data to PHP via AJAX
fetch('../src/set_security_question.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `question_ID=${encodeURIComponent(question_ID)}&answer=${encodeURIComponent(answer)}&current_password=${encodeURIComponent(current_password)}`
})
.then(response => response.text())
.then(data => {
    if (data.includes('success')) {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: data,
            confirmButtonColor: '#198754'
        });
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: data,
            confirmButtonColor: '#dc3545'
        });
    }
    document.getElementById('passwordModal3').style.display = 'none';
})
.catch(err => console.error(err));
});