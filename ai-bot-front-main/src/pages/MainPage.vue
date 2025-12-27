<template>
    <!-- Hero Section -->
    <div
        class="hero-placeholder max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pointer-events-none min-h-screen flex items-center"
    >
        <HeroSection class="w-full" />
    </div>

    <div class="mx-auto px-4 pt-5 md:pt-24 pb-7 md:pb-16 sm:px-6 lg:px-8">
        <!-- Savings Section -->
        <section id="savings" class="mb-16 min-h-[400px]">
            <div class="text-center mb-8">
                <h2
                    class="text-[32px] md:text-[48px] lg:text-[64px] font-medium text-gray-900 dark:text-white mt-3"
                    v-html="$t('savings.title')"
                ></h2>
            </div>
            <p
                class="text-center mb-10 text-lg leading-6 text-gray-700 dark:text-gray-300"
                v-html="$t('savings.description')"
            ></p>
            <SavingsOn />
        </section>

        <!-- Steps Section -->
        <section id="steps" class="min-h-[300px]">
            <div class="text-center mb-8 relative z-2">
                <h2
                    class="text-[32px] md:text-[48px] lg:text-[64px] text-gray-900 dark:text-white mt-3 leading-none"
                    v-html="$t('steps.title')"
                ></h2>
            </div>
            <StepsSection />
        </section>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-16 sm:px-6 lg:px-8">
        <!-- Services Section -->
        <section id="services" class="mb-16 min-h-[600px]">
            <div class="text-center mb-16">
                <h2
                    class="text-[32px] md:text-[48px] lg:text-[64px] text-gray-900 dark:text-white mt-3 leading-none"
                    v-html="$t('services.title')"
                ></h2>
            </div>
            <ServiceSelector />
        </section>

        <!-- Articles Section -->
        <section id="articles" class="mb-16 min-h-[500px]">
            <ArticleSection />
        </section>

        <!-- About Section -->
        <section id="about" class="flex flex-col lg:flex-row items-center justify-center gap-20 pt-20 pb-24 min-h-[400px]">
            <AboutSection />
        </section>

        <!-- Promote Section -->
        <section id="promote" class="mb-16 min-h-[300px]">
            <div class="text-center mb-16">
                <h2
                    class="text-[32px] md:text-[48px] lg:text-[64px] font-medium text-gray-900 dark:text-white mt-3"
                    v-html="$t('promote.title')"
                ></h2>
            </div>
            <PromoteSection />
        </section>

        <!-- Reviews Section -->
        <section id="reviews" class="mb-16 min-h-[400px]">
            <div class="text-center mb-8">
                <h2
                    class="text-[32px] md:text-[48px] lg:text-[64px] font-medium text-gray-900 dark:text-white mt-3"
                    v-html="$t('reviews.title')"
                ></h2>
            </div>
            <ReviewSection />
        </section>

        <!-- FAQ Section -->
        <section id="faq" class="mb-16 min-h-[400px]">
            <div class="text-center mb-8">
                <h2
                    class="text-[32px] md:text-[48px] lg:text-[64px] font-medium text-gray-900 dark:text-white mt-3"
                    v-html="$t('faq.title')"
                ></h2>
            </div>
            <FAQSection />
        </section>

        <!-- Subscribe Section -->
        <section id="subscribe" class="mb-16 min-h-[300px]">
            <SubscribeSection />
        </section>
    </div>
</template>

<script setup lang="ts">
import { defineAsyncComponent } from 'vue';
import HeroSection from '../components/home/HeroSection.vue';

// Асинхронная загрузка всех компонентов для уменьшения начального бандла
const ServiceSelector = defineAsyncComponent(() => import('../components/home/ServiceSelector.vue'));
const SavingsOn = defineAsyncComponent(() => import('../components/home/SavingsOn.vue'));
const AboutSection = defineAsyncComponent(() => import('../components/home/AboutSection.vue'));
const ArticleSection = defineAsyncComponent(() => import('../components/home/ArticleSection.vue'));
const PromoteSection = defineAsyncComponent(() => import('../components/home/PromoteSection.vue'));
const StepsSection = defineAsyncComponent(() => import('../components/home/StepsSection.vue'));
const SubscribeSection = defineAsyncComponent(() => import('../components/home/SubscribeSection.vue'));
const ReviewSection = defineAsyncComponent(() => import('../components/home/ReviewSection.vue'));
const FAQSection = defineAsyncComponent(() => import('../components/home/FAQSection.vue'));
</script>
