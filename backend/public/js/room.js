// Motel index - Filter toggle functionality
document.addEventListener("DOMContentLoaded", function () {
    const filterButton = document.querySelector('[data-bs-toggle="collapse"]');
    if (filterButton) {
        filterButton.addEventListener("click", function () {
            const form = document.getElementById("filterForm");
            if (form) {
                form.classList.toggle("show");
            }
        });
    }
});

// Create motel - Image upload functionality
document.addEventListener("DOMContentLoaded", function () {
    // Kiểm tra xem các phần tử có tồn tại không
    const imageInput = document.getElementById("images");
    const preview = document.getElementById("image-preview");
    const form = document.getElementById("roomForm");

    // Nếu không tìm thấy các phần tử cần thiết, thoát
    if (!imageInput || !preview || !form) {
        console.warn("Không tìm thấy các phần tử cần thiết cho upload ảnh");
        return;
    }

    let selectedFiles = []; // Mảng lưu trữ tất cả file đã chọn

    imageInput.addEventListener("change", function (event) {
        console.log("File input changed"); // Debug log

        // Thêm file mới vào bộ sưu tập hiện có
        const newFiles = Array.from(event.target.files);

        if (newFiles.length === 0) {
            console.log("Không có file nào được chọn");
            return;
        }

        console.log("Số file mới:", newFiles.length); // Debug log

        // Thêm files mới vào mảng selectedFiles
        selectedFiles = selectedFiles.concat(newFiles);

        // Hiển thị tất cả file mới
        displaySelectedImages(newFiles);

        // Reset input để có thể chọn lại cùng file
        event.target.value = '';
    });

    function updateFileInput() {
        try {
            // Tạo đối tượng DataTransfer mới
            const dataTransfer = new DataTransfer();

            // Thêm các file còn lại
            selectedFiles.forEach((file) => {
                dataTransfer.items.add(file);
            });

            // Cập nhật input file
            imageInput.files = dataTransfer.files;

            // Cập nhật lại tất cả các index
            updateAllIndexes();
        } catch (error) {
            console.error("Lỗi khi cập nhật file input:", error);
        }
    }

    function updateAllIndexes() {
        // Cập nhật lại tất cả các index cho preview items
        const previewItems = preview.querySelectorAll('.image-preview-item');
        previewItems.forEach((item, index) => {
            item.dataset.fileIndex = index;
        });

        // Cập nhật lại tất cả các index cho hidden inputs
        const hiddenInputs = form.querySelectorAll('input[name="image_names[]"]');
        hiddenInputs.forEach((input, index) => {
            input.dataset.fileIndex = index;
        });
    }

    function displaySelectedImages(newFiles) {
        console.log("Đang hiển thị", newFiles.length, "ảnh mới"); // Debug log

        newFiles.forEach((file, index) => {
            if (file && file.type && file.type.startsWith("image/")) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    console.log("Đã load ảnh:", file.name); // Debug log

                    // Tính index thực tế trong selectedFiles
                    const actualIndex = selectedFiles.length - newFiles.length + index;

                    const col = document.createElement("div");
                    col.className = "col-md-3 mb-2 position-relative image-preview-item";
                    col.dataset.fileIndex = actualIndex;

                    // Tạo input ẩn cho tên file
                    const hiddenInput = document.createElement("input");
                    hiddenInput.type = "hidden";
                    hiddenInput.name = "image_names[]";
                    hiddenInput.value = file.name;
                    hiddenInput.dataset.fileIndex = actualIndex;
                    form.appendChild(hiddenInput);

                    // Tạo container cho ảnh
                    const imageContainer = document.createElement("div");
                    imageContainer.className = "border rounded overflow-hidden position-relative";
                    imageContainer.style.cssText = "height: 150px; background-color: #f8f9fa;";

                    // Tạo ảnh
                    const img = document.createElement("img");
                    img.src = e.target.result;
                    img.className = "img-fluid";
                    img.style.cssText = "width: 100%; height: 100%; object-fit: cover;";
                    img.alt = file.name;

                    // Tạo nút xóa
                    const deleteBtn = document.createElement("button");
                    deleteBtn.innerHTML = "&times;";
                    deleteBtn.className = "btn btn-sm btn-danger position-absolute";
                    deleteBtn.style.cssText = "top: 5px; right: 5px; z-index: 10; width: 25px; height: 25px; border-radius: 50%; padding: 0; font-size: 14px; line-height: 1;";
                    deleteBtn.type = "button";
                    deleteBtn.title = "Xóa ảnh";
                    deleteBtn.addEventListener("click", function () {
                        removeImage(col);
                    });

                    // Tạo label hiển thị tên file
                    const fileLabel = document.createElement("div");
                    fileLabel.className = "text-truncate small text-muted mt-1";
                    fileLabel.textContent = file.name;
                    fileLabel.title = file.name;

                    // Ghép các phần tử
                    imageContainer.appendChild(img);
                    imageContainer.appendChild(deleteBtn);
                    col.appendChild(imageContainer);
                    col.appendChild(fileLabel);
                    preview.appendChild(col);

                    console.log("Đã thêm ảnh vào preview:", file.name); // Debug log
                };

                reader.onerror = function() {
                    console.error("Lỗi khi đọc file:", file.name);
                    showError("Không thể đọc file: " + file.name);
                };

                reader.readAsDataURL(file);
            } else {
                console.warn("File không phải là ảnh:", file?.name || "Unknown file");
                showError("File không phải là ảnh: " + (file?.name || "Unknown file"));
            }
        });
    }

    function removeImage(col) {
        try {
            const index = parseInt(col.dataset.fileIndex);

            if (index >= 0 && index < selectedFiles.length) {
                // Xóa file khỏi mảng
                selectedFiles.splice(index, 1);

                // Xóa input ẩn tương ứng
                const hiddenInputs = form.querySelectorAll('input[name="image_names[]"]');
                hiddenInputs.forEach((input) => {
                    if (parseInt(input.dataset.fileIndex) === index) {
                        input.remove();
                    }
                });

                // Xóa phần xem trước
                col.remove();

                // Cập nhật input file và index
                updateFileInput();

                console.log("Đã xóa ảnh, còn lại:", selectedFiles.length, "ảnh");
            } else {
                console.error("Index không hợp lệ:", index);
            }
        } catch (error) {
            console.error("Lỗi khi xóa ảnh:", error);
        }
    }

    function showError(message) {
        // Tạo toast notification hoặc alert đơn giản
        const alertDiv = document.createElement("div");
        alertDiv.className = "alert alert-warning alert-dismissible fade show position-fixed";
        alertDiv.style.cssText = "top: 20px; right: 20px; z-index: 9999; max-width: 400px;";
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);

        // Tự động xóa sau 5 giây
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
});

// Room management functionality (có thể thêm sau)
document.addEventListener("DOMContentLoaded", function () {
    // Room-specific functionality can be added here
    console.log("Room management functionality loaded");
});

// Utility functions
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function validateImageFile(file) {
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    const maxSize = 5 * 1024 * 1024; // 5MB

    if (!allowedTypes.includes(file.type)) {
        return { valid: false, message: 'Chỉ chấp nhận file ảnh (JPEG, PNG, GIF, WebP)' };
    }

    if (file.size > maxSize) {
        return { valid: false, message: 'File ảnh không được vượt quá 5MB' };
    }

    return { valid: true };
}
