@Echo off

color 2f

set Path=C:\xampp\;C:\xampp\php;C:\xampp\perl\site\bin;C:\xampp\perl\bin;C:\xampp\apache\bin;C:\xampp\mysql\bin;C:\xampp\FileZillaFTP;C:\xampp\MercuryMail;C:\xampp\sendmail;C:\xampp\webalizer;C:\xampp\tomcat\bin;%PATH%
set PHPRC=C:\xampp\php
set PHP_PEAR_BIN_DIR=C:\xampp\php
set PHP_PEAR_CFG_DIR=C:\xampp\php\cfg
set PHP_PEAR_DATA_DIR=C:\xampp\php\data
set PHP_PEAR_DOC_DIR=C:\xampp\php\docs
set PHP_PEAR_INSTALL_DIR=C:\xampp\php\pear
set PHP_PEAR_PHP_BIN=C:\xampp\php\php.exe
set PHP_PEAR_SYSCONF_DIR=C:\xampp\php
set PHP_PEAR_TEST_DIR=C:\xampp\php\tests
set PHP_PEAR_WWW_DIR=C:\xampp\php\www
set MIBDIRS=C:/xampp/php/extras/mibs
set MYSQL_HOME=C:\xampp\mysql\bin

c:
cd C:\Users\franc\OneDrive\devenv\htdocs\DnD
cls

php yii event-server/start

pause
