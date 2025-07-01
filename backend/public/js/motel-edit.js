FilePond.registerPlugin(
    FilePondPluginImagePreview,
    FilePondPluginFileValidateType
);

// Global variables
let selectedNewFiles = [];
let mainImageIndex = 0;
let selectedMainImageType = 'existing'; // 'existing' or 'new'
let selectedMainImageId = null; // for existing images

// Initialize FilePond
const inputElement = document.querySelector('input[type="file"].filepond');
const pond = FilePond.create(inputElement, {
    allowMultiple: true,
    acceptedFileTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
    instantUpload: false,
    labelIdle: 'Kéo và thả hình ảnh hoặc <span class="filepond--label-action">Chọn tệp</span>',
    credits: false,
    imagePreviewHeight: 100,
    allowImagePreview: true,
    maxFiles: 10,
    maxFileSize: '2MB',
    onaddfile: (error, file) => {
        if (error) {
            console.error('FilePond error on add file:', error);
            return;
        }
        selectedNewFiles.push(file.file);
        updateNewImagePreview();
        console.log('File added:', file.filename);
    },
    onremovefile: (error, file) => {
        if (file && file.file) {
            const index = selectedNewFiles.findIndex(f => f.name === file.file.name);
            if (index > -1) {
                selectedNewFiles.splice(index, 1);
                if (selectedMainImageType === 'new' && mainImageIndex >= selectedNewFiles.length) {
                    mainImageIndex = Math.max(0, selectedNewFiles.length - 1);
                    if (selectedNewFiles.length === 0) {
                        const firstExistingRadio = document.querySelector('.main-image-radio');
                        if (firstExistingRadio) {
                            selectedMainImageType = 'existing';
                            selectedMainImageId = firstExistingRadio.value;
                            firstExistingRadio.checked = true;
                        }
                    }
                }
                updateNewImagePreview();
            }
        }
        console.log('File removed:', file ? file.filename : 'unknown');
    }
});

// Update new image preview with main image selector
function updateNewImagePreview() {
    const container = document.getElementById('newImagePreviewContainer');
    const grid = document.getElementById('newImagePreviewGrid');

    if (selectedNewFiles.length === 0) {
        container.style.display = 'none';
        return;
    }
    container.style.display = 'block';
    grid.innerHTML = '';
    selectedNewFiles.forEach((file, index) => {
        const colDiv = document.createElement('div');
        colDiv.className = 'col-6 col-md-4 col-lg-3';
        const imageDiv = document.createElement('div');
        imageDiv.className = `image-preview-item ${selectedMainImageType === 'new' && index === mainImageIndex ? 'main-image' : ''}`;
        imageDiv.onclick = () => setMainImageNew(index);
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.alt = file.name;
        const badge = document.createElement('div');
        badge.className = 'main-image-badge';
        badge.innerHTML = selectedMainImageType === 'new' && index === mainImageIndex
            ? '<i class="fas fa-star"></i>'
            : (index + 1);
        badge.title = selectedMainImageType === 'new' && index === mainImageIndex
            ? 'Ảnh chính'
            : 'Click để chọn làm ảnh chính';
        const removeBtn = document.createElement('button');
        removeBtn.className = 'image-remove-btn';
        removeBtn.innerHTML = '<i class="fas fa-times"></i>';
        removeBtn.title = 'Xóa ảnh';
        removeBtn.type = 'button';
        removeBtn.onclick = (e) => {
            e.stopPropagation();
            removeNewImage(index);
        };
        const filename = document.createElement('div');
        filename.className = 'image-filename';
        filename.textContent = file.name;
        imageDiv.appendChild(img);
        imageDiv.appendChild(badge);
        imageDiv.appendChild(removeBtn);
        imageDiv.appendChild(filename);
        colDiv.appendChild(imageDiv);
        grid.appendChild(colDiv);
    });
}

// Set main image from new images
function setMainImageNew(index) {
    if (index >= 0 && index < selectedNewFiles.length) {
        selectedMainImageType = 'new';
        mainImageIndex = index;
        selectedMainImageId = null;
        document.querySelectorAll('.main-image-radio').forEach(radio => {
            radio.checked = false;
        });
        updateNewImagePreview();
        updateExistingImagesDisplay();
        showNotification('Đã chọn ảnh chính từ ảnh mới!', 'success');
    }
}

// Remove new image
function removeNewImage(index) {
    if (index >= 0 && index < selectedNewFiles.length) {
        const pondFiles = pond.getFiles();
        if (pondFiles[index]) {
            pond.removeFile(pondFiles[index]);
        }
        selectedNewFiles.splice(index, 1);
        if (selectedMainImageType === 'new') {
            if (mainImageIndex >= selectedNewFiles.length) {
                mainImageIndex = Math.max(0, selectedNewFiles.length - 1);
            } else if (index < mainImageIndex) {
                mainImageIndex--;
            }
            if (selectedNewFiles.length === 0) {
                const firstExistingRadio = document.querySelector('.main-image-radio');
                if (firstExistingRadio) {
                    selectedMainImageType = 'existing';
                    selectedMainImageId = firstExistingRadio.value;
                    firstExistingRadio.checked = true;
                }
            }
        }
        updateNewImagePreview();
        updateExistingImagesDisplay();
        showNotification('Đã xóa ảnh!', 'info');
    }
}

// Handle existing image main selection
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing image selection...');

    document.querySelectorAll('.main-image-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            console.log('Radio changed:', this.value, 'checked:', this.checked);
            if (this.checked) {
                selectedMainImageType = 'existing';
                selectedMainImageId = this.value;
                mainImageIndex = 0;

                // Uncheck other radios
                document.querySelectorAll('.main-image-radio').forEach(otherRadio => {
                    if (otherRadio !== this) {
                        otherRadio.checked = false;
                    }
                });

                updateNewImagePreview();
                updateExistingImagesDisplay();
                showNotification('Đã chọn ảnh chính từ ảnh hiện có!', 'success');
            }
        });

        // Add click event to the whole image item for easier selection
        const imageItem = radio.closest('.existing-image-item');
        if (imageItem) {
            imageItem.addEventListener('click', function(e) {
                // Don't trigger if clicking delete button
                if (!e.target.closest('.delete-existing-btn')) {
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change'));
                }
            });
        }
    });

    document.querySelectorAll('.delete-existing-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const imageId = this.getAttribute('data-image-id');
            const motelId = this.getAttribute('data-motel-id');

            if (confirm('Bạn có chắc muốn xóa ảnh này?')) {
                deleteExistingImage(imageId, motelId, this);
            }
        });
    });

    // Initialize existing main image selection
    const checkedRadio = document.querySelector('.main-image-radio:checked');
    if (checkedRadio) {
        console.log('Found checked radio:', checkedRadio.value);
        selectedMainImageType = 'existing';
        selectedMainImageId = checkedRadio.value;
        updateExistingImagesDisplay();
    } else {
        console.log('No checked radio found');
        // If no main image is selected, try to select the first one
        const firstRadio = document.querySelector('.main-image-radio');
        if (firstRadio) {
            firstRadio.checked = true;
            selectedMainImageType = 'existing';
            selectedMainImageId = firstRadio.value;
            updateExistingImagesDisplay();
        }
    }
});

// Update existing images display
function updateExistingImagesDisplay() {
    document.querySelectorAll('.existing-image-item').forEach(item => {
        const radio = item.querySelector('.main-image-radio');

        // Remove existing main-selected class and badges
        item.classList.remove('main-selected');
        const existingBadges = item.querySelectorAll('.main-image-badge');
        existingBadges.forEach(badge => {
            // Only remove badges that are NOT hardcoded in the HTML
            if (!badge.innerHTML.includes('Ảnh chính')) {
                badge.remove();
            }
        });

        if (radio && radio.checked && selectedMainImageType === 'existing') {
            item.classList.add('main-selected');
            // Add a temporary badge if no existing badge
            const existingBadge = item.querySelector('.main-image-badge');
            if (!existingBadge) {
                const newBadge = document.createElement('div');
                newBadge.className = 'main-image-badge temp-badge';
                newBadge.innerHTML = '<i class="fas fa-crown"></i><span>Ảnh chính</span>';
                item.querySelector('.image-wrapper').appendChild(newBadge);
            }
        } else {
            // Remove only temporary badges, keep original ones
            const tempBadges = item.querySelectorAll('.main-image-badge.temp-badge');
            tempBadges.forEach(badge => badge.remove());
        }
    });
}

// Delete existing image via AJAX with improved error handling
function deleteExistingImage(imageId, motelId, button) {
    const imageItem = button.closest('.existing-image-item');
    const originalButtonHtml = button.innerHTML;

    // Disable button and show loading
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    // Tạo URL đúng format
    const deleteUrl = `/motels/${motelId}/images/${imageId}/delete`;
    console.log('Deleting image with URL:', deleteUrl); // Debug log

    fetch(deleteUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        credentials: 'same-origin' // Đảm bảo gửi cookies
    })
    .then(async response => {
        const responseText = await response.text();
        console.log('Raw response:', responseText); // Debug log

        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error('Failed to parse JSON:', e);
            throw new Error('Server returned invalid JSON response');
        }

        if (!response.ok) {
            throw new Error(data.message || data.error || `HTTP error! status: ${response.status}`);
        }

        return data;
    })
    .then(data => {
        console.log('Parsed response data:', data); // Debug log

        // Restore button state
        button.disabled = false;
        button.innerHTML = originalButtonHtml;

        // Check various success indicators
        const isSuccess = data.success ||
                         (data.data && data.data.success) ||
                         (data.status === 'success') ||
                         (!data.error && !data.message?.includes('lỗi'));

        if (isSuccess) {
            // Remove image from DOM
            imageItem.remove();

            // Show success message
            const message = data.data?.message || data.message || 'Đã xóa ảnh thành công';
            showNotification(message, 'success');

            // Handle main image logic
            handleMainImageAfterDelete(imageId);
        } else {
            // Show error message
            const errorMsg = data.error || data.message || 'Có lỗi xảy ra khi xóa ảnh';
            showNotification(errorMsg, 'danger');
        }
    })
    .catch(error => {
        // Restore button state
        button.disabled = false;
        button.innerHTML = originalButtonHtml;

        console.error('Delete image error:', error);

        // Show user-friendly error message
        let errorMessage = 'Có lỗi xảy ra khi xóa ảnh';
        if (error.message.includes('HTTP error! status: 500')) {
            errorMessage = 'Lỗi server khi xóa ảnh. Vui lòng thử lại!';
        } else if (error.message.includes('Failed to fetch')) {
            errorMessage = 'Không thể kết nối đến server. Vui lòng kiểm tra kết nối mạng!';
        } else if (error.message) {
            errorMessage = error.message;
        }

        showNotification(errorMessage, 'danger');
    });
}

