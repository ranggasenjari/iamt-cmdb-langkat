<?php
$out = shell_exec("ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=accept-new root@192.168.4.6 'qm list' 2>/dev/null");
echo '$out === null: ' . var_export($out === null, true) . "\n";
echo '$out === "": ' . var_export($out === "", true) . "\n";
echo 'length: ' . strlen($out ?? 'NULL') . "\n";
echo "---BEGIN---\n";
echo $out ?? 'NULL';
echo "---END---\n";
