<?php
# nsupdate-web
# Updates BIND records.
#
# (c) Karl-Martin Skontorp <kms@skontorp.net> ~ http://22pf.org/
# Licensed under the GNU GPL 2.0 or later.
#
# 2012-04-04  Vlad Vissoultchev <wqweto@gmail.com>
#   - Supports local and remote DNS servers
#   - Implemented defaults on every parameter
#   - Output mimics http://dyn.com/support/developers/api/return-codes/
#

header("Content-Type: text/plain");

// Config
$nsupdate = '/usr/bin/nsupdate -v';
$dnsServer = '127.0.0.1';
$cacheDir = '/tmp/nsupdate-cache'; // chmod 777 this
$default = array(
    'ip' => $_SERVER['REMOTE_ADDR'],
	'ttl' => 60,
	'key' => 'ddns-key abcdef123456789=',
);

// GET/POST parameters
$config = array(
	'hostname' => $_REQUEST['hostname'],
	'key' => $_REQUEST['key'],
	'ttl' => $_REQUEST['ttl'],
	'ip' => $_REQUEST['ip'],
);

// Handling of missing parameters
$error = 0;
foreach ($config as $k => $v) {
	$error |= empty($v) && empty($default[$k]);
}

if (empty($config['hostname']) && empty($default['hostname'])) {
	echo "nohost\n";
	exit;
}
elseif ($error) {
	echo "911\n";
	echo "Error: Missing parameters with no defaults\n\n";
    print_r($config);
    exit;
}
elseif (isset($_REQUEST['debug'])) {
	print_r($config);
    exit;
}

// Default params
foreach ($config as $k => $v) {
	if (empty($v))
		$config[$k] = $default[$k];
}

// Handle cache of old/current IP address
$cacheFile = $cacheDir . '/' . basename($config['hostname']);

if (is_readable($cacheFile)) {
    $config['old_ip'] = trim(file_get_contents($cacheFile));
}

// Exit now unless IP address is new
if ($config['old_ip'] == $config['ip']) {
	echo 'nochg ' . $config['ip'] . "\n"; // dyndns compatible output
    exit;
}

// nsupdate commands
$nsupdateCommands = 'server ' . $dnsServer . "\n";
$nsupdateCommands .= 'key ' . $config['key'] . "\n";
$nsupdateCommands .= 'update delete ' . $config['hostname'] . "\n";
$nsupdateCommands .= 'update add ' . $config['hostname'] . ' ' . $config['ttl'] . ' A ' . $config['ip'] . "\n";
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

if (is_resource($process)) {
    fwrite($pipes[0], $nsupdateCommands);
    fclose($pipes[0]);

	$prefix = 'STDOUT: ';
    while ($s = fgets($pipes[1], 1024)) {
        $errors .= $prefix . $s;
		$prefix = '';
    }
    fclose($pipes[1]);

	$prefix = 'STDERR: ';
    while ($s = fgets($pipes[2], 1024)) {
        $errors .= $prefix . $s;
		$prefix = '';
    }
    fclose($pipes[2]);

    $returnValue = proc_close($process);

    $errors .= 'RETURN: ' . $returnValue . "\n";
}

// Output errors if unsuccessfull, else update cache
if ($returnValue != 0) {
	echo "dnserr\n" . $errors;
	exit;
}
echo 'good ' . $config['ip'] . "\n";

// Update cache
if (is_writable($cacheFile)
	|| (!file_exists($cacheFile) && is_writeable($cacheDir))) {
	$f = fopen($cacheFile, 'w');
	fwrite($f, $config['ip']);
	fclose($f);
} else {
	echo "Error: Cache not updated!\n";
}

?>
