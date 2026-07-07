<?php
$tests = [
    "ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=accept-new root@192.168.4.6 'qm list' 2>&1",
    "ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=accept-new root@192.168.4.6 'ls /etc/pve/nodes/node6/qemu-server/' 2>&1",
    "ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=accept-new root@192.168.4.6 'qm list' </dev/null 2>&1",
];
foreach ($tests as $i => $cmd) {
    $out = shell_exec($cmd);
    echo "Test $i: null=" . var_export($out === null, true) . " out=" . var_export($out, true) . "\n";
    echo "---\n";
}
