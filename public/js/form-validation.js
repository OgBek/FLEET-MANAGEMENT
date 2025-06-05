document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[data-validate]');

    const validationRules = {
        name: {
            pattern: /^[a-zA-Z\s]{2,50}$/,
            message: 'Name must be 2-50 characters long and contain only letters'
        },
        email: {
            pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            message: 'Please enter a valid email address'
        },
        phone: {
            pattern: /^\+?[\d\s-]{10,15}$/,
            message: 'Please enter a valid phone number'
        },
        password: {
            pattern: /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/,
            message: 'Password must be at least 8 characters long and include letters and numbers'
        },
        license_number: {
            pattern: /^[A-Z0-9-]{5,15}$/,
            message: 'Please enter a valid license number'
        }
    };

    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let hasError = false;
            const inputs = form.querySelectorAll('input[data-validate], select[data-validate]');

            inputs.forEach(input => {
                const field = input.getAttribute('data-validate');
                const value = input.value.trim();
                const errorDiv = input.nextElementSibling;

                if (validationRules[field]) {
                    if (!validationRules[field].pattern.test(value)) {
                        e.preventDefault();
                        hasError = true;
                        errorDiv.textContent = validationRules[field].message;
                        errorDiv.classList.remove('hidden');
                        input.classList.add('border-red-500');
                    } else {
                        errorDiv.classList.add('hidden');
                        input.classList.remove('border-red-500');
                    }
                }

                if (input.hasAttribute('required') && !value) {
                    e.preventDefault();
                    hasError = true;
                    errorDiv.textContent = 'This field is required';
                    errorDiv.classList.remove('hidden');
                    input.classList.add('border-red-500');
                }
            });

            if (!hasError) {
                form.classList.add('submitting');
            }
        });
    });
});
