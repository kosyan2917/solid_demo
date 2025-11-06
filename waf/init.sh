#!/bin/sh
service mariadb restart
USER="kokowaf"
PASSWORD="ctf123"

echo "Creating new user ${MYSQL_USER} ..."
mysql -uroot -e "CREATE USER '${USER}'@'localhost' IDENTIFIED BY '${PASSWORD}';"
echo "Granting privileges..."
mysql -uroot -e "GRANT ALL PRIVILEGES ON *.* TO '${USER}'@'localhost';"
mysql -uroot -e "FLUSH PRIVILEGES;"
echo "Done! Permissions granted"
echo "insert into flags(id,flag) values('1', '$FLAG')" >> /init.db;
mysql -u$USER -p$PASSWORD -e "CREATE database kokowaf;"
mysql -u$USER -p$PASSWORD kokowaf  < /init.db
echo "All done."
service mariadb start && service apache2 start && tail -f /dev/null


