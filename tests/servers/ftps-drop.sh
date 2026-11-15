#!/bin/sh
# Deploys over ftps:// and resets its connections when the log reaches a pattern, like a hosting firewall or
# connection limit. The deployment must recover by retrying, with no PHP warning turned fatal, also at shutdown.
# Patterns cover createDir() during the upload and renameFile(); both lack a reconnect of their own,
# so the failure goes through RetryServer. Usage: ./ftps-drop.sh [pattern ...]
set -eu
cd "$(dirname "$0")"
files=${FILES:-200}
[ $# -gt 0 ] || set -- "of $((files * 2))\) /d2[0-9]/" '^Renaming:' "of $((files + 1))\) Renaming /d5"

rm -rf data/ftps-local
for i in $(seq 1 "$files"); do
	mkdir -p "data/ftps-local/d$i"
	head -c 1000 /dev/urandom > "data/ftps-local/d$i/f.bin"
done

failed=0
for dropOn in "$@"; do
	docker compose exec -T ftps sh -c 'rm -rf /home/deploy/site && install -d -o deploy /home/deploy/site'

	# exception_ignore_args=0 as on development PHP setups, where traces keep the connection alive
	: > ftps-drop.log
	php -d zend.exception_ignore_args=0 -d display_errors=stderr -d log_errors=1 -d error_log="$PWD/ftps-drop.log" ../../deployment ftps.ini --no-progress > ftps-drop.log 2>&1 &
	pid=$!
	while kill -0 "$pid" 2> /dev/null && ! grep -q -E "$dropOn" ftps-drop.log; do
		sleep 0.02
	done
	reset=$(docker compose exec -T ftps ss -K -t -H state established '( sport = :21 or sport >= :21110 )' | wc -l)
	status=0
	wait "$pid" || status=$?

	uploaded=$(docker compose exec -T ftps find /home/deploy/site -name '*.bin' | wc -l)
	if [ "$reset" -eq 0 ]; then
		result="INCONCLUSIVE: no connection to reset"
	elif [ "$status" -ne 0 ]; then
		result="FAILED: exit code $status"
	elif grep -q -E 'Warning|Fatal' ftps-drop.log; then
		result="FAILED: PHP warning or fatal error"
	elif [ "$uploaded" -ne "$files" ]; then
		result="FAILED: $uploaded of $files files on the server"
	else
		result="OK"
	fi
	[ "$result" = OK ] || { failed=1; cp ftps-drop.log "ftps-drop.$(echo "$dropOn" | tr -c 'a-zA-Z0-9' _).log"; }
	echo "[$dropOn] $result, $reset connections reset"
	grep -E '^Error|Warning|Fatal' ftps-drop.log | cut -c1-200 | sort -u | sed 's/^/    /'
done

# a deployment does not show warnings raised while PHP shuts down, so the freeing is checked separately
php ftps-reset.php > ftps-drop.log 2>&1 &
pid=$!
while kill -0 "$pid" 2> /dev/null && ! grep -q connected ftps-drop.log; do
	sleep 0.02
done
docker compose exec -T ftps ss -K -t -H state established '( sport = :21 )' > /dev/null
status=0
wait "$pid" || status=$?
if [ "$status" -eq 0 ] && grep -q retried ftps-drop.log && ! grep -q -E 'Warning|Fatal' ftps-drop.log; then
	echo "[ftps-reset.php] OK"
else
	failed=1
	echo "[ftps-reset.php] FAILED: exit code $status"
	grep -E 'Warning|Fatal|Error' ftps-drop.log | cut -c1-200 | sed 's/^/    /'
fi
exit $failed
