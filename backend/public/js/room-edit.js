$(document).ready(function() {
    console.log('DOM ready');
    if (typeof jQuery == 'undefined') {
        console.error('jQuery not loaded');
    } else {
        console.log('jQuery loaded successfully');
    }

    if ($('.delete-image-btn').length) {
        console.log('Delete buttons found: ' + $('.delete-image-btn').length);
    } else {
        console.error('No delete buttons found');
    }

    // Sử dụng event delegation để đảm bảo nút hoạt động ngay cả khi DOM thay đổi
    $(document).on('click', '.delete-image-btn', function() {
        console.log('Delete button clicked');
        const imageId = $(this).data('image-id');
        const roomId = $(this).data('room-id');
        const $button = $(this);
        const $imageContainer = $button.closest('[data-image-id]'); // Lưu reference tới container

        console.log('Image ID:', imageId, 'Room ID:', roomId);
        console.log('Image container:', $imageContainer.length ? 'found' : 'not found');

        if (!imageId || !roomId) {
            alert('Không thể xác định hình ảnh hoặc phòng trọ');
            return;
        }

        if (confirm('Bạn có chắc chắn muốn xóa hình ảnh này?')) {
            // Hiển thị trạng thái đang xóa
            $button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

            let deleteUrl = '/rooms/' + roomId + '/images/' + imageId + '/delete';
            console.log('Delete URL:', deleteUrl);

            $.ajax({
                url: deleteUrl,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    image_id: imageId,
                    room_id: roomId
                },
                success: function(response) {
                    console.log('Success response:', response);
                    if (response.success) {
                        // Xác nhận container trước khi xóa
                        console.log('Removing container:', $imageContainer);

                        // Sử dụng hiệu ứng fade rõ ràng hơn
                        $imageContainer.css('transition', 'opacity 0.5s')
                            .css('opacity', '0');

                        // Sau khi hiệu ứng hoàn tất, xóa phần tử khỏi DOM
                        setTimeout(function() {
                            $imageContainer.remove();
                            console.log('Container removed from DOM');

                            // Xóa bất kỳ thông báo cũ nào trước khi thêm thông báo mới
                            $('.alert.alert-success').remove();

                            // Tạo và hiển thị thông báo mới với nút đóng
                            const successNotice = $(
                                '<div class="alert alert-success alert-dismissible mt-2" role="alert">' +
                                'Hình ảnh đã được xóa thành công ' +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                '</div>'
                            );
                            $('.row.mt-3').prepend(successNotice);
                        }, 100);
                    } else {
                        // Khôi phục nút khi có lỗi
                        $button.prop('disabled', false).html('<i class="fa fa-trash"></i>');
                        alert('Lỗi: ' + (response.error || 'Không thể xóa hình ảnh'));
                    }
                },
                error: function(xhr, status, error) {
                    // Khôi phục nút khi có lỗi
                    $button.prop('disabled', false).html('<i class="fa fa-trash"></i>');

                    console.error('AJAX error:', status, error);
                    console.error('Response:', xhr.responseText);
                    alert('Đã xảy ra lỗi khi xóa hình ảnh: ' + (xhr.responseJSON?.error || error));
                }
            });
        }
    });

    // Đảm bảo CSRF token có sẵn
    if (!$('meta[name="csrf-token"]').length) {
        $('head').append('<meta name="csrf-token" content="' + $('input[name="_token"]').val() + '">');
    }
});
