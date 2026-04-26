<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$tenants = App\Models\Tenant::with('domains')->get();
foreach ($tenants as $t) {
    $domain = $t->domains->first() ? $t->domains->first()->domain : 'no domain';
    echo $t->id . ' | ' . $t->school_name . ' | http://' . $domain . PHP_EOL;
}

echo PHP_EOL . "--- USERS (first 5) ---" . PHP_EOL;
// Get users from central DB
$users = App\Models\User::take(5)->get(['id','name','email','role','is_admin']);
foreach ($users as $u) {
    echo $u->id . ' | ' . $u->email . ' | role: ' . $u->role . ' | is_admin: ' . $u->is_admin . PHP_EOL;
}
