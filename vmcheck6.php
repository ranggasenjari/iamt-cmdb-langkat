<?php
$tests = [
    "ssh -tt -o ConnectTimeout=10 -o StrictHostKeyChecking=accept-new root@192.168.4.6 'qm list' 2>&1",
    "ssh -T -o ConnectTimeout=10 -o StrictHostKeyChecking=accept-new root@192.168.4.6 'qm list' 2>&1",
    "ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=accept-new root@192.168.4.6 'cat /etc/pve/nodes/node6/qemu-server/*.conf' 2>&1",
];
foreach ($tests as $i => $cmd) {
    echo "Test $i:\n";
    $out = shell_exec($cmd);
    echo "  null=" . var_export($out === null, true) . " out=" . var_export($out, true) . "\n";
}
