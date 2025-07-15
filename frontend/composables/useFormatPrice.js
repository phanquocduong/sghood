export const useFormatPrice = () => {
    // Hàm định dạng giá tiền
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

    return {
        formatPrice,
        formatFees
    };
};
