// document.addEventListener("DOMContentLoaded", function () {
//     const filterButton = document.querySelector('[data-bs-toggle="collapse"]');
//     if (filterButton) {
//         filterButton.addEventListener("click", function () {
//             const form = document.getElementById("filterForm");
//             if (form) {
//                 form.classList.toggle("show");
//             }
//         });
//     }

//     // Logic cho create motel
//     const createImageInput = document.getElementById("images");
//     const createPreview = document.getElementById("image-preview");
//     const createForm = document.getElementById("motelForm");
//     let createSelectedFiles = []; // Mảng lưu trữ tất cả file đã chọn trong create

//     if (createImageInput && createPreview && createForm) {
//         createImageInput.addEventListener("change", function (event) {
//             const newFiles = Array.from(event.target.files);
//             createSelectedFiles = createSelectedFiles.concat(newFiles);
//             displaySelectedImages(
//                 newFiles,
//                 createPreview,
//                 createForm,
//                 createSelectedFiles,
//                 createImageInput
//             );
//         });

//         createForm.addEventListener("submit", function () {
//             const deleteImages = createForm.querySelectorAll(
//                 'input[name="delete_images[]"]'
//             );
//             console.log(
//                 "Images to delete (create):",
//                 Array.from(deleteImages).map((input) => input.value)
//             );
//         });
//     }

//     // Logic cho edit motel
//     const existingImagePreview = document.getElementById(
//         "existing-image-preview"
//     );
//     const editImageInput = document.getElementById("images");
//     const editPreview = document.getElementById("image-preview");
//     const editForm = document.getElementById("motelForm");
//     let editSelectedFiles = []; // Mảng lưu trữ file mới trong edit

//     if (existingImagePreview && editForm) {
//         const existingImages = JSON.parse(
//             existingImagePreview.dataset.images || "[]"
//         );

//         existingImages.forEach((image) => {
//             const col = document.createElement("div");
//             col.className = "col-md-3 mb-2 position-relative image-item";
//             col.dataset.imageId = image.id;

//             const radioInput = document.createElement("input");
//             radioInput.type = "radio";
//             radioInput.name = "main_image_index";
//             radioInput.value = image.id;
//             radioInput.className = "position-absolute";
//             radioInput.style.top = "5px";
//             radioInput.style.left = "5px";
//             if (image.is_main == 1) {
//                 radioInput.checked = true;
//             }

//             const img = document.createElement("img");
//             img.src = image.image_url;
//             img.className = "img-fluid rounded shadow-sm";
//             img.style.maxHeight = "100px";

//             const deleteBtn = document.createElement("button");
//             deleteBtn.innerHTML = "×";
//             deleteBtn.className = "btn btn-sm btn-danger delete-btn";
//             deleteBtn.type = "button";
//             deleteBtn.addEventListener("click", function () {
//                 const imageId = col.dataset.imageId;
//                 const hiddenInput = document.createElement("input");
//                 hiddenInput.type = "hidden";
//                 hiddenInput.name = "delete_images[]";
//                 hiddenInput.value = imageId;
//                 const formElement = document.querySelector("#motelForm");
//                 formElement.appendChild(hiddenInput);
//                  console.log("Thêm input xoá: ", hiddenInput);
//                 col.remove();

//                 updateFileInput(selectedFiles, imageInput);
//             });

//             col.appendChild(radioInput);
//             col.appendChild(img);
//             col.appendChild(deleteBtn);
//             existingImagePreview.appendChild(col);
//         });

//         if (editImageInput) {
//             editImageInput.addEventListener("change", function (event) {
//                 const newFiles = Array.from(event.target.files);
//                 editSelectedFiles = editSelectedFiles.concat(newFiles);
//                 displaySelectedImages(
//                     newFiles,
//                     editPreview,
//                     editForm,
//                     editSelectedFiles,
//                     editImageInput,
//                     existingImages.length
//                 );
//             });
//         }

//         editForm.addEventListener("submit", function () {
//             const deleteImages = editForm.querySelectorAll(
//                 'input[name="delete_images[]"]'
//             );
//             console.log(
//                 "Images to delete (edit):",
//                 Array.from(deleteImages).map((input) => input.value)
//             );
//         });
//     }

