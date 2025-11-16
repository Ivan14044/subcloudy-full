type DirectiveValue =
    | string
    | {
        class?: string;
        hiddenClass?: string;
        once?: boolean;
        threshold?: number;
        rootMargin?: string;
    };

type NormalizedOptions = {
    showClass: string;
    hiddenClass: string;
    once: boolean;
    threshold: number;
    rootMargin: string;
};

type RevealState = {
    observer?: IntersectionObserver;
    revealed: boolean;
    options: NormalizedOptions;
    pageshowHandler?: (event: WindowEventMap['pageshow']) => void;
};

const elementState = new WeakMap<HTMLElement, RevealState>();

const supportsIO = typeof window !== 'undefined' && 'IntersectionObserver' in window;
const prefersReducedMotion = () =>
    typeof window !== 'undefined' && window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

const normalizeOptions = (value: DirectiveValue): NormalizedOptions => {
    const base = typeof value === 'object' && value !== null ? value : { class: value };

    return {
        showClass: typeof base.class === 'string' && base.class.length > 0 ? base.class : 'reveal-visible',
        hiddenClass:
            typeof base.hiddenClass === 'string' && base.hiddenClass.length > 0
                ? base.hiddenClass
                : 'reveal-hidden',
        once: base.once !== false,
        threshold: typeof base.threshold === 'number' ? base.threshold : 0.15,
        rootMargin: typeof base.rootMargin === 'string' ? base.rootMargin : '0px 0px -12% 0px'
    };
};

const reveal = (el: HTMLElement, state: RevealState) => {
    if (state.revealed) return;
    state.revealed = true;

    requestAnimationFrame(() => {
        el.classList.add(state.options.showClass);
        el.classList.remove(state.options.hiddenClass);
    });

    if (state.options.once && state.observer) {
        state.observer.unobserve(el);
        state.observer.disconnect();
        state.observer = undefined;
    }
};

export default {
    beforeMount(el: HTMLElement, binding: { value: DirectiveValue }) {
        const options = normalizeOptions(binding.value);
        el.classList.remove(options.showClass);
        el.classList.add(options.hiddenClass);
    },
    mounted(el: HTMLElement, binding: { value: DirectiveValue }) {
        const options = normalizeOptions(binding.value);

        if (!supportsIO || prefersReducedMotion()) {
            el.classList.add(options.showClass);
            el.classList.remove(options.hiddenClass);
            elementState.set(el, { revealed: true, options });
            return;
        }

        const state: RevealState = {
            revealed: false,
            options
        };

        const observer = new IntersectionObserver(
            (entries) => {
                for (const entry of entries) {
                    if (entry.target !== el) continue;

                    const visible = entry.isIntersecting || entry.intersectionRatio > 0;
                    if (!visible) continue;

                    if (entry.intersectionRatio >= options.threshold) {
                        reveal(el, state);
                    }
                }
            },
            {
                rootMargin: options.rootMargin,
                threshold: [0, options.threshold, 0.999]
            }
        );

        const handlePageShow = (event: WindowEventMap['pageshow']) => {
            if (event.persisted) {
                reveal(el, state);
            }
        };

        window.addEventListener('pageshow', handlePageShow);

        state.observer = observer;
        state.pageshowHandler = handlePageShow;

        observer.observe(el);
        elementState.set(el, state);
    },
    unmounted(el: HTMLElement) {
        const state = elementState.get(el);
        if (!state) return;

        state.observer?.unobserve(el);
        state.observer?.disconnect();

        if (state.pageshowHandler) {
            window.removeEventListener('pageshow', state.pageshowHandler);
        }

        elementState.delete(el);
    }
};
