// Лёгкая утилита для управления тегами SEO без сторонних зависимостей
// Обновляет <title>, <meta name="description">, canonical, OpenGraph, Twitter Cards и JSON-LD
// Вызовы идемпотентны: теги создаются один раз и далее переиспользуются

function ensureTag(tagName, attrs) {
	const head = document.head || document.getElementsByTagName('head')[0];
	let selector = tagName;
	for (const [key, value] of Object.entries(attrs)) {
		selector += `[${key}="${value}"]`;
	}
	let el = head.querySelector(selector);
	if (!el) {
		el = document.createElement(tagName);
		for (const [key, value] of Object.entries(attrs)) {
			el.setAttribute(key, value);
		}
		head.appendChild(el);
	}
	return el;
}

function setTitle(title) {
	if (typeof title === 'string' && title.trim().length > 0) {
		document.title = title.trim();
	}
}

function setMetaName(name, content) {
	if (!content) return;
	const el = ensureTag('meta', { name });
	el.setAttribute('content', String(content));
}

function setMetaProperty(property, content) {
	if (!content) return;
	const el = ensureTag('meta', { property });
	el.setAttribute('content', String(content));
}

function setLinkRel(rel, href) {
	if (!href) return;
	const el = ensureTag('link', { rel });
	el.setAttribute('href', String(href));
}

function stripHtml(html) {
	if (!html) return '';
	const tmp = document.createElement('div');
	tmp.innerHTML = html;
	return (tmp.textContent || tmp.innerText || '').replace(/\s+/g, ' ').trim();
}

function buildAbsoluteUrl(pathOrUrl) {
	try {
		const test = new URL(pathOrUrl);
		return test.toString();
	} catch {
		const base = window.location.origin.replace(/\/$/, '');
		const path = String(pathOrUrl || '').startsWith('/') ? pathOrUrl : `/${pathOrUrl || ''}`;
		return `${base}${path}`;
	}
}

export function updateSeo({
	title,
	description,
	keywords,
	image,
	canonical,
	type = 'website',
	locale,
	siteName = 'SubCloudy',
	jsonLd = null,
	hreflangs = null
} = {}) {
	if (title) setTitle(title);
	if (description) setMetaName('description', description);
	if (keywords) setMetaName('keywords', Array.isArray(keywords) ? keywords.join(', ') : String(keywords));

	const canonicalUrl = buildAbsoluteUrl(canonical || window.location.pathname + window.location.search);
	setLinkRel('canonical', canonicalUrl);

	setMetaProperty('og:type', type);
	setMetaProperty('og:title', title || document.title);
	if (description) setMetaProperty('og:description', description);
	setMetaProperty('og:url', canonicalUrl);
	setMetaProperty('og:site_name', siteName);
	if (image) setMetaProperty('og:image', buildAbsoluteUrl(image));
	if (locale) setMetaProperty('og:locale', locale);

	setMetaName('twitter:card', image ? 'summary_large_image' : 'summary');
	if (title) setMetaName('twitter:title', title);
	if (description) setMetaName('twitter:description', description);
	if (image) setMetaName('twitter:image', buildAbsoluteUrl(image));

	const existing = document.getElementById('seo-jsonld');
	if (existing) existing.remove();
	if (jsonLd) {
		const script = document.createElement('script');
		script.type = 'application/ld+json';
		script.id = 'seo-jsonld';
		script.text = JSON.stringify(jsonLd);
		(document.head || document.documentElement).appendChild(script);
	}

	[...document.querySelectorAll('link[rel=\"alternate\"][hreflang]')].forEach(n => n.remove());
	if (Array.isArray(hreflangs) && hreflangs.length) {
		for (const alt of hreflangs) {
			if (!alt?.href || !alt?.lang) continue;
			const link = document.createElement('link');
			link.setAttribute('rel', 'alternate');
			link.setAttribute('hreflang', alt.lang);
			link.setAttribute('href', buildAbsoluteUrl(alt.href));
			(document.head || document.documentElement).appendChild(link);
		}
	}
}

export function updateWebPageSEO({ title, description, keywords, canonical, image, locale, hreflangs } = {}) {
	updateSeo({
		title,
		description,
		keywords,
		canonical,
		image,
		locale,
		type: 'website',
		siteName: 'SubCloudy',
		jsonLd: {
			'@context': 'https://schema.org',
			'@type': 'WebPage',
			name: title || 'SubCloudy',
			description: description || '',
			url: buildAbsoluteUrl(canonical || window.location.pathname)
		},
		hreflangs
	});
}

export function updateArticleSEO({ title, description, image, canonical, locale, datePublished, dateModified, authorName = 'SubCloudy', breadcrumbs = null } = {}) {
	const cleanDescription = description ? stripHtml(description) : '';
	const articleJson = {
		'@context': 'https://schema.org',
		'@type': 'Article',
		headline: title || '',
		image: image ? [buildAbsoluteUrl(image)] : undefined,
		datePublished: datePublished || undefined,
		dateModified: dateModified || datePublished || undefined,
		author: authorName ? { '@type': 'Person', name: authorName } : undefined,
		mainEntityOfPage: {
			'@type': 'WebPage',
			'@id': buildAbsoluteUrl(canonical || window.location.pathname)
		},
		description: cleanDescription
	};

	let jsonLd = articleJson;
	if (Array.isArray(breadcrumbs) && breadcrumbs.length) {
		jsonLd = [
			articleJson,
			{
				'@context': 'https://schema.org',
				'@type': 'BreadcrumbList',
				itemListElement: breadcrumbs.map((b, idx) => ({
					'@type': 'ListItem',
					position: idx + 1,
					name: b.name,
					item: buildAbsoluteUrl(b.href)
				}))
			}
		];
	}

	updateSeo({
		title,
		description: cleanDescription,
		canonical,
		image,
		locale,
		type: 'article',
		siteName: 'SubCloudy',
		jsonLd
	});
}


