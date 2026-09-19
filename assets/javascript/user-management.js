let csrfToken = window.csrfToken || "";
const form = document.getElementById('addUserForm');
const message = document.getElementById('userMessage');
const submitButton = form.querySelector('button[type="submit"]');
const userIdInput = form.querySelector('input[name="userId"]');
const passwordInput = form.querySelector('input[name="password"]');

const CREATE_ENDPOINT = '../php/add/add-user.php';
const UPDATE_ENDPOINT = '../php/update/update-user.php';

let isEditMode = false;

function enterEditMode(data) {
    isEditMode = true;

    userIdInput.value = data.id;
    form.querySelector('input[name="username"]').value = data.username;
    form.querySelector('input[name="firstName"]').value = data.firstName;
    form.querySelector('input[name="lastName"]').value = data.lastName;
    form.querySelector('select[name="role"]').value = data.role;
    form.querySelector('input[name="email"]').value = data.email;
    form.querySelector('input[name="phone"]').value = data.phone;
    form.querySelector('input[name="isDoctor"]').checked = data.isDoctor === '1';

    // Password isn't required when editing (leave blank to keep current one)
    passwordInput.required = false;
    passwordInput.placeholder = 'Leave blank to keep current password';

    submitButton.innerHTML = '<i class="fa-solid fa-pen mr-2"></i>Update account';
    showCancelButton();

    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function exitEditMode() {
    isEditMode = false;
    form.reset();
    userIdInput.value = '';
    passwordInput.required = true;
    passwordInput.placeholder = '8+ characters';
    submitButton.innerHTML = '<i class="fa-solid fa-plus mr-2"></i>Create account';
    hideCancelButton();
}

function showCancelButton() {
    if (document.getElementById('cancelEditBtn')) return;
    const cancelBtn = document.createElement('button');
    cancelBtn.type = 'button';
    cancelBtn.id = 'cancelEditBtn';
    cancelBtn.className = 'md:col-span-2 xl:col-span-4 border border-slate-300 text-slate-600 rounded-lg p-2.5 font-semibold hover:bg-slate-50 transition';
    cancelBtn.textContent = 'Cancel edit';
    cancelBtn.addEventListener('click', exitEditMode);
    submitButton.insertAdjacentElement('afterend', cancelBtn);
}

function hideCancelButton() {
    const cancelBtn = document.getElementById('cancelEditBtn');
    if (cancelBtn) cancelBtn.remove();
}

// Populate the form when any Edit button is clicked
document.querySelectorAll('.edit-user-btn').forEach((button) => {
    button.addEventListener('click', () => {
        enterEditMode({
            id: button.dataset.id,
            username: button.dataset.username,
            firstName: button.dataset.firstName,
            lastName: button.dataset.lastName,
            role: button.dataset.role,
            isDoctor: button.dataset.isDoctor,
            email: button.dataset.email,
            phone: button.dataset.phone,
        });
    });
});

form.addEventListener('submit', async (event) => {
    event.preventDefault();
    submitButton.disabled = true;
    submitButton.textContent = isEditMode ? 'Updating...' : 'Creating...';
    message.className = 'mt-4 rounded p-3 hidden';

    const formData = new FormData(form);
    formData.set('csrf_token', csrfToken);

    const endpoint = isEditMode ? UPDATE_ENDPOINT : CREATE_ENDPOINT;

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();
        if (data.csrf_token) {
            csrfToken = data.csrf_token;
        }
        if (data.status !== 'success') {
            throw new Error(data.message || 'Unable to save the account.');
        }
        message.textContent = data.message;
        message.className = 'mt-4 rounded bg-green-100 p-3 text-green-700';

        if (isEditMode) {
            exitEditMode();
        } else {
            form.reset();
        }
    } catch (error) {
        message.textContent = error.message;
        message.className = 'mt-4 rounded bg-red-100 p-3 text-red-700';
    } finally {
        submitButton.disabled = false;
        submitButton.textContent = isEditMode ? 'Update account' : 'Create account';
    }
});

document.getElementById('togglePassword').addEventListener('click', function () {
    const passwordInput = document.getElementById('Password');
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    this.classList.toggle('fa-eye-slash');
});