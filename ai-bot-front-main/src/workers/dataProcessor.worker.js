// Web Worker для обработки больших массивов данных
// Используется для фильтрации, сортировки и других тяжелых операций

self.addEventListener('message', (e) => {
    const { type, data } = e.data;
    
    try {
        let result;
        
        switch (type) {
            case 'FILTER_SERVICES':
                result = filterServices(data.services, data.filter);
                break;
            case 'SORT_ARTICLES':
                result = sortArticles(data.articles, data.sortBy);
                break;
            case 'PROCESS_LARGE_ARRAY':
                result = processLargeArray(data.array, data.chunkSize);
                break;
            default:
                throw new Error(`Unknown worker task: ${type}`);
        }
        
        self.postMessage({ success: true, result });
    } catch (error) {
        self.postMessage({ success: false, error: error.message });
    }
});

function filterServices(services, filter) {
    if (!filter || !filter.trim()) {
        return services;
    }
    
    const lowerFilter = filter.toLowerCase();
    return services.filter(service => {
        const name = (service.translations?.en?.name || service.name || '').toLowerCase();
        const description = (service.translations?.en?.description || service.description || '').toLowerCase();
        return name.includes(lowerFilter) || description.includes(lowerFilter);
    });
}

function sortArticles(articles, sortBy) {
    const sorted = [...articles];
    
    switch (sortBy) {
        case 'date':
            sorted.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            break;
        case 'title':
            sorted.sort((a, b) => {
                const titleA = (a.title || '').toLowerCase();
                const titleB = (b.title || '').toLowerCase();
                return titleA.localeCompare(titleB);
            });
            break;
        default:
            // Без сортировки
            break;
    }
    
    return sorted;
}

function processLargeArray(array, chunkSize = 100) {
    const chunks = [];
    for (let i = 0; i < array.length; i += chunkSize) {
        chunks.push(array.slice(i, i + chunkSize));
    }
    return chunks;
}

