#!/usr/bin/env bash

set -e

sudo apt-get update
sudo apt-get install -yqq php7.0-cli php7.0-xml mariadb-server php7.0-mysql

sudo mysql --defaults-file=/etc/mysql/debian.cnf <<EOF
FLUSH PRIVILEGES;
CREATE DATABASE IF NOT EXISTS lafranceinsoumise;
DELETE FROM mysql.user WHERE User = 'lafranceinsoumise';
CREATE USER lafranceinsoumise@localhost IDENTIFIED BY 'lafranceinsoumise';
GRANT ALL PRIVILEGES ON lafranceinsoumise.* TO lafranceinsoumise@localhost;
EOF

cat > /vagrant/.env <<'EOF'
DB_NAME=lafranceinsoumise
DB_USER=lafranceinsoumise
DB_PASSWORD=lafranceinsoumise
DB_HOST=localhost

WP_ENV=development
WP_HOME=http://lafranceinsoumise.local:8000
WP_SITEURL=${WP_HOME}/wp/

AUTH_KEY='mn#8K1PvAj^A}G{Roo?N)i|r%y/Q^=O]]aX}#y78]F$.8jd[8qP3J;,lP!2N.sAC'
SECURE_AUTH_KEY='qA;qCM^q/M)ed016QOu=C>FAOgdGvVJ-L_nRo1-c.[ntxGIqZ#06/37Fh-P@*:gc'
LOGGED_IN_KEY='Y;^Dx|0|K$5}TW|+@R)>]>g{vZ.`8r3$dXFu%vICL)isgxesH=awV73)+F5hvRd{'
NONCE_KEY='SB$=#kSC9kITu7kc9p]>,oyetw+#&QP$}y6zPKo$P<!Plg]2g^ZI@vJ@%MJwW$H='
AUTH_SALT='Ran2O_,^N.|K9,<a#$N4sV.)6VO-IcINM4:L|wDqe-@={};8>3PdDm:;lpH^_^,v'
SECURE_AUTH_SALT='#E+=%a{o+_aPzG;b!Vpox(mZQPo`CUhX(iS}J|X#$?G^Vdvj/h3Tz@#E[o!R&i$6'
LOGGED_IN_SALT='q3VA@$=`.chF!S(:>l>Iw)mX<nc0`FdZ]bgymt5,u^rCjd+5QdzoV/ss/gTL#s/:'
NONCE_SALT='A.xH)`InK,!}R}`]zm{ZIi9|URkOjC5[7zAiQU=ZRgmOWFWO.0Mp]2J{V(SQaOd6'

EOF


sudo bash -c "cat > /etc/systemd/system/php-server.service" <<-EOF
[Unit]
Description=PHP Server
After=syslog.target network.target

[Service]
WorkingDirectory=/vagrant/web/
User=vagrant
Type=simple
ExecStart=/usr/bin/php -S 0.0.0.0:8000
StandardOutput=journal
Restart=on-failure

[Install]
WantedBy=multi-user.target
EOF


sudo systemctl daemon-reload
sudo systemctl enable php-server.service
sudo systemctl start php-server.service
