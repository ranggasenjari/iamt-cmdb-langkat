<?php
$host = '192.168.4.6';

// Just echo nothing
$out = shell_exec("echo -n '' 2>/dev/null");
echo "Empty echo: null=" . var_export($out === null, true) . " val=" . json_encode($out) . "\n";

// Hostname (known working)
$out = shell_exec("ssh -o ConnectTimeout=10 root@$host 'hostname' 2>/dev/null");
echo "Hostname: null=" . var_export($out === null, true) . " val=" . json_encode($out) . "\n";

// Qm list with explicit stderr to stdout in the remote command
$out = shell_exec("ssh -o ConnectTimeout=10 root@$host 'qm list 2>&1' 2>/dev/null");
echo "qm+stderr: null=" . var_export($out === null, true) . " val=" . json_encode($out) . "\n";

// Qm list in different style
$out = shell_exec("ssh -o ConnectTimeout=10 root@$host qm list 2>/dev/null");
echo "qm noq: null=" . var_export($out === null, true) . " val=" . json_encode($out) . "\n";
