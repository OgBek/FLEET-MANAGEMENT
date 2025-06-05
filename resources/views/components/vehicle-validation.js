document.addEventListener('DOMContentLoaded', function() {
    const vinValidation = {
        // VIN validation based on ISO 3779 standard
        pattern: /^[A-HJ-NPR-Z0-9]{17}$/,
        checkDigit: function(vin) {
            const weights = [8,7,6,5,4,3,2,10,0,9,8,7,6,5,4,3,2];
            const values = {
                'A':1,'B':2,'C':3,'D':4,'E':5,'F':6,'G':7,'H':8,
                'J':1,'K':2,'L':3,'M':4,'N':5,'P':7,'R':9,'S':2,
                'T':3,'U':4,'V':5,'W':6,'X':7,'Y':8,'Z':9
            };
            
            let sum = 0;
            for(let i = 0; i < 17; i++) {
                let char = vin[i];
                let value = values[char] || parseInt(char);
                sum += value * weights[i];
            }
            
            let checkDigit = sum % 11;
            return checkDigit === 10 ? 'X' : checkDigit.toString();
        },
        validate: function(vin) {
            if (!this.pattern.test(vin)) {
                return 'VIN must be 17 characters long and contain only uppercase letters (except I,O,Q) and numbers';
            }
            
            // Validate check digit (9th character)
            const calculatedCheckDigit = this.checkDigit(vin);
            if (vin[8] !== calculatedCheckDigit) {
                return 'Invalid VIN check digit';
            }
            
            return '';
        }
    };

    const registrationValidation = {
        // Pattern varies by country, this is a general format
        pattern: /^[A-Z0-9]{2,10}$/,
        validate: function(reg) {
            if (!this.pattern.test(reg)) {
                return 'Registration number must be 2-10 characters long and contain only uppercase letters and numbers';
            }
            return '';
        }
    };

    const yearValidation = {
        validate: function(year) {
            const currentYear = new Date().getFullYear();
            const yearNum = parseInt(year);
            
            if (isNaN(yearNum) || yearNum < 1900 || yearNum > currentYear + 1) {
                return `Year must be between 1900 and ${currentYear + 1}`;
            }
            return '';
        }
    };

    const mileageValidation = {
        validate: function(mileage) {
            const miles = parseInt(mileage);
            if (isNaN(miles) || miles < 0 || miles > 999999) {
                return 'Mileage must be between 0 and 999,999';
            }
            return '';
        }
    };

    // Add validation to form fields
    document.querySelectorAll('[data-validate-vin]').forEach(input => {
        input.addEventListener('input', function() {
            const error = vinValidation.validate(this.value.toUpperCase());
            const errorDiv = this.nextElementSibling;
            if (error) {
                errorDiv.textContent = error;
                errorDiv.classList.remove('hidden');
                this.classList.add('border-red-500');
            } else {
                errorDiv.classList.add('hidden');
                this.classList.remove('border-red-500');
            }
        });
    });

    document.querySelectorAll('[data-validate-registration]').forEach(input => {
        input.addEventListener('input', function() {
            const error = registrationValidation.validate(this.value.toUpperCase());
            const errorDiv = this.nextElementSibling;
            if (error) {
                errorDiv.textContent = error;
                errorDiv.classList.remove('hidden');
                this.classList.add('border-red-500');
            } else {
                errorDiv.classList.add('hidden');
                this.classList.remove('border-red-500');
            }
        });
    });

    document.querySelectorAll('[data-validate-year]').forEach(input => {
        input.addEventListener('input', function() {
            const error = yearValidation.validate(this.value);
            const errorDiv = this.nextElementSibling;
            if (error) {
                errorDiv.textContent = error;
                errorDiv.classList.remove('hidden');
                this.classList.add('border-red-500');
            } else {
                errorDiv.classList.add('hidden');
                this.classList.remove('border-red-500');
            }
        });
    });

    document.querySelectorAll('[data-validate-mileage]').forEach(input => {
        input.addEventListener('input', function() {
            const error = mileageValidation.validate(this.value);
            const errorDiv = this.nextElementSibling;
            if (error) {
                errorDiv.textContent = error;
                errorDiv.classList.remove('hidden');
                this.classList.add('border-red-500');
            } else {
                errorDiv.classList.add('hidden');
                this.classList.remove('border-red-500');
            }
        });
    });
});
