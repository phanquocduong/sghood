document.addEventListener("DOMContentLoaded", function () {
    const imageInput = document.getElementById("images");
    const preview = document.getElementById("image-preview");
    const form = document.getElementById("roomForm");

    if (!imageInput || !preview || !form) {
        console.warn("Không tìm thấy các phần tử cần thiết cho upload ảnh");
        return;
    }

    let selectedFiles = []; // Mảng lưu trữ tất cả file đã chọn (for preview purposes)

    imageInput.addEventListener("change", function (event) {
        console.log("File input changed");

        const newFiles = Array.from(event.target.files);

        if (newFiles.length === 0) {
            console.log("Không có file nào được chọn");
            return;
        }

        console.log("Số file mới:", newFiles.length);

        // Thêm files mới vào mảng selectedFiles (for preview purposes)
        selectedFiles = selectedFiles.concat(newFiles);

        // Hiển thị tất cả file mới
        displaySelectedImages(newFiles);
    });

    function displaySelectedImages(newFiles) {
        console.log("Đang hiển thị", newFiles.length, "ảnh mới");

        newFiles.forEach((file, index) => {
            if (file && file.type && file.type.startsWith("image/")) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    console.log("Đã load ảnh:", file.name);

                    // Tính index thực tế trong selectedFiles
                    const actualIndex = selectedFiles.length - newFiles.length + index;

                    const col = document.createElement("div");
                    col.className = "col-md-3 mb-2 position-relative image-preview-item";
                    col.dataset.fileIndex = actualIndex;

                    const imageContainer = document.createElement("div");
                    imageContainer.className = "border rounded overflow-hidden position-relative";
                    imageContainer.style.cssText = "height: 150px; background-color: #f8f9fa;";

                    const img = document.createElement("img");
                    img.src = e.target.result;
                    img.className = "img-fluid";
                    img.style.cssText = "width: 100%; height: 100%; object-fit: cover;";
                    img.alt = file.name;

                    const deleteBtn = document.createElement("button");
                    deleteBtn.innerHTML = "×";
                    deleteBtn.className = "btn btn-sm btn-danger position-absolute";
                    deleteBtn.style.cssText = "top: 5px; right: 5px; z-index: 10; width: 25px; height: 25px; border-radius: 50%; padding: 0; font-size: 14px; line-height: 1;";
                    deleteBtn.type = "button";
                    deleteBtn.title = "Xóa ảnh";
                    deleteBtn.addEventListener("click", function () {
                        removeImage(col);
                    });

                    const fileLabel = document.createElement("div");
                    fileLabel.className = "text-truncate small text-muted mt-1";
                    fileLabel.textContent = file.name;
                    fileLabel.title = file.name;

                    imageContainer.appendChild(img);
                    imageContainer.appendChild(deleteBtn);
                    col.appendChild(imageContainer);
                    col.appendChild(fileLabel);
                    preview.appendChild(col);

                    console.log("Đã thêm ảnh vào preview:", file.name);
                };

                reader.onerror = function () {
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

            console.log("Trying to remove image at index:", index, "with selectedFiles length:", selectedFiles.length);

            if (index >= 0 && index < selectedFiles.length) {
                // Xóa file khỏi mảng
                selectedFiles.splice(index, 1);

                // Xóa phần xem trước
                col.remove();

                // Cập nhật lại file input
                const dataTransfer = new DataTransfer();
                selectedFiles.forEach((file) => {
                    dataTransfer.items.add(file);
                });
                imageInput.files = dataTransfer.files;

                // Cập nhật lại index cho các phần tử còn lại
                const previewItems = preview.querySelectorAll('.image-preview-item');
                previewItems.forEach((item, newIndex) => {
                    item.dataset.fileIndex = newIndex;
                });

                console.log("Đã xóa ảnh, còn lại:", selectedFiles.length, "ảnh");

                // Nếu không còn file nào, reset input
                if (selectedFiles.length === 0) {
                    imageInput.value = '';
                }
            } else {
                console.error("Index không hợp lệ:", index, "selectedFiles length:", selectedFiles.length);
            }
        } catch (error) {
            console.error("Lỗi khi xóa ảnh:", error);
        }
    }

    function showError(message) {
        const alertDiv = document.createElement("div");
        alertDiv.className = "alert alert-warning alert-dismissible fade show position-fixed";
        alertDiv.style.cssText = "top: 20px; right: 20px; z-index: 9999; max-width: 400px;";
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);

        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
});
