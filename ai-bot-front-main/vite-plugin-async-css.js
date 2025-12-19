// Vite plugin для асинхронной загрузки CSS
export default function asyncCssPlugin() {
    return {
        name: 'async-css',
        transformIndexHtml(html) {
            // Находим все ссылки на CSS файлы
            const cssLinkRegex = /<link[^>]+rel=["']stylesheet["'][^>]*>/gi;
            const loadCssPolyfill = `
    <script>
    !function(e){"use strict";var t=function(t,n,r){var o,i=e.document,a=i.createElement("link");if(n)o=n;else{var l=(i.body||i.getElementsByTagName("head")[0]).childNodes;o=l[l.length-1]}var d=i.styleSheets;a.rel="stylesheet",a.href=t,a.media="only x",function e(t){if(i.body)return t();setTimeout(function(){e(t)})}(function(){o.parentNode.insertBefore(a,n?o:o.nextSibling)});var f=function(e){for(var t=a.href,n=d.length;n--;)if(d[n].href===t)return e();setTimeout(function(){f(e)})};return a.addEventListener&&a.addEventListener("load",function(){this.media=r||"all"}),a.onloadcssdefined=f,f(function(){a.media!==r&&(a.media=r||"all")}),a};"undefined"!=typeof exports?exports.loadCSS=t:e.loadCSS=t}("undefined"!=typeof global?global:this);
    </script>`;

            // Заменяем обычные CSS ссылки на preload с асинхронной загрузкой
            html = html.replace(cssLinkRegex, (match) => {
                // Пропускаем критические CSS (уже встроены)
                if (match.includes('critical') || match.includes('materialdesignicons')) {
                    return match;
                }

                // Извлекаем href
                const hrefMatch = match.match(/href=["']([^"']+)["']/);
                if (!hrefMatch) return match;

                const href = hrefMatch[1];
                
                // Создаем preload с асинхронной загрузкой
                return `
    <link rel="preload" href="${href}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="${href}"></noscript>`;
            });

            // Убеждаемся, что polyfill добавлен только один раз
            if (!html.includes('loadCSS')) {
                html = html.replace('</head>', `${loadCssPolyfill}\n    </head>`);
            }

            return html;
        }
    };
}

