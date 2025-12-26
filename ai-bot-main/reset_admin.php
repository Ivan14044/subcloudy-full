<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::where('email', 'admin@mail.com')->first();
if ($user) {
    $user->password = Hash::make('admin123456');
    $user->save();
    echo "Password for admin@mail.com updated to admin123456\n";
} else {
    echo "Admin not found\n";
}

