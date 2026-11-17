# Test FTP/SFTP servers

Local servers for running real deployments, e.g. to check `remote:` jobs against a `remote` with a subdirectory.

```bash
docker compose up -d
mkdir -p data/ftp/site data/sftp/site data/sftp-restricted/site
php ../../deployment ftp.ini        # ftp://deploy:deploy@127.0.0.1:2121/site (vsftpd, chroot to user home)
php ../../deployment sftp.ini       # sftp:// (ext-ssh2), same server as phpsec.ini
php ../../deployment phpsec.ini     # phpsec://deploy:deploy@127.0.0.1:2222/config/www/site (OpenSSH, no chroot, shell access)
php ../../deployment sftp-restricted.ini    # 127.0.0.1:2223, SFTP refuses mkdir/chmod (internal-sftp -P), shell allows them
php ../../deployment phpsec-restricted.ini  # the same server via phpseclib
php shell-fallback.php              # the shell fallback on sftp-restricted gets paths with % ! $ ' " ` intact
./ftps-drop.sh     # ftps://deploy:deploy@127.0.0.1:2990/site, resets the connections mid-deploy, must recover
php rename-retry.php [url]          # renameFile() retried after a lost reply keeps the file (ftp:// by default, or the ftps:// url)
docker compose down
```

Server content is bind-mounted to `data/ftp` (= FTP root), `data/sftp` (= `/config/www`), `data/sftp-restricted` (= `/home/deploy/www`) and `data/ftps` (= FTPS root).
`sftp://` needs `ext-ssh2` (WSL: `sudo apt-get install php8.5-ssh2`); `phpsec://` uses the bundled phpseclib.
FTP passive ports are 21100–21109, FTPS 21110–21119 (30000+ are taken on the Windows host).

`ftps-drop.sh` deploys generated files from `data/ftps-local` and resets the TCP connections with `ss -K`
(the container has `NET_ADMIN`) when the log reaches a pattern: during `createDir()` in the upload and during renaming.
A reset, unlike killing the session process, makes the client's TLS `close_notify` fail, as on hostings that drop
connections. It also runs `ftps-reset.php`, which checks that the dropped connection does not warn
`SSL_shutdown failed` at PHP shutdown; a deployment does not show warnings from that phase.
Custom patterns: `./ftps-drop.sh '^Renaming:'`; failed runs keep their log as `ftps-drop.<pattern>.log`.
