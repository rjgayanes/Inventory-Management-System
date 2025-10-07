// Update the classification buttons functionality
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('addTaskForm');
    if (!form) return;

    const classificationInput = document.getElementById('classificationInput');
    const classificationBtns = document.querySelectorAll('.classification-btn');
    const timeInputs = form.querySelector('.task-time-inputs');

    // Classification buttons click handler
    classificationBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            classificationBtns.forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            // Update hidden input value
            classificationInput.value = this.dataset.classification;
            // Update time inputs visibility
            updateTimeInputsVisibility();
        });
    });

    function updateTimeInputsVisibility() {
        const cls = classificationInput ? classificationInput.value : 'Notes';
        if (cls === 'Reminders' || cls === 'Checklist') {
            if (timeInputs) timeInputs.style.display = 'flex';
                } else {
            if (timeInputs) timeInputs.style.display = 'none';
            const dateEl = form.querySelector('input[name="due_date"]');
            const timeEl = form.querySelector('input[name="due_time"]');
            if (dateEl) dateEl.value = '';
            if (timeEl) timeEl.value = '';
        }
    }

    // Initialize time inputs visibility
    updateTimeInputsVisibility();

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const submitBtn = form.querySelector('.save-task-btn');
        if (submitBtn) {
            submitBtn.disabled = true;
        }

        try {
            const formData = new FormData(form);
            const response = await fetch('dashboard.php', {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' },
                cache: 'no-store'
            });

            const contentType = (response.headers.get('content-type') || '').toLowerCase();
            let result;
            if (contentType.includes('application/json')) {
                result = await response.json();
            } else {
                const text = await response.text();
                try { result = JSON.parse(text); } catch (_) { result = { success: false, error: 'Invalid response' }; }
            }

            if (result && result.success) {
                // Show a lightweight toast message with classification info
                const classification = classificationInput.value;
                let classificationText = '';
                switch(classification) {
                    case 'Notes':
                        classificationText = 'as a Note';
                        break;
                    case 'Reminders':
                        classificationText = 'as a Reminder';
                        break;
                    case 'Checklist':
                        classificationText = 'as a Checklist';
                        break;
                    default:
                        classificationText = 'successfully';
                }
                showToast(`Task added ${classificationText}`, 'success');

                // Prepend the new task card to the list without reloading
                const taskList = document.querySelector('.task-list');
                if (taskList) {
                    const title = form.querySelector('input[name="title"]').value.trim();
                    const classification = classificationInput.value;
                    const description = (form.querySelector('textarea[name="description"]').value || '').trim();
                    const status = 'Pending';
                    const dueDate = form.querySelector('input[name="due_date"]').value;
                    const dueTime = form.querySelector('input[name="due_time"]').value;

                    const statusLower = status.toLowerCase();
                    let statusIcon = '<i class="bi bi-three-dots"></i>';
                    if (statusLower === 'completed') statusIcon = '<i class="bi bi-check-circle-fill"></i>';
                    if (statusLower === 'cancelled') statusIcon = '<i class="bi bi-x-circle"></i>';
                    let classIcon = '<i class="bi bi-journal-text"></i>';
                    if (classification === 'Reminders') classIcon = '<i class="bi bi-alarm"></i>';
                    if (classification === 'Checklist') classIcon = '<i class="bi bi-list-check"></i>';

                    // Format date (fallback to raw yyyy-mm-dd)
                    const dateDisplay = dueDate ? new Date(dueDate).toLocaleDateString() : 'No due date';
                    const timeDisplay = dueTime ? ` at ${dueTime}` : '';

                    const card = document.createElement('div');
                    card.className = `tm-card status-${statusLower}`;
                    if (result.task_id) {
                        card.dataset.taskId = result.task_id;
                    }
                    // due date and time as data attributes
                    card.dataset.dueDate = dueDate || '';
                    card.dataset.dueTime = dueTime || '';

                    card.innerHTML = `
                        <div class="tm-card-top">
                            <h3>${title.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</h3>
                            <div class="d-flex align-items-center" style="gap:10px;">
                                <button type="button" class="tm-edit btn btn-link p-0 m-0" title="Edit" style="text-decoration:none; color: inherit;"><i class="bi bi-pencil-square"></i></button>
                                <button type="button" class="tm-delete btn btn-link p-0 m-0" title="Delete" style="text-decoration:none; color: inherit;"><i class="bi bi-trash"></i></button>
                                <button type="button" class="tm-status btn btn-link p-0 m-0" data-status="${status}" style="text-decoration:none; color: inherit;">${statusIcon} ${status}</button>
                            </div>
                        </div>
                        <div class="tm-card-bottom">
                            <span class="tm-date"><i class="bi bi-calendar3"></i> ${dateDisplay}${timeDisplay}</span>
                            <span class="tm-dot">•</span>
                            <span class="tm-class">${classIcon} ${classification}</span>
                        </div>
                        <div class="tm-details" style="display:none;">
                            <div class="tm-desc-label">Description:</div>
                            <div class="tm-desc-text">${(description || 'No description provided').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div>
                        </div>
                    `;

                    // Remove placeholder if present
                    const placeholder = taskList.querySelector('.no-tasks');
                    if (placeholder) placeholder.remove();
                    taskList.prepend(card);
                    
                    // Check for urgent tasks after adding new task
                    setTimeout(updateUrgentTasks, 100);

                    // Refresh filters after successful AJAX operations
                    if (typeof window.refreshTaskFilters === 'function') {
                        window.refreshTaskFilters();
                    }
                }

                // Reset form and collapse
                form.reset();
                // Reset classification buttons to default
                classificationBtns.forEach(btn => btn.classList.remove('active'));
                classificationBtns[0].classList.add('active');
                classificationInput.value = 'Notes';
                updateTimeInputsVisibility();
                const collapseEl = document.getElementById('collapseExample');
                if (collapseEl && window.bootstrap && window.bootstrap.Collapse) {
                    const c = window.bootstrap.Collapse.getOrCreateInstance(collapseEl);
                    c.hide();
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Add Task Failed!',
                    text: result && result.error ? result.error : 'Failed to add task',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true
                });
            }
        } catch (err) {
            console.error('Add task error:', err);
            Swal.fire({
                icon: 'error',
                title: 'Network Error!',
                text: 'Error adding task: ' + err.message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true
            });
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
            }
        }
    });
    
    // Initial check for urgent tasks
    updateUrgentTasks();
    
    // Check every 30 seconds for urgent tasks
    setInterval(updateUrgentTasks, 30000);
});

