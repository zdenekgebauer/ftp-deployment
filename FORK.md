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
| release s pharem: tag `v*` → testy, `build-phar.sh`, GitHub Release s `deployment.phar`; ruční spuštění jen uloží phar jako artefakt | `.github/workflows/release.yml` | jen ve forku |
| `finally[]` joby: běží na konci každého deploye (i neúspěšného nebo bez synchronizace), chyba jednoho nezastaví ostatní ani nepřebije chybu deploye; přeskočí se jen, když deploy odmítne zámek `.running` | `src/Deployment/Deployer.php`, `src/Deployment/CliRunner.php`, `tests/Deployer.finally.phpt`, `readme.md`, `deployment.sample.ini`, `docs/internals.md` | PR [#188](https://github.com/dg/ftp-deployment/pull/188) (větev `finally-jobs`) |
| volba `followRedirects` (výchozí `true`) pro `http(s)://` joby; při `false` je přesměrování chybou | `src/Deployment/Helpers.php`, `src/Deployment/JobRunner.php`, `src/Deployment/Deployer.php`, `src/Deployment/CliRunner.php`, `readme.md`, `deployment.sample.ini` | PR [#187](https://github.com/dg/ftp-deployment/pull/187) (větev `http-follow-redirects`) |
| `sftp://` a `phpsec://`: `mkdir` a `chmod` se při odmítnutí SFTP serverem zkusí přes shell | `src/Deployment/SshServer.php`, `src/Deployment/PhpsecServer.php`, `tests/servers/` (`*-restricted.ini`) | PR [#186](https://github.com/dg/ftp-deployment/pull/186) (větev `sftp-shell-fallback`) |
| FTPS: `FtpServer` při reconnectu a v destruktoru staré spojení explicitně zavře (`ftp_close()` s timeoutem 1 s, chyby ignoruje); spojení předané `ftp_*()`, jehož varování `Safe` převedl na výjimku, PHP nikdy neuvolní a jeho pozdější uvolnění po výpadku hlásí `SSL_shutdown failed`, z čehož CLI handler udělá fatal error (Wedos) | `src/Deployment/FtpServer.php`, `tests/servers/` (`ftps-drop.sh`, `ftps-reset.php`) | jen ve forku |
| `FtpServer::renameFile()` po výpadku: když se ztratí odpověď na `RNTO`, opakovaný pokus (`RetryServer`) dřív smazal právě přejmenovaný soubor a selhal na chybějícím dočasném; teď si rozpracované přejmenování pamatuje a při opakování ověří přes `ftp_size()`, jestli už proběhlo | `src/Deployment/FtpServer.php`, `src/Deployment/Safe.php`, `tests/servers/rename-retry.php` | jen ve forku |

## Převzato upstreamem

Upstream ve v3.8.0 převzal (po force-pushi `master`, fork je od v3.8.1 přenesený na novou historii):
`remote:` cesty relativní vůči `remote` (PR #181, issue #180 → `3107c08`), test přeskočených operací nezávislý na pořadí
(PR #182 → `d88a380`), klíč cache filtrů (PR #182 → `61053eb`), User-Agent bez ext-curl (PR #183 → `ec14741`);
CI matici PHP opravil sám.
