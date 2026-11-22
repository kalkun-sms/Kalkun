#!/bin/bash

set -e

SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
TESTSDIR="$(realpath "${SCRIPT_DIR}/../tests/")"

USERNAME="$(php -f "${TESTSDIR}/testutils/DBVars.php" -- username)"
PASSWORD="$(php -f "${TESTSDIR}/testutils/DBVars.php" -- password)"

function is_mysql_native_password_disabled() {
  MYSQLD_VERSION=$(mysqld --version | cut -f 4 -d " ")
  sudo mysql -u root -proot -e "SHOW PLUGINS;" > /tmp/mysql-plugins
  grep mysql_native_password /tmp/mysql-plugins | grep -q DISABLED
}

# As per https://github.com/actions/runner-images/blob/releases/ubuntu24/20241215/images/ubuntu/Ubuntu2404-Readme.md
if ! systemctl is-active --quiet mysql.service; then
  echo "Starting mysql.service"
  sudo systemctl start mysql.service
fi

# Create user
echo "Creating user for mysql"
if [ -n "$CI" ]; then
  echo "Printing mysqld version"
  mysqld --version
  # This in on github CI
  if [ "$(php -r "echo version_compare(phpversion(), '7.4', '>=');")" = "1" ]; then
    sudo /usr/bin/mysql -u root -proot \
      -e "CREATE USER IF NOT EXISTS '$USERNAME'@'localhost' IDENTIFIED BY '$PASSWORD';
GRANT ALL ON *.* TO '$USERNAME'@'localhost' WITH GRANT OPTION;"
  else
    # For PHP version < 7.4, special treatment is required, otherwise mysqli driver reports:
    # mysqli::real_connect(): The server requested authentication method unknown to the client [caching_sha2_password]
    # See: https://stackoverflow.com/questions/50026939
    if [ "$(php -r "echo version_compare(phpversion(), '7.1', '<');")" = "1" ] || is_mysql_native_password_disabled; then
      # See: https://stackoverflow.com/questions/50026939
      # See: https://dev.mysql.com/blog-archive/upgrading-to-mysql-8-0-default-authentication-plugin-considerations/
      MYSQLD_VERSION=$(mysqld --version | cut -f 4 -d " ")
      sudo sh -c 'echo "[mysqld]" >> /etc/mysql/conf.d/authentication.cnf'
      if mysqld --version | grep -ivq MariaDB && dpkg --compare-versions "$MYSQLD_VERSION" gt 8.4~ ; then
        # Since Mysql 8.4, parameter changed from default-authentication-plugin to mysql_native_password + authentication_policy
        sudo sh -c 'echo "mysql_native_password=ON" >> /etc/mysql/conf.d/authentication.cnf'
        sudo sh -c 'echo "authentication_policy=*:mysql_native_password" >> /etc/mysql/conf.d/authentication.cnf'
      else
        sudo sh -c 'echo "default-authentication-plugin=mysql_native_password" >> /etc/mysql/conf.d/authentication.cnf'
      fi
      echo "Printing /etc/mysql/conf.d/authentication.cnf"
      cat /etc/mysql/conf.d/authentication.cnf
      set +e
      sudo systemctl restart mysql.service
      RESPONSE="$?"
      set -e
      if [ $RESPONSE -ne 0 ]; then
        sudo systemctl status mysql.service || true
        sudo journalctl -xu mysql.service || true
      fi
    fi
    sudo /usr/bin/mysql -u root -proot -e "CREATE USER IF NOT EXISTS '$USERNAME'@'localhost' IDENTIFIED WITH mysql_native_password BY '$PASSWORD';
GRANT ALL ON *.* TO '$USERNAME'@'localhost' WITH GRANT OPTION;"
  fi
else
  # This in on local system
  sudo /usr/bin/mysql -e "GRANT ALL ON *.* TO '$USERNAME'@'localhost' IDENTIFIED BY '$PASSWORD' WITH GRANT OPTION"
fi

# As per https://github.com/actions/runner-images/blob/releases/ubuntu24/20241215/images/ubuntu/Ubuntu2404-Readme.md
if ! systemctl is-active --quiet postgresql.service; then
  echo "Starting postgresql.service"
  sudo systemctl start postgresql.service
fi

# Create user - We need to set --createdb because the user will create and drop databases.
echo "Creating user for postgresql"
sudo -u postgres createuser --createdb --no-createrole --no-superuser "$USERNAME"

# Set password
echo "Setting password for postgresql"
sudo -u postgres psql -c "ALTER USER $USERNAME PASSWORD '$PASSWORD';"