// Helper function to update task status
async function updateTaskStatus(taskId, newStatus, card, statusBtn) {
    try {
        const fd = new FormData();
        fd.append('update_task_status', '1');
        fd.append('task_id', taskId);
        fd.append('status', newStatus);
        const res = await fetch('dashboard.php', { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        
        if (data.success) {
            // Update UI
            statusBtn.dataset.status = newStatus;
            statusBtn.innerHTML = (newStatus === 'Completed' ? '<i class="bi bi-check-circle-fill"></i>' : '<i class="bi bi-x-circle"></i>') + ' ' + newStatus;
            card.classList.remove('status-pending','status-completed','status-cancelled');
            card.classList.add('status-' + newStatus.toLowerCase());

            // Stop shaking if task is completed or cancelled
            stopShakingForCompletedTasks(card);

            // Refresh filters after successful AJAX operations
            if (typeof window.refreshTaskFilters === 'function') {
                window.refreshTaskFilters();
            }

            Swal.fire({
                icon: 'success',
                title: 'Status Updated!',
                text: `Task marked as ${newStatus}`,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            
            // Re-sort tasks after status change
            updateUrgentTasks();
            } else {
            throw new Error(data.error || 'Failed to update status');
        }
    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Update Failed!',
            text: 'Error: ' + err.message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true
        });
    }
}

// Click handler for task interactions
document.addEventListener('click', async function(e) {
    // Toggle details on card click (but ignore status button clicks and edit button clicks)
    const clickedCard = e.target.closest('.tm-card');
    if (clickedCard && !e.target.closest('.tm-status') && !e.target.closest('.tm-edit') && !e.target.closest('.tm-delete') && !e.target.closest('.tm-inline-editor')) {
        const details = clickedCard.querySelector('.tm-details');
        if (details) {
            details.style.display = (details.style.display === 'none' || details.style.display === '') ? 'block' : 'none';
        }
    }

    // Handle delete
    const delBtn = e.target.closest('.tm-delete');
    if (delBtn) {
        e.stopPropagation(); // Prevent event from bubbling up to card click handler
        
        const card = delBtn.closest('.tm-card');
        if (!card) return;
        const taskId = card.dataset.taskId;
        if (!taskId) return;
       const result = await Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        });

        if (!result.isConfirmed) return;
        try {
            const fd = new FormData();
            fd.append('delete_task', '1');
            fd.append('task_id', taskId);
            const res = await fetch('dashboard.php', { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (data.success) {
                card.remove();
                
                // Show success toast for delete
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: 'Task has been deleted successfully.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });

                // Refresh filters after successful AJAX operations
                if (typeof window.refreshTaskFilters === 'function') {
                    window.refreshTaskFilters();
                }
                
                // Show "no tasks" message if all tasks are deleted
                const taskList = document.querySelector('.task-list');
                if (taskList && taskList.children.length === 0) {
                    const noTasksDiv = document.createElement('div');
                    noTasksDiv.className = 'no-tasks';
                    noTasksDiv.innerHTML = '<p>No tasks yet</p>';
                    taskList.appendChild(noTasksDiv);
                }
                
                // Update urgent tasks after deletion
                updateUrgentTasks();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.error || 'Failed to delete task',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true
                });
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Error: ' + err.message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true
            });
        }
    }

    // Handle edit: turn title and description into editable inputs with status buttons
    const editBtn = e.target.closest('.tm-edit');
    if (editBtn) {
        e.stopPropagation(); // Prevent event from bubbling up to card click handler
        
        const card = editBtn.closest('.tm-card');
        if (!card) return;
        
        // Check if editor already exists, if so, remove it first
        const existingEditor = card.querySelector('.tm-inline-editor');
        if (existingEditor) {
            existingEditor.remove();
            return;
        }
        
        const taskId = card.dataset.taskId;
        const titleEl = card.querySelector('.tm-card-top h3');
        const descEl = card.querySelector('.tm-desc-text');
        const statusBtn = card.querySelector('.tm-status');
        const currentStatus = statusBtn.dataset.status;
        const originalTitle = titleEl ? titleEl.textContent : '';
        const originalDesc = descEl ? descEl.textContent : '';

        // Ensure details section is visible
        const details = card.querySelector('.tm-details');
        if (!details) return;
        
        details.style.display = 'block';

        // Build inline editor with status buttons
        const editor = document.createElement('div');
        editor.className = 'tm-inline-editor';
        editor.innerHTML = `
            <div class="mt-2" style="display:flex; flex-direction:column; gap:8px;">
                <!-- Status buttons - only show Complete/Cancel if task is Pending -->
                ${currentStatus === 'Pending' ? `
                <div class="tm-status-buttons">
                    <button type="button" class="tm-status-complete-btn" ${currentStatus !== 'Pending' ? 'disabled' : ''}>
                        <i class="bi bi-check-circle"></i> Mark Complete
                    </button>
                    <button type="button" class="tm-status-cancel-btn" ${currentStatus !== 'Pending' ? 'disabled' : ''}>
                        <i class="bi bi-x-circle"></i> Cancel Task
                    </button>
                </div>
                ` : `
                <div class="text-muted" style="font-size: 12px; font-style: italic;">
                    Status: ${currentStatus} - Task can only be change when it's pending
                </div>
                `}

                <input type="text" class="form-control form-control-sm tm-edit-title" value="${originalTitle.replace(/"/g, '&quot;')}">
                <textarea class="form-control form-control-sm tm-edit-desc" rows="3">${originalDesc.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</textarea>
                
                <div style="display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" class="btn btn-primary btn-sm tm-save">Save Changes</button>
                    <button type="button" class="btn btn-secondary btn-sm tm-cancel">Cancel</button>
                </div>
            </div>`;

        // Insert editor after bottom row
        details.appendChild(editor);

        const save = editor.querySelector('.tm-save');
        const cancel = editor.querySelector('.tm-cancel');
        const completeBtn = editor.querySelector('.tm-status-complete-btn');
        const cancelBtn = editor.querySelector('.tm-status-cancel-btn');
        
        // Cancel button handler
        cancel.addEventListener('click', function(e) {
            e.stopPropagation();
            editor.remove();
        });
        
        // Complete button handler
        if (completeBtn) {
            completeBtn.addEventListener('click', async function(e) {
                e.stopPropagation();
                await updateTaskStatus(taskId, 'Completed', card, statusBtn);
                editor.remove();
            });
        }
        
        // Cancel task button handler
        if (cancelBtn) {
            cancelBtn.addEventListener('click', async function(e) {
                e.stopPropagation();
                await updateTaskStatus(taskId, 'Cancelled', card, statusBtn);
                editor.remove();
            });
        }
        
        // Save button handler (for title/description only)
        save.addEventListener('click', async function(e) {
            e.stopPropagation();
            const newTitle = editor.querySelector('.tm-edit-title').value.trim();
            const newDesc = editor.querySelector('.tm-edit-desc').value.trim();
            if (!newTitle) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Title is required',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
                return;
            }
            try {
                const fd = new FormData();
                fd.append('update_task_fields', '1');
                fd.append('task_id', taskId);
                fd.append('title', newTitle);
                fd.append('description', newDesc);
                const res = await fetch('dashboard.php', { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                if (data.success) {
                    if (titleEl) titleEl.textContent = newTitle;
                    if (descEl) descEl.textContent = newDesc || 'No description provided';
                    editor.remove();

                    // Refresh filters after successful AJAX operations
                    if (typeof window.refreshTaskFilters === 'function') {
                        window.refreshTaskFilters();
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: 'Task updated successfully',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Save Failed!',
                        text: data.error || 'Failed to save changes',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 5000,
                        timerProgressBar: true
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Error: ' + err.message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true
                });
            }
        });
        
        // Focus on title input for better UX
        editor.querySelector('.tm-edit-title').focus();
    }
});

// Function to check if a task is urgent (5 minutes or less from due time) OR overdue
function checkIfTaskIsUrgent(task) {
    const status = task.querySelector('.tm-status').dataset.status;
    const classification = task.querySelector('.tm-class').textContent.trim();
    
    // Skip if already completed/cancelled or if it's a Note
    if (status === 'Completed' || status === 'Cancelled' || classification === 'Notes') {
        return false;
    }
    
    // Get due date and time from data attributes
    const dueDate = task.dataset.dueDate;
    const dueTime = task.dataset.dueTime;
    
    if (!dueDate) {
        return false;
    }
    
    try {
        // Combine date and time
        const dueDateTimeStr = dueDate + (dueTime ? 'T' + dueTime : 'T23:59:00');
        const dueDateTime = new Date(dueDateTimeStr);
        const now = new Date();
        
        // Calculate time difference in minutes
        const diffMinutes = (dueDateTime - now) / (1000 * 60);
        
        console.log(`Task: ${task.querySelector('h3').textContent}, Time until due: ${diffMinutes.toFixed(2)} minutes`);
        
        // Task is urgent if:
        // 1. It's due within 5 minutes AND not overdue, OR
        // 2. It's overdue (negative diffMinutes) AND still pending
        return (diffMinutes <= 5 && diffMinutes >= 0) || (diffMinutes < 0);
    } catch (error) {
        console.error('Error parsing due date:', error);
        return false;
    }
}

// Function to add shaking bell icon (will shake only for upcoming urgent tasks, but stay visible for overdue)
function addShakingBell(task) {
    // Remove existing bell if any
    removeShakingBell(task);
    
    const statusBtn = task.querySelector('.tm-status');
    const bellIcon = document.createElement('i');
    bellIcon.className = 'bi bi-bell-fill';
    bellIcon.style.marginRight = '5px';
    bellIcon.style.color = '#dc3545';
    
    // Check if task is upcoming urgent (within 5 minutes) to add shaking class
    const dueDate = task.dataset.dueDate;
    const dueTime = task.dataset.dueTime;
    
    if (dueDate) {
        try {
            const dueDateTimeStr = dueDate + (dueTime ? 'T' + dueTime : 'T23:59:00');
            const dueDateTime = new Date(dueDateTimeStr);
            const now = new Date();
            const diffMinutes = (dueDateTime - now) / (1000 * 60);
            
            // Only add shaking class if task is upcoming (within 5 minutes)
            if (diffMinutes <= 5 && diffMinutes >= 0) {
                bellIcon.classList.add('shaking-bell');
            }
        } catch (error) {
            console.error('Error checking due date for shaking:', error);
        }
    }
    
    // Insert bell before status
    statusBtn.parentNode.insertBefore(bellIcon, statusBtn);
}

