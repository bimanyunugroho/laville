/**
 * Format number to Indonesian Rupiah currency with Rp symbol
 * @param value - The number to format
 * @returns Formatted currency string with Rp symbol (e.g. "Rp 15.000")
 */
export const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    if (!dateString) return '-';
    return dateString;
}

export const getMonthName = (monthNumber: number | string): string => {
    const months: string[] = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    if (typeof monthNumber === 'string') {
        monthNumber = parseInt(monthNumber);
    }

    if (isNaN(monthNumber) || monthNumber < 1 || monthNumber > 12) {
        throw new Error('Bulan harus antara 1 dan 12');
    }

    return months[monthNumber - 1];
}
