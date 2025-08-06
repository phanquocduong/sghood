<div class="user-info-wrapper">
    <div class="user-info-container">
        <div class="avatar">
            <img src="{{ $user->avatar && file_exists(public_path($user->avatar))
                ? asset($user->avatar)
                : asset('img/user.jpg') }}"
                alt="Avatar">
        </div>

        <div class="info">
            <h4 class="user-name">{{ $user->name }}</h4>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Số điện thoại:</strong> {{ $user->phone }}</p>
            <p><strong>Vai trò:</strong> {{ $user->role }}</p>
            <p><strong>Trạng thái:</strong> {{ $user->status }}</p>
            <p><strong>Ngày tạo:</strong> {{ $user->created_at->format('d/m/Y H:i') }}</p>
            <p><strong>Địa chỉ:</strong> {{ $user->address }}</p>
            <p>
                <strong>Ngày sinh:</strong>
                {{ $user->birthdate instanceof \Carbon\Carbon ? $user->birthdate->format('d/m/Y') : 'Chưa cập nhật' }}
            </p>
            <p><strong>Giới tính:</strong> {{ $user->gender }}</p>
        </div>
    </div>
</div>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Modal</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
    /* Modal Styling */
    .modal-content {
        border: none;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 20px 25px;
        position: relative;
    }

    .modal-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
    }

    .modal-title {
        font-size: 20px;
        font-weight: 600;
        z-index: 1;
        position: relative;
        color: white;
    }

    .btn-close {
        filter: brightness(0) invert(1);
        z-index: 2;
        position: relative;
    }

    .modal-body {
        padding: 0;
        background: #f8fafc;
    }

    /* User Info Container */
    .user-info-wrapper {
        padding: 30px;
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    }

    .user-info-container {
        display: flex;
        gap: 25px;
        align-items: flex-start;
        flex-wrap: wrap;
        position: relative;
    }

    /* Avatar Styling */
    .avatar {
        position: relative;
        flex-shrink: 0;
    }

    .avatar img {
        width: 140px;
        height: 140px;
        object-fit: cover;
        border-radius: 20px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        border: 4px solid white;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .avatar img:hover {
        transform: scale(1.05);
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.2);
    }

    .avatar::after {
        content: '';
        position: absolute;
        bottom: 10px;
        right: 10px;
        width: 20px;
        height: 20px;
        background: #10b981;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    /* Info Section */
    .info {
        flex: 1;
        min-width: 280px;
    }

    .user-name {
        margin: 0 0 20px 0;
        font-size: 28px;
        font-weight: 700;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .info-grid {
        display: grid;
        gap: 16px;
    }

    .info-item {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }

    .info-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border-left-color: #667eea;
    }

    .info-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea20 0%, #764ba220 100%);
        border-radius: 10px;
        margin-right: 15px;
        color: #667eea;
        font-size: 16px;
    }

    .info-content {
        flex: 1;
    }

    .info-label {
        font-size: 12px;
        color: #64748b;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .info-value {
        font-size: 15px;
        color: #1e293b;
        font-weight: 600;
        line-height: 1.4;
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-active {
        background: #dcfce7;
        color: #166534;
    }

    .status-inactive {
        background: #fef2f2;
        color: #991b1b;
    }

    /* Role Badge */
    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 25px;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    /* Responsive Design */
    @media (max-width: 576px) {
        .user-info-container {
            flex-direction: column;
            text-align: center;
        }

        .avatar {
            align-self: center;
        }

        .user-name {
            font-size: 24px;
            text-align: center;
        }

        .info-item {
            flex-direction: column;
            text-align: center;
            gap: 8px;
        }

        .info-icon {
            margin-right: 0;
            margin-bottom: 8px;
        }
    }

    /* Loading Animation */
    .loading {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 40px;
        color: #64748b;
    }

    .loading i {
        animation: spin 1s linear infinite;
        margin-right: 10px;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* Backdrop Blur Effect */
    .modal.show .modal-backdrop {
        backdrop-filter: blur(8px);
        background-color: rgba(0, 0, 0, 0.3);
    }
</style>
