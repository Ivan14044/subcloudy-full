<?php
$path = '/var/www/subcloudy/backend/ai-bot-main/routes/api.php';
$content = file_get_contents($path);
$old = "Route::get('/ticket', [SupportController::class, 'getOrCreateTicket']);";
$new = "Route::match(['get', 'post'], '/ticket', [SupportController::class, 'getOrCreateTicket']);";
$new_content = str_replace($old, $new, $content);
file_put_contents($path, $new_content);
echo "Updated routes/api.php\n";


