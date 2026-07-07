<?php
$cmd = "ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=accept-new root@192.168.4.6 'qm list' 2>&1";
echo "CMD: $cmd\n";
$out = shell_exec($cmd);
echo "null: " . var_export($out === null, true) . "\n";
echo "OUT: " . var_export($out, true) . "\n";
