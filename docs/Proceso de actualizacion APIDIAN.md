# Procedimiento de actualización APIDIAN

> 🛟 Si algo falla durante la actualización, consulte **[Errores frecuentes APIDIAN](Errores%20frecuentes%20APIDIAN.md)**.

**Versión PHP:** 8.3
**Documento:** Procedimiento de actualización y/o sincronización del repositorio.

> 📌 **Notas generales**
>
> - Los comandos `apt-get` y `chmod` aplican en Linux (servidor) o dentro del contenedor Docker.
> - Si está en Windows (Laragon), esos comandos se ejecutan en WSL/Docker o donde corra Linux.
> - Si aparecen conflictos de Git y necesita descartar cambios locales: `git reset --hard origin/main`.

---

## 1. Actualizar desde una versión antigua de APIDIAN hacia la actual

### 1.1 IMPORTANTE (Docker)

Si la instalación corre en Docker, primero actualice la imagen a la nueva versión.

> ⚠️ Antes de editar el `docker-compose.yml`, **baje/detenga los contenedores** para que el
> cambio se aplique correctamente (`docker compose down` o `docker-compose down` en el directorio del `.yml`).

Edite el archivo (desde el directorio del proyecto donde está `docker-compose.yml`):

```bash
nano docker-compose.yml
```

Verifique que la sección `php_api` tenga esta estructura (si no aparece, cámbiela):

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

### 1.2 Cambiar el origin del repositorio (en el proyecto antiguo)

Dentro del directorio del proyecto antiguo (`apidian2021` / `apidian2022` / `apidian2023` / `apidian2024` / `apidian2025`):

```bash
git config --global http.sslverify false
git remote remove origin
git remote add origin https://github.com/facturalatam/apidian.git
```

### 1.3 Validar/completar la configuración de Git

```bash
nano .git/config
```

Debe quedar así (si falta algo, agregarlo manualmente):

```ini
[core]
    repositoryformatversion = 0
    filemode = false
    bare = false
    logallrefupdates = true
    symlinks = false
    ignorecase = true
[remote "origin"]
    url = https://github.com/facturalatam/apidian.git
    fetch = +refs/heads/*:refs/remotes/origin/*
[branch "main"]
    remote = origin
    merge = refs/heads/main
```

### 1.4 Sincronizar código

```bash
git pull
git pull origin main
```

Si aparecen conflictos:

```bash
git reset --hard origin/main
```

### 1.5 Dependencias / migraciones / post-instalación

```bash
composer clear-cache
composer self-update 2.8.0
```

> ⚠️ **[Solo Windows/Laragon]** si el self-update falla con `rename(...): Acceso denegado (code: 5)`,
> **NO es un problema de permisos** (Administrador no lo soluciona) — ver la solución en
> [Errores frecuentes APIDIAN → error #2](Errores%20frecuentes%20APIDIAN.md#2-composer-self-update-acceso-denegado-code-5).

```bash
rm composer.lock
composer install
```

Luego:

```bash
php artisan migrate --seed
chmod -R 777 storage
chmod -R 777 bootstrap/cache
chmod -R 777 vendor/mpdf/mpdf
php artisan config:cache && php artisan config:clear && php artisan cache:clear
```

> 📌 Las plantillas **"urn"** ya se aplican automáticamente con `composer install` / `composer update`.
> Solo si necesita re-aplicarlas manualmente: `php urn_on.php`

### Nota (Docker)

Se deberá ajustar también el archivo `docker-compose.yml`: buscar el campo `php_api` y verificar
que tenga la estructura mostrada en el punto [1.1](#11-importante-docker).

> ⚠️ Antes de editar el `docker-compose.yml`, **baje/detenga los contenedores** para que el
> cambio se aplique correctamente (`docker compose down` o `docker-compose down`).

Esto vuelve a habilitar la opción de cargar fichas RUT.

---

## 2. Actualizar un APIDIAN ya existente

Dentro del directorio del proyecto (`apidian`):

```bash
git pull
git pull origin main
```

Si aparecen conflictos:

```bash
git reset --hard origin/main
```

Luego:

```bash
php artisan migrate --seed
composer clear-cache
composer self-update 2.8.0
rm composer.lock
composer install
chmod -R 777 storage
chmod -R 777 bootstrap/cache
chmod -R 777 vendor/mpdf/mpdf
php artisan config:cache && php artisan config:clear && php artisan cache:clear
```

> ⚠️ **[Solo Windows/Laragon]** Si el self-update sale con `rename(...): Acceso denegado (code: 5)`,
> ver [Errores frecuentes APIDIAN → error #2](Errores%20frecuentes%20APIDIAN.md#2-composer-self-update-acceso-denegado-code-5).
>
> 📌 Las plantillas **"urn"** ya se aplican automáticamente con `composer install` / `composer update`.
> Solo si necesita re-aplicarlas manualmente: `php urn_on.php`

---

## 3. Actualización rápida (si no cambió la versión del API)

```bash
git pull origin main
php artisan migrate
php artisan config:cache && php artisan cache:clear && php artisan optimize:clear
```
