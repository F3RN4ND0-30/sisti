# Instalar Drivers para Utilizar SQLServer

https://learn.microsoft.com/es-es/sql/connect/php/download-drivers-php-sql-server?view=sql-server-ver17

# Copiar los archivos necesarios para esta parte que son:

php_sqlsrv_82_ts_x64.dll
php_pdo_sqlsrv_82_ts_x64.dll
php_sqlsrv_82_nts_x64.dll
php_pdo_sqlsrv_82_nts_x64.dll
php_sqlsrv_82_ts_x86.dll
php_pdo_sqlsrv_82_ts_x86.dll

# Agregar al php.ini lo siguiente al ultimo donde estan las extensiones:

extension=php_sqlsrv_82_ts_x64.dll
extension=php_pdo_sqlsrv_82_ts_x64.dll
