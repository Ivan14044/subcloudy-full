<template>
    <footer class="footer-container mt-auto">
        <!-- Основной контент футера -->
        <div class="footer-content">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Верхняя часть футера -->
                <div class="footer-top">
                    <div class="footer-grid">
                        <!-- Блок с логотипом и описанием -->
                        <div class="footer-brand">
                            <div class="brand-logo">
                                <h3 class="brand-title">SubCloudy</h3>
                                <div class="brand-gradient-line"></div>
                            </div>
                            <p class="brand-description">
                                {{ $t('footer.description', 'Совместные подписки на премиум сервисы с экономией до 80%') }}
                            </p>
                            <div class="brand-contact">
                                <a 
                                    href="mailto:support@subcloudy.com" 
                                    class="contact-link"
                                >
                                    <svg class="contact-icon" viewBox="0 0 24 24" fill="none">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <polyline points="22,6 12,13 2,6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    support@subcloudy.com
                                </a>
                            </div>
                        </div>

                        <!-- Навигационные ссылки -->
                        <div class="footer-nav">
                            <h4 class="nav-title">{{ $t('footer.navigation', 'Навигация') }}</h4>
                            <FooterMenu />
                        </div>

                        <!-- Юридические ссылки -->
                        <div class="footer-legal">
                            <h4 class="nav-title">{{ $t('footer.legal', 'Правовая информация') }}</h4>
                            <ul class="legal-links">
                                <li>
                                    <router-link to="/terms-of-service" class="footer-link">
                                        {{ $t('footer.termsOfService') }}
                                    </router-link>
                                </li>
                                <li>
                                    <router-link to="/privacy-policy" class="footer-link">
                                        {{ $t('footer.privacyPolicy') }}
                                    </router-link>
                                </li>
                                <li>
                                    <router-link to="/refund-policy" class="footer-link">
                                        {{ $t('footer.refundPolicy') }}
                                    </router-link>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Разделитель -->
                <div class="footer-divider"></div>

                <!-- Нижняя часть футера -->
                <div class="footer-bottom">
                    <div class="footer-bottom-content">
                        <div class="footer-copyright">
                            <p class="copyright-text">{{ $t('footer.copyright') }}</p>
                        </div>
                        <div class="footer-payment">
                            <img 
                                class="payment-logo" 
                                src="/img/visa-mastercard.png" 
                                alt="Visa Mastercard"
                                width="168"
                                height="56"
                                loading="lazy"
                                decoding="async"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liquid glass overlay эффект -->
        <div class="footer-glass-overlay"></div>
        <div class="footer-glass-shine"></div>
    </footer>
</template>

<script setup lang="ts">
import FooterMenu from '@/components/layout/FooterMenu.vue';
</script>

<style scoped>
.footer-container {
    position: relative;
    margin-top: auto;
    width: 100%;
    z-index: 1;
    overflow: hidden;
    flex-shrink: 0;
    background: rgb(22, 23, 25);
}

.dark .footer-container {
    background: rgb(10, 14, 24);
}

.footer-content {
    position: relative;
    z-index: 2;
    width: 100%;
    padding: 3rem 0 1.5rem;
    background: rgb(22, 23, 25);
    border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.dark .footer-content {
    background: rgb(10, 14, 24);
}

.footer-top {
    margin-bottom: 2rem;
}

.footer-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2.5rem;
}

@media (min-width: 640px) {
    .footer-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .footer-grid {
        grid-template-columns: 2fr 1fr 1fr;
        gap: 3rem;
    }
}

/* Бренд блок */
.footer-brand {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.brand-logo {
    position: relative;
}

.brand-title {
    font-size: 1.75rem;
    font-weight: 700;
    background: linear-gradient(135deg, #ffffff 0%, rgba(255, 255, 255, 0.8) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 0.5rem;
    letter-spacing: -0.02em;
}

.brand-gradient-line {
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899);
    border-radius: 2px;
    margin-top: 0.5rem;
}

.brand-description {
    font-size: 0.875rem;
    line-height: 1.6;
    color: rgba(255, 255, 255, 0.7);
    max-width: 300px;
}

.brand-contact {
    margin-top: 0.5rem;
}

.contact-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all 0.3s ease;
    padding: 0.5rem 0;
}

.contact-link:hover {
    color: #ffffff;
    transform: translateX(4px);
}

.contact-icon {
    width: 18px;
    height: 18px;
    opacity: 0.8;
    transition: opacity 0.3s ease;
}

.contact-link:hover .contact-icon {
    opacity: 1;
}

/* Навигация */
.footer-nav,
.footer-legal {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.nav-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.9);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.5rem;
}

.legal-links {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.footer-link {
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
    display: inline-block;
    padding-left: 0;
}

.footer-link::before {
    content: '';
    position: absolute;
    left: 0;
    bottom: -2px;
    width: 0;
    height: 1px;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6);
    transition: width 0.3s ease;
}

.footer-link:hover {
    color: #ffffff;
    padding-left: 8px;
}

.footer-link:hover::before {
    width: 4px;
}

/* Разделитель */
.footer-divider {
    height: 1px;
    background: linear-gradient(
        90deg,
        transparent 0%,
        rgba(255, 255, 255, 0.1) 20%,
        rgba(255, 255, 255, 0.1) 80%,
        transparent 100%
    );
    margin: 2rem 0 1.5rem;
}

/* Нижняя часть */
.footer-bottom {
    padding-top: 1.5rem;
}

.footer-bottom-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
}

@media (min-width: 768px) {
    .footer-bottom-content {
        flex-direction: row;
    }
}

.copyright-text {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.6);
    text-align: center;
}

@media (min-width: 768px) {
    .copyright-text {
        text-align: left;
    }
}

.payment-logo {
    height: 32px;
    width: auto;
    object-fit: contain;
    opacity: 0.8;
    transition: opacity 0.3s ease;
    filter: brightness(0.9);
}

.payment-logo:hover {
    opacity: 1;
    filter: brightness(1);
}

/* Liquid glass эффекты */
.footer-glass-overlay {
    position: absolute;
    inset: 0;
    z-index: 1;
    pointer-events: none;
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    background: transparent;
    border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.footer-glass-shine {
    position: absolute;
    inset: 0;
    z-index: 1;
    pointer-events: none;
    background: radial-gradient(
        ellipse at top,
        rgba(255, 255, 255, 0.08) 0%,
        rgba(255, 255, 255, 0.02) 40%,
        transparent 70%
    );
    mix-blend-mode: overlay;
    opacity: 0.6;
}

/* Анимация при появлении */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.footer-brand,
.footer-nav,
.footer-legal {
    animation: fadeInUp 0.6s ease-out;
}

.footer-nav {
    animation-delay: 0.1s;
}

.footer-legal {
    animation-delay: 0.2s;
}
</style>