// Function to remove shaking bell icon (only when task is completed/cancelled)
function removeShakingBell(task) {
    const existingBell = task.querySelector('.bi.bi-bell-fill');
    if (existingBell) {
        existingBell.remove();
    }
}

// Function to stop shaking for completed/cancelled tasks
function stopShakingForCompletedTasks(task) {
    task.classList.remove('urgent-task');
    removeShakingBell(task);
}

// Function to check and update urgent tasks
function updateUrgentTasks() {
    console.log('Checking for urgent tasks...');
    const tasks = document.querySelectorAll('.tm-card');
    const taskList = document.querySelector('.task-list');
    
    if (!taskList) return;
    
    let urgentTasks = [];
    let normalTasks = [];
    
    tasks.forEach(task => {
        const status = task.querySelector('.tm-status').dataset.status;
        const classification = task.querySelector('.tm-class').textContent.trim();
        const isUrgent = checkIfTaskIsUrgent(task);
        
        // Debug logging for each task
        console.log(`Task: ${task.querySelector('h3').textContent}, Status: ${status}, Classification: ${classification}, Urgent: ${isUrgent}`);
        console.log(`Due Date: ${task.dataset.dueDate}, Due Time: ${task.dataset.dueTime}`);
        
        // Skip if task is completed or cancelled
        if (status === 'Completed' || status === 'Cancelled') {
            // Remove bell and urgent styling for completed/cancelled tasks
            task.classList.remove('urgent-task');
            removeShakingBell(task);
            normalTasks.push(task);
            return;
        }
        
        // Skip if it's a Note (no due date functionality)
        if (classification === 'Notes') {
            // Remove bell and urgent styling for Notes
            task.classList.remove('urgent-task');
            removeShakingBell(task);
            normalTasks.push(task);
            return;
        }
        
        if (isUrgent) {
            // Add urgent class and bell (with or without shaking)
            task.classList.add('urgent-task');
            addShakingBell(task);
            urgentTasks.push(task);
        } else {
            // Remove urgent class and bell if no longer urgent
            task.classList.remove('urgent-task');
            removeShakingBell(task);
            normalTasks.push(task);
        }
    });
    
    // Clear the task list
    taskList.innerHTML = '';
    
    // Add urgent tasks first
    urgentTasks.forEach(task => {
        taskList.appendChild(task);
    });
    
    // Add normal tasks after
    normalTasks.forEach(task => {
        taskList.appendChild(task);
    });
    
    // Show "no tasks" message if empty
    if (taskList.children.length === 0) {
        const noTasksDiv = document.createElement('div');
        noTasksDiv.className = 'no-tasks';
        noTasksDiv.innerHTML = '<p>No tasks yet</p>';
        taskList.appendChild(noTasksDiv);
    }
    
    // Final debug log
    console.log(`Found ${urgentTasks.length} urgent tasks and ${normalTasks.length} normal tasks`);
}

// showToast function
function showToast(message, type = 'info') {
    const config = {
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    };

        switch(type) {
            case 'success':
            Swal.fire({
                ...config,
                icon: 'success',
                title: message,
                background: '#d1f7df',
                color: '#1f5131'
            });
                break;
            case 'error':
            Swal.fire({
                ...config,
                icon: 'error',
                title: message,
                background: '#f8d7da',
                color: '#721c24'
            });
                break;
        case 'info':
            Swal.fire({
                ...config,
                icon: 'info',
                title: message,
                background: '#cce5ff',
                color: '#004085'
            });
                break;
    }
}