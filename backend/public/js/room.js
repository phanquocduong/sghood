// Register FilePond plugins
    FilePond.registerPlugin(
        FilePondPluginImagePreview,
        FilePondPluginFileValidateType
    );

    // Initialize FilePond
    const inputElement = document.querySelector('input[type="file"].filepond');
    const pond = FilePond.create(inputElement, {
        allowMultiple: true,
        acceptedFileTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        instantUpload: false,
        labelIdle: 'Kéo và thả hình ảnh hoặc <span class="filepond--label-action">Chọn tệp</span>',
        credits: false,
        imagePreviewHeight: 100,
        allowImagePreview: true,
        maxFiles: 10,
        maxFileSize: '2MB',
        onaddfile: (error, file) => {
            if (error) {
                console.error('FilePond error on add file:', error);
                return;
            }
            console.log('File added:', file.filename);
        },
        onremovefile: (error, file) => {
            console.log('File removed:', file ? file.filename : 'unknown');
        }
    });

    // Xử lý form submit - QUAN TRỌNG
    document.getElementById('roomForm').addEventListener('submit', function (e) {
    // Không preventDefault() - để Laravel xử lý tự nhiên

    // Chỉ cần đảm bảo FilePond files được thêm vào form
    const files = pond.getFiles();

    // Tạo hidden inputs cho mỗi file
    files.forEach((fileItem, index) => {
        if (fileItem.file instanceof File) {
            const input = document.createElement('input');
            input.type = 'file';
            input.name = 'images[]';
            input.files = createFileList([fileItem.file]);
            input.style.display = 'none';
            this.appendChild(input);
        }
    });

    // Form sẽ submit bình thường và Laravel sẽ xử lý validation
});

// Helper function để tạo FileList
function createFileList(files) {
    const dataTransfer = new DataTransfer();
    files.forEach(file => dataTransfer.items.add(file));
    return dataTransfer.files;
}
