<?php declare(strict_types=1);

// mkdir and chmod refused by SFTP fall back to the shell, which must get paths with shell-special characters intact.
// Needs the sftp-restricted server from compose.yaml; checks the result in its bind mount data/sftp-restricted.
// Usage: php shell-fallback.php

require __DIR__ . '/../../vendor/autoload.php';

use Deployment\{PhpsecServer, SshServer};

$remoteRoot = '/home/deploy/www';
$localRoot = __DIR__ . '/data/sftp-restricted';
$local = tempnam(sys_get_temp_dir(), 'sf');
file_put_contents($local, 'content');
$failed = false;


function check(string $name, bool $ok, bool &$failed): void
{
	echo ($ok ? 'OK    ' : 'FAILED') . " $name\n";
	$failed = $failed || !$ok;
}


$servers = ['phpsec' => fn() => new PhpsecServer('phpsec://deploy:deploy@127.0.0.1:2223')];
if (extension_loaded('ssh2')) {
	$servers['sftp'] = fn() => new SshServer('sftp://deploy:deploy@127.0.0.1:2223');
}

foreach ($servers as $scheme => $factory) {
	$server = $factory();
	$server->dirPermissions = 0o750;
	$server->filePermissions = 0o640;
	$server->connect();

	$base = 'shell-fallback-' . uniqid();
	$dir = "$base/50% off!/a\$b'c \"d\" `e`";
	try {
		$server->createDir("$remoteRoot/$dir");
		$server->writeFile($local, "$remoteRoot/$dir/f.txt");
		$server->chmod("$remoteRoot/$dir/f.txt", 0o604);
	} catch (Throwable $e) {
		check("$scheme: {$e->getMessage()}", false, $failed);
	}

	clearstatcache();
	check("$scheme: directory created under the exact name", is_dir("$localRoot/$dir"), $failed);
	check("$scheme: nothing else created", scandir("$localRoot/$base") === ['.', '..', '50% off!'], $failed);
	check("$scheme: directory permissions", is_dir("$localRoot/$dir") && (fileperms("$localRoot/$dir") & 0o777) === 0o750, $failed);
	check("$scheme: file permissions", is_file("$localRoot/$dir/f.txt") && (fileperms("$localRoot/$dir/f.txt") & 0o777) === 0o604, $failed);

	exec('rm -rf ' . escapeshellarg("$localRoot/$base"));
}

unlink($local);
exit($failed ? 1 : 0);
