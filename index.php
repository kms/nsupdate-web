<?php
# nsupdate-web
# Updates BIND records.
#
# (c) Karl-Martin Skontorp <kms@skontorp.net> ~ http://picofarad.org/
# Licensed under the GNU GPL 2.0 or later.

header("Content-Type: text/plain");

// Config
$nsupdate = '/usr/bin/nsupdate -v';
$cacheDir = 'cache';

// GET/POST parameters
$config['hostname'] = $_REQUEST['hostname'];
$config['key'] = $_REQUEST['key'];
$config['ttl'] = $_REQUEST['ttl'];
$config['ip'] = $_REQUEST['ip'];

// Default IP is remote host address
if (empty($config['ip'])) {
    $config['ip'] = $_SERVER['REMOTE_ADDR'];
}

// Handling of missing parameters
$error = false;
$error |= empty($config['hostname']);
$error |= empty($config['key']);
$error |= empty($config['ttl']);
$error |= empty($config['ip']);

if ($_REQUEST['debug'] == 'yes' || $error) {
    echo "nsupdate web-interface\n";
    echo "----------------------\n\n";
    echo "Error: Missing parameters\n\n";
    print_r($config);
    exit;
}

// Handle cache of old/current IP address
$cacheFile = $cacheDir . '/' . basename($config['hostname']);

if (is_readable($cacheFile)) {
    $config['old_ip'] = trim(file_get_contents($cacheFile));
}

// Exit now unless IP address is new
if ($config['old_ip'] == $config['ip']) {
    exit;
}

// nsupdate commands
$nsupdateCommands =  'key ' . $config['hostname'] 
    . ' ' . $config['key'] .  "\n";
$nsupdateCommands .= 'update delete ' . $config['hostname'] . "\n";
$nsupdateCommands .= 'update add ' . $config['hostname'] . ' ' 
    .  $config['ttl'] . ' A ' . $config['ip'] . "\n";
$nsupdateCommands .= "send\n\n";

// Prepare to execute nsupdate binary
$descriptors = array(
    0 => array("pipe", "r"),
    1 => array("pipe", "w"),
    2 => array("pipe", "w")
);
$returnValue = 127;
$errors = '';

// Execute nsupdate and print status info
$process = proc_open($nsupdate, $descriptors, $pipes);

echo "nsupdate web-interface\n";
echo "----------------------\n\n";
echo date("D M j G:i:s T Y") . "\n\n";
echo "Previous IP: " . $config['old_ip'] . "\n";
echo "New IP:      " . $config['ip'] . "\n";

if (is_resource($process)) {
    fwrite($pipes[0], $nsupdateCommands);
    fclose($pipes[0]);

    while ($s = fgets($pipes[1], 1024)) {
	$errors .= 'STDOUT: ' . $s;
    }
    fclose($pipes[1]);

    while ($s = fgets($pipes[2], 1024)) {
	$errors .= 'STDERR: ' . $s;
    }
    fclose($pipes[2]);

    $returnValue = proc_close($process);

    $errors .= 'RETURN: ' . $returnValue . "\n";
}

// Output errors if unsuccessfull, else update cache
if ($returnValue != 0) {
    echo "\nError: DNS not updated!\n\n";
    echo $errors;
} else {
    echo "\nDNS updated!\n";

    // Update cache
    if (is_writable($cacheFile)
	|| (!file_exists($cacheFile) && is_writeable($cacheDir))) {
	$f = fopen($cacheFile, 'w');
	fwrite($f, $config['ip']);
	fclose($f);
	echo "Cache updated!\n";
    } else {
	echo "Error: Cache not updated!\n";
    }
}

?>
