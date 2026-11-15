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
| `finally[]` joby: běží na konci každého deploye (i neúspěšného nebo bez synchronizace), chyba jednoho nezastaví ostatní ani nepřebije chybu deploye; přeskočí se jen, když deploy odmítne zámek `.running` | `src/Deployment/Deployer.php`, `src/Deployment/CliRunner.php`, `tests/Deployer.finally.phpt`, `readme.md`, `deployment.sample.ini`, `docs/internals.md` | jen ve forku |
| volba `followRedirects` (výchozí `true`) pro `http(s)://` joby; při `false` je přesměrování chybou | `src/Deployment/Helpers.php`, `src/Deployment/JobRunner.php`, `src/Deployment/Deployer.php`, `src/Deployment/CliRunner.php`, `readme.md`, `deployment.sample.ini` | jen ve forku |
| `sftp://` a `phpsec://`: `mkdir` a `chmod` se při odmítnutí SFTP serverem zkusí přes shell | `src/Deployment/SshServer.php`, `src/Deployment/PhpsecServer.php`, `tests/servers/` (`*-restricted.ini`) | jen ve forku |

## Převzato upstreamem

Upstream ve v3.8.0 převzal (po force-pushi `master`, fork je od v3.8.1 přenesený na novou historii):
`remote:` cesty relativní vůči `remote` (PR #181, issue #180 → `3107c08`), test přeskočených operací nezávislý na pořadí
(PR #182 → `d88a380`), klíč cache filtrů (PR #182 → `61053eb`), User-Agent bez ext-curl (PR #183 → `ec14741`);
CI matici PHP opravil sám.
