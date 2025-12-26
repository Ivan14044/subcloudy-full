<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$user = User::where('email', 'admin@mail.com')->first();
if ($user) {
    $user->is_main_admin = 1;
    $user->save();
    echo "User admin@mail.com is now a main admin\n";
} else {
    echo "Admin not found\n";
}

