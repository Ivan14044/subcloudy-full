import i18n from '@/i18n';
import Swal from 'sweetalert2';

// Безопасное получение функции перевода
const getT = () => {
    try {
        if (i18n && i18n.global && i18n.global.t) {
            return i18n.global.t;
        }
    } catch (e) {
        console.warn('[Alert] Error getting i18n t function:', e);
    }
    // Fallback функция
    return (key) => key;
};

export function useAlert() {
    const t = getT();

    const baseOptions = {
        customClass: {
            popup: 'swal2-glass-modal',
            title: 'text-xl font-semibold text-gray-800 dark:text-white',
            htmlContainer: 'text-gray-600 dark:text-gray-300',
            confirmButton:
                'swal2-glass-button text-white px-4 py-2 rounded-lg mr-2',
            cancelButton:
                'swal2-glass-button text-gray-800 dark:text-gray-200 px-4 py-2 rounded-lg'
        },
        scrollbarPadding: false,
        buttonsStyling: false
    };

    const showAlert = ({ title, text, icon = 'info', confirmText } = {}) => {
        return Swal.fire({
            ...baseOptions,
            title: title || t('alert.title'),
            text: text || t('alert.text'),
            icon,
            confirmButtonText: confirmText || t('alert.ok')
        });
    };

    const showConfirm = ({ title, text, icon = 'warning', confirmText, cancelText } = {}) => {
        return Swal.fire({
            ...baseOptions,
            title: title || t('confirm.title'),
            text: text || t('confirm.text'),
            icon,
            showCancelButton: true,
            confirmButtonText: confirmText || t('confirm.yes'),
            cancelButtonText: cancelText || t('confirm.cancel')
        });
    };

    return { showAlert, showConfirm };
}
