@extends('layouts.app')

@section('title', 'Chi tiết nhà trọ')

@section('content')
<div class="container-fluid pt-4 px-4">
    <div class="bg-light rounded-top p-4">
        <h6 class="mb-4">Chi tiết nhà trọ: Nhà trọ An Bình</h6>

        <div class="alert alert-success">Hiển thị dữ liệu mẫu</div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Nhà trọ An Bình</h5>
                <p><strong>Địa chỉ:</strong> 123 Đường Láng, Đống Đa</p>
                <p><strong>Quận/Huyện:</strong> Quận 1</p>
                <p><strong>Trạng thái:</strong> Còn trống</p>
                <p><strong>Mô tả:</strong> Nhà trọ sạch sẽ, gần trung tâm, tiện nghi đầy đủ.</p>
                
                <h6>Chi phí</h6>
                <ul>
                    <li><strong>Tiền điện:</strong> 3,500.00 VNĐ/kWh</li>
                    <li><strong>Tiền nước:</strong> 20,000.00 VNĐ/m³</li>
                    <li><strong>Phí giữ xe:</strong> 100,000.00 VNĐ/tháng</li>
                    <li><strong>Phí rác:</strong> 20,000.00 VNĐ/tháng</li>
                    <li><strong>Phí internet:</strong> 150,000.00 VNĐ/tháng</li>
                    <li><strong>Phí dịch vụ:</strong> 50,000.00 VNĐ/tháng</li>
                </ul>

                <h6>Tiện ích</h6>
                <ul>
                    <li>Wifi</li>
                    <li>Máy lạnh</li>
                    <li>Bãi đỗ xe</li>
                </ul>

                <h6>Bản đồ</h6>
                <div class="mb-3">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.6696134446223!2d106.68414697499018!3d10.759138989389291!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f1b7c3ed289%3A0xa06651894598e6d8!2sHo%20Chi%20Minh%20City%2C%20Vietnam!5e0!3m2!1sen!2s!4v1698765432109!5m2!1sen!2s" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>

                <h6>Ảnh nhà trọ</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <img src="https://picsum.photos/seed/motel1/300/200" class="img-fluid" alt="Motel Image" style="max-height: 200px;">
                    </div>
                    <div class="col-md-3 mb-3">
                        <img src="https://picsum.photos/seed/motel2/300/200" class="img-fluid" alt="Motel Image" style="max-height: 200px;">
                    </div>
                    <div class="col-md-3 mb-3">
                        <img src="https://picsum.photos/seed/motel3/300/200" class="img-fluid" alt="Motel Image" style="max-height: 200px;">
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a href="{{ route('motels.index') }}" class="btn btn-secondary">Quay lại</a>
            <a href="{{ route('motels.edit', 1) }}" class="btn btn-warning">Sửa</a>
            <form action="{{ route('motels.destroy', 1) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</button>
            </form>
        </div>
    </div>
</div>
@endsection