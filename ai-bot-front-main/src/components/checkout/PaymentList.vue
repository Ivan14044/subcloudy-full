<template>
    <div class="space-y-3 text-dark dark:text-white">
        <div :class="paymentClass('card')" @click="selectPaymentMethod('card')">
            <div class="flex items-center gap-3 w-100">
                <div class="w-10 h-10 rounded flex items-center justify-center">
                    <svg
                        width="28"
                        height="20"
                        viewBox="0 0 28 20"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <rect
                            x="0.5"
                            y="1"
                            width="27"
                            height="18"
                            rx="3"
                            stroke="currentColor"
                            stroke-opacity="0.9"
                        />
                        <rect
                            x="3"
                            y="6"
                            width="8"
                            height="2"
                            rx="1"
                            fill="currentColor"
                            fill-opacity="0.9"
                        />
                        <rect
                            x="3"
                            y="10"
                            width="5"
                            height="2"
                            rx="1"
                            fill="currentColor"
                            fill-opacity="0.9"
                        />
                    </svg>
                </div>

                <div class="flex-1">
                    <div class="font-medium">{{ $t('checkout.credit_card') }}</div>
                    <div class="text-xs">{{ $t('checkout.credit_card_placeholder') }}</div>
                    <div class="mt-2 flex items-center gap-2">
                        <div class="icon-pill" title="Apple Pay">
                            <img
                                :src="applePayLogo"
                                alt="Apple Pay"
                                width="40"
                                height="16"
                                class="h-3 object-contain object-center"
                                style="aspect-ratio: 5 / 2;"
                            />
                        </div>
                        <div class="icon-pill" title="Google Pay">
                            <img
                                :src="googlePayLogo"
                                alt="google pay"
                                width="40"
                                height="16"
                                class="h-3 object-contain object-center"
                                style="aspect-ratio: 5 / 2;"
                            />
                        </div>
                        <div class="icon-pill" title="Visa">
                            <img
                                :src="visaLogo"
                                alt="Visa"
                                width="40"
                                height="16"
                                class="h-4 object-contain object-center"
                                style="aspect-ratio: 5 / 2;"
                            />
                        </div>
                        <div class="icon-pill" title="Mastercard">
                            <img
                                :src="mastercardLogo"
                                alt="mastercard"
                                width="40"
                                height="24"
                                class="h-4 object-contain object-center"
                                style="aspect-ratio: 5 / 3;"
                            />
                        </div>
                    </div>
                </div>

                <div class="ml-auto">
                    <div
                        :class="[
                            'w-4 h-4 rounded-full border-2 transition-all duration-300',
                            selectedPaymentMethod === 'card'
                                ? 'border-blue-400 bg-blue-400'
                                : 'border-gray-800 dark:border-gray-100'
                        ]"
                    />
                </div>
            </div>
        </div>

        <div 
            :class="paymentClass('crypto')" 
            @click="selectPaymentMethod('crypto')"
            class="relative"
        >
            <div class="flex items-center gap-3 w-100">
                <div class="w-10 h-10 rounded flex items-center justify-center">
                    <span class="text-xl">₿</span>
                </div>

                <div class="flex-1">
                    <div class="font-medium">
                        {{ $t('checkout.crypto') }}
                    </div>
                    <div class="text-xs">{{ $t('checkout.crypto_placeholder') }}</div>
                    <div v-if="hasTrial" class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                        {{ $t('checkout.trial_crypto_hint') }}
                    </div>

                    <div class="mt-2 flex items-center gap-2">
                        <div class="icon-pill" title="Bitcoin">
                            <img
                                :src="bitcoinLogo"
                                alt="bitcoin"
                                width="24"
                                height="24"
                                class="h-4 object-contain object-center"
                                style="aspect-ratio: 1 / 1;"
                            />
                        </div>
                        <div class="icon-pill" title="Ethereum">
                            <img
                                :src="ethereumLogo"
                                alt="ethereum"
                                width="24"
                                height="24"
                                class="h-4 object-contain object-center"
                                style="aspect-ratio: 1 / 1;"
                            />
                        </div>
                        <div class="icon-pill" title="Tether">
                            <img
                                :src="tetherLogo"
                                alt="tether"
                                width="24"
                                height="24"
                                class="h-4 object-contain object-center"
                                style="aspect-ratio: 1 / 1;"
                            />
                        </div>
                    </div>
                </div>

                <div class="ml-auto">
                    <div
                        :class="[
                            'w-4 h-4 rounded-full border-2 transition-all duration-300',
                            selectedPaymentMethod === 'crypto'
                                ? 'border-blue-400 bg-blue-400'
                                : 'border-gray-800 dark:border-gray-100',
                            hasTrial ? 'opacity-50' : ''
                        ]"
                    />
                </div>
            </div>
        </div>

        <!-- Блок безопасных платежей -->
        <div class="mt-4 glass-card rounded-lg p-4 flex items-center gap-3">
            <div class="flex-shrink-0">
                <svg
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    class="text-green-500 dark:text-green-400"
                >
                    <path
                        d="M12 1L3 5V11C3 16.55 6.84 21.74 12 23C17.16 21.74 21 16.55 21 11V5L12 1ZM10 17L5 12L6.41 10.59L10 14.17L17.59 6.58L19 8L10 17Z"
                        fill="currentColor"
                    />
                </svg>
            </div>
            <div class="flex-1 text-sm text-gray-700 dark:text-gray-300">
                {{ $t('checkout.secure_payment_text') }}
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { computed } from 'vue';
import { useCartStore } from '@/stores/cart';
import applePayLogo from '@/assets/payment-icons/Apple_Pay_logo.svg.png';
import googlePayLogo from '@/assets/payment-icons/Google_Pay_Logo.svg.png';
import visaLogo from '@/assets/payment-icons/VISA-logo.png';
import mastercardLogo from '@/assets/payment-icons/mastercard-logo.png';
import bitcoinLogo from '@/assets/payment-icons/Bitcoin.svg.png';
import ethereumLogo from '@/assets/payment-icons/Ethereum_logo_translucent.png';
import tetherLogo from '@/assets/payment-icons/tether-logo.png';

const props = defineProps<{
    modelValue: 'card' | 'crypto' | '';
    disabled?: boolean;
}>();
const emit = defineEmits<{
    (e: 'update:modelValue', value: 'card' | 'crypto' | ''): void;
}>();

const selectedPaymentMethod = ref(props.modelValue);
const cartStore = useCartStore();

watch(
    () => props.modelValue,
    val => {
        selectedPaymentMethod.value = val;
    }
);

const hasTrial = computed(() => {
    return cartStore.items.some(service => cartStore.getSubscriptionType(service.id) === 'trial');
});

const selectPaymentMethod = (method: 'card' | 'crypto') => {
    if (props.disabled) return;
    // Блокируем выбор криптовалюты для триала
    if (method === 'crypto' && hasTrial.value) {
        return;
    }
    selectedPaymentMethod.value = method;
    emit('update:modelValue', method);
};

const paymentClass = (method: 'card' | 'crypto') => {
    const isCryptoDisabled = hasTrial.value && method === 'crypto';
    return [
        'payment-method rounded-lg p-4 border-2 flex items-center gap-3',
        isCryptoDisabled ? 'cursor-not-allowed opacity-60' : 'cursor-pointer',
        selectedPaymentMethod.value === method
            ? 'border-blue-400 bg-blue-400/10'
            : 'border-transparent glass-card hover:border-blue-400/50',
        props.disabled ? 'opacity-50 pointer-events-none' : ''
    ];
};
</script>

<style scoped>
.icon-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 34px;
    height: 28px;
    padding: 0 8px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.04);
    font-size: 12px;
}

</style>
