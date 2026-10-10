# SFTP refuses mkdir and chmod while the shell allows them, like some hostings
FROM alpine:3.20
RUN apk add --no-cache openssh \
	&& adduser -D -u 1000 deploy \
	&& echo 'deploy:deploy' | chpasswd \
	&& mkdir -p /home/deploy/www \
	&& sed -i 's/^#\?PasswordAuthentication.*/PasswordAuthentication yes/; s/^Subsystem.*/Subsystem sftp internal-sftp -P setstat,fsetstat,mkdir/' /etc/ssh/sshd_config
EXPOSE 22
CMD ["sh", "-c", "ssh-keygen -A && exec /usr/sbin/sshd -D -e"]
