

:: Configure this (use absolute path)
:: php cli path
set PHP=C:\php\php.exe
:: daemon.php path
set DAEMON=C:\kalkun\scripts\outbox_queue.php

:: Execute
%PHP% %DAEMON%
