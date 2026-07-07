<?php
$host = '192.168.4.6';
$cmds = [
    "ssh -o ConnectTimeout=10 root@$host 'hostname' 2>/dev/null",
    "ssh -o ConnectTimeout=10 -tt root@$host 'qm list' 2>/dev/null",
    "ssh -o ConnectTimeout=10 root@$host 'qm list' 2>/dev/null",
    "ssh -o ConnectTimeout=10 -tt root@$host 'ls /etc/pve/nodes/node6/qemu-server/' 2>/dev/null",
    "ssh -o ConnectTimeout=10 root@$host 'echo DONE' 2>/dev/null",
];
foreach ($cmds as $i => $cmd) {
    putenv('SSH_ASKPASS=/usr/bin/false');
    $out = shell_exec($cmd);
    $null = $out === null;
    $len = strlen($out ?? '');
    echo "T$i: null=" . ($null ? 'Y' : 'N') . " len=$len out=" . json_encode($out) . "\n";
}
