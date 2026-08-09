<?php
require '/home/iamt/public_html/vendor/autoload.php';
$app = require_once '/home/iamt/public_html/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Server records ===\n";
foreach (App\Models\Server::select('id', 'nama', 'status')->get() as $s) {
    echo $s->id . ' | ' . $s->nama . ' | ' . $s->status . "\n";
}

echo "\n=== VM count per server ===\n";
foreach (App\Models\VirtualMachine::with('server:id,nama')->get() as $vm) {
    echo ($vm->server?->nama ?? 'NULL') . ' | ' . $vm->nama . " | " . $vm->status . "\n";
}