import { ref as firebaseRef, uploadBytes, getDownloadURL } from 'firebase/storage'; // Nhập các hàm từ Firebase Storage để quản lý tệp
import { v4 as uuidv4 } from 'uuid'; // Nhập hàm uuid để tạo tên tệp duy nhất

// Hàm tải hình ảnh lên Firebase Storage và trả về URL tải xuống
export const uploadImageToFirebase = async (file, storage) => {
    // Tạo tên tệp duy nhất bằng cách kết hợp thời gian hiện tại, UUID và tên gốc của tệp
    const fileName = `${Date.now()}-${uuidv4()}-${file.name}`;
    // Tạo tham chiếu đến vị trí lưu trữ trong Firebase Storage (thư mục 'images')
    const fileRef = firebaseRef(storage, `images/${fileName}`);
    // Tải tệp lên Firebase Storage
    const snapshot = await uploadBytes(fileRef, file);
    // Lấy URL tải xuống của tệp đã tải lên
    const url = await getDownloadURL(snapshot.ref);
    // Trả về URL của hình ảnh
    return url;
};
