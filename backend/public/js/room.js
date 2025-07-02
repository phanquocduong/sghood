// filepath: d:\DuAnTotNghiep\troviet-platform\backend\public\js\room.js
// Register FilePond plugins
FilePond.registerPlugin(
    FilePondPluginImagePreview,
    FilePondPluginFileValidateType
);

// Global variables
let selectedFiles = [];
let mainImageIndex = 0;

// Initialize FilePond
const inputElement = document.querySelector('input[type="file"].filepond');
const pond = FilePond.create(inputElement, {
    allowMultiple: true,
    acceptedFileTypes: ["image/jpeg", "image/png", "image/gif", "image/webp"],
    instantUpload: false,
    labelIdle:
        'Kéo và thả hình ảnh hoặc <span class="filepond--label-action">Chọn tệp</span>',
    credits: false,
    imagePreviewHeight: 100,
    allowImagePreview: true,
    maxFiles: 10,
    maxFileSize: "2MB",
    onaddfile: (error, file) => {
        if (error) {
            console.error("FilePond error on add file:", error);
            return;
        }

        selectedFiles.push(file.file);
        updateImagePreview();
        console.log("File added:", file.filename);
    },
    onremovefile: (error, file) => {
        if (file && file.file) {
            const index = selectedFiles.findIndex(
                (f) => f.name === file.file.name
            );
            if (index > -1) {
                selectedFiles.splice(index, 1);

                // Adjust main image index if necessary
                if (mainImageIndex >= selectedFiles.length) {
                    mainImageIndex = Math.max(0, selectedFiles.length - 1);
                }

                updateImagePreview();
            }
        }
        console.log("File removed:", file ? file.filename : "unknown");
    },
});

// Update image preview with main image selector
function updateImagePreview() {
    const container = document.getElementById("imagePreviewContainer");
    const grid = document.getElementById("imagePreviewGrid");
    const mainImageInput = document.getElementById("mainImageIndex");

    if (selectedFiles.length === 0) {
        container.style.display = "none";
        return;
    }

    container.style.display = "block";
    grid.innerHTML = "";

    selectedFiles.forEach((file, index) => {
        const colDiv = document.createElement("div");
        colDiv.className = "col-6 col-md-4 col-lg-3";

        const imageDiv = document.createElement("div");
        imageDiv.className = `image-preview-item ${
            index === mainImageIndex ? "main-image" : ""
        }`;
        imageDiv.onclick = () => setMainImage(index);

        // Create image element
        const img = document.createElement("img");
        img.src = URL.createObjectURL(file);
        img.alt = file.name;

        // Create main image badge
        const badge = document.createElement("div");
        badge.className = "main-image-badge";
        badge.innerHTML =
            index === mainImageIndex
                ? '<i class="fas fa-star"></i>'
                : index + 1;
        badge.title =
            index === mainImageIndex
                ? "Ảnh chính"
                : "Click để chọn làm ảnh chính";

        // Create remove button
        const removeBtn = document.createElement("button");
        removeBtn.className = "image-remove-btn";
        removeBtn.innerHTML = '<i class="fas fa-times"></i>';
        removeBtn.title = "Xóa ảnh";
        removeBtn.type = "button";
        removeBtn.onclick = (e) => {
            e.stopPropagation();
            removeImage(index);
        };

        // Create filename label
        const filename = document.createElement("div");
        filename.className = "image-filename";
        filename.textContent = file.name;

        imageDiv.appendChild(img);
        imageDiv.appendChild(badge);
        imageDiv.appendChild(removeBtn);
        imageDiv.appendChild(filename);
        colDiv.appendChild(imageDiv);
        grid.appendChild(colDiv);
    });

    // Update hidden input
    mainImageInput.value = mainImageIndex;
}

// Set main image
function setMainImage(index) {
    if (index >= 0 && index < selectedFiles.length) {
        mainImageIndex = index;
        updateImagePreview();

        // Show success message
        showNotification("Đã chọn ảnh chính!", "success");
    }
}

// Remove image
function removeImage(index) {
    if (index >= 0 && index < selectedFiles.length) {
        // Remove from FilePond
        const pondFiles = pond.getFiles();
        if (pondFiles[index]) {
            pond.removeFile(pondFiles[index]);
        }

        // Remove from our array
        selectedFiles.splice(index, 1);

        // Adjust main image index
        if (mainImageIndex >= selectedFiles.length) {
            mainImageIndex = Math.max(0, selectedFiles.length - 1);
        } else if (index < mainImageIndex) {
            mainImageIndex--;
        }

        updateImagePreview();

        // Show success message
        showNotification("Đã xóa ảnh!", "info");
    }
}

// Show notification
function showNotification(message, type = "info") {
    // Create notification element
    const notification = document.createElement("div");
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

    // Auto remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}

// Handle form submit
document.getElementById("roomForm").addEventListener("submit", function (e) {
    // Validate at least one image
    // if (selectedFiles.length === 0) {
    //     e.preventDefault();
    //     showNotification('Vui lòng chọn ít nhất một hình ảnh!', 'danger');
    //     return;
    // }

    // Clear existing file inputs
    const existingInputs = this.querySelectorAll(
        'input[name="images[]"]:not(.filepond)'
    );
    existingInputs.forEach((input) => input.remove());

    // Create file inputs for each selected file
    selectedFiles.forEach((file, index) => {
        const input = document.createElement("input");
        input.type = "file";
        input.name = "images[]";
        input.style.display = "none";

        // Create new FileList with single file
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        input.files = dataTransfer.files;

        this.appendChild(input);
    });

    // Set main image index
    document.getElementById("mainImageIndex").value = mainImageIndex;

    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML =
        '<i class="fas fa-spinner fa-spin me-2"></i>Đang xử lý...';
    submitBtn.disabled = true;

    // Form will submit normally
});

// Cleanup object URLs when page unloads
window.addEventListener("beforeunload", () => {
    selectedFiles.forEach((file) => {
        if (file instanceof File) {
            URL.revokeObjectURL(URL.createObjectURL(file));
        }
    });
});

// Initialize tooltips if Bootstrap is available
document.addEventListener("DOMContentLoaded", function () {
    if (typeof bootstrap !== "undefined") {
        const tooltipTriggerList = [].slice.call(
            document.querySelectorAll("[title]")
        );
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});
