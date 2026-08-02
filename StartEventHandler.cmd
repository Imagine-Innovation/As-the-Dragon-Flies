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

cd C:\Users\franc\OneDrive\devenv\htdocs\DnD

call rotate-logs EventHandler.log 10
call rotate-logs BroadcastMessage.log 10

REM Command Line Use : baretail [options] {file(s)}
REM where options can be:
REM --window-position left top width height => Specifies the window position at startup in pixels. Note that the -ws, --window-state option, as well as the stored windows state in the registry (from the last run) overrides this option when the state is minimised or maximised.
REM --window-state 0 | 1 | 2      => Specifies the window state at startup: 0 Normal state (neither minimised or maximised), 1 Minimised, 2 Maximised
REM --tile-window-count count --tile-window-index index => Specifies the window position at startup for vertical tiling of applications on the screen. The count specifies the number of equal height vertical slots for application windows. The index specifies the zero-based (zero is the top) offset of the slot in which to position the window.

start C:\Portable\Baretail\baretail.exe --window-state 1 --window-position 0 0 900 1000   c:\temp\EventHandler.log
start C:\Portable\Baretail\baretail.exe --window-state 1 --window-position 901 0 900 1000 c:\temp\BroadcastMessage.log

rem cls

php yii event-server/start

pause
