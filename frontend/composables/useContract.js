import { nextTick, ref } from 'vue';
import { useFirebaseAuth } from '~/composables/useFirebaseAuth';
import { useApi } from '~/composables/useApi';

// Hàm composable useContract xử lý logic liên quan đến hợp đồng
export function useContract({
    contract,
    signatureData,
    identityDocument,
    identityImages,
    contractContainer,
    loading,
    extractLoading,
    saveLoading,
    toast,
    router,
    route,
    dropzoneInstance
}) {
    const { $api } = useNuxtApp(); // Lấy instance API từ Nuxt
    const { sendOTP, verifyOTP } = useFirebaseAuth(); // Sử dụng composable Firebase để gửi và xác thực OTP
    const { handleBackendError } = useApi(); // Sử dụng composable useApi để xử lý lỗi backend

    // Khởi tạo các biến trạng thái sử dụng ref
    const phoneNumber = ref(null); // Lưu số điện thoại của người dùng
    const otpCode = ref(''); // Lưu mã OTP nhập vào
    const extractErrorCount = ref(0); // Đếm số lần quét CCCD thất bại
    const bypassExtract = ref(false); // Cờ cho phép bỏ qua quét CCCD nếu thất bại nhiều lần
    const currentAction = ref(null); // Theo dõi hành động hiện tại: 'sign' hoặc 'early_termination'

    // Xử lý nội dung hợp đồng, loại bỏ thuộc tính readonly nếu hợp đồng đang ở trạng thái "Chờ xác nhận"
    const processContractContent = () => {
        if (!contract.value?.content) return; // Kiểm tra nếu không có nội dung hợp đồng thì thoát

        let processedContent = contract.value.content; // Lấy nội dung hợp đồng

        if (contract.value.status === 'Chờ xác nhận') {
            // Loại bỏ thuộc tính readonly khỏi nội dung HTML
            processedContent = processedContent.replace(/\s*readonly\s*(?=\s|>|\/)/gi, '');
            // Loại bỏ readonly từ các thẻ input type="text"
            processedContent = processedContent.replace(/<input([^>]*type="text"[^>]*)>/gi, (match, attributes) => {
                const cleanAttributes = attributes.replace(/\s*readonly\s*/gi, '');
                return `<input${cleanAttributes}>`;
            });
        }

        contract.value.content = processedContent; // Cập nhật nội dung hợp đồng
    };

    // Đồng bộ dữ liệu CCCD với các trường input trong container hợp đồng
    const syncIdentityData = () => {
        if (!contractContainer.value) return; // Kiểm tra nếu không có container hợp đồng thì thoát

        const inputs = contractContainer.value.querySelectorAll('input[type="text"]'); // Lấy tất cả input text
        inputs.forEach(input => {
            const { name } = input;
            if (name in identityDocument.value) {
                if (identityDocument.value[name]) {
                    input.value = identityDocument.value[name]; // Gán giá trị từ identityDocument vào input
                } else {
                    identityDocument.value[name] = input.value; // Cập nhật identityDocument từ input
                }
            }
        });
    };

    // Lấy thông tin hợp đồng từ server
    const fetchContract = async () => {
        loading.value = true; // Bật trạng thái loading
        try {
            // Gửi yêu cầu GET để lấy thông tin hợp đồng
            const response = await $api(`/contracts/${route.params.id}`, { method: 'GET' });
            contract.value = response.data; // Cập nhật dữ liệu hợp đồng
            phoneNumber.value = response.data.user_phone; // Cập nhật số điện thoại
            processContractContent(); // Xử lý nội dung hợp đồng
        } catch (error) {
            console.error('Lỗi khi lấy hợp đồng:', error); // Ghi log lỗi
            toast.error('Lỗi khi tải hợp đồng, vui lòng thử lại.'); // Hiển thị thông báo lỗi
            router.push('/quan-ly/hop-dong'); // Chuyển hướng về trang quản lý hợp đồng
        } finally {
            loading.value = false; // Tắt trạng thái loading
            await nextTick(); // Đợi DOM cập nhật
            syncIdentityData(); // Đồng bộ dữ liệu CCCD
        }
    };

    // Xử lý tải lên ảnh CCCD và trích xuất thông tin
    const handleIdentityUpload = async files => {
        if (bypassExtract.value) {
            identityImages.value = files; // Nếu bypass quét, lưu ảnh và thoát
            return;
        }

        const formData = new FormData(); // Tạo FormData để gửi ảnh
        files.forEach(file => formData.append('identity_images[]', file)); // Thêm các ảnh vào FormData

        extractLoading.value = true; // Bật trạng thái loading khi quét
        try {
            // Gửi yêu cầu POST để trích xuất thông tin từ ảnh CCCD
            const response = await $api('/extract-identity-images', {
                method: 'POST',
                body: formData,
                headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value }
            });
            identityDocument.value = response.data; // Cập nhật thông tin CCCD
            identityImages.value = files; // Lưu danh sách ảnh
            toast.success(response.message); // Hiển thị thông báo thành công
            extractErrorCount.value = 0; // Reset số lần quét lỗi

            // Vô hiệu hóa Dropzone nếu CCCD hợp lệ
            if (dropzoneInstance.value && identityDocument.value.has_valid) {
                dropzoneInstance.value.disable();
            }
        } catch (error) {
            // Xử lý lỗi khi quét CCCD
            if (error.response?.status === 422 && error.response?._data?.error) {
                const errorMessage = error.response._data.error;
                if (
                    errorMessage.includes('Lỗi xử lý ảnh CCCD') ||
                    errorMessage.includes('Không thể xác định mặt trước hoặc mặt sau') ||
                    errorMessage.includes('Không tìm thấy văn bản trong ảnh CCCD')
                ) {
                    extractErrorCount.value += 1; // Tăng số lần quét lỗi
                }
            }

            handleBackendError(error, toast); // Xử lý lỗi từ backend
            // Reset thông tin CCCD khi quét thất bại
            identityDocument.value = {
                full_name: '',
                year_of_birth: '',
                identity_number: '',
                date_of_issue: '',
                place_of_issue: '',
                permanent_address: '',
                has_valid: false
            };
            identityImages.value = []; // Xóa danh sách ảnh
            if (dropzoneInstance.value) {
                dropzoneInstance.value.removeAllFiles(true); // Xóa tất cả file trong Dropzone
            }

            // Nếu quét thất bại 5 lần, bật chế độ bypass
            if (extractErrorCount.value >= 5) {
                bypassExtract.value = true;
                toast.warning(
                    'Quét CCCD thất bại 5 lần. Bạn có thể nhập thông tin CCCD trực tiếp vào hợp đồng và tải ảnh lên để admin xác nhận.'
                );
            }
        } finally {
            extractLoading.value = false; // Tắt trạng thái loading
            await nextTick(); // Đợi DOM cập nhật
            syncIdentityData(); // Đồng bộ dữ liệu CCCD
        }
    };

    // Xử lý chữ ký, chuyển đổi kích thước và định dạng ảnh chữ ký
    const processSignature = signature => {
        return new Promise(resolve => {
            const img = new Image();
            img.crossOrigin = 'Anonymous';
            img.onload = () => {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');

                const scaleFactor = 2; // Tỷ lệ phóng to để đảm bảo chất lượng
                const targetWidth = 200 * scaleFactor;
                const targetHeight = 100 * scaleFactor;
                canvas.width = targetWidth;
                canvas.height = targetHeight;

                ctx.drawImage(img, 0, 0, targetWidth, targetHeight); // Vẽ ảnh chữ ký lên canvas
                ctx.imageSmoothingEnabled = true;
                ctx.imageSmoothingQuality = 'high';

                // Tạo canvas mới để resize lại kích thước chuẩn
                const outputCanvas = document.createElement('canvas');
                const outputCtx = outputCanvas.getContext('2d');
                outputCanvas.width = 200;
                outputCanvas.height = 100;
                outputCtx.drawImage(canvas, 0, 0, targetWidth, targetHeight, 0, 0, 200, 100);

                resolve(outputCanvas.toDataURL('image/png', 0.9)); // Trả về dữ liệu ảnh dạng base64
            };
            img.onerror = () => resolve(null); // Trả về null nếu tải ảnh thất bại
            img.src = signature; // Gán nguồn ảnh chữ ký
        });
    };

    // Cập nhật nội dung hợp đồng với chữ ký
    const updateContractWithSignature = async () => {
        const processedSignature = await processSignature(signatureData.value); // Xử lý chữ ký
        const parser = new DOMParser();
        const doc = parser.parseFromString(contract.value.content, 'text/html'); // Parse nội dung hợp đồng thành DOM

        const sideBSections = doc.getElementsByClassName('col-6 text-center'); // Tìm các section của Bên B
        if (sideBSections.length >= 2) {
            const sideB = sideBSections[1]; // Lấy section thứ hai (Bên B)
            const signatureImg = doc.createElement('img');
            signatureImg.src = processedSignature; // Gán nguồn ảnh chữ ký
            signatureImg.className = 'signature-image';
            signatureImg.alt = 'Chữ ký Bên B';

            const nameParagraph = doc.createElement('p');
            const nameStrong = doc.createElement('strong');
            nameStrong.textContent = identityDocument.value.full_name; // Gán tên đầy đủ từ CCCD
            nameParagraph.appendChild(nameStrong);
            nameParagraph.className = 'signature-name';

            const signaturePlaceholder = sideB.querySelector('p.mark-sign'); // Tìm placeholder chữ ký
            if (signaturePlaceholder) {
                signaturePlaceholder.after(nameParagraph); // Thêm tên sau placeholder
                signaturePlaceholder.after(signatureImg); // Thêm ảnh chữ ký sau placeholder
            } else {
                sideB.appendChild(signatureImg); // Thêm ảnh chữ ký vào section
                sideB.appendChild(nameParagraph); // Thêm tên vào section
            }
            return doc.documentElement.innerHTML; // Trả về nội dung HTML đã cập nhật
        }
        return contract.value.content; // Trả về nội dung gốc nếu không tìm thấy section
    };

    // Yêu cầu gửi mã OTP
    const requestOTP = async action => {
        if (!phoneNumber.value) {
            toast.error('Không tìm thấy số điện thoại.'); // Hiển thị lỗi nếu không có số điện thoại
            return false;
        }

        currentAction.value = action; // Lưu hành động hiện tại
        await nextTick(); // Đợi DOM cập nhật

        // Kiểm tra môi trường và reCAPTCHA container
        if (typeof window === 'undefined' || !document.getElementById('recaptcha-container')) {
            toast.error('Không thể khởi tạo reCAPTCHA. Vui lòng thử lại.');
            return false;
        }

        // Mở popup OTP
        window.jQuery.magnificPopup.open({
            items: { src: '#otp-dialog', type: 'inline' },
            fixedContentPos: false,
            fixedBgPos: true,
            overflowY: 'auto',
            closeBtnInside: true,
            preloader: false,
            midClick: true,
            removalDelay: 300,
            mainClass: 'my-mfp-zoom-in',
            closeOnBgClick: false,
            callbacks: {
                open: async () => {
                    const success = await sendOTP(phoneNumber.value); // Gửi OTP
                    if (!success) {
                        window.jQuery.magnificPopup.close(); // Đóng popup nếu gửi thất bại
                        toast.error('Lỗi khi gửi OTP. Vui lòng thử lại.');
                        return;
                    }
                    const otpInput = document.getElementById('otp-input');
                    if (otpInput) {
                        setTimeout(() => otpInput.focus(), 100); // Focus vào input OTP
                    }
                }
            }
        });

        return true; // Trả về true nếu yêu cầu OTP thành công
    };

    // Ký hợp đồng
    const signContract = async () => {
        try {
            const otpSent = await requestOTP('sign'); // Gửi yêu cầu OTP cho hành động ký
            if (!otpSent) return; // Thoát nếu không gửi được OTP
        } catch (error) {
            console.error(error); // Ghi log lỗi
        }
    };

    // Yêu cầu kết thúc hợp đồng sớm
    const earlyTermination = async id => {
        try {
            const otpSent = await requestOTP('early_termination'); // Gửi yêu cầu OTP cho hành động kết thúc sớm
            if (!otpSent) return; // Thoát nếu không gửi được OTP
        } catch (error) {
            console.error('Lỗi khi yêu cầu OTP cho kết thúc sớm:', error);
            toast.error('Lỗi khi yêu cầu OTP. Vui lòng thử lại.');
        }
    };

    // Xác thực OTP và thực hiện hành động tương ứng
    const verifyOTPAndExecute = async () => {
        saveLoading.value = true; // Bật trạng thái loading
        try {
            const verified = await verifyOTP(otpCode.value); // Xác thực OTP
            if (!verified) {
                toast.error('OTP không hợp lệ.'); // Hiển thị lỗi nếu OTP không hợp lệ
                saveLoading.value = false;
                return;
            }

            if (currentAction.value === 'sign') {
                const updatedContent = await updateContractWithSignature(); // Cập nhật hợp đồng với chữ ký
                // Gửi yêu cầu ký hợp đồng
                const response = await $api(`/contracts/${route.params.id}/sign`, {
                    method: 'POST',
                    body: {
                        _method: 'PATCH',
                        signature: signatureData.value,
                        content: updatedContent
                    },
                    headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value }
                });
                toast.success(response.message); // Hiển thị thông báo thành công
                if (window.jQuery && window.jQuery.fn.magnificPopup) {
                    window.jQuery.magnificPopup.close(); // Đóng popup
                }
                router.push(`/quan-ly/hoa-don/${response.invoice_code}/thanh-toan`); // Chuyển hướng đến trang thanh toán
            } else if (currentAction.value === 'early_termination') {
                // Gửi yêu cầu kết thúc hợp đồng sớm
                const response = await $api(`/contracts/${route.params.id}/early-termination`, {
                    method: 'POST',
                    headers: {
                        'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
                    },
                    body: { _method: 'PATCH' }
                });
                toast.success('Bạn đã kết thúc sớm hợp đồng thành công.');
                router.push('/quan-ly/hop-dong');
                if (window.jQuery && window.jQuery.fn.magnificPopup) {
                    window.jQuery.magnificPopup.close(); // Đóng popup
                }
            }
        } catch (error) {
            console.error(`Lỗi khi thực hiện ${currentAction.value}:`, error);
            handleBackendError(error, toast); // Xử lý lỗi backend
        } finally {
            saveLoading.value = false; // Tắt trạng thái loading
            currentAction.value = null; // Reset hành động
        }
    };

    // Cập nhật HTML của hợp đồng với dữ liệu từ input
    const updateContractHtml = () => {
        let html = contract.value.content; // Lấy nội dung hợp đồng
        const inputs = contractContainer.value.querySelectorAll('input[type="text"]'); // Lấy tất cả input text
        const inputWidths = {
            full_name: '200px',
            year_of_birth: '100px',
            identity_number: '150px',
            date_of_issue: '150px',
            place_of_issue: '500px',
            permanent_address: '500px'
        }; // Định nghĩa chiều rộng cho các input

        inputs.forEach(input => {
            const { name, value = '' } = input;
            if (name in identityDocument.value) {
                const width = inputWidths[name] || '200px';
                // Thay thế input trong HTML với giá trị và thuộc tính readonly
                const regex = new RegExp(`<input[^>]*name="${name}"[^>]*>`, 'g');
                html = html.replace(
                    regex,
                    `<input type="text" class="form-control flat-line d-inline-block" style="width: ${width};" name="${name}" value="${value}" readonly>`
                );
            }
        });
        return html; // Trả về HTML đã cập nhật
    };

    // Lưu hợp đồng
    const saveContract = async () => {
        saveLoading.value = true; // Bật trạng thái loading
        try {
            const updatedHtml = updateContractHtml(); // Cập nhật HTML hợp đồng
            const formData = new FormData();
            formData.append('contract_content', updatedHtml); // Thêm nội dung hợp đồng vào FormData

            // Nếu hợp đồng ở trạng thái "Chờ xác nhận", thêm ảnh CCCD
            if (contract.value.status === 'Chờ xác nhận') {
                identityImages.value.forEach(file => formData.append('identity_images[]', file));
            }

            formData.append('_method', 'PATCH'); // Sử dụng phương thức PATCH

            // Gửi yêu cầu cập nhật hợp đồng
            const response = await $api(`/contracts/${route.params.id}`, {
                method: 'POST',
                body: formData,
                headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value }
            });
            toast.success(response.message); // Hiển thị thông báo thành công
            router.push('/quan-ly/hop-dong');
        } catch (error) {
            console.error('Lỗi khi lưu hợp đồng:', error);
            handleBackendError(error, toast); // Xử lý lỗi backend
        } finally {
            saveLoading.value = false; // Tắt trạng thái loading
        }
    };

    // Trả về các hàm và biến trạng thái để sử dụng trong component
    return {
        fetchContract,
        handleIdentityUpload,
        signContract,
        verifyOTPAndExecute,
        saveContract,
        phoneNumber,
        otpCode,
        extractErrorCount,
        bypassExtract,
        earlyTermination
    };
}
