# Fork zdenekgebauer/ftp-deployment

Fork [dg/ftp-deployment](https://github.com/dg/ftp-deployment). Dokumentace upstreamu: `AGENTS.md`, `docs/internals.md`, `readme.md`.

## Větve

- `master` = `upstream/master` + vlastní úpravy níže; z něj se builduje phar
- PR větve vždy od `upstream/master`, bez souborů jen pro fork (`FORK.md`, `tests/servers/`)

## Odchylky od upstreamu

| úprava | soubory | stav upstreamu |
|---|---|---|
| testovací FTP/SFTP servery v Dockeru | `tests/servers/` | jen ve forku |
| build pharu přes [Box](https://github.com/box-project/box) (`box` a `composer` v PATH): `./build-phar.sh [out.phar]` | `box.json`, `build-phar.sh`, `.gitignore` | upstream build skript nemá (`AGENTS.md` odkazuje na neexistující `create-phar/`) |
| `remote: mkdir\|rmdir\|unlink\|mv\|chmod` bere relativní cestu vůči `remote`, absolutní ponechá (upstream od `34243a3` dělá z `x` vždy `/x`) | `src/Deployment/JobRunner.php`, `tests/JobRunner.remote.phpt` | větev `fix-remote-job-paths`, PR k issue [#180](https://github.com/dg/ftp-deployment/issues/180) zatím neodeslán |

## Známé chyby upstreamu

| chyba | reprodukce | stav |
|---|---|---|
| `composer phpstan` hlásí 1 chybu v `Deployer.php:481` (`implode` s `callable&list<mixed>`) | `composer phpstan` | v upstreamu, informativní |
