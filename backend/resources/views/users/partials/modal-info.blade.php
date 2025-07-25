<div>
    <p><strong></strong> <img src="{{ $user->avatar }}" alt="Avatar" class="img-fluid" style="max-width: 150px; border-radius: 10px;"></p>
    <p><strong>Họ tên:</strong> {{ $user->name }}</p>
    <p><strong>Email:</strong> {{ $user->email }}</p>
    <p><strong>Số điện thoại:</strong> {{ $user->phone }}</p>
    <p><strong>Vai trò:</strong> {{ $user->role }}</p>
    <p><strong>Trạng thái:</strong> {{ $user->status }}</p>
    <p><strong>Ngày tạo:</strong> {{ $user->created_at->format('d/m/Y H:i') }}</p>
    <p><strong>Địa chỉ:</strong> {{ $user->address }}</p>
    <p><strong>Ngày sinh:</strong> {{ $user->birthdate->format('d/m/Y') }}</p>
    <p><strong>Giới tính:</strong> {{ $user->gender }}</p>
    <p><strong>Vai trò:</strong> {{ $user->role }}</p>
    <p><strong>Trạng thái:</strong> {{ $user->status }}</p>
</div>