// Handle main image after deletion
function handleMainImageAfterDelete(deletedImageId) {
    const remainingRadios = document.querySelectorAll('.main-image-radio');

    if (selectedMainImageType === 'existing' && selectedMainImageId === deletedImageId) {
        if (remainingRadios.length > 0) {
            remainingRadios[0].checked = true;
            selectedMainImageId = remainingRadios[0].value;
            selectedMainImageType = 'existing';
            updateExistingImagesDisplay();
            showNotification('Đã tự động chọn ảnh chính mới từ ảnh còn lại', 'info');
        } else if (selectedNewFiles.length > 0) {
            selectedMainImageType = 'new';
            mainImageIndex = 0;
            selectedMainImageId = null;
            updateNewImagePreview();
            showNotification('Đã tự động chọn ảnh chính từ ảnh mới', 'info');
        } else {
            selectedMainImageType = null;
            selectedMainImageId = null;
            mainImageIndex = 0;
        }
    }
}

// Show notification
function showNotification(message, type = 'info') {
    document.querySelectorAll('.custom-notification').forEach(n => n.remove());

    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed custom-notification`;
    notification.style.cssText = `
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        max-width: 500px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border-radius: 8px;
    `;

    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 4000);
}

// Handle form submit
document.getElementById('motelForm').addEventListener('submit', function (e) {
    console.log('Form submitting...', {
        selectedMainImageType,
        selectedMainImageId,
        mainImageIndex,
        selectedNewFilesLength: selectedNewFiles.length
    });

    // Remove existing hidden image inputs
    const existingInputs = this.querySelectorAll('input[name="images[]"]:not(.filepond)');
    existingInputs.forEach(input => input.remove());

    // Add new files to form
    selectedNewFiles.forEach((file, index) => {
        const input = document.createElement('input');
        input.type = 'file';
        input.name = 'images[]';
        input.style.display = 'none';
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        input.files = dataTransfer.files;
        this.appendChild(input);
    });

    // Handle main image selection
    if (selectedMainImageType === 'existing' && selectedMainImageId) {
        console.log('Setting existing main image:', selectedMainImageId);

        // Make sure the correct radio is checked
        document.querySelectorAll('.main-image-radio').forEach(radio => {
            radio.checked = (radio.value === selectedMainImageId);
        });

        // Remove any new main image index
        const existingMainInput = this.querySelector('input[name="new_main_image_index"]');
        if (existingMainInput) {
            existingMainInput.remove();
        }
    } else if (selectedMainImageType === 'new' && selectedNewFiles.length > 0) {
        console.log('Setting new main image index:', mainImageIndex);

        const mainImageInput = document.createElement('input');
        mainImageInput.type = 'hidden';
        mainImageInput.name = 'new_main_image_index';
        mainImageInput.value = mainImageIndex;
        this.appendChild(mainImageInput);

        // Uncheck all existing radios when new image is main
        document.querySelectorAll('.main-image-radio').forEach(radio => {
            radio.checked = false;
        });
    }

    // Debug: Log form data before submit
    console.log('Final form state before submit:', {
        'selectedMainImageType': selectedMainImageType,
        'selectedMainImageId': selectedMainImageId,
        'mainImageIndex': mainImageIndex,
        'checkedRadios': Array.from(document.querySelectorAll('.main-image-radio:checked')).map(r => ({
            value: r.value,
            name: r.name,
            checked: r.checked
        }))
    });

    const submitBtn = this.querySelector('button[type="submit"]');
    if (submitBtn) {
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang cập nhật...';
        submitBtn.disabled = true;
        submitBtn.setAttribute('data-original-text', originalText);
    }
});

// Cleanup object URLs
window.addEventListener('beforeunload', () => {
    selectedNewFiles.forEach(file => {
        if (file instanceof File) {
            const objectUrl = URL.createObjectURL(file);
            URL.revokeObjectURL(objectUrl);
        }
    });
});
