<?php
$tests = [
    "ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=accept-new root@192.168.4.6 'hostname' 2>&1",
    "ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=accept-new root@192.168.4.6 'echo hello' 2>&1",
    "which ssh 2>&1",
    "echo test",
];
foreach ($tests as $i => $cmd) {
    $out = shell_exec($cmd);
    echo "Test $i: null=" . var_export($out === null, true) . " out=" . var_export($out, true) . "\n";
}
