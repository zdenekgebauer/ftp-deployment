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
| `remote: mkdir\|rmdir\|unlink\|mv\|chmod` bere relativní cestu vůči `remote`, absolutní ponechá (upstream od `34243a3` dělá z `x` vždy `/x`) | `src/Deployment/JobRunner.php`, `tests/JobRunner.remote.phpt` | PR [#181](https://github.com/dg/ftp-deployment/pull/181) (větev `fix-remote-job-paths`), issue [#180](https://github.com/dg/ftp-deployment/issues/180) |
| CI: oprava matice PHP v `tests.yml` (testy v upstreamu od `53d1e08` neběží), test přeskočených operací nezávislý na pořadí souborů, klíč cache preprocesoru opět obsahuje název filtru (phpstan) | `.github/workflows/tests.yml`, `tests/Deployer.interrupt.phpt`, `src/Deployment/Deployer.php` | PR [#182](https://github.com/dg/ftp-deployment/pull/182) (větev `fix-tests-workflow`) |
| `Helpers::fetchUrl()` posílá User-Agent i v záložní větvi bez ext-curl (WEDOS.protection jinak vrací 401) | `src/Deployment/Helpers.php` | PR [#183](https://github.com/dg/ftp-deployment/pull/183) (větev `fetchurl-user-agent`) |
