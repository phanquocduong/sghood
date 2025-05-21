   // motel index
        // Ensure Bootstrap collapse works (requires Bootstrap JS)
        // If not using Bootstrap JS, add custom toggle logic
        document.addEventListener('DOMContentLoaded', function () {
            const filterButton = document.querySelector('[data-bs-toggle="collapse"]');
            filterButton.addEventListener('click', function () {
                const form = document.getElementById('filterForm');
                form.classList.toggle('show');
            });
        });

    // create motel
        document.addEventListener('DOMContentLoaded', function () {
            const imageInput = document.getElementById('images');
            const preview = document.getElementById('image-preview');
            let selectedFiles = []; // Array to store all selected files

            imageInput.addEventListener('change', function (event) {
                // Add new files to the existing collection
                const newFiles = Array.from(event.target.files);
                selectedFiles = selectedFiles.concat(newFiles);

                // Display all files (old + new)
                displaySelectedImages(newFiles);
            });

            function displaySelectedImages(newFiles) {
                // Only display the newly added images (don't clear previous ones)
                for (let i = 0; i < newFiles.length; i++) {
                    const file = newFiles[i];
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            const col = document.createElement('div');
                            col.className = 'col-md-3 mb-2 position-relative';
                            col.dataset.fileIndex = selectedFiles.indexOf(file);

                            // Create delete button
                            const deleteBtn = document.createElement('button');
                            deleteBtn.innerHTML = '&times;';
                            deleteBtn.className = 'btn btn-sm btn-danger position-absolute';
                            deleteBtn.style.top = '5px';
                            deleteBtn.style.right = '20px';
                            deleteBtn.type = 'button';
                            deleteBtn.addEventListener('click', function () {
                                // Remove file from array
                                const index = parseInt(col.dataset.fileIndex);
                                selectedFiles.splice(index, 1);

                                // Remove preview
                                col.remove();

                                // Update file input
                                updateFileInput();
                            });

                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.className = 'img-fluid';
                            img.style.maxHeight = '150px';

                            col.appendChild(img);
                            col.appendChild(deleteBtn);
                            preview.appendChild(col);
                        };
                        reader.readAsDataURL(file);
                    }
                }
            }

            function updateFileInput() {
                // Create a new DataTransfer object
                const dataTransfer = new DataTransfer();

                // Add remaining files
                selectedFiles.forEach(file => {
                    dataTransfer.items.add(file);
                });

                // Update the file input
                imageInput.files = dataTransfer.files;
            }
        });

