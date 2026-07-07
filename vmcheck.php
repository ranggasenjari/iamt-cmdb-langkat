<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo "VM total: " . App\Models\VirtualMachine::count() . "\n";
echo "Running: " . App\Models\VirtualMachine::where('status', 'running')->count() . "\n";
echo "Tanpa server: " . App\Models\VirtualMachine::whereNull('server_id')->count() . "\n";
