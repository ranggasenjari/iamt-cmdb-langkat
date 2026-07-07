<?php
$host = '192.168.4.6';
// Capture both output and exit code
$cmd = "ssh -o ConnectTimeout=10 root@$host 'qm list' 2>/dev/null; echo EXITCODE:\$?";
$out = shell_exec($cmd);
echo "null=" . var_export($out === null, true) . "\n";
echo "out=" . json_encode($out) . "\n";

// Try with explicit bash
$cmd2 = "bash -c 'ssh -o ConnectTimeout=10 root@$host \"qm list\" 2>/dev/null; echo EXITCODE:\$?'";
$out2 = shell_exec($cmd2);
echo "null2=" . var_export($out2 === null, true) . "\n";
echo "out2=" . json_encode($out2) . "\n";
