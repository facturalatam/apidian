# Instalación API DIAN — Windows local (Laragon)

> 🛟 Si algo falla durante la instalación, consulte **[Errores frecuentes APIDIAN](Errores%20frecuentes%20APIDIAN.md)**.

## Requisitos

| Herramienta | Versión | Descarga |
|---|---|---|
| Laragon | 5.0 | [laragon-wamp.x86.exe](https://github.com/leokhoa/laragon/releases/download/5.0.0/laragon-wamp.x86.exe) — instalar por defecto en `C:\laragon` |
| PHP | 8.3 o superior | [php-8.3.0-nts-Win32-vs16-x64.zip](https://windows.php.net/downloads/releases/archives/php-8.3.0-nts-Win32-vs16-x64.zip) |
| MySQL | 5.7.20 | [mysql-5.7.20-winx64.zip](https://downloads.mysql.com/archives/get/p/23/file/mysql-5.7.20-winx64.zip) |
| Git | — | [git-scm.com](https://git-scm.com/install/windows) (solo si no lo tiene) |
| Composer | — | [getcomposer.org](https://getcomposer.org/download/) (solo si no lo tiene) |
| Visual Studio Code | — | [code.visualstudio.com](https://code.visualstudio.com/) |
| Postman | — | [postman.com/downloads](https://www.postman.com/downloads/) |

Verificar en la consola de Laragon:

```bash
php -v      # debe mostrar 8.3 o superior
mysql -V    # debe mostrar 5.7.20
```

**Guías para agregar estas versiones a Laragon:**

- [Otras versiones de MySQL en Laragon (5.7.20)](https://misterdigital.es/como-actualizar-mysql-en-laragon/)
- [Otras versiones de PHP en Laragon](https://villagrabaez.medium.com/actualizar-php-a-la-versi%C3%B3n-7-4-en-laragon-75f3546114f1)

> ⚠️ **Error `no se encontró MSVCR120.dll`:** descargar el
> [Visual C++ Redistributable 2013](https://www.microsoft.com/en-us/download/details.aspx?id=40784)
> e instalar **ambos**: `vcredist_x86.exe` y `vcredist_x64.exe`.

---

## Comandos de instalación

> 📌 Usar la **terminal de Laragon** para todos los comandos.

### 1. Clonar el proyecto

```bash
git config --global http.sslverify false
git clone https://github.com/facturalatam/apidian.git apidian
cd apidian
cp .env.example .env
```

### 2. Base de datos y `.env`

1. Crear la base de datos en Laragon.
2. Abrir el archivo `.env` y verificar la cadena de conexión a la base de datos (variables con prefijo `DB_`).
3. Verificar que exista esta línea para la conversión del certificado (ya viene en `.env.example`):

```ini
URL_API_CERT_MODERNIZER="http://62.146.176.127:8091"
```

### 3. Actualizar Composer

```bash
composer self-update 2.8.0
```

> 📌 Puede ver la versión instalada con `composer -V` — los Laragon antiguos traen composer **1.x** incluido.
>
> Si da error de **permisos** por haber instalado en la unidad C, abra Visual Studio Code como **Administrador**.

> ⚠️ **CASO ESPECIAL:** si el self-update falla con `rename(...): Acceso denegado (code: 5)`,
> **NO es un problema de permisos** (Administrador no lo soluciona) — ver la solución en
> [Errores frecuentes APIDIAN — error #2](Errores%20frecuentes%20APIDIAN.md#2-composer-self-update-acceso-denegado-code-5).

### 4. Instalar dependencias

```bash
composer install
```

> ⚠️ Si sale error por el paquete `spatie/laravel-backup`, active la extensión **zip**
> desde Laragon o desde `php.ini` (quitar el `;` a `;extension=zip`).

### 5. Base de datos, storage y permisos

```bash
php artisan key:generate
php artisan migrate --seed
php artisan migrate
unzip storage.zip
chmod -R 777 storage
chmod -R 777 bootstrap/cache
chmod -R 777 vendor/mpdf/mpdf
php artisan storage:link
```

> 📌 Las plantillas **"urn"** ya se aplican automáticamente con `composer install` / `composer update`.
> Solo si necesita re-aplicarlas manualmente: `php urn_on.php`
> (en PowerShell el `.bat` se ejecuta con `.\urn_on.bat`).

### 6. Limpiar cachés

```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

### 7. Abrir la aplicación

1. **Reiniciar Laragon** (botón *Stop* → *Start All*). Esto es necesario porque Laragon genera los
   dominios `.test` al arrancar Apache: como el proyecto se clonó con Laragon ya encendido, sin
   reiniciar el dominio no existe o queda apuntando a la carpeta equivocada.
2. Abrir <http://apidian.test/login>
3. Las credenciales del administrador quedan en el archivo `usuario_admin.txt` en la raíz del
   proyecto (las crea la migración). Guardar la clave y borrar el archivo.

> ⚠️ **PROBLEMA FRECUENTE:** si al abrir sale `Not Found — The requested URL was not found on
> this server` (404 de Apache), falta activar **`mod_rewrite`** en el `httpd.conf` de Laragon
> (los Apache 64 bits nuevos lo traen desactivado de fábrica) — ver la solución completa en
> [Errores frecuentes APIDIAN — error #1](Errores%20frecuentes%20APIDIAN.md#1-not-found-404-de-apache-al-abrir-login).
