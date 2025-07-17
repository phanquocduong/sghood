document.addEventListener("DOMContentLoaded", function () {
    const updateModal = new bootstrap.Modal(
        document.getElementById("updateMeterModal")
    );
    const motelButtons = document.querySelectorAll("[data-motel-button]");
    const motelNameSpan = document.getElementById("update_motel_name");
    const periodSpan = document.getElementById("update_period");
    const roomInputsContainer = document.getElementById(
        "room_inputs_container"
    );
    const filterForm = document.getElementById("filterForm");
    const displayResults = document.getElementById("displayResults");
    const displayIndex = document.getElementById("displayIndex");

    // Lấy dữ liệu từ Blade
    const errors = window.readingErrors || {};
    const oldInput = window.oldInput || []; // Đảm bảo oldInput được truyền từ Blade
    const motelData = window.motelData || null; // Đảm bảo motelData được truyền từ Blade

    function renderModal(data) {
        motelNameSpan.textContent = data.motel_name || "Unknown";
        periodSpan.textContent = `Tháng ${data.month}/${data.year}`;
        roomInputsContainer.innerHTML = "";

        data.rooms.forEach((room, index) => {
            const electricityError =
                errors?.[`readings.${index}.electricity_kwh`] || "";
            const waterError = errors?.[`readings.${index}.water_m3`] || "";
            const oldElectricity = oldInput[index]?.electricity_kwh || "";
            const oldWater = oldInput[index]?.water_m3 || "";

            const roomHtml = `
                <div class="mb-2 mt-3 fw-bold text-primary">${room.name} (ID: ${
                room.id
            })</div>
                <input type="hidden" name="readings[${index}][room_id]" value="${
                room.id
            }">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Chỉ số điện (kWh)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-warning text-dark">
                                    <i class="fas fa-bolt"></i>
                                </span>
                                <input type="number" step="0.01" min="0" name="readings[${index}][electricity_kwh]" class="form-control" placeholder="0.00" value="${oldElectricity}" required>
                            </div>
                            ${
                                electricityError
                                    ? `<div class="text-danger small mt-1">${electricityError}</div>`
                                    : ""
                            }
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Chỉ số nước (m³)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-info text-white">
                                    <i class="fas fa-tint"></i>
                                </span>
                                <input type="number" step="0.01" min="0" name="readings[${index}][water_m3]" class="form-control" placeholder="0.00" value="${oldWater}" required>
                            </div>
                            ${
                                waterError
                                    ? `<div class="text-danger small mt-1">${waterError}</div>`
                                    : ""
                            }
                        </div>
                    </div>
                </div>
            `;
            roomInputsContainer.insertAdjacentHTML("beforeend", roomHtml);
        });

        updateModal.show();
    }

    motelButtons.forEach((button) => {
        button.addEventListener("click", function () {
            const data = JSON.parse(this.getAttribute("data-motel-button"));
            renderModal(data);
        });
    });

    // Mở lại modal nếu có lỗi validation
    if (window.shouldOpenUpdateModal && motelData) {
        renderModal({
            motel_name: motelData.motel_name || "Unknown",
            month: motelData.month,
            year: motelData.year,
            rooms: motelData.rooms || [],
        });
    }

    // Xử lý submit form filter
    filterForm.addEventListener("submit", function (e) {
        e.preventDefault();
        displayResults.innerHTML =
            '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>';
        displayResults.style.display = "block";
        displayIndex.style.display = "none";

        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData).toString();
        const url = `{{ route('meter_readings.filter') }}?${params}`;

        fetch(url, {
            method: "GET",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                Accept: "text/html", // Sửa thành text/html vì server trả về view
            },
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then((data) => {
                displayResults.innerHTML = data;
            })
            .catch((error) => {
                console.error("Error filtering:", error);
                displayResults.innerHTML =
                    '<div class="alert alert-danger">Đã xảy ra lỗi khi lọc dữ liệu.</div>';
            });
    });

    // Xử lý reset form
    filterForm.addEventListener("reset", function () {
        displayResults.innerHTML =
            '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>';
        displayResults.style.display = "block";
        displayIndex.style.display = "none";

        fetch("{{ route('meter_readings.index') }}", {
            method: "GET",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                Accept: "text/html",
            },
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then((data) => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(data, "text/html");
                const newDisplayIndex = doc.getElementById("displayIndex");
                const newDisplayResults = doc.getElementById("displayResults");

                displayIndex.innerHTML = newDisplayIndex.innerHTML;
                displayIndex.style.display = "block";
                displayResults.innerHTML = newDisplayResults.innerHTML;
                displayResults.style.display = "none";

                // Reset các trường input
                filterForm.reset();
            })
            .catch((error) => {
                console.error("Error resetting:", error);
                window.location.href = "{{ route('meter_readings.index') }}";
            });
    });
});
