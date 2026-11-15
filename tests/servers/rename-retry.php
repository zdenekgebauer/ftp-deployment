<?php declare(strict_types=1);

// FtpServer::renameFile() retried after its reply was lost must not delete the renamed file.
// The lost reply is simulated: a second connection does the rename, then renameFile() sees it as pending.
// Usage: php rename-retry.php [ftp://... or ftps://...], the server from compose.yaml by default.

require __DIR__ . '/../../vendor/autoload.php';

use Deployment\FtpServer;

$url = $argv[1] ?? 'ftp://deploy:deploy@127.0.0.1:2121';
$server = new FtpServer($url);
$server->connect();
$dir = '/rename-retry-' . uniqid();
$server->createDir($dir);
$local = tempnam(sys_get_temp_dir(), 'rr');
$failed = false;


function upload(FtpServer $server, string $local, string $remote, string $content): void
{
	file_put_contents($local, $content);
	$server->writeFile($local, $remote);
}


function content(FtpServer $server, string $local, string $remote): ?string
{
	try {
		$server->readFile($remote, $local);
		return (string) file_get_contents($local);
	} catch (Deployment\ServerException) {
		return null;
	}
}


function check(string $name, bool $ok, bool &$failed): void
{
	echo ($ok ? 'OK    ' : 'FAILED') . " $name\n";
	$failed = $failed || !$ok;
}


// without the property (code before the fix) the retry runs as it used to
$setPending = fn(?array $value) => property_exists($server, 'pendingRename')
	&& (new ReflectionProperty($server, 'pendingRename'))->setValue($server, $value) === null;
$raw = parse_url($url);
$rawConn = ($raw['scheme'] === 'ftps' ? 'ftp_ssl_connect' : 'ftp_connect')($raw['host'], $raw['port'] ?? 21);
ftp_login($rawConn, $raw['user'], $raw['pass']);
ftp_pasv($rawConn, true);

// lost reply to RNTO: the rename took place
upload($server, $local, "$dir/a.tmp", 'new');
upload($server, $local, "$dir/a", 'old');
ftp_delete($rawConn, "$dir/a");
ftp_rename($rawConn, "$dir/a.tmp", "$dir/a");
$setPending(["$dir/a.tmp", "$dir/a"]);
try {
	$server->renameFile("$dir/a.tmp", "$dir/a");
	check('retry after a completed rename keeps the file', content($server, $local, "$dir/a") === 'new', $failed);
} catch (Throwable $e) {
	check("retry after a completed rename keeps the file: {$e->getMessage()}", false, $failed);
}

// lost reply to RNFR: the rename did not take place
upload($server, $local, "$dir/b.tmp", 'new');
upload($server, $local, "$dir/b", 'old');
$setPending(["$dir/b.tmp", "$dir/b"]);
$server->renameFile("$dir/b.tmp", "$dir/b");
check('retry after a rename that did not happen renames', content($server, $local, "$dir/b") === 'new'
	&& content($server, $local, "$dir/b.tmp") === null, $failed);

// no failure before
upload($server, $local, "$dir/c.tmp", 'new');
upload($server, $local, "$dir/c", 'old');
$server->renameFile("$dir/c.tmp", "$dir/c");
check('plain rename overwrites the target', content($server, $local, "$dir/c") === 'new', $failed);

@ftp_close($rawConn); // @ vsftpd closes FTPS without close_notify
$server->purge($dir);
$server->removeDir($dir);
unlink($local);
exit($failed ? 1 : 0);
