# FTPS (explicit TLS) with a self-signed certificate, see vsftpd-ftps.conf
FROM delfer/alpine-ftp-server:latest
RUN apk add --no-cache openssl iproute2 \
	&& openssl req -x509 -newkey rsa:2048 -nodes -days 3650 -subj /CN=localhost \
		-keyout /etc/ssl/private/vsftpd.key -out /etc/ssl/certs/vsftpd.crt
