// Composable định dạng giá tiền và phí
export const useFormatPrice = () => {
    // Hàm định dạng giá tiền sang định dạng tiền tệ Việt Nam
    const formatPrice = price => {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
    };

    // Hàm định dạng danh sách phí
    const formatFees = fees => {
        return fees.map(fee => ({
            name: fee.name,
            price: `${formatPrice(fee.price)}/${fee.unit}`
        }));
    };

    // Trả về các hàm định dạng
    return {
        formatPrice,
        formatFees
    };
};
