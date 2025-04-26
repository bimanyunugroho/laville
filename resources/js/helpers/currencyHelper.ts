/**
 * Format number to Indonesian Rupiah currency with Rp symbol
 * @param value - The number to format
 * @returns Formatted currency string with Rp symbol (e.g. "Rp 15.000")
 */
export const formatCurrency = (value: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

/**
 * Format number to Indonesian Rupiah format without currency symbol
 * @param value - The number to format
 * @returns Formatted number string without Rp symbol (e.g. "15.000")
 */
export const formatNumber = (value: number): string => {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};
