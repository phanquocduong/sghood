FilePond.registerPlugin(
    FilePondPluginImagePreview,
    FilePondPluginFileValidateType
);

// Global variables
let selectedFiles = [];
let mainImageIndex = 0;

// Initialize FilePond
const inputElement = document.querySelector('input[type="file"].filepond');
if (inputElement) {
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
            selectedFiles.push(file.file);
            updateImagePreview();
            console.log('File added:', file.filename);
        },
        onremovefile: (error, file) => {
            if (file && file.file) {
                const index = selectedFiles.findIndex(f => f.name === file.file.name);
                if (index > -1) {
                    selectedFiles.splice(index, 1);
                    if (mainImageIndex >= selectedFiles.length) {
                        mainImageIndex = Math.max(0, selectedFiles.length - 1);
                    }
                    updateImagePreview();
                }
            }
            console.log('File removed:', file ? file.filename : 'unknown');
        }
    });
}

// Update image preview with main image selector
function updateImagePreview() {
    const container = document.getElementById('imagePreviewContainer');
    const grid = document.getElementById('imagePreviewGrid');
    const mainImageInput = document.getElementById('mainImageIndex');

    if (!container || !grid || !mainImageInput) return;

    if (selectedFiles.length === 0) {
        container.style.display = 'none';
        return;
    }

    container.style.display = 'block';
    grid.innerHTML = '';

    selectedFiles.forEach((file, index) => {
        const colDiv = document.createElement('div');
        colDiv.className = 'col-6 col-md-4 col-lg-3';

        const imageDiv = document.createElement('div');
        imageDiv.className = `image-preview-item ${index === mainImageIndex ? 'main-image' : ''}`;
        imageDiv.onclick = () => setMainImage(index);

        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.alt = file.name;

        const badge = document.createElement('div');
        badge.className = 'main-image-badge';
        badge.innerHTML = index === mainImageIndex ? '<i class="fas fa-star"></i>' : (index + 1);
        badge.title = index === mainImageIndex ? 'Ảnh chính' : 'Click để chọn làm ảnh chính';

        const removeBtn = document.createElement('button');
        removeBtn.className = 'image-remove-btn';
        removeBtn.innerHTML = '<i class="fas fa-times"></i>';
        removeBtn.title = 'Xóa ảnh';
        removeBtn.type = 'button';
        removeBtn.onclick = (e) => {
            e.stopPropagation();
            removeImage(index);
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

    mainImageInput.value = mainImageIndex;
}

// Set main image
function setMainImage(index) {
    if (index >= 0 && index < selectedFiles.length) {
        mainImageIndex = index;
        updateImagePreview();
        showNotification('Đã chọn ảnh chính!', 'success');
    }
}

// Remove image
function removeImage(index) {
    if (index >= 0 && index < selectedFiles.length) {
        const pond = FilePond.find(document.querySelector('input[type="file"].filepond'));
        if (pond) {
            const pondFiles = pond.getFiles();
            if (pondFiles[index]) {
                pond.removeFile(pondFiles[index]);
            }
        }

        selectedFiles.splice(index, 1);
        if (mainImageIndex >= selectedFiles.length) {
            mainImageIndex = Math.max(0, selectedFiles.length - 1);
        } else if (index < mainImageIndex) {
            mainImageIndex--;
        }

        updateImagePreview();
        showNotification('Đã xóa ảnh!', 'info');
    }
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = `
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}

// Handle form submit
const form = document.getElementById('motelForm');
if (form) {
    form.addEventListener('submit', function (e) {
        console.log('Submitting motelForm with files:', selectedFiles.map(f => f.name));
        console.log('Main image index:', mainImageIndex);

        // Uncomment to require at least one image
        // if (selectedFiles.length === 0) {
        //     e.preventDefault();
        //     showNotification('Vui lòng chọn ít nhất một hình ảnh!', 'danger');
        //     return;
        // }

        const existingInputs = this.querySelectorAll('input[name="images[]"]:not(.filepond)');
        existingInputs.forEach(input => input.remove());

        selectedFiles.forEach((file, index) => {
            const input = document.createElement('input');
            input.type = 'file';
            input.name = 'images[]';
            input.style.display = 'none';

            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            input.files = dataTransfer.files;

            this.appendChild(input);
        });

        const mainImageInput = document.getElementById('mainImageIndex');
        if (mainImageInput) {
            mainImageInput.value = mainImageIndex;
        } else {
            console.error('mainImageIndex input not found');
        }

        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn) {
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang xử lý...';
            submitBtn.disabled = true;
        } else {
            console.error('Submit button not found');
        }
    });
} else {
    console.error('motelForm not found');
}

// Cleanup object URLs
window.addEventListener('beforeunload', () => {
    selectedFiles.forEach(file => {
        if (file instanceof File) {
            URL.revokeObjectURL(URL.createObjectURL(file));
        }
    });
});

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});
