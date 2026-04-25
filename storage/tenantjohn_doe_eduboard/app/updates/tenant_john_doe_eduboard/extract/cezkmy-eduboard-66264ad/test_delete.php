<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

tenancy()->initialize('john_doe_eduboard');
$permission = \App\Models\TenantPermission::create(['code' => 'test_code', 'label' => 'Test Perm', 'group' => 'Custom']);
echo "Created: " . $permission->code . "\n";
$controller = app(\App\Http\Controllers\Tenant\RoleController::class);
$response = $controller->deleteCustomPermission('test_code');
echo "Response: " . $response->getContent() . "\n";
