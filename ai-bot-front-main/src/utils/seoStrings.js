// Набор базовых SEO-строк для статичных страниц без SSR.
// Для динамики (статьи/контент) берём заголовки из данных.

const DICT = {
	home: {
		ru: {
			title: 'SubCloudy — Экономия на подписках и AI-сервисах (ChatGPT, Midjourney)',
			description:
				'Помогаем экономить на зарубежных подписках. ChatGPT, Midjourney, Claude и другие AI-сервисы с удобной оплатой и аналитикой расходов.',
			keywords: ['оплата ChatGPT', 'подписка Midjourney', 'экономия на подписках', 'AI сервисы', 'SubCloudy']
		},
		uk: {
			title: 'SubCloudy — Економія на підписках і AI-сервісах (ChatGPT, Midjourney)',
			description:
				'Допомагаємо економити на зарубіжних підписках. ChatGPT, Midjourney, Claude та інші AI-сервіси зі зручною оплатою та аналітикою витрат.',
			keywords: ['оплата ChatGPT', 'підписка Midjourney', 'економія на підписках', 'AI сервіси', 'SubCloudy']
		},
		en: {
			title: 'SubCloudy — Save on Subscriptions and AI Services (ChatGPT, Midjourney)',
			description:
				'Save on global subscriptions. ChatGPT, Midjourney, Claude and other AI services with easy payments and expense analytics.',
			keywords: ['ChatGPT subscription', 'Midjourney payment', 'save on subscriptions', 'AI services', 'SubCloudy']
		},
		es: {
			title: 'SubCloudy — Ahorre en Suscripciones y Servicios de IA (ChatGPT, Midjourney)',
			description:
				'Ahorre en suscripciones globales. ChatGPT, Midjourney, Claude и otros servicios de IA con pagos fáciles y analítica de gastos.',
			keywords: ['suscripción ChatGPT', 'pago Midjourney', 'ahorro en suscripciones', 'servicios de IA', 'SubCloudy']
		},
		zh: {
			title: 'SubCloudy — 节省订阅和 AI 服务费用 (ChatGPT, Midjourney)',
			description: '节省全球订阅费用。ChatGPT、Midjourney、Claude 和其他 AI 服务，提供便捷的支付和支出分析。',
			keywords: ['ChatGPT 订阅', 'Midjourney 支付', '节省订阅费用', 'AI 服务', 'SubCloudy']
		}
	},
	articles: {
		ru: {
			title: 'Полезные статьи об AI и экономии на подписках — SubCloudy',
			description: 'Гайды, обзоры и советы по использованию ChatGPT, Midjourney и других нейросетей. Как экономить на цифровых сервисах.'
		},
		uk: {
			title: 'Корисні статті про AI та економію на підписках — SubCloudy',
			description: 'Гайди, огляди та поради щодо використання ChatGPT, Midjourney та інших нейромереж. Как економити на цифрових сервісах.'
		},
		en: {
			title: 'AI Guides and Subscription Saving Tips — SubCloudy',
			description: 'Guides, reviews and tips on using ChatGPT, Midjourney and other AI tools. How to save on digital services.'
		},
		es: {
			title: 'Guías de IA y Consejos para Ahorrar en Suscripciones — SubCloudy',
			description: 'Guías, reseñas и consejos sobre el uso de ChatGPT, Midjourney и otras herramientas de IA. Cómo ahorrar en servicios digitales.'
		},
		zh: {
			title: 'AI 指南和订阅节省技巧 — SubCloudy',
			description: '关于使用 ChatGPT、Midjourney 和其他 AI 工具的指南、评论和技巧。如何节省数字服务费用。'
		}
	},
	category: {
		ru: { title: 'Статьи по категориям — SubCloudy', description: 'Подборки статей по нейросетям, экономии и технологиям.' },
		uk: { title: 'Статті за категоріями — SubCloudy', description: 'Добірки статей з нейромереж, економії та технологій.' },
		en: { title: 'Category Articles — SubCloudy', description: 'Collections of articles on AI, saving, and technology.' },
		es: { title: 'Artículos por Categoría — SubCloudy', description: 'Colecciones de artículos sobre IA, ahorro y tecnología.' },
		zh: { title: '分类文章 — SubCloudy', description: '关于人工智能、节省和技术的文章集。' }
	}
};

export function getSeoStrings(locale, key) {
	const lang = ['ru', 'uk', 'en', 'es', 'zh'].includes(locale) ? locale : 'ru';
	const dictForKey = DICT[key] || {};
	return dictForKey[lang] || dictForKey['ru'] || { title: 'SubCloudy', description: '' };
}
