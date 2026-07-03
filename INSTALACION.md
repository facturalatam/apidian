# Guía de instalación — APIDIAN Community Edition

Tres formas de instalar según tu entorno:

| Opción | Entorno | Recomendado para |
|---|---|---|
| [A. Windows (Laragon)](#opción-a--windows-con-laragon) | Desarrollo local | Pruebas y desarrollo |
| [B. Linux Ubuntu (VPS)](#opción-b--linux-ubuntu-vps-con-apache) | Apache + MariaDB | Producción |
| [C. Docker](#opción-c--docker-ubuntu-2204) | Contenedores | Producción / despliegue rápido |

> 📖 Documentación general: [https://manual.facturalatam.com/](https://manual.facturalatam.com/)

---

## Opción A — Windows con Laragon

### Requisitos
- [Laragon 5.0](https://github.com/leokhoa/laragon/releases/download/5.0.0/laragon-wamp.x86.exe) (instalar por defecto en `C:/laragon`)
- [PHP 8.3](https://windows.php.net/downloads/releases/archives/php-8.3.0-nts-Win32-vs16-x64.zip) — verificar con `php -v`
- [MySQL 5.7.20](https://downloads.mysql.com/archives/get/p/23/file/mysql-5.7.20-winx64.zip) — verificar con `mysql -V`
- [Git](https://git-scm.com/install/windows) y [Composer](https://getcomposer.org/download/)
- Opcionales: [Postman](https://www.postman.com/downloads/), [VS Code](https://code.visualstudio.com/docs/?dv=win)

Guías para agregar versiones a Laragon:
[otra versión de MySQL](https://misterdigital.es/como-actualizar-mysql-en-laragon/) ·
[otra versión de PHP](https://villagrabaez.medium.com/actualizar-php-a-la-versi%C3%B3n-7-4-en-laragon-75f3546114f1)

### Comandos (en la terminal de Laragon)

```bash
git clone <URL-DEL-REPOSITORIO> apidian
cd apidian
cp .env.example .env
```

1. **Crear la base de datos** en Laragon (HeidiSQL).
2. Editar `.env` y configurar las variables `DB_*` con la conexión a esa base.

```bash
composer self-update 2.8.0
composer install
php artisan key:generate
unzip storage.zip
php artisan migrate --seed
php artisan storage:link
urn_on.bat
php artisan config:clear && php artisan cache:clear && php artisan config:cache
```

### Problemas comunes (Windows)
- **Error `MSVCR120.dll` no encontrado** → instalar [Visual C++ 2013](https://www.microsoft.com/en-us/download/details.aspx?id=40784) (`vcredist_x86.exe` **y** `vcredist_x64.exe`).
- **Error de permisos con Composer** (instalado en unidad C) → abrir la terminal/VS Code **como Administrador**.
- **Error con `spatie/laravel-backup`** → activar la extensión **zip** desde Laragon o en `php.ini` (`extension=zip`).
- **Error de clonación por SSL** → `git config --global http.sslverify false`.

---

## Opción B — Linux Ubuntu (VPS con Apache)

### Requisitos
- VPS con Ubuntu 20+ (no sirven hostings con cPanel). Proveedores: Hetzner, Contabo, AWS, GCP, DigitalOcean.
- Cliente SSH (Putty o similar).

### 1. Sistema, PHP y Apache
```bash
apt-get update
apt-get -y install software-properties-common
add-apt-repository ppa:ondrej/php
apt-get update
apt-get -y install php8.3 php8.3-mbstring php8.3-soap php8.3-zip php8.3-mysql php8.3-curl php8.3-gd php8.3-xml php8.3-intl php8.3-imap git curl zip unzip
apt-get -y install apache2
apt-get -y install poppler-utils
```

> Para cambiar el puerto de Apache (por defecto 80): editar `Listen 80` en
> `/etc/apache2/ports.conf` y `service apache2 restart`.

### 2. MariaDB
```bash
apt-get install mariadb-server-core-10.3 mariadb-server-10.3 mariadb-client-10.3
```

> Para cambiar el puerto (por defecto 3306): descomentar `port` en
> `/etc/mysql/mariadb.conf.d/50-server.cnf` y `service mysql restart`.

Crear usuario y base de datos (**usa una contraseña segura propia**):
```sql
mysql -u root

CREATE USER 'apidian'@'%' IDENTIFIED BY 'TU_CONTRASEÑA_SEGURA';
GRANT ALL PRIVILEGES ON * . * TO 'apidian'@'%';
FLUSH PRIVILEGES;
CREATE DATABASE apidian CHARACTER SET utf8 COLLATE utf8_spanish_ci;
exit
```

### 3. Composer
```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

### 4. Instalar la API
```bash
cd /var/www/html/
git clone <URL-DEL-REPOSITORIO> apidian
cd apidian
cp .env.example .env
nano .env   # configurar DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD

composer self-update 2.8.0
composer install
php artisan key:generate
unzip storage.zip
chmod -R 777 storage bootstrap/cache vendor/mpdf/mpdf
php artisan config:cache && php artisan cache:clear
php artisan storage:link
php artisan migrate --seed
chmod 700 urn_on.sh
./urn_on.sh
```

### 5. VirtualHost de Apache
```bash
cd /etc/apache2/sites-available/
nano api.conf
```

Contenido (ajusta el puerto si lo cambiaste):
```apache
<VirtualHost *:80>
    ServerAdmin admin@example.com
    DocumentRoot /var/www/html/apidian/public

    <Directory /var/www/html/apidian/public>
        Options +FollowSymlinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

Activar el sitio:
```bash
a2dissite 000-default.conf
a2ensite api.conf
a2enmod rewrite
service apache2 restart
cd /var/www/html/apidian
php artisan config:clear && php artisan cache:clear && php artisan config:cache
```

---

## Opción C — Docker (Ubuntu 22.04)

Despliegue sin dominio y sin proxy.

### 1. Instalar Docker y docker-compose
```bash
apt-get -y install git-core zip unzip apt-transport-https ca-certificates curl gnupg-agent software-properties-common
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -
add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable"
apt-get -y update && apt-get -y install docker-ce && systemctl start docker && systemctl enable docker
curl -L "https://github.com/docker/compose/releases/download/1.29.2/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose
```

### 2. Clonar y configurar
```bash
git clone <URL-DEL-REPOSITORIO> apidian
cd apidian
```

1. Editar `docker/init.sql` y asignar **usuario y contraseña seguros** para la base de datos.
2. Copiar el archivo de despliegue: `cp docker-compose.yml.example docker-compose.yml`
   (modificar puertos / nombres de contenedores si es necesario).
3. Copiar el entorno: `cp .env.example .env` y ajustar según lo configurado:
   ```
   APP_PORT=80
   DB_CONNECTION=mysql
   DB_HOST=mariadb_api
   DB_PORT=3306
   DB_DATABASE=db_api
   DB_USERNAME=user_api
   DB_PASSWORD=password_api
   ```

### 3. Desplegar
```bash
docker network create apinet
docker-compose up -d
```

### 4. Comandos de despliegue Laravel (dentro del contenedor PHP)
```bash
docker exec -ti <contenedor-fpm> bash

composer self-update 2.8.0
composer install
php artisan key:generate
unzip storage.zip
chmod -R 777 storage bootstrap/cache vendor/mpdf/mpdf
php artisan config:clear && php artisan cache:clear && php artisan config:cache && php artisan storage:link && php artisan migrate --seed
chmod 700 urn_on.sh
./urn_on.sh
```

### Extra: carga de fichas RUT al crear empresa
Para usar la opción de cargar fichas RUT, el contenedor PHP debe construirse con el
`Dockerfile` del proyecto. En `docker-compose.yml`, editar el servicio `php_api` a:

```yaml
php_api:
  build:
    context: .
    dockerfile: Dockerfile
  container_name: php_api
  working_dir: /var/www/html
  volumes:
    - ./:/var/www/html
  networks:
    - apinet
```

> ⚠️ Antes de editar `docker-compose.yml`, **detén los contenedores** para que el cambio
> se aplique correctamente (`docker compose down` o `docker-compose down`).

---

## Configurar carga de fichas RUT (Poppler / pdftotext)

La opción de **subir fichas RUT al crear empresa** requiere la utilidad `pdftotext`
(paquete **Poppler**) en el servidor.

### Windows
1. Descargar [Poppler para Windows](https://github.com/oschwartz10612/poppler-windows/releases/tag/v25.11.0-0).
2. Extraer en una ruta fija, recomendado `C:\poppler`
   (el ejecutable queda en `C:\poppler\Library\bin\pdftotext.exe`).
3. Agregar al **PATH** del sistema: *Editar las variables de entorno del sistema →
   Variables de entorno → Path → Editar → Añadir* `C:\poppler\Library\bin`.
4. Verificar desde la terminal de Laragon: `pdftotext -v`
5. Agregar al `.env`:
   ```
   PDFTOTEXT_PATH="C:/poppler/Library/bin/pdftotext.exe"
   ```
6. Refrescar caché:
   ```bash
   php artisan config:clear && php artisan cache:clear && php artisan config:cache
   ```

### Linux (Ubuntu/Debian)
```bash
sudo apt-get update
sudo apt-get install -y poppler-utils
pdftotext -v   # verificar
```
Agregar al `.env`:
```
PDFTOTEXT_PATH=/usr/bin/pdftotext
```
Y refrescar caché:
```bash
php artisan config:clear && php artisan cache:clear && php artisan config:cache
```

### Docker
```bash
docker ps                       # identificar el contenedor PHP (COMMAND php-api o similar)
docker exec -ti <CONTAINER_ID> bash

apt-get update
apt-get install -y poppler-utils
php artisan config:cache && php artisan cache:clear && php artisan optimize:clear
```

> ⚠️ Si el contenedor se reinicia, hay que volver a ejecutar estos comandos.
> Para hacerlo **permanente**, construye el contenedor con el `Dockerfile` del proyecto
> (ver sección *Extra: carga de fichas RUT* de la Opción C).

---

## Notas finales (todas las opciones)

- **`unzip storage.zip`** crea el esqueleto de la carpeta `storage/` (subcarpetas y
  fuentes para PDF). Es un paso obligatorio antes de `storage:link`.
- **`urn_on`** (`.bat` en Windows, `.sh` en Linux) copia las plantillas XML con el
  namespace `urn` — necesario para la **firma** de los documentos.
- Para la conversión de certificados en la carga de fichas RUT existe la variable
  opcional `URL_API_CERT_MODERNIZER` en el `.env` (consulta el valor con Facturalatam).
- Para reiniciar la base de datos desde cero: `php artisan migrate:fresh --seed`.
- Requiere `local_infile` habilitado en MySQL/MariaDB (carga de catálogos con `LOAD DATA`).
