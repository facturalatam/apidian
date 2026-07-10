# APIDIAN — Community Edition

API de **facturación electrónica para la DIAN (Colombia)** — versión **Community**
(gratuita y de código abierto) mantenida por **[Facturalatam](https://facturalatam.com)**.


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

### Ejemplo JSON envio de factura

```bash
{
  "number": 994500240, // Número consecutivo de la factura
  "type_document_id": 1, // 1 = Factura electrónica
  "date": "2025-07-22", // Fecha de emisión
  "time": "04:08:12", // Hora de emisión
  "resolution_number": "18760000001", // Número de resolución DIAN
  "prefix": "SETP", // Prefijo autorizado

  "customer": {
    "identification_number": "900428042", // NIT del cliente
    "name": "TAMPAC TECNOLOGÍA EN AUTOMATIZACIÓN SAS"
  },

  "payment_form": {
    "payment_form_id": 1, // Contado
    "payment_method_id": 30, // Transferencia electrónica
    "payment_due_date": "2025-07-22", // Fecha de vencimiento (igual a emisión si es contado)
    "duration_measure": "0" // Días de plazo 
  },

  "allowance_charges": [
    {
      "discount_id": 1,
      "charge_indicator": false, // false = es descuento, true = es recargo
      "allowance_charge_reason": "Descuento Global",
      "amount": "100.00", // Descuento global aplicado sobre el total
      "base_amount": "1900.00" // Base sobre la cual se calculó el descuento
    }
  ],

  "legal_monetary_totals": {
    "line_extension_amount": "1900.00", // Subtotal (suma de líneas con descuentos de línea aplicados)
    "tax_exclusive_amount": "1900.00", // Total sin IVA (antes del IVA, pero después de descuentos por línea)
    "tax_inclusive_amount": "2261.00", // Total con IVA incluido (1900 + 361 IVA)
    "allowance_total_amount": "100.00", // Descuento total informado en la sección global
    "charge_total_amount": "0.00", // Total de recargos (en este caso, ninguno)
    "payable_amount": "2161.00" // Total a pagar: 2261.00 - 100.00 (descuento global)
  },

  "tax_totals": [
    {
      "tax_id": 1, // 1 = IVA
      "tax_amount": "361.00", // IVA total sumando ambas líneas (180.50 + 180.50)
      "percent": "19.00", // Porcentaje de IVA
      "taxable_amount": "1900.00" // Base gravable total (suma de 950 + 950)
    }
  ],

  "invoice_lines": [
    {
      "unit_measure_id": 70, // Unidad de medida
      "invoiced_quantity": "1", // Cantidad facturada
      "line_extension_amount": "950.00", // Precio - descuento de línea (1000 - 50)
      "free_of_charge_indicator": false, // No es gratuito
      "description": "Producto de prueba 1",
      "code": "PRUEBA1",
      "type_item_identification_id": 4, // Código del tipo de ítem
      "price_amount": "1000.00", // Precio original antes de descuento
      "base_quantity": "1",

      "allowance_charges": [
        {
          "discount_id": 10,
          "charge_indicator": false,
          "allowance_charge_reason": "Descuento por promoción",
          "amount": "50.00", // Descuento de la línea
          "base_amount": "1000.00"
        }
      ],

      "tax_totals": [
        {
          "tax_id": 1,
          "tax_amount": "180.50", // IVA de esta línea: 950 x 0.19
          "taxable_amount": "950.00", // Base gravable después del descuento de línea
          "percent": "19.00"
        }
      ]
    },

    {
      "unit_measure_id": 70,
      "invoiced_quantity": "1",
      "line_extension_amount": "950.00", // 1000 - 50
      "free_of_charge_indicator": false,
      "description": "Producto de prueba 2",
      "code": "PRUEBA2",
      "type_item_identification_id": 4,
      "price_amount": "1000.00",
      "base_quantity": "1",

      "allowance_charges": [
        {
          "discount_id": 11,
          "charge_indicator": false,
          "allowance_charge_reason": "Descuento por volumen",
          "amount": "50.00",
          "base_amount": "1000.00"
        }
      ],

      "tax_totals": [
        {
          "tax_id": 1,
          "tax_amount": "180.50", // 950 x 0.19
          "taxable_amount": "950.00",
          "percent": "19.00"
        }
      ]
    }
  ]
}
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
