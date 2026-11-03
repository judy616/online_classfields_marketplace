/**
 * Main JavaScript
 * Online Classifieds Marketplace
 */

document.addEventListener('DOMContentLoaded', function () {
  // 1. Auto-dismiss flash alerts after 5 seconds
  const autoDismissAlerts = document.querySelectorAll('.alert-dismissible');
  autoDismissAlerts.forEach(function (alertEl) {
    setTimeout(function () {
      const bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
      if (bsAlert) {
        bsAlert.close();
      }
    }, 5000);
  });

  // 2. Image upload preview for Create/Edit forms
  const imageInput = document.getElementById('listingImageInput');
  const previewContainer = document.getElementById('imagePreviewContainer');
  const previewImg = document.getElementById('imagePreview');

  if (imageInput && previewContainer && previewImg) {
    imageInput.addEventListener('change', function (event) {
      const file = event.target.files[0];
      if (file) {
        // Quick size check on client side (3MB limit)
        if (file.size > 3 * 1024 * 1024) {
          alert('File size exceeds 3MB. Please select a smaller image.');
          imageInput.value = '';
          previewContainer.classList.add('d-none');
          return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
          previewImg.src = e.target.result;
          previewContainer.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
      } else {
        previewContainer.classList.add('d-none');
      }
    });
  }

  // 3. Delete confirmation handler
  const confirmTriggers = document.querySelectorAll('[data-confirm]');
  confirmTriggers.forEach(function (el) {
    el.addEventListener('click', function (e) {
      const message = el.getAttribute('data-confirm') || 'Are you sure you want to proceed?';
      if (!confirm(message)) {
        e.preventDefault();
      }
    });
  });
});
