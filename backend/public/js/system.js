// motel index
// Ensure Bootstrap collapse works (requires Bootstrap JS)
// If not using Bootstrap JS, add custom toggle logic
document.addEventListener("DOMContentLoaded", function () {
    const filterButton = document.querySelector('[data-bs-toggle="collapse"]');
    filterButton.addEventListener("click", function () {
        const form = document.getElementById("filterForm");
        form.classList.toggle("show");
    });
});

// create motel
document.addEventListener("DOMContentLoaded", function () {
    const imageInput = document.getElementById("images");
    const preview = document.getElementById("image-preview");
    const form = document.getElementById("motelForm");
    let selectedFiles = []; // Mảng lưu trữ tất cả file đã chọn

    imageInput.addEventListener("change", function (event) {
        // Thêm file mới vào bộ sưu tập hiện có
        const newFiles = Array.from(event.target.files);
        selectedFiles = selectedFiles.concat(newFiles);

        // Hiển thị tất cả file (cũ + mới)
        displaySelectedImages(newFiles);
    });

    function updateFileInput() {
        // Tạo đối tượng DataTransfer mới
        const dataTransfer = new DataTransfer();

        // Thêm các file còn lại
        selectedFiles.forEach((file) => {
            dataTransfer.items.add(file);
        });

        // Cập nhật input file
        imageInput.files = dataTransfer.files;

        // Cập nhật chỉ số cho các input ẩn
        const hiddenInputs = form.querySelectorAll(
            'input[name="image_names[]"]'
        );
        hiddenInputs.forEach((input, i) => {
            input.dataset.fileIndex = i;
        });
    }

    function displaySelectedImages(newFiles) {
        // Chỉ hiển thị những hình ảnh mới thêm vào (không xóa những cái cũ)
        for (let i = 0; i < newFiles.length; i++) {
            const file = newFiles[i];
            if (file.type.startsWith("image/")) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const col = document.createElement("div");
                    col.className = "col-md-3 mb-2 position-relative";
                    col.dataset.fileIndex = selectedFiles.indexOf(file);

                    // Tạo input ẩn cho tên file
                    const hiddenInput = document.createElement("input");
                    hiddenInput.type = "hidden";
                    hiddenInput.name = "image_names[]";
                    hiddenInput.value = file.name;
                    hiddenInput.dataset.fileIndex = selectedFiles.indexOf(file);
                    form.appendChild(hiddenInput);

                    // Tạo nút xóa
                    const deleteBtn = document.createElement("button");
                    deleteBtn.innerHTML = "&times;";
                    deleteBtn.className =
                        "btn btn-sm btn-danger position-absolute";
                    deleteBtn.style.top = "5px";
                    deleteBtn.style.right = "20px";
                    deleteBtn.type = "button";
                    deleteBtn.addEventListener("click", function () {
                        // Xóa file khỏi mảng
                        const index = parseInt(col.dataset.fileIndex);
                        selectedFiles.splice(index, 1);

                        // Xóa input ẩn
                        const hiddenInputs = form.querySelectorAll(
                            'input[name="image_names[]"]'
                        );
                        hiddenInputs.forEach((input) => {
                            if (parseInt(input.dataset.fileIndex) === index) {
                                input.remove();
                            }
                        });

                        // Xóa phần xem trước
                        col.remove();

                        // Cập nhật input file
                        updateFileInput();
                    });

                    const img = document.createElement("img");
                    img.src = e.target.result;
                    img.className = "img-fluid";
                    img.style.maxHeight = "150px";

                    col.appendChild(img);
                    col.appendChild(deleteBtn);
                    preview.appendChild(col);
                };
                reader.readAsDataURL(file);
            }
        }
    }
});
