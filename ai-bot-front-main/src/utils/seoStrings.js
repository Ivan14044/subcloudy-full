// Набор базовых SEO-строк для статичных страниц без SSR.
// Для динамики (статьи/контент) берём заголовки из данных.

const DICT = {
	home: {
		ru: {
			title: 'SubCloudy — Экономия на подписках и сервисах',
			description:
				'Аналитика расходов, рекомендации, автоматизация и удобная оплата подписок.',
			keywords: ['подписки', 'экономия', 'аналитика', 'автоматизация', 'SubCloudy']
		},
		uk: {
			title: 'SubCloudy — Економія на підписках і сервісах',
			description:
				'Аналітика витрат, рекомендації, автоматизація та зручна оплата підписок.',
			keywords: ['підписки', 'економія', 'аналітика', 'автоматизація', 'SubCloudy']
		},
		en: {
			title: 'SubCloudy — Save on subscriptions and services',
			description:
				'Expense analytics, recommendations, automation and easy subscription payments.',
			keywords: ['subscriptions', 'saving', 'analytics', 'automation', 'SubCloudy']
		},
		es: {
			title: 'SubCloudy — Ahorra en suscripciones y servicios',
			description:
				'Analítica de gastos, recomendaciones, automatización y pagos de suscripciones.',
			keywords: ['suscripciones', 'ahorro', 'analítica', 'automatización', 'SubCloudy']
		},
		zh: {
			title: 'SubCloudy — 订阅与服务省钱助手',
			description: '支出分析、推荐、自动化与便捷的订阅支付。',
			keywords: ['订阅', '省钱', '分析', '自动化', 'SubCloudy']
		}
	},
	articles: {
		ru: {
			title: 'Статьи — SubCloudy',
			description: 'Полезные статьи о подписках, экономии и цифровых сервисах.'
		},
		uk: {
			title: 'Статті — SubCloudy',
			description: 'Корисні статті про підписки, економію та цифрові сервіси.'
		},
		en: {
			title: 'Articles — SubCloudy',
			description: 'Useful articles on subscriptions, saving and digital services.'
		},
		es: {
			title: 'Artículos — SubCloudy',
			description: 'Artículos útiles sobre suscripciones, ahorro y servicios digitales.'
		},
		zh: {
			title: '文章 — SubCloudy',
			description: '关于订阅、省钱与数字服务的实用文章。'
		}
	},
	category: {
		ru: { title: 'Категория — SubCloudy', description: 'Статьи по выбранной категории.' },
		uk: { title: 'Категорія — SubCloudy', description: 'Статті за обраною категорією.' },
		en: { title: 'Category — SubCloudy', description: 'Articles by selected category.' },
		es: { title: 'Categoría — SubCloudy', description: 'Artículos por categoría seleccionada.' },
		zh: { title: '分类 — SubCloudy', description: '所选分类下的文章。' }
	}
};

export function getSeoStrings(locale, key) {
	const lang = ['ru', 'uk', 'en', 'es', 'zh'].includes(locale) ? locale : 'ru';
	const dictForKey = DICT[key] || {};
	return dictForKey[lang] || dictForKey['ru'] || { title: 'SubCloudy', description: '' };
}