//     // Hàm dùng chung để hiển thị ảnh và xử lý xóa
//     function displaySelectedImages(
//         newFiles,
//         preview,
//         form,
//         selectedFiles,
//         imageInput,
//         existingImagesCount = 0
//     ) {
//         for (let i = 0; i < newFiles.length; i++) {
//             const file = newFiles[i];
//             if (file.type.startsWith("image/")) {
//                 const reader = new FileReader();
//                 reader.onload = function (e) {
//                     const col = document.createElement("div");
//                     col.className =
//                         "col-md-3 mb-2 position-relative image-item";
//                     col.dataset.fileIndex = selectedFiles.indexOf(file);

//                     // Tạo input ẩn cho tên file
//                     const hiddenInput = document.createElement("input");
//                     hiddenInput.type = "hidden";
//                     hiddenInput.name = "image_names[]";
//                     hiddenInput.value = file.name;
//                     hiddenInput.dataset.fileIndex = selectedFiles.indexOf(file);
//                     form.appendChild(hiddenInput);

//                     // Tạo radio button để chọn ảnh chính
//                     const radioInput = document.createElement("input");
//                     radioInput.type = "hidden";
//                     radioInput.name = "main_image_index";
//                     radioInput.value =
//                         selectedFiles.indexOf(file) + existingImagesCount;
//                     radioInput.className = "position-absolute";
//                     radioInput.style.top = "5px";
//                     radioInput.style.left = "5px";
//                     if (
//                         selectedFiles.indexOf(file) === 0 &&
//                         existingImagesCount === 0
//                     ) {
//                         radioInput.checked = true; // Mặc định ảnh đầu tiên là chính nếu không có ảnh hiện tại
//                     }

//                     // Tạo nút xóa
//                     const deleteBtn = document.createElement("button");
//                     deleteBtn.innerHTML = "×";
//                     deleteBtn.className =
//                         "btn btn-sm btn-danger position-absolute delete-btn";
//                     deleteBtn.style.top = "5px";
//                     deleteBtn.style.right = "5px";
//                     deleteBtn.type = "button";
//                     deleteBtn.addEventListener("click", function () {
//                         const index = parseInt(col.dataset.fileIndex);
//                         selectedFiles.splice(index, 1);

//                         // Xóa input ẩn tương ứng
//                         const hiddenInputs = form.querySelectorAll(
//                             'input[name="image_names[]"]'
//                         );
//                         hiddenInputs.forEach((input) => {
//                             if (parseInt(input.dataset.fileIndex) === index) {
//                                 input.remove();
//                             }
//                         });

//                         // Xóa ảnh khỏi form nếu là ảnh mới
//                         updateFileInput(selectedFiles, imageInput);
//                         col.remove();
//                     });

//                     const img = document.createElement("img");
//                     img.src = e.target.result;
//                     img.className = "img-fluid";
//                     img.style.maxHeight = "150px";

//                     col.appendChild(radioInput);
//                     col.appendChild(img);
//                     col.appendChild(deleteBtn);
//                     preview.appendChild(col);
//                 };
//                 reader.readAsDataURL(file);
//             }
//         }

//         // Đảm bảo input ẩn main_image_index luôn tồn tại và được cập nhật
//         let mainImageInput = form.querySelector(
//             'input[name="main_image_index"]'
//         );
//         if (!mainImageInput) {
//             mainImageInput = document.createElement("input");
//             mainImageInput.type = "hidden";
//             mainImageInput.name = "main_image_index";
//             form.appendChild(mainImageInput);
//         }
//         mainImageInput.value =
//             existingImagesCount > 0 ? existingImages[0].id : "0";

//         // Cập nhật giá trị main_image_index khi radio thay đổi
//         const radioInputs = preview.querySelectorAll(
//             'input[name="main_image_index"]'
//         );
//         radioInputs.forEach((radio) => {
//             radio.addEventListener("change", function () {
//                 mainImageInput.value = this.value;
//                 console.log("Selected main image index:", this.value); // Debug
//             });
//         });
//     }

//     function updateFileInput(selectedFiles, imageInput) {
//         const dataTransfer = new DataTransfer();
//         selectedFiles.forEach((file) => {
//             dataTransfer.items.add(file);
//         });
//         imageInput.files = dataTransfer.files;

//         const hiddenInputs = document.querySelectorAll(
//             'input[name="image_names[]"]'
//         );
//         hiddenInputs.forEach((input, i) => {
//             input.dataset.fileIndex = i;
//         });
//     }
// });
