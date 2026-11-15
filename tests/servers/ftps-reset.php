<?php declare(strict_types=1);

// Run by ftps-drop.sh, which resets the connection during the sleep. The retry must succeed and the dropped
// connection must not warn 'SSL_shutdown failed' when PHP frees it, which the error handler (as in CliRunner)
// would turn into a fatal error at shutdown.

require __DIR__ . '/../../vendor/autoload.php';

set_error_handler(function (int $severity, string $message, string $file, int $line): never {
	throw new ErrorException($message, 0, $severity, $file, $line);
});

$server = new Deployment\RetryServer(
	new Deployment\FtpServer('ftps://deploy:deploy@127.0.0.1:2990/site'),
	new Deployment\Logger('php://memory'),
);
$server->connect();
echo "connected\n";
sleep(2);
$server->createDir('/reset-' . uniqid());
echo "retried\n";
