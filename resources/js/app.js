import './bootstrap';

// Profile Photo Preview
document.addEventListener('DOMContentLoaded', function() {
    const photoInput = document.querySelector('#photo');
    const photoPreview = document.querySelector('.mt-2 span');
    const photoPreviewContainer = photoPreview?.parentElement;

    if (photoInput && photoPreview && photoPreviewContainer) {
        photoInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const file = e.target.files[0];
                const reader = new FileReader();

                reader.onload = function(e) {
                    photoPreview.style.backgroundImage = `url(${e.target.result})`;
                    photoPreviewContainer.style.display = 'block';
                }

                reader.readAsDataURL(file);
            } else {
                photoPreviewContainer.style.display = 'none';
            }
        });
    }
});
