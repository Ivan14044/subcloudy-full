<template>
    <div
        v-intersect="{ threshold: 0.01, rootMargin: '0px 0px -80% 0px' }"
        class="section-container max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pointer-events-none min-h-screen flex align-center mt-[50px] lg:mt-0"
    >
        <!-- Hero Section -->
        <section id="hero" class="bg-transparent absolute top-0 left-0 px-[15px] md:static md:px-0">
            <HeroSection />
        </section>
    </div>
    <div
        v-intersect="{ threshold: 0.08, rootMargin: '0px 0px -40% 0px' }"
        class="mx-auto px-4 pt-5 md:pt-24 pb-7 md:pb-16 sm:px-6 lg:px-8"
    >
        <!-- Savings Section -->
        <section id="savings" v-intersect="{ threshold: 0.08, rootMargin: '0px 0px -40% 0px' }" class="mb-16">
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
        <section id="steps">
            <div class="text-center mb-8 relative z-2">
                <h2
                    class="text-[32px] md:text-[48px] lg:text-[64px]m text-gray-900 dark:text-white mt-3 leading-none"
                    v-html="$t('steps.title')"
                ></h2>
            </div>

            <StepsSection />
        </section>
    </div>
    <div class="max-w-7xl mx-auto px-4 py-16 sm:px-6 lg:px-8">
        <!-- Services Section -->
        <section id="services" v-intersect="{ threshold: 0.08, rootMargin: '0px 0px -40% 0px' }" class="mb-16">
            <div class="text-center mb-16">
                <h2
                    class="text-[32px] md:text-[48px] lg:text-[64px] text-gray-900 dark:text-white mt-3 leading-none"
                    v-html="$t('services.title')"
                ></h2>
            </div>

            <ServiceSelector />
        </section>

        <!-- Articles Section -->
        <section id="articles" v-intersect="{ threshold: 0.08, rootMargin: '0px 0px -40% 0px' }" class="mb-16">
            <ArticleSection />
        </section>

        <!-- About Section -->
        <section
            id="about"
            v-intersect="{ threshold: 0.08, rootMargin: '0px 0px -40% 0px' }"
            class="flex flex-col lg:flex-row items-center justify-center gap-20 pt-20 pb-24"
        >
            <AboutSection />
        </section>

        <!-- Promote Section -->
        <section id="promote" v-intersect="{ threshold: 0.08, rootMargin: '0px 0px -40% 0px' }" class="mb-16">
            <div class="text-center mb-16">
                <h2
                    class="text-[32px] md:text-[48px] lg:text-[64px] font-medium text-gray-900 dark:text-white mt-3"
                    v-html="$t('promote.title')"
                ></h2>
            </div>

            <PromoteSection />
        </section>


        <!-- Reviews Section -->
        <section id="reviews" v-intersect="{ threshold: 0.08, rootMargin: '0px 0px -40% 0px' }" class="mb-16">
            <div class="text-center mb-8">
                <h2
                    class="text-[32px] md:text-[48px] lg:text-[64px] font-medium text-gray-900 dark:text-white mt-3"
                    v-html="$t('reviews.title')"
                ></h2>
            </div>
            
            <ReviewSection />
        </section>

        <!-- FAQ Section -->
        <section id="faq" v-intersect="{ threshold: 0.08, rootMargin: '0px 0px -40% 0px' }" class="mb-16">
            <div class="text-center mb-8">
                <h2
                    class="text-[32px] md:text-[48px] lg:text-[64px] font-medium text-gray-900 dark:text-white mt-3"
                    v-html="$t('faq.title')"
                ></h2>
            </div>
            <FAQSection />
        </section>

        <!-- Subscribe Section -->
        <section id="subscribe" v-intersect="{ threshold: 0.08, rootMargin: '0px 0px -40% 0px' }" class="mb-16">
            <SubscribeSection />
        </section>
    </div>
</template>

<script setup lang="ts">
import AboutSection from '../components/home/AboutSection.vue';
import ArticleSection from '../components/home/ArticleSection.vue';
import ServiceSelector from '../components/home/ServiceSelector.vue';
import HeroSection from '../components/home/HeroSection.vue';
import PromoteSection from '../components/home/PromoteSection.vue';
import StepsSection from '../components/home/StepsSection.vue';
import SubscribeSection from '../components/home/SubscribeSection.vue';
import ReviewSection from '../components/home/ReviewSection.vue';
import SavingsOn from '../components/home/SavingsOn.vue';
import FAQSection from '../components/home/FAQSection.vue';
</script>
