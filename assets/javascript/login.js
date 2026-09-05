  // Role Selection
        function selectRole(role) {
            const roleSelection = document.getElementById('roleSelection');
            const adminScreen = document.getElementById('adminScreen');
            const patientScreen = document.getElementById('patientScreen');
            const staffScreen = document.getElementById('staffScreen');

            roleSelection.classList.add('hidden');

            if (role === 'admin') {
                adminScreen.classList.remove('hidden');
            } else if (role === 'patient') {
                patientScreen.classList.remove('hidden');
            } else {
                staffScreen.classList.remove('hidden');
            }
        }

        function backToRoleSelection() {
            const roleSelection = document.getElementById('roleSelection');
            const adminScreen = document.getElementById('adminScreen');
            const patientScreen = document.getElementById('patientScreen');
            const staffScreen = document.getElementById('staffScreen');

            roleSelection.classList.remove('hidden');
            adminScreen.classList.add('hidden');
            patientScreen.classList.add('hidden');
            staffScreen.classList.add('hidden');
        }

        // Toggle Admin Password Visibility
        document.getElementById('toggleAdminPassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('adminPassword');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });

        // Toggle Staff Password Visibility
        document.getElementById('toggleStaffPassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('staffPassword');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });

        // Toggle Patient Password Visibility
        document.getElementById('togglePatientPassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('patientPassword');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });