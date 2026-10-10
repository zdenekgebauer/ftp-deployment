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
docker compose down
```

Server content is bind-mounted to `data/ftp` (= FTP root), `data/sftp` (= `/config/www`) and `data/sftp-restricted` (= `/home/deploy/www`).
`sftp://` needs `ext-ssh2` (WSL: `sudo apt-get install php8.5-ssh2`); `phpsec://` uses the bundled phpseclib.
FTP passive ports are 21100–21109 (30000+ are taken on the Windows host).
