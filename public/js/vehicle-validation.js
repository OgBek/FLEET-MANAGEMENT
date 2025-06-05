document.addEventListener('DOMContentLoaded', function() {
    const vinInput = document.getElementById('vin_number');
    const engineInput = document.getElementById('engine_number');
    const form = document.querySelector('form');

    function convertToUppercase(input) {
        if (input) {
            input.value = input.value.toUpperCase();
        }
    }

    function validateInput(input) {
        if (input) {
            const value = input.value;
            const isValid = /^[A-Z0-9]+$/.test(value);
            const errorDiv = input.nextElementSibling;
            
            if (!isValid && value) {
                if (!errorDiv || !errorDiv.classList.contains('text-red-600')) {
                    const error = document.createElement('div');
                    error.className = 'mt-1 text-sm text-red-600';
                    error.textContent = `${input.id === 'vin_number' ? 'VIN' : 'Engine'} number must only contain uppercase letters and numbers (no special characters or spaces).`;
                    input.parentNode.insertBefore(error, input.nextSibling);
                }
                return false;
            } else if (errorDiv && errorDiv.classList.contains('text-red-600')) {
                errorDiv.remove();
            }
            return true;
        }
        return true;
    }

    if (vinInput) {
        vinInput.addEventListener('input', function() {
            convertToUppercase(this);
            validateInput(this);
        });
    }

    if (engineInput) {
        engineInput.addEventListener('input', function() {
            convertToUppercase(this);
            validateInput(this);
        });
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            if (vinInput) isValid = validateInput(vinInput) && isValid;
            if (engineInput) isValid = validateInput(engineInput) && isValid;
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
});
