document.addEventListener('DOMContentLoaded', function() {
    const dateTimeInputs = document.querySelectorAll('input[type="datetime-local"], input[type="date"]');

    dateTimeInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const value = e.target.value;
            const now = new Date();
            const selected = new Date(value);
            const errorDiv = input.nextElementSibling;
            
            // Get validation rules from data attributes
            const minDate = input.dataset.minDate === 'now' ? now : new Date(input.dataset.minDate || '1900-01-01');
            const maxDate = input.dataset.maxDate === 'now' ? now : new Date(input.dataset.maxDate || '2100-01-01');
            
            if (selected < minDate) {
                errorDiv.textContent = 'Date cannot be earlier than ' + minDate.toLocaleDateString();
                errorDiv.classList.remove('hidden');
                input.classList.add('border-red-500');
                e.preventDefault();
            } else if (selected > maxDate) {
                errorDiv.textContent = 'Date cannot be later than ' + maxDate.toLocaleDateString();
                errorDiv.classList.remove('hidden');
                input.classList.add('border-red-500');
                e.preventDefault();
            } else {
                errorDiv.classList.add('hidden');
                input.classList.remove('border-red-500');
            }
        });
    });
});
