import { ref } from 'vue';

export type OS = 'windows' | 'macos' | 'unknown';

export function useOSDetection() {
    const detectOS = (): OS => {
        if (typeof window === 'undefined') return 'unknown';
        
        const userAgent = navigator.userAgent.toLowerCase();
        const platform = navigator.platform.toLowerCase();

        if (userAgent.includes('win') || platform.includes('win')) {
            return 'windows';
        }
        if (userAgent.includes('mac') || platform.includes('mac')) {
            return 'macos';
        }
        return 'unknown';
    };

    const os = ref<OS>(detectOS());

    return {
        os,
        isWindows: () => os.value === 'windows',
        isMacOS: () => os.value === 'macos'
    };
}

