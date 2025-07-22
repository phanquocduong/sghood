 document.addEventListener('DOMContentLoaded', function () {
            const selects = document.querySelectorAll('.status-select');
            let currentForm = null;

            selects.forEach(select => {
                select.addEventListener('change', function (e) {
                    const newStatus = e.target.value;
                    const scheduleId = e.target.dataset.scheduleId;
                    currentForm = document.getElementById(`status-form-${scheduleId}`);

                    if (newStatus === 'Huỷ bỏ' || newStatus === 'Từ chối') {
                        const modal = new bootstrap.Modal(document.getElementById('cancelReasonModal'));
                        modal.show();
                    } else {
                        if (confirm(`Bạn có chắc muốn thay đổi trạng thái sang "${newStatus}"?`)) {
                            currentForm.submit();
                        } else {
                            e.target.value = e.target.dataset.currentStatus || e.target.options[0].value;
                        }
                    }
                });

                // Store current status for reset if needed
                select.dataset.currentStatus = select.value;
            });

            document.getElementById('confirmCancel').addEventListener('click', function () {
                const reason = document.getElementById('cancelReason').value.trim();
                if (!reason) {
                    alert('Vui lòng nhập lý do hủy!');
                    return;
                }
                if (currentForm) {
                    currentForm.querySelector('.cancel-reason-input').value = reason;
                    currentForm.submit();
                }
                const modal = bootstrap.Modal.getInstance(document.getElementById('cancelReasonModal'));
                modal.hide();
                document.getElementById('cancelReason').value = '';
            });
        });