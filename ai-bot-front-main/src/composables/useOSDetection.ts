import { ref } from 'vue';

export type OS = 'windows' | 'macos' | 'linux' | 'unknown';

export function useOSDetection() {
    const detectOS = (): OS => {
        if (typeof window === 'undefined') return 'unknown';
        
        const userAgent = navigator.userAgent.toLowerCase();
        const platform = navigator.platform.toLowerCase();

        // Windows detection
        if (userAgent.includes('win') || platform.includes('win')) {
            return 'windows';
        }
        
        // macOS detection
        if (userAgent.includes('mac') || platform.includes('mac')) {
            return 'macos';
        }
        
        // Linux detection
        if (userAgent.includes('linux') || platform.includes('linux') || 
            userAgent.includes('x11') || platform.includes('x11')) {
            return 'linux';
        }
        
        return 'unknown';
    };

    const os = ref<OS>(detectOS());

    return {
        os,
        isWindows: () => os.value === 'windows',
        isMacOS: () => os.value === 'macos',
        isLinux: () => os.value === 'linux'
    };
}

