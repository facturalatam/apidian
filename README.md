# APIDIAN — Community Edition

API de **facturación electrónica para la DIAN (Colombia)** — versión **Community**
(gratuita y de código abierto) mantenida por **[Facturalatam](https://facturalatam.com)**.

Esta edición contiene el **núcleo de facturación electrónica** completo y funcional.
Es una versión **recortada**: se removieron los módulos exclusivos de la edición
**Enterprise**, dejando intacto todo lo necesario para emitir los documentos
electrónicos principales ante la DIAN.

📖 **Documentación:** [https://manual.facturalatam.com/](https://manual.facturalatam.com/)

---

## ✅ Módulos incluidos (Community)

- **Factura electrónica de venta**: nacional, exportación y contingencia.
- **Notas Crédito y Notas Débito**.
- **Documento Soporte** y sus **Notas de Ajuste**.
- **Eventos RADIAN** (acuse de recibo, recibo del bien/servicio, aceptación, etc.).
- Firma **XAdES**, transmisión a la DIAN, generación de **PDF** y envío por **correo**.
- Catálogos DIAN, resoluciones por empresa y paso a producción.

## 🔒 Módulos NO incluidos (exclusivos de Enterprise)

- **Nómina electrónica** (individual y de ajuste).
- **Documentos equivalentes** (POS / tiquete de máquina registradora).
- **RIPS** (Registro Individual de Prestación de Servicios de Salud).
- **Sector Salud**: campos de salud en el XML/PDF de las facturas.
- **Aplicación móvil**.
- **Módulo Extractor**.
- Gestión avanzada de **usuarios/roles**, **acceso a la app** y **almacenamiento en la nube**.

> Estos módulos se comercializan en la edición **Enterprise** de
> [Facturalatam](https://facturalatam.com).

---

## ⚙️ Requisitos

- PHP 8.1+ (con extensiones `zip`, `gd`, `soap`, `openssl`, `mbstring`, etc.)
- Composer
- MySQL / MariaDB
- Node.js + npm
- `local_infile` habilitado en MySQL (para la carga de catálogos vía `LOAD DATA`)

## 🚀 Instalación

📋 **Guía completa paso a paso:** [INSTALACION.md](INSTALACION.md) — Windows (Laragon),
Linux Ubuntu (VPS con Apache) y Docker, incluida la configuración de fichas RUT (Poppler).

### Resumen rápido

```bash
# 1. Dependencias
composer install
npm install && npm run prod

# 2. Entorno
cp .env.example .env
php artisan key:generate
# edita .env con tus credenciales de base de datos

# 3. Esqueleto de storage (obligatorio)
unzip storage.zip

# 4. Base de datos (crea la BD antes) + catálogos
php artisan migrate --seed

# 5. Enlace de storage y plantillas de firma
php artisan storage:link
# Windows: urn_on.bat   |   Linux: bash urn_on.sh
```

> Para reiniciar la base de datos desde cero: `php artisan migrate:fresh --seed`.
> Guía detallada en la [documentación](https://manual.facturalatam.com/).

---

## 📄 Licencia

Este proyecto está publicado bajo la licencia **MIT**. Puedes usarlo, copiarlo,
modificarlo, distribuirlo y venderlo libremente, siempre que mantengas el aviso de
copyright y la licencia original.

Basado en el proyecto open source **apidian**. Edición Community mantenida por
**[Facturalatam](https://facturalatam.com)**.
