# Fork zdenekgebauer/ftp-deployment

Fork [dg/ftp-deployment](https://github.com/dg/ftp-deployment). Dokumentace upstreamu: `AGENTS.md`, `docs/internals.md`, `readme.md`.

## Větve

- `master` = `upstream/master` + vlastní úpravy níže; z něj se builduje phar
- PR větve vždy od `upstream/master`, bez souborů jen pro fork (`FORK.md`, `tests/servers/`)

## Odchylky od upstreamu

| úprava | soubory | stav upstreamu |
|---|---|---|
| testovací FTP/SFTP servery v Dockeru | `tests/servers/` | jen ve forku |

## Známé chyby upstreamu

| chyba | reprodukce | stav |
|---|---|---|
| `JobRunner::remote()` ignoruje `remoteDir`: `remote: mkdir\|rmdir\|unlink\|mv\|chmod x` míří na `/x` místo `<remote>/x` (od `34243a3`) | `tests/servers/*.ini` | issue [#180](https://github.com/dg/ftp-deployment/issues/180), oprava rozpracovaná |
