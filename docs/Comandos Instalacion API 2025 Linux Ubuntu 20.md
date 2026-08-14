# Instalación API DIAN — VPS Linux Ubuntu 20

> 🛟 Si algo falla durante la instalación, consulte **[Errores frecuentes APIDIAN](Errores%20frecuentes%20APIDIAN.md)**
> (en especial el error #6 si los certificados `.p12` dan "datos corruptos" en Ubuntu 22+).

## Requisitos

- **VPS con Ubuntu 20** (⚠️ no sirven hostings con cPanel). Proveedores probados:
  - [hetzner.com](https://hetzner.com)
  - [contabo.com](https://contabo.com)
  - Amazon AWS
  - Google GCP
  - [digitalocean.com](https://digitalocean.com)
- **Putty** (cliente SSH)

---

## 1. Actualizar sistema, repositorios, PHP y servidor web

```bash
apt-get update
apt-get -y install software-properties-common
apt-get -y install python-software-properties
add-apt-repository ppa:ondrej/php
apt-get update
apt-get -y install php8.3 php8.3-mbstring php8.3-soap php8.3-zip php8.3-mysql php8.3-curl php8.3-gd php8.3-xml php8.3-intl php8.3-imap git curl zip unzip
apt-get -y install apache2
apt-get -y install poppler-utils
```

### Cambiar puerto de Apache (por defecto 80) — opcional

```bash
nano /etc/apache2/ports.conf
```

> Cambiar el puerto en la línea que hace mención a `Listen 80`.

```bash
service apache2 restart
```

## 2. Instalar MySQL (MariaDB)

```bash
apt-get install mariadb-server-core-10.3 mariadb-server-10.3 mariadb-client-10.3
```

### Cambiar puerto de MySQL (por defecto 3306) — opcional

```bash
nano /etc/mysql/mariadb.conf.d/50-server.cnf
```

> Descomentar la línea que hace referencia a `port` y asignar el puerto de preferencia.

```bash
service mysql restart
```

## 3. Crear usuario y base de datos MySQL

```bash
mysql -u root
```

> ⚠️ **Cambie la contraseña** en lugar de `uZ78R` y guárdela.

```sql
CREATE USER 'apidian'@'%' IDENTIFIED BY 'uZ78R';
GRANT ALL PRIVILEGES ON * . * TO 'apidian'@'%';
FLUSH PRIVILEGES;
CREATE DATABASE apidian CHARACTER SET utf8 COLLATE utf8_spanish_ci;
exit
```

## 4. Instalar Composer

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

## 5. Instalar APIDIAN

```bash
cd /var/www/html/
git config --global http.sslverify false
git clone https://github.com/facturalatam/apidian.git apidian
cd apidian
cp .env.example .env
nano .env
```

Cambiar la cadena de conexión en el archivo `.env`, asignando el puerto configurado anteriormente:

```ini
DB_PORT=3306
DB_DATABASE=apidian
DB_USERNAME=apidian
DB_PASSWORD=uZ78R
```

## 6. Dependencias, migraciones y storage

```bash
rm vendor composer.lock
composer self-update 2.8.0
composer install
php artisan key:generate
unzip storage.zip
# aceptar los cambios al descomprimir
chmod -R 777 storage bootstrap/cache vendor/mpdf/mpdf
php artisan config:cache && php artisan cache:clear
php artisan storage:link
php artisan migrate --seed
```

> 📌 Las plantillas **"urn"** ya se aplican automáticamente con `composer install` / `composer update`.
> Solo si necesita re-aplicarlas manualmente: `php urn_on.php`

## 7. Configurar Apache (VirtualHost)

```bash
cd /etc/apache2/sites-available/
touch api.conf
nano api.conf
```

Pegar el siguiente contenido — **cambiar el puerto** si se modificó anteriormente:

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

Activar el sitio y reiniciar:

```bash
a2dissite 000-default.conf
a2ensite api.conf
a2enmod rewrite
service apache2 restart
```

## 8. Limpiar cachés

```bash
cd /var/www/html/apidian
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```
