@extends('layouts.app')

@section('title', 'Sửa nhà trọ')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded-top p-4">
            <h6 class="mb-4">Thêm nhà trọ</h6>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('motels.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Tên dãy trọ</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Địa chỉ</label>
                    <input type="text" class="form-control" id="address" name="address" value="{{ old('address') }}"
                        required>
                </div>
                <div class="mb-3">
                    <label for="district" class="form-label">Quận/Huyện</label>
                    <select class="form-control" id="district" name="district" required>
                        <option value="">Chọn quận/huyện</option>
                        <option value="district_1" {{ old('district') == 'district_1' ? 'selected' : '' }}>Quận 1</option>
                        <option value="district_2" {{ old('district') == 'district_2' ? 'selected' : '' }}>Quận 2</option>
                        <option value="district_3" {{ old('district') == 'district_3' ? 'selected' : '' }}>Quận 3</option>
                        <!-- Add more districts as needed -->
                    </select>
                </div>
                <div class="mb-3">
                    <label for="map_embed_url" class="form-label">URL nhúng bản đồ</label>
                    <input type="url" class="form-control" id="map_embed_url" name="map_embed_url"
                        value="{{ old('map_embed_url') }}" placeholder="https://maps.google.com/...">
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Mô tả</label>
                    <textarea class="form-control" id="description" name="description"
                        rows="3">{{ old('description') }}</textarea>
                </div>
                <div class="mb-3">
                    <label for="electricity_cost" class="form-label">Tiền điện (VNĐ/kWh)</label>
                    <input type="number" class="form-control" id="electricity_cost" name="electricity_cost"
                        value="{{ old('electricity_cost') }}" step="0.01" min="0">
                </div>
                <div class="mb-3">
                    <label for="water_cost" class="form-label">Tiền nước (VNĐ/m³)</label>
                    <input type="number" class="form-control" id="water_cost" name="water_cost"
                        value="{{ old('water_cost') }}" step="0.01" min="0">
                </div>
                <div class="mb-3">
                    <label for="parking_fee" class="form-label">Phí giữ xe (VNĐ/tháng)</label>
                    <input type="number" class="form-control" id="parking_fee" name="parking_fee"
                        value="{{ old('parking_fee') }}" step="0.01" min="0">
                </div>
                <div class="mb-3">
                    <label for="garbage_fee" class="form-label">Phí rác (VNĐ/tháng)</label>
                    <input type="number" class="form-control" id="garbage_fee" name="garbage_fee"
                        value="{{ old('garbage_fee') }}" step="0.01" min="0">
                </div>
                <div class="mb-3">
                    <label for="internet_fee" class="form-label">Phí internet (VNĐ/tháng)</label>
                    <input type="number" class="form-control" id="internet_fee" name="internet_fee"
                        value="{{ old('internet_fee') }}" step="0.01" min="0">
                </div>
                <div class="mb-3">
                    <label for="service_fee" class="form-label">Phí dịch vụ (VNĐ/tháng)</label>
                    <input type="number" class="form-control" id="service_fee" name="service_fee"
                        value="{{ old('service_fee') }}" step="0.01" min="0">
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select class="form-control" id="status" name="status" required>
                        <option value="available" {{ old('status') == 'available' ? 'selected' : '' }}>Còn trống</option>
                        <option value="occupied" {{ old('status') == 'occupied' ? 'selected' : '' }}>Đã thuê</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="amenities" class="form-label">Tiện ích</label>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="amenity_wifi" name="amenities[]" value="wifi" {{ in_array('wifi', old('amenities', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="amenity_wifi">Wifi</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="amenity_air_conditioner" name="amenities[]"
                                    value="air_conditioner" {{ in_array('air_conditioner', old('amenities', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="amenity_air_conditioner">Máy lạnh</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="amenity_parking" name="amenities[]"
                                    value="parking" {{ in_array('parking', old('amenities', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="amenity_parking">Bãi đỗ xe</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="amenity_security" name="amenities[]"
                                    value="security" {{ in_array('security', old('amenities', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="amenity_security">An ninh</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="amenity_gym" name="amenities[]"
                                    value="gym" {{ in_array('gym', old('amenities', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="amenity_gym">Phòng tập</label>
                            </div>
                        </div>
                        <!-- Add more amenities as needed -->
                    </div>
                </div>
                <div class="mb-3">
                    <label for="images" class="form-label">Hình ảnh</label>
                    <input type="file" class="form-control" id="images" name="images[]" accept="image/*" multiple>
                    <div id="image-preview" class="row mt-3"></div>
                </div>
                <button type="submit" class="btn btn-primary">Thêm nhà trọ</button>
                <a href="{{ route('motels.index') }}" class="btn btn-secondary">Hủy</a>
            </form>
        </div>
    </div>
    <script>
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

    </script>
@endsection