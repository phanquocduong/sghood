import { nextTick, ref } from 'vue';
import { useFirebaseAuth } from '~/composables/useFirebaseAuth';

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
    const { $api } = useNuxtApp();
    const { sendOTP, verifyOTP } = useFirebaseAuth();

    const phoneNumber = ref(null);
    const otpCode = ref('');
    const extractErrorCount = ref(0); // Đếm số lần quét lỗi
    const bypassExtract = ref(false); // Cờ cho phép bypass quét CCCD

    const processContractContent = () => {
        if (!contract.value?.content) return;

        let processedContent = contract.value.content;

        if (contract.value.status === 'Chờ chỉnh sửa') {
            processedContent = processedContent.replace(/\s*readonly\s*(?=\s|>|\/)/gi, '');
            processedContent = processedContent.replace(/<input([^>]*type="text"[^>]*)>/gi, (match, attributes) => {
                const cleanAttributes = attributes.replace(/\s*readonly\s*/gi, '');
                return `<input${cleanAttributes}>`;
            });
        }

        contract.value.content = processedContent;
    };

    const syncIdentityData = () => {
        if (!contractContainer.value) return;

        const inputs = contractContainer.value.querySelectorAll('input[type="text"]');
        inputs.forEach(input => {
            const { name } = input;
            if (name in identityDocument.value) {
                if (identityDocument.value[name]) {
                    input.value = identityDocument.value[name];
                } else {
                    identityDocument.value[name] = input.value;
                }
            }
        });
    };

    const handleBackendError = error => {
        const data = error.response?._data;
        if (data?.error) {
            toast.error(data.error);
            return;
        }
        if (data?.errors) {
            Object.values(data.errors).forEach(err => toast.error(err[0]));
            return;
        }
        toast.error('Đã có lỗi xảy ra. Vui lòng thử lại.');
    };

    const fetchContract = async () => {
        loading.value = true;
        try {
            const response = await $api(`/contracts/${route.params.id}`, { method: 'GET' });
            contract.value = response.data;
            phoneNumber.value = response.data.user_phone;
            processContractContent();
        } catch (error) {
            console.error('Lỗi khi lấy hợp đồng:', error);
            toast.error('Lỗi khi tải hợp đồng, vui lòng thử lại.');
            router.push('/quan-ly/hop-dong');
        } finally {
            loading.value = false;
            await nextTick();
            syncIdentityData();
        }
    };

    const handleIdentityUpload = async files => {
        if (bypassExtract.value) {
            identityImages.value = files;
            return;
        }

        const formData = new FormData();
        files.forEach(file => formData.append('identity_images[]', file));

        extractLoading.value = true;
        try {
            const response = await $api('/extract-identity-images', {
                method: 'POST',
                body: formData,
                headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value }
            });
            identityDocument.value = response.data;
            identityImages.value = files;
            toast.success(response.message);
            extractErrorCount.value = 0; // Reset lỗi khi quét thành công

            if (dropzoneInstance.value && identityDocument.value.has_valid) {
                dropzoneInstance.value.disable();
            }
        } catch (error) {
            extractErrorCount.value += 1;
            handleBackendError(error);
            identityDocument.value = {
                full_name: '',
                year_of_birth: '',
                identity_number: '',
                date_of_issue: '',
                place_of_issue: '',
                permanent_address: '',
                has_valid: false
            };
            identityImages.value = [];
            if (dropzoneInstance.value) {
                dropzoneInstance.value.removeAllFiles(true);
            }

            if (extractErrorCount.value >= 5) {
                bypassExtract.value = true;
                toast.warning(
                    'Quét CCCD thất bại 5 lần. Bạn có thể nhập thông tin CCCD trực tiếp vào hợp đồng và tải ảnh lên để admin xác nhận.'
                );
            }
        } finally {
            extractLoading.value = false;
            await nextTick();
            syncIdentityData();
        }
    };

    const processSignature = signature => {
        return new Promise(resolve => {
            const img = new Image();
            img.crossOrigin = 'Anonymous';
            img.onload = () => {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');

                const scaleFactor = 2;
                const targetWidth = 200 * scaleFactor;
                const targetHeight = 100 * scaleFactor;
                canvas.width = targetWidth;
                canvas.height = targetHeight;

                ctx.drawImage(img, 0, 0, targetWidth, targetHeight);
                ctx.imageSmoothingEnabled = true;
                ctx.imageSmoothingQuality = 'high';

                const outputCanvas = document.createElement('canvas');
                const outputCtx = outputCanvas.getContext('2d');
                outputCanvas.width = 200;
                outputCanvas.height = 100;
                outputCtx.drawImage(canvas, 0, 0, targetWidth, targetHeight, 0, 0, 200, 100);

                resolve(outputCanvas.toDataURL('image/png', 0.9));
            };
            img.onerror = () => resolve(null);
            img.src = signature;
        });
    };

    const updateContractWithSignature = async () => {
        const processedSignature = await processSignature(signatureData.value);
        const parser = new DOMParser();
        const doc = parser.parseFromString(contract.value.content, 'text/html');

        const sideBSections = doc.getElementsByClassName('col-6 text-center');
        if (sideBSections.length >= 2) {
            const sideB = sideBSections[1];
            const signatureImg = doc.createElement('img');
            signatureImg.src = processedSignature;
            signatureImg.className = 'signature-image';
            signatureImg.alt = 'Chữ ký Bên B';

            const nameParagraph = doc.createElement('p');
            const nameStrong = doc.createElement('strong');
            nameStrong.textContent = identityDocument.value.full_name;
            nameParagraph.appendChild(nameStrong);
            nameParagraph.className = 'signature-name';

            const signaturePlaceholder = sideB.querySelector('p.mark-sign');
            if (signaturePlaceholder) {
                signaturePlaceholder.after(nameParagraph);
                signaturePlaceholder.after(signatureImg);
            } else {
                sideB.appendChild(signatureImg);
                sideB.appendChild(nameParagraph);
            }
            return doc.documentElement.innerHTML;
        }
        return contract.value.content;
    };

    const requestOTP = async () => {
        if (!phoneNumber.value) {
            toast.error('Không tìm thấy số điện thoại.');
            return false;
        }

        await nextTick();

        if (typeof window === 'undefined' || !document.getElementById('recaptcha-container')) {
            toast.error('Không thể khởi tạo reCAPTCHA. Vui lòng thử lại.');
            return false;
        }

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
                    const success = await sendOTP(phoneNumber.value);
                    if (!success) {
                        window.jQuery.magnificPopup.close();
                        toast.error('Lỗi khi gửi OTP. Vui lòng thử lại.');
                        return;
                    }
                    const otpInput = document.getElementById('otp-input');
                    if (otpInput) {
                        setTimeout(() => otpInput.focus(), 100);
                    }
                }
            }
        });

        return true;
    };

    const signContract = async () => {
        try {
            const otpSent = await requestOTP();
            if (!otpSent) return;
        } catch (error) {
            console.error(error);
        }
    };

    const confirmOTPAndSign = async () => {
        saveLoading.value = true;
        try {
            const verified = await verifyOTP(otpCode.value);
            if (!verified) {
                saveLoading.value = false;
                return;
            }

            const updatedContent = await updateContractWithSignature();
            const response = await $api(`/contracts/${route.params.id}/sign`, {
                method: 'POST',
                body: {
                    signature: signatureData.value,
                    content: updatedContent
                },
                headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value }
            });
            toast.success(response.message);
            if (window.jQuery && window.jQuery.fn.magnificPopup) {
                window.jQuery.magnificPopup.close();
            }
            router.push(`/quan-ly/hoa-don/${response.invoice_code}/thanh-toan`);
        } catch (error) {
            console.error('Lỗi khi ký hợp đồng:', error);
            handleBackendError(error);
        } finally {
            saveLoading.value = false;
        }
    };

    const updateContractHtml = () => {
        let html = contract.value.content;
        const inputs = contractContainer.value.querySelectorAll('input[type="text"]');
        const inputWidths = {
            full_name: '200px',
            year_of_birth: '100px',
            identity_number: '150px',
            date_of_issue: '150px',
            place_of_issue: '500px',
            permanent_address: '500px'
        };

        inputs.forEach(input => {
            const { name, value = '' } = input;
            if (name in identityDocument.value) {
                const width = inputWidths[name] || '200px';
                const regex = new RegExp(`<input[^>]*name="${name}"[^>]*>`, 'g');
                html = html.replace(
                    regex,
                    `<input type="text" class="form-control flat-line d-inline-block" style="width: ${width};" name="${name}" value="${value}" readonly>`
                );
            }
        });
        return html;
    };

    const saveContract = async () => {
        saveLoading.value = true;
        try {
            const updatedHtml = updateContractHtml();
            const formData = new FormData();
            formData.append('contract_content', updatedHtml);
            formData.append('bypass_extract', bypassExtract);

            if (contract.value.status === 'Chờ xác nhận') {
                identityImages.value.forEach(file => formData.append('identity_images[]', file));
            }

            formData.append('_method', 'PATCH');

            const response = await $api(`/contracts/${route.params.id}`, {
                method: 'POST',
                body: formData,
                headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value }
            });
            toast.success(response.message);
            await fetchContract();
        } catch (error) {
            console.error('Lỗi khi lưu hợp đồng:', error);
            handleBackendError(error);
        } finally {
            saveLoading.value = false;
        }
    };

    return {
        fetchContract,
        handleIdentityUpload,
        signContract,
        confirmOTPAndSign,
        saveContract,
        phoneNumber,
        otpCode,
        extractErrorCount,
        bypassExtract
    };
}
