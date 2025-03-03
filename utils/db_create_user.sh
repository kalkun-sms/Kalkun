#!/bin/bash

set -e

SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
TESTSDIR="$(realpath "${SCRIPT_DIR}/../tests/")"

USERNAME="$(php -f "${TESTSDIR}/testutils/DBVars.php" -- username)"
PASSWORD="$(php -f "${TESTSDIR}/testutils/DBVars.php" -- password)"

# As per https://github.com/actions/runner-images/blob/releases/ubuntu24/20241215/images/ubuntu/Ubuntu2404-Readme.md
if ! systemctl is-active --quiet mysql.service; then
  echo "Starting mysql.service"
  sudo systemctl start mysql.service
fi

# Create user
echo "Creating user for mysql"
if [ -n "$CI" ]; then
  # This in on github CI
  if [ "$(php -r "echo version_compare(phpversion(), '7.4', '>=');")" = "1" ]; then
    sudo /usr/bin/mysql -u root -proot \
      -e "CREATE USER IF NOT EXISTS '$USERNAME'@'localhost' IDENTIFIED BY '$PASSWORD';
GRANT ALL ON *.* TO '$USERNAME'@'localhost' WITH GRANT OPTION;"
  else
    # For PHP version < 7.4, special treatment is required, otherwise mysqli driver reports:
    # mysqli::real_connect(): The server requested authentication method unknown to the client [caching_sha2_password]
    # See: https://stackoverflow.com/questions/50026939
    sudo /usr/bin/mysql -u root -proot -e "CREATE USER IF NOT EXISTS '$USERNAME'@'localhost' IDENTIFIED WITH mysql_native_password BY '$PASSWORD';
GRANT ALL ON *.* TO '$USERNAME'@'localhost' WITH GRANT OPTION;"
    if [ "$(php -r "echo version_compare(phpversion(), '7.1', '<');")" = "1" ]; then
      # See: https://stackoverflow.com/questions/50026939
      # See: https://dev.mysql.com/blog-archive/upgrading-to-mysql-8-0-default-authentication-plugin-considerations/
      sudo sh -c 'echo "[mysqld]" >> /etc/mysql/conf.d/authentication.cnf'
      sudo sh -c 'echo "default-authentication-plugin=mysql_native_password" >> /etc/mysql/conf.d/authentication.cnf'
      sudo systemctl restart mysql.service
    fi
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
