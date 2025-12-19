import { onMounted, onBeforeUnmount, Ref, ref } from 'vue';

export function useScrollAnimation(
    selector: string | Ref<HTMLElement | null>,
    options: {
        threshold?: number;
        rootMargin?: string;
        animationClass?: string;
        once?: boolean;
    } = {}
) {
    const {
        threshold = 0.1,
        rootMargin = '0px 0px -100px 0px',
        animationClass = 'animate',
        once = true
    } = options;

    let observer: IntersectionObserver | null = null;

    const observe = () => {
        if (typeof window === 'undefined' || !('IntersectionObserver' in window)) {
            return;
        }

        observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add(animationClass);
                        if (once) {
                            observer?.unobserve(entry.target);
                        }
                    } else if (!once) {
                        entry.target.classList.remove(animationClass);
                    }
                });
            },
            {
                threshold,
                rootMargin
            }
        );

        const elements = typeof selector === 'string' 
            ? document.querySelectorAll(selector)
            : selector.value ? [selector.value] : [];

        elements.forEach((el) => {
            if (el) {
                observer?.observe(el as Element);
            }
        });
    };

    onMounted(() => {
        if (typeof selector === 'string') {
            observe();
        } else {
            // Для ref нужно дождаться следующего тика
            setTimeout(observe, 0);
        }
    });

    onBeforeUnmount(() => {
        if (observer) {
            observer.disconnect();
            observer = null;
        }
    });

    return {
        observe,
        disconnect: () => {
            if (observer) {
                observer.disconnect();
                observer = null;
            }
        }
    };
}

