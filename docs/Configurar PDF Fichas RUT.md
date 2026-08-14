# Habilitar carga de Fichas RUT — poppler / pdftotext

> 🛟 Si algo falla durante la configuración, consulte **[Errores frecuentes APIDIAN](Errores%20frecuentes%20APIDIAN.md)**.

## ¿Qué habilita esta configuración?

La opción de **subir la Ficha RUT (PDF)** al crear una empresa: el API lee el PDF y
**autocompleta el formulario** (NIT, razón social, dirección, municipio, responsabilidades, etc.).

Internamente el API usa **`pdftotext`** (parte de la suite **Poppler**) para extraer el texto
del formulario DIAN. Laravel localiza el ejecutable mediante la variable **`PDFTOTEXT_PATH`**
del archivo `.env`, y **valida que esa ruta exista**; si la variable falta o la ruta es
incorrecta, la carga de RUT mostrará el error:

```
Poppler (pdftotext) no está configurado. Añade la ruta en .env como PDFTOTEXT_PATH=
```

> 📌 El PDF debe ser la **ficha RUT original descargada del MUISCA** (PDF con texto).
> Un RUT **escaneado o fotografiado** es una imagen: `pdftotext` no puede leerlo y la
> extracción no llenará ningún campo.

---

## Windows (Laragon)

### 1. Descargar Poppler para Windows

Descargar el ZIP de la última versión desde el repositorio oficial de builds para Windows:

- **[poppler-windows v25.11.0](https://github.com/oschwartz10612/poppler-windows/releases/tag/v25.11.0-0)** → archivo `Release-25.11.0-0.zip`

> ⚠️ Antes de extraer: clic derecho sobre el ZIP → **Propiedades** → si aparece la casilla
> **"Desbloquear"**, márquela y acepte. Evita que Windows/SmartScreen bloquee los `.exe`.

### 2. Extraer en una ruta fija

Extraer el ZIP y dejar la carpeta como **`C:\poppler`** (el ZIP trae una carpeta
`poppler-25.11.0`; muévala/renómbrela). La estructura debe quedar así:

```
C:\poppler\Library\bin\pdftotext.exe   ← este es el ejecutable que usará Laravel
```

> 📌 Evite rutas con espacios o tildes. Si usa otra carpeta, ajuste la ruta en los pasos siguientes.

Verificar que el ejecutable funciona (PowerShell o terminal de Laragon):

```powershell
& "C:\poppler\Library\bin\pdftotext.exe" -v    # debe mostrar: pdftotext version 25.11.0
```

### 3. Configurar el `.env` del proyecto (paso obligatorio)

Agregar esta línea al `.env`:

```env
PDFTOTEXT_PATH="C:/poppler/Library/bin/pdftotext.exe"
```

> 📌 Usar **barras normales `/`** (o dobles `\\`). Esta variable es lo **único obligatorio**:
> Laravel llama al ejecutable por su ruta completa, no necesita el PATH de Windows.

Refrescar la configuración de Laravel (terminal de Laragon, en la raíz del proyecto):

```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

### 4. (Opcional) Agregar Poppler al PATH de Windows

Solo sirve para poder ejecutar `pdftotext` directo en cualquier terminal — **no** lo necesita Laravel:

1. Buscar en Windows: **"Editar las variables de entorno del sistema"**
2. Botón **Variables de entorno…**
3. En **Variables del sistema** seleccionar **Path** → **Editar** → **Nuevo**
4. Añadir: `C:\poppler\Library\bin`
5. Aceptar todo y **abrir una terminal nueva**:

```bash
pdftotext -v    # debe mostrar la versión
```

---

## Linux (Ubuntu / Debian — instalación LAMP)

### 1. Instalar Poppler

```bash
sudo apt-get update
sudo apt-get install -y poppler-utils
```

### 2. Verificar

```bash
which pdftotext    # → /usr/bin/pdftotext
pdftotext -v       # debe mostrar la versión
```

### 3. Configurar el `.env` y refrescar caché

```env
PDFTOTEXT_PATH=/usr/bin/pdftotext
```

```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

---

## Docker

> ✔ La imagen PHP del proyecto **ya incluye `poppler-utils`** (viene en el `Dockerfile`),
> por lo que **no hay nada que instalar**: solo configurar el `.env` y refrescar la caché.

### 1. Configurar el `.env`

Agregar al `.env` del proyecto (si la carpeta está montada como volumen puede editarse
desde el host; si no, editar dentro del contenedor):

```env
PDFTOTEXT_PATH=/usr/bin/pdftotext
```

### 2. Refrescar la caché de Laravel dentro del contenedor

```bash
docker ps                          # identificar el contenedor PHP (COMMAND php-api26 o similar)
docker exec -it CONTAINER_ID bash

php artisan config:clear && php artisan config:cache && php artisan cache:clear && php artisan optimize:clear
```

### Solo si la imagen es antigua (no trae poppler)

Verificar dentro del contenedor:

```bash
which pdftotext    # debe responder: /usr/bin/pdftotext
```

Si no responde nada, el contenedor fue construido con una imagen anterior. Lo correcto es
**reconstruir con la imagen actual** (`docker compose up -d --build`), que ya lo incluye.
Como salida rápida puede instalarse manualmente:

```bash
apt-get update && apt-get install -y poppler-utils
```

> ⚠️ La instalación manual sobrevive a `docker stop/start`, pero **se pierde si el
> contenedor se recrea** (`docker compose down && up`, `docker rm`). La solución
> definitiva es reconstruir con la imagen actual.

---

## Verificación final

1. Ingresar al API y usar **Crear empresa** (wizard).
2. En el primer paso, subir una **Ficha RUT en PDF** (original del MUISCA, no escaneada).
3. El formulario debe autocompletarse con los datos de la empresa.

## Solución de problemas

| Síntoma | Causa | Solución |
|---|---|---|
| `Poppler (pdftotext) no está configurado...` | Falta `PDFTOTEXT_PATH` en `.env`, la ruta tiene un error de escritura o el archivo no existe | Verificar la ruta exacta al ejecutable y ejecutar `php artisan config:clear && php artisan config:cache` |
| En Windows: `pdftotext` no se reconoce en la terminal | El PATH no está aplicado (paso opcional) | Abrir una terminal **nueva** tras editar el PATH — no afecta a Laravel, que usa la ruta del `.env` |
| En Windows: el `.exe` no abre / bloqueado | ZIP descargado quedó bloqueado por Windows | Propiedades del ZIP → **Desbloquear**, volver a extraer |
| En Docker: sigue el error tras configurar | Falta `PDFTOTEXT_PATH` en el `.env` o caché sin refrescar | Completar pasos 1 y 2 de la sección Docker |
| En Docker: `which pdftotext` no responde | Contenedor construido con una imagen antigua sin poppler | Reconstruir con la imagen actual (`docker compose up -d --build`) o instalar manualmente |
| Sube el RUT pero no llena ningún campo | PDF escaneado (imagen) o formato distinto al formulario DIAN 001 | Usar la ficha RUT original en PDF descargada del MUISCA |
