<?php
// Если это запрос к API, админке или auth - передаем в Laravel
if (strpos($_SERVER['REQUEST_URI'], '/auth/') === 0 || 
    strpos($_SERVER['REQUEST_URI'], '/api/') === 0 || 
    strpos($_SERVER['REQUEST_URI'], '/admin') === 0) {
    
    // Сохраняем оригинальный REQUEST_URI
    $originalUri = $_SERVER['REQUEST_URI'];
    
    // Меняем рабочую директорию на Laravel public
    $laravelPublic = __DIR__.'/../ai-bot-main/public';
    chdir($laravelPublic);
    
    // Устанавливаем правильные переменные для Laravel
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['SCRIPT_FILENAME'] = $laravelPublic.'/index.php';
    $_SERVER['DOCUMENT_ROOT'] = $laravelPublic;
    $_SERVER['REQUEST_URI'] = $originalUri;
    
    // Загружаем Laravel
    require $laravelPublic.'/index.php';
    exit;
}

// Иначе отдаем фронтенд
if (file_exists(__DIR__.'/index.html')) {
    readfile(__DIR__.'/index.html');
    exit;
}

