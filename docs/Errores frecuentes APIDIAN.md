# Errores frecuentes APIDIAN — Causas y soluciones

Playbook de soporte: los errores que más se repiten en instalaciones y actualizaciones,
con su causa real y la solución paso a paso. Cada entrada indica el **síntoma** literal
(para encontrarla buscando el texto del error), la **causa** y la **solución**.

## Índice

| # | Error | Entorno |
|---|---|---|
| 1 | [Not Found (404 de Apache) al abrir /login](#1-not-found-404-de-apache-al-abrir-login) | Windows local (Laragon) |
| 2 | [composer self-update: Acceso denegado (code: 5)](#2-composer-self-update-acceso-denegado-code-5) | Windows local (Laragon) |
| 3 | [urn_on.bat no se reconoce en PowerShell](#3-urn_onbat-no-se-reconoce-en-powershell) | Windows local |
| 4 | [Falta MSVCR120.dll](#4-falta-msvcr120dll) | Windows local (Laragon) |
| 5 | [composer install falla por laravel-backup / ext-zip](#5-composer-install-falla-por-laravel-backup--ext-zip) | Windows local (Laragon) |
| 6 | [Certificado .p12/.pfx: "datos corruptos" o no carga](#6-certificado-p12pfx-datos-corruptos-o-no-carga) | VPS Ubuntu 22+ / PHP 8.3 |
| 7 | [composer self-update no funciona en Hostinger](#7-composer-self-update-no-funciona-en-hostinger) | Hostinger / compartido |
| 8 | [Error 4166 LOAD DATA LOCAL INFILE en migraciones](#8-error-4166-load-data-local-infile-en-migraciones) | Hostinger / compartido |
| 9 | [Paso a producción: cURL error 35 ... https://localhost](#9-paso-a-producción-curl-error-35--httpslocalhost) | Hostinger / compartido |
| 10 | [Al subir certificado: Undefined array key "cert"](#10-al-subir-certificado-undefined-array-key-cert) | Todos |
| 11 | [Cambios en .env que no aplican](#11-cambios-en-env-que-no-aplican) | Todos |
| 12 | [DIAN rechaza documentos con regla ZE02](#12-dian-rechaza-documentos-con-regla-ze02) | Todos |

---

## 1. Not Found (404 de Apache) al abrir /login

**Entorno:** Windows local (Laragon).

**Síntoma:** al terminar la instalación y abrir `http://apidian.test/login` (u otra ruta) aparece:

```
Not Found
The requested URL was not found on this server.
```

Esa página 404 es **de Apache, no de Laravel** (la de Laravel se ve con diseño): la petición
nunca llegó al API, así que la instalación está bien — el problema está en Apache/Laragon.

**Causa más común (confirmada): `mod_rewrite` desactivado.** Las versiones nuevas de Apache
64 bits que traen los Laragon recientes tienen el módulo `rewrite` **comentado de fábrica**
(los Laragon viejos lo traían activado). El `.htaccess` de Laravel envuelve sus reglas en
`<IfModule mod_rewrite.c>`, así que Apache lo ignora **en silencio** (sin error 500): la raíz
`/` puede abrir, pero rutas como `/login` buscan un archivo físico que no existe → 404.

**Solución (en orden):**

1. **Activar `mod_rewrite`:** Menú Laragon → Apache → `httpd.conf`, buscar la línea

   ```apache
   #LoadModule rewrite_module modules/mod_rewrite.so
   ```

   quitarle el `#` del inicio, guardar y reiniciar Laragon (*Stop* → *Start All*).
2. Si persiste, reiniciar Laragon de todas formas: los dominios `.test` se generan al arrancar
   Apache, y si el proyecto se clonó con Laragon ya corriendo el vhost puede estar sin regenerar.
3. Verificar el vhost generado: Menú Laragon → Apache → `sites-enabled` →
   `auto.apidian.test.conf`. El `DocumentRoot` debe terminar en `/public`. Si no, borrar el
   archivo y reiniciar Laragon para que lo regenere.
4. Verificar que el proyecto quedó en `C:\laragon\www\apidian` directamente y no en una
   subcarpeta doble (`C:\laragon\www\apidian\apidian` — pasa cuando se clona dentro de
   una carpeta ya creada). Si es el caso, mover el contenido un nivel arriba y reiniciar Laragon.
5. Prueba que descarta a Apache: en la carpeta del proyecto ejecutar `php artisan serve` y abrir
   <http://127.0.0.1:8000/login>. Si ahí abre, el proyecto está perfecto y el problema es 100%
   de Apache (repetir pasos 1–4).

## 2. composer self-update: Acceso denegado (code: 5)

**Entorno:** Windows local (Laragon).

**Síntoma:** al ejecutar `composer self-update 2.8.0`:

```
[ErrorException]
rename(C:\laragon\bin\composer/composer-temp.phar,C:\laragon\bin\composer\composer.phar): Acceso denegado (code: 5)
```

**NO es un problema de permisos** y ejecutar la consola como Administrador **NO lo soluciona**.

**Causa:** Windows no permite sobrescribir `composer.phar` porque es el mismo archivo que está
corriendo el self-update en ese momento (y a veces el antivirus retiene el phar recién
descargado). Es típico del **composer 1.x viejo** que traían los Laragon antiguos (se reconoce
por la lluvia de `Deprecation Notice` al ejecutar cualquier comando); el composer 2.x ya no
sufre esto. Puede verificar la versión con `composer -V`.

**Solución:** la versión nueva ya quedó descargada como `composer-temp.phar`.
En la carpeta `C:\laragon\bin\composer` ejecutar:

```bash
ren composer.phar composer-viejo.phar
ren composer-temp.phar composer.phar
composer -V    # debe mostrar 2.8.0; después puede borrar composer-viejo.phar
```

Si `composer-temp.phar` no existiera, descargar el phar directamente (sin usar self-update):

```bash
php -r "copy('https://getcomposer.org/download/2.8.0/composer.phar','C:/laragon/bin/composer/composer.phar');"
```

> Este error solo ocurre en Windows local; en Linux/VPS no existe (el sistema permite
> reemplazar archivos en uso).

## 3. urn_on.bat no se reconoce en PowerShell

**Entorno:** Windows local.

**Síntoma:** al ejecutar `urn_on.bat` en la terminal de VS Code (PowerShell):

```
urn_on.bat : El término 'urn_on.bat' no se reconoce como nombre de un cmdlet, función,
archivo de script o programa ejecutable.
```

En la terminal de Laragon (cmd) sí funciona.

**Causa:** PowerShell, por seguridad, **no ejecuta comandos de la carpeta actual** — solo del
PATH del sistema. cmd sí busca primero en la carpeta actual. El archivo existe y está bien.

**Solución:** ya **no es necesario ejecutarlo**: las plantillas "urn" se
aplican automáticamente en cada `composer install` / `composer update`. Si se quiere aplicar
manualmente:

```powershell
php urn_on.php     # funciona en cualquier terminal y sistema
.\urn_on.bat       # el .\ le indica a PowerShell "de esta carpeta"
```

## 4. Falta MSVCR120.dll

**Entorno:** Windows local (Laragon/MySQL).

**Síntoma:** `no se encontró MSVCR120.dll` al iniciar servicios.

**Causa:** falta el runtime de Visual C++ 2013 que necesitan los binarios de MySQL.

**Solución:** descargar el
[Visual C++ Redistributable 2013](https://www.microsoft.com/en-us/download/details.aspx?id=40784)
e instalar **ambos**: `vcredist_x86.exe` y `vcredist_x64.exe`.

## 5. composer install falla por laravel-backup / ext-zip

**Entorno:** Windows local (Laragon).

**Síntoma:** `composer install` se detiene con un error que menciona el paquete
`spatie/laravel-backup` (requiere `ext-zip`).

**Causa:** la extensión `zip` de PHP viene desactivada.

**Solución:** activar la extensión **zip** desde el menú de Laragon (PHP → Extensions) o en el
`php.ini` quitando el `;` a la línea `;extension=zip`. Reiniciar Laragon y repetir
`composer install`.

## 6. Certificado .p12/.pfx: "datos corruptos" o no carga

**Entorno:** VPS Ubuntu 22 o superior / PHP 8.3 (OpenSSL 3). En Hostinger ver la nota al final.

**Síntoma:** certificados DIAN que funcionaban en el servidor viejo dejan de cargar al migrar
(error de "datos corruptos" / contraseña inválida aunque la clave sea correcta).

**Causa:** OpenSSL 3 (el de Ubuntu 22+) **deshabilitó los cifrados viejos** (RC2/3DES) con los
que muchas CA emiten los `.p12`/`.pfx`. PHP ya no puede abrirlos aunque estén perfectos.

**Solución en VPS (activar el legacy provider de OpenSSL):** editar `/etc/ssl/openssl.cnf` y
dejar activas estas secciones (la línea `openssl_conf = openssl_init` ya existe al inicio del
archivo):

```ini
[openssl_init]
providers = provider_sect

[provider_sect]
default = default_sect
legacy = legacy_sect

[default_sect]
activate = 1

[legacy_sect]
activate = 1
```

Luego reiniciar el servicio web: `service apache2 restart` (o `php8.3-fpm` si aplica).

**Alternativas sin tocar el servidor:**

- El API convierte automáticamente los certificados viejos vía `URL_API_CERT_MODERNIZER`
  (debe estar configurada en el `.env`).
- Conversión manual en un PC:

  ```bash
  openssl pkcs12 -legacy -in cert.p12 -nodes -out temp.pem
  openssl pkcs12 -export -in temp.pem -out cert_moderno.p12
  # borrar temp.pem después
  ```

> En Hostinger/hosting compartido no se puede activar el legacy provider (no hay acceso a
> `openssl.cnf`): usar el modernizador automático o la conversión manual.

## 7. composer self-update no funciona en Hostinger

**Entorno:** Hostinger / hosting compartido.

**Síntoma:** `composer self-update` falla (el binario del sistema no es editable, no hay root).

**Solución:** instalar un composer propio en el home:

```bash
cd ~
curl -sS https://getcomposer.org/installer | php -- --version=2.8.0 --install-dir=$HOME/bin --filename=composer
composer -V   # Composer version 2.8.0
```

(Requiere tener `~/bin` en el PATH — ver la sección "PHP CLI" del manual de Hostinger.)

## 8. Error 4166 LOAD DATA LOCAL INFILE en migraciones

**Entorno:** Hostinger / hosting compartido (MariaDB con `local_infile` deshabilitado).

**Síntoma:** error 4166 en migraciones/seeders que usan `LOAD DATA LOCAL INFILE`.

**Solución:** **no requiere acción** — el código trae fallback PHP en
`RegularizeDataHelper::loadCsvFile` que inserta los CSV cuando `LOAD DATA` falla. Si el error
aparece igualmente, la instalación tiene una versión vieja del código: hacer `git pull` +
`composer install` y repetir la migración.

## 9. Paso a producción: cURL error 35 ... https://localhost

**Entorno:** Hostinger / hosting compartido (aplica a cualquier servidor).

**Síntoma:** el paso a producción falla con `cURL error 35 ... https://localhost`.

**Causa:** `APP_URL` malformada en el `.env` (sin las dos barras `//`, o con puerto que no
corresponde), y el API termina llamándose a sí mismo por `localhost`.

**Solución:** en el `.env` dejar `APP_URL=https://SUBDOMINIO` (con `https://` completo y sin
puerto), y luego:

```bash
php artisan config:clear && php artisan config:cache
```

## 10. Al subir certificado: Undefined array key "cert"

**Entorno:** todos (frecuente con `.pfx` de algunas CA).

**Síntoma:** al subir el certificado sale `Undefined array key "cert"`, o el modernizador
falla en el paso `-export` aunque la contraseña sea correcta.

**Causa:** hay `.pfx` **mal emitidos por la CA** donde la llave privada no corresponde al
certificado del titular. No tienen arreglo local.

**Diagnóstico** (en un PC con OpenSSL):

```bash
openssl pkcs12 -legacy -in cert.pfx -nodes -out t.pem
openssl pkcs12 -export -in t.pem -out /dev/null -passout pass:x
```

Si responde **`No cert in -in file matches private key`** → el archivo nació mal: pedir
re-descarga/reemisión a la CA (la contraseña puede estar correcta).

## 11. Cambios en .env que no aplican

**Entorno:** todos.

**Síntoma:** se edita el `.env` (URL, credenciales, rutas) y el API se comporta como si nada
hubiera cambiado.

**Causa:** Laravel cachea la configuración; el `.env` no se lee en cada petición.

**Solución:** tras **cualquier** cambio en `.env`:

```bash
php artisan config:clear && php artisan config:cache
```

## 12. DIAN rechaza documentos con regla ZE02

**Entorno:** todos.

**Síntoma:** la DIAN rechaza los documentos con la regla **ZE02** (falta `urn:` en el XML).

**Causa:** las plantillas/clases de firma "urn" no están aplicadas. Pasaba típicamente después
de un `composer update` (composer reinstala el paquete `ubl21dian` limpio y se perdía la
copia) cuando el paso manual `urn_on` se olvidaba.

**Solución:** las plantillas urn se aplican **automáticamente** en cada
`composer install` / `composer update` (hook en `composer.json`). Si la instalación tiene
código viejo: hacer `git pull` + `composer install`. Para aplicarlas manualmente en cualquier
sistema: `php urn_on.php`.
