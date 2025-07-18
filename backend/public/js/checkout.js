// Biến tạm để lưu trữ dữ liệu inventory cho mỗi checkout
let inventoryData = {};

// Biến để lưu trữ dữ liệu gốc từ server
let originalInventoryData = {};

// Hàm xóa hình ảnh hiện tại
function removeExistingImage(button, imagePath, checkoutId) {
    if (!confirm("Bạn có chắc chắn muốn xóa hình ảnh này không?")) {
        return;
    }

    const imageContainer = button.closest(".col-4");
    const hiddenInput = imageContainer.querySelector(
        'input[name="existing_images[]"]'
    );

    if (hiddenInput) {
        hiddenInput.remove();
    }

    const form = document.getElementById(`checkoutForm${checkoutId}`);
    const deleteInput = document.createElement("input");
    deleteInput.type = "hidden";
    deleteInput.name = "deleted_images[]";
    deleteInput.value = imagePath;
    form.appendChild(deleteInput);

    imageContainer.style.display = "none";
}

// Hàm preview hình ảnh
function previewImages(input, checkoutId) {
    const previewContainer = document.getElementById(
        `image_preview_${checkoutId}`
    );
    if (!previewContainer) return;

    previewContainer.innerHTML = "";

    if (input.files && input.files.length > 0) {
        Array.from(input.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const col = document.createElement("div");
                col.className = "col-4 mb-2";
                col.innerHTML = `
                    <div class="image-preview-item position-relative">
                        <img src="${e.target.result}"
                             class="img-fluid rounded"
                             style="max-height: 80px; object-fit: cover; width: 100%;"
                             alt="Preview">
                        <button type="button"
                                class="btn btn-sm btn-danger position-absolute top-0 end-0"
                                onclick="removeNewImage(this, ${index}, ${checkoutId})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                previewContainer.appendChild(col);
            };
            reader.readAsDataURL(file);
        });
    }
}

// Hàm xóa hình ảnh mới
function removeNewImage(button, imageIndex, checkoutId) {
    if (!confirm("Bạn có chắc chắn muốn xóa hình ảnh này không?")) {
        return;
    }

    const imageContainer = button.closest(".col-4");
    const fileInput = document.querySelector(`#images_input_${checkoutId}`);

    imageContainer.remove();

    if (fileInput) {
        const dt = new DataTransfer();
        const files = fileInput.files;

        for (let i = 0; i < files.length; i++) {
            if (i !== imageIndex) {
                dt.items.add(files[i]);
            }
        }
        fileInput.files = dt.files;
    }
}

// Hàm lấy dữ liệu inventory từ server hoặc local storage
function getInventoryDataFromServer(checkoutId) {
    // Kiểm tra xem có dữ liệu trong window object không (được truyền từ server)
    if (window.existingInventoryData && window.existingInventoryData[checkoutId]) {
        return window.existingInventoryData[checkoutId].map((item) => ({
            id: Date.now() + Math.random(),
            item_name: item.item_name || "",
            item_condition: item.item_condition || "",
            item_cost: item.item_cost || 0,
        }));
    }

    // Nếu không có dữ liệu từ server, tạo mảng rỗng
    return [];
}

// Khởi tạo dữ liệu inventory cho một checkout
function initializeInventoryData(checkoutId, forceReload = false) {
    console.log(
        `Initializing inventory data for checkout ${checkoutId}`,
        { forceReload }
    );

    // Nếu đã có dữ liệu và không force reload, giữ nguyên
    if (inventoryData[checkoutId] && !forceReload) {
        console.log("Using existing inventory data");
        updateInventoryDisplay(checkoutId);
        updateDeductionTotal(checkoutId);
        return;
    }

    // Lấy dữ liệu từ server hoặc sử dụng dữ liệu gốc đã lưu
    let existingData = null;
    if (originalInventoryData[checkoutId]) {
        existingData = JSON.parse(JSON.stringify(originalInventoryData[checkoutId]));
    } else {
        existingData = getInventoryDataFromServer(checkoutId);
        // Lưu dữ liệu gốc
        originalInventoryData[checkoutId] = JSON.parse(JSON.stringify(existingData));
    }

    inventoryData[checkoutId] = existingData;

    // Đảm bảo mỗi item có id duy nhất
    inventoryData[checkoutId] = inventoryData[checkoutId].map((item) => ({
        ...item,
        id: item.id || Date.now() + Math.random(),
    }));

    updateInventoryDisplay(checkoutId);
    updateDeductionTotal(checkoutId);

    console.log("Inventory data initialized:", inventoryData[checkoutId]);
}

// Hàm reset dữ liệu về trạng thái ban đầu
function resetInventoryData(checkoutId) {
    console.log(`Resetting inventory data for checkout ${checkoutId}`);

    if (originalInventoryData[checkoutId]) {
        inventoryData[checkoutId] = JSON.parse(JSON.stringify(originalInventoryData[checkoutId]));

        // Đảm bảo mỗi item có id duy nhất
        inventoryData[checkoutId] = inventoryData[checkoutId].map((item) => ({
            ...item,
            id: item.id || Date.now() + Math.random(),
        }));

        updateInventoryDisplay(checkoutId);
        updateDeductionTotal(checkoutId);

        console.log("Inventory data reset to original:", inventoryData[checkoutId]);
    }
}

// Thêm một item mới vào mảng tạm
function addInventoryItem(checkoutId) {
    if (!inventoryData[checkoutId]) {
        inventoryData[checkoutId] = [];
    }

    const newItem = {
        id: Date.now() + Math.random(),
        item_name: "",
        item_condition: "",
        item_cost: 0,
        item_quantity: 1,
    };

    inventoryData[checkoutId].push(newItem);
    updateInventoryDisplay(checkoutId);
    updateDeductionTotal(checkoutId);

    console.log(`Added new item to checkout ${checkoutId}:`, newItem);
}

// Xóa item khỏi mảng tạm
function removeInventoryItem(checkoutId, itemId) {
    if (!confirm("Bạn có chắc chắn muốn xóa mục này không?")) {
        return;
    }

    if (inventoryData[checkoutId]) {
        inventoryData[checkoutId] = inventoryData[checkoutId].filter(
            (item) => item.id !== itemId
        );
        updateInventoryDisplay(checkoutId);
        updateDeductionTotal(checkoutId);
        console.log(`Removed item ${itemId} from checkout ${checkoutId}`);
    }
}

// Cập nhật dữ liệu item trong mảng tạm
function updateInventoryItem(checkoutId, itemId, field, value) {
    if (inventoryData[checkoutId]) {
        const item = inventoryData[checkoutId].find(
            (item) => item.id === itemId
        );
        if (item) {
            if (field === "item_cost") {
                item[field] = parseFloat(value) || 0;
            } else {
                item[field] = value;
            }
            updateDeductionTotal(checkoutId);
            console.log(
                `Updated item ${itemId} in checkout ${checkoutId}:`,
                item
            );
        }
    }
}

// Hiển thị dữ liệu inventory từ mảng tạm
function updateInventoryDisplay(checkoutId) {
    const container = document.getElementById(
        `inventory_items_container_${checkoutId}`
    );
    if (!container) return;

    container.innerHTML = "";

    // Thêm header cho các cột
    const headerDiv = document.createElement("div");
    headerDiv.className = "row g-2 fw-bold text-muted small mb-2";
    headerDiv.innerHTML = `
        <div class="col-md-5">Tên mục</div>
        <div class="col-md-4">Tình trạng</div>
        <div class="col-md-2">Chi phí</div>
        <div class="col-md-1">Xóa</div>
    `;
    container.appendChild(headerDiv);

    if (inventoryData[checkoutId]) {
        inventoryData[checkoutId].forEach((item) => {
            const div = document.createElement("div");
            div.className = "inventory-item mb-2";
            div.dataset.itemId = item.id;

            div.innerHTML = `
                <div class="row g-2">
                    <div class="col-md-5">
                        <input type="text"
                               class="form-control form-control-sm"
                               value="${item.item_name || ""}"
                               placeholder="Tên mục *"
                               onchange="updateInventoryItem(${checkoutId}, ${
                item.id
            }, 'item_name', this.value)"
                               required>
                    </div>
                    <div class="col-md-4">
                        <input type="text"
                               class="form-control form-control-sm"
                               value="${item.item_condition || ""}"
                               placeholder="Tình trạng"
                               onchange="updateInventoryItem(${checkoutId}, ${
                item.id
            }, 'item_condition', this.value)">
                    </div>
                    <div class="col-md-2">
                        <input type="number"
                               class="form-control form-control-sm"
                               value="${item.item_cost || ""}"
                               placeholder="Chi phí"
                               step="1000"
                               min="0"
                               onchange="updateInventoryItem(${checkoutId}, ${
                item.id
            }, 'item_cost', this.value)">
                    </div>
                    <div class="col-md-1">
                        <button type="button"
                                class="btn btn-sm btn-danger"
                                onclick="removeInventoryItem(${checkoutId}, ${
                item.id
            })"
                                title="Xóa mục này">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;

            container.appendChild(div);
        });
    }
}

// Cập nhật tổng tiền khấu trừ
function updateDeductionTotal(checkoutId) {
    const totalInput = document.getElementById(
        `deduction_amount_total_${checkoutId}`
    );
    const finalRefundedInput = document.getElementById(
        `final_refunded_amount_${checkoutId}`
    );
    const depositAmountInput = document.getElementById(
        `deposit_amount_${checkoutId}`
    );

    if (!totalInput || !finalRefundedInput || !depositAmountInput) return;

    let totalDeduction = 0;
    if (inventoryData[checkoutId]) {
        inventoryData[checkoutId].forEach((item) => {
            const cost = parseFloat(item.item_cost) || 0;
            totalDeduction += cost;
        });
    }

    // Lấy số tiền cọc
    const depositAmount = parseFloat(depositAmountInput.value) || 0;

    // Tính số tiền hoàn trả cuối cùng
    const finalRefundedAmount = Math.max(0, depositAmount - totalDeduction);

    // Cập nhật các input
    totalInput.value = totalDeduction.toFixed(0);
    finalRefundedInput.value = finalRefundedAmount.toFixed(0);

    // Thêm màu sắc tùy theo số tiền hoàn trả
    if (finalRefundedAmount === depositAmount) {
        // Hoàn trả đầy đủ - màu xanh
        finalRefundedInput.style.backgroundColor = "#d4edda";
        finalRefundedInput.style.color = "#155724";
    } else if (finalRefundedAmount > 0) {
        // Hoàn trả một phần - màu vàng
        finalRefundedInput.style.backgroundColor = "#fff3cd";
        finalRefundedInput.style.color = "#856404";
    } else {
        // Không hoàn trả - màu đỏ nhạt
        finalRefundedInput.style.backgroundColor = "#f8d7da";
        finalRefundedInput.style.color = "#721c24";
    }

    console.log(`Updated totals for checkout ${checkoutId}:`, {
        depositAmount,
        totalDeduction,
        finalRefundedAmount,
    });
}

// Chuẩn bị dữ liệu để submit
function prepareFormDataForSubmit(checkoutId) {
    const form = document.getElementById(`checkoutForm${checkoutId}`);
    if (!form) {
        console.error("Form not found:", `checkoutForm${checkoutId}`);
        return false;
    }

    // Xóa các input cũ
    const oldInputs = form.querySelectorAll('input[name^="item_"]');
    oldInputs.forEach((input) => input.remove());

    // Xóa deduction_amount và final_refunded_amount cũ
    const oldDeductionInput = form.querySelector(
        'input[name="deduction_amount"]'
    );
    if (oldDeductionInput) {
        oldDeductionInput.remove();
    }

    const oldFinalRefundedInput = form.querySelector(
        'input[name="final_refunded_amount"]'
    );
    if (oldFinalRefundedInput) {
        oldFinalRefundedInput.remove();
    }

    // Kiểm tra dữ liệu inventory
    if (!inventoryData[checkoutId] || inventoryData[checkoutId].length === 0) {
        console.log("No inventory data found for checkout:", checkoutId);

        // Thêm dữ liệu mặc định
        const deductionInput = document.createElement("input");
        deductionInput.type = "hidden";
        deductionInput.name = "deduction_amount";
        deductionInput.value = 0;
        form.appendChild(deductionInput);

        return true;
    }

    // Thêm các input mới từ mảng tạm
    inventoryData[checkoutId].forEach((item, index) => {
        // Chỉ thêm những item có tên
        if (item.item_name && item.item_name.trim() !== "") {
            // Tên mục
            const nameInput = document.createElement("input");
            nameInput.type = "hidden";
            nameInput.name = "item_name[]";
            nameInput.value = item.item_name.trim();
            form.appendChild(nameInput);

            // Tình trạng
            const conditionInput = document.createElement("input");
            conditionInput.type = "hidden";
            conditionInput.name = "item_condition[]";
            conditionInput.value = item.item_condition || "";
            form.appendChild(conditionInput);

            // Chi phí
            const costInput = document.createElement("input");
            costInput.type = "hidden";
            costInput.name = "item_cost[]";
            costInput.value = item.item_cost || 0;
            form.appendChild(costInput);

            console.log(`Added item ${index + 1} to form:`, {
                name: item.item_name,
                condition: item.item_condition,
                cost: item.item_cost,
            });
        }
    });

    // Thêm tổng tiền khấu trừ
    const totalInput = document.getElementById(
        `deduction_amount_total_${checkoutId}`
    );
    if (totalInput) {
        const deductionInput = document.createElement("input");
        deductionInput.type = "hidden";
        deductionInput.name = "deduction_amount";
        deductionInput.value = totalInput.value || 0;
        form.appendChild(deductionInput);

        console.log("Added deduction amount:", totalInput.value);
    }

    // Thêm số tiền hoàn trả cuối cùng
    const finalRefundedInput = document.getElementById(
        `final_refunded_amount_${checkoutId}`
    );
    if (finalRefundedInput) {
        const finalRefundedHiddenInput = document.createElement("input");
        finalRefundedHiddenInput.type = "hidden";
        finalRefundedHiddenInput.name = "final_refunded_amount";
        finalRefundedHiddenInput.value = finalRefundedInput.value || 0;
        form.appendChild(finalRefundedHiddenInput);

        console.log("Added final refunded amount:", finalRefundedInput.value);
    }

    console.log("Form prepared for submit:", checkoutId);
    return true;
}

// Khởi tạo khi trang được load
document.addEventListener("DOMContentLoaded", function () {
    console.log("DOM Content Loaded - Initializing checkout forms");

    // Khởi tạo dữ liệu cho tất cả các checkout form
    document.querySelectorAll('form[id^="checkoutForm"]').forEach((form) => {
        const checkoutId = form.id.replace("checkoutForm", "");
        console.log("Initializing form for checkout:", checkoutId);

        // Khởi tạo dữ liệu inventory
        initializeInventoryData(checkoutId);

        // Xử lý submit form
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            console.log("Form submit intercepted for checkout:", checkoutId);

            // Chuẩn bị dữ liệu
            const prepared = prepareFormDataForSubmit(checkoutId);

            if (!prepared) {
                console.error("Failed to prepare form data");
                return;
            }

            // Debug: Log tất cả form data
            const formData = new FormData(this);
            console.log("Final form data:");
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }

            // Submit form
            console.log("Submitting form...");
            this.submit();
        });
    });

    // Xử lý sự kiện khi modal được mở
    document.addEventListener("show.bs.modal", function (e) {
        const modal = e.target;
        const modalId = modal.id;

        // Kiểm tra xem có phải là edit modal không
        if (modalId.startsWith("editModal")) {
            const checkoutId = modalId.replace("editModal", "");
            console.log("Edit modal opened for checkout:", checkoutId);

            // Khởi tạo lại dữ liệu inventory khi mở modal
            initializeInventoryData(checkoutId);
        }
    });

    // Xử lý sự kiện khi modal được đóng
    document.addEventListener("hidden.bs.modal", function (e) {
        const modal = e.target;
        const modalId = modal.id;

        // Kiểm tra xem có phải là edit modal không
        if (modalId.startsWith("editModal")) {
            const checkoutId = modalId.replace("editModal", "");
            console.log("Edit modal closed for checkout:", checkoutId);

            // Reset dữ liệu về trạng thái ban đầu khi đóng modal
            resetInventoryData(checkoutId);
        }
    });

    // Add event listeners cho các nút "Thêm mục"
    document.addEventListener("click", function (e) {
        if (e.target.closest(".add-inventory-item")) {
            e.preventDefault();
            const button = e.target.closest(".add-inventory-item");
            const checkoutId = button.getAttribute("data-checkout-id");
            console.log("Add inventory item clicked for checkout:", checkoutId);
            addInventoryItem(checkoutId);
        }
    });

    console.log("Checkout form initialization completed");
});

// Hàm chuyển trạng thái sang "Kiểm kê lại"
function changeToReInventory(checkoutId) {
    const confirmModal = `
        <div class="modal fade" id="confirmReInventoryModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-triangle me-2"></i>Xác nhận kiểm kê lại
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Bạn có chắc chắn muốn chuyển trạng thái checkout này sang <strong>"Kiểm kê lại"</strong> không?</p>
                        <p class="text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            Hành động này sẽ cho phép bạn chỉnh sửa lại thông tin kiểm kê.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Hủy
                        </button>
                        <button type="button" class="btn btn-warning" onclick="confirmReInventory(${checkoutId})">
                            <i class="fas fa-redo me-1"></i>Đồng ý
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    const existingModal = document.getElementById("confirmReInventoryModal");
    if (existingModal) {
        existingModal.remove();
    }

    document.body.insertAdjacentHTML("beforeend", confirmModal);
    const modal = new bootstrap.Modal(
        document.getElementById("confirmReInventoryModal")
    );
    modal.show();
}

// Hàm xác nhận và gửi request
function confirmReInventory(checkoutId) {
    const form = document.createElement("form");
    form.method = "POST";
    form.action = `/checkouts/${checkoutId}/re-inventory`;

    const csrfToken = document.createElement("input");
    csrfToken.type = "hidden";
    csrfToken.name = "_token";
    csrfToken.value = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
    form.appendChild(csrfToken);

    const methodInput = document.createElement("input");
    methodInput.type = "hidden";
    methodInput.name = "_method";
    methodInput.value = "PUT";
    form.appendChild(methodInput);

    document.body.appendChild(form);
    form.submit();

    const modal = bootstrap.Modal.getInstance(
        document.getElementById("confirmReInventoryModal")
    );
    modal.hide();
}

// Export functions to global scope
window.removeExistingImage = removeExistingImage;
window.previewImages = previewImages;
window.removeNewImage = removeNewImage;
window.updateInventoryItem = updateInventoryItem;
window.removeInventoryItem = removeInventoryItem;
window.changeToReInventory = changeToReInventory;
window.confirmReInventory = confirmReInventory;
window.initializeInventoryData = initializeInventoryData;
window.resetInventoryData = resetInventoryData;
