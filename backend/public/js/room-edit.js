$(document).ready(function() {
    // Get CSRF token from the meta tag that Laravel already provides
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    if (!csrfToken) {
        console.error('CSRF token not found. Make sure to include <meta name="csrf-token" content="{{ csrf_token() }}"> in your layout.');
    }

    // Image deletion functionality
    $(document).on('click', '.delete-image-btn', function() {
        const imageId = $(this).data('image-id');
        const roomId = $(this).data('room-id');
        const $button = $(this);
        const $imageContainer = $button.closest('[data-image-id]');

        if (!imageId || !roomId) {
            alert('Không thể xác định hình ảnh hoặc phòng trọ');
            return;
        }

        if (confirm('Bạn có chắc chắn muốn xóa hình ảnh này?')) {
            // Show loading state
            $button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

            // Construct the delete URL properly
            const deleteUrl = `/rooms/${roomId}/images/${imageId}/delete`;

            $.ajax({
                url: deleteUrl,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                                success: function(response) {
                    // Xác định thành công nếu có response.data hoặc không có response.error
                    if (response.data || !response.error) {
                        // Lưu tham chiếu đến container cha trước khi xóa
                        const $parentContainer = $imageContainer.closest('.mb-3');

                        // Apply fade-out effect and then remove the element
                        $imageContainer.fadeOut(300, function() {
                            // Xóa container hình ảnh
                            $(this).remove();

                            // Tạo thông báo thành công và thêm vào container cha
                            const successMsg = `
                                <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
                                    Hình ảnh đã được xóa thành công
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            `;

                            // Thêm thông báo vào container cha
                            $parentContainer.prepend(successMsg);

                            // Tự động ẩn thông báo sau 3 giây
                            setTimeout(function() {
                                $('.alert-success').fadeOut('slow', function() {
                                    $(this).remove();
                                });
                            }, 3000);
                        });
                    } else {
                        // Restore button when there's an error
                        $button.prop('disabled', false).html('<i class="fa fa-trash"></i>');
                        alert('Lỗi: ' + (response.error || 'Không thể xóa hình ảnh'));
                    }
                },
                error: function(xhr) {
                    // Restore button when there's an error
                    $button.prop('disabled', false).html('<i class="fa fa-trash"></i>');

                    let errorMsg = 'Đã xảy ra lỗi khi xóa hình ảnh';

                    // Try to extract error message from response
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    }

                    alert(errorMsg);
                }
            });
        }
    });

    // Preview uploaded images
    // $('#images').on('change', function() {
    //     const files = this.files;
    //     const $preview = $('#image-preview');

    //     $preview.empty();

    //     if (files && files.length > 0) {
    //         for (let i = 0; i < files.length; i++) {
    //             const reader = new FileReader();

    //             reader.onload = function(e) {
    //                 $preview.append(`
    //                     <div class="col-md-3 mb-3">
    //                         <div class="image-container" style="height: 200px; overflow: hidden;">
    //                             <img src="${e.target.result}" class="img-thumbnail"
    //                                  alt="Preview image" style="width: 100%; height: 100%; object-fit: cover;">
    //                         </div>
    //                     </div>
    //                 `);
    //             };

    //             reader.readAsDataURL(files[i]);
    //         }
    //     }
    // });
});
