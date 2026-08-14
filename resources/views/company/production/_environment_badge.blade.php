{{-- Badge de ambiente DIAN del tipo de documento (1 = Producción, 2 = Habilitación).
     Recibe 'environmentStatus' (getEnvironmentStatus del ProductionController) o,
     en su defecto, 'company' + 'type' (invoice|pos|support|payroll|event) y lo
     resuelve desde las columnas de la empresa. Sin datos asume Habilitación.

     La columna solo alimenta el ProfileExecutionID del XML y la URL del QR; el
     destino real del envío es la URL del software (url, url_eqdocs, url_payroll,
     url_support_document, url_event). Cuando llega 'company' se comparan las dos
     y se avisa si no coinciden, porque en ese caso el badge por sí solo miente.

     'label' es opcional y sirve para prefijar el badge en listas mixtas. --}}
@php
    $envBadgeColumns = [
        'invoice' => 'type_environment_id',
        'pos'     => 'eqdocs_type_environment_id',
        'support' => 'support_document_type_environment_id',
        'payroll' => 'payroll_type_environment_id',
        'event'   => 'event_type_environment_id',
    ];
    $envBadgeUrlColumns = [
        'invoice' => 'url',
        'pos'     => 'url_eqdocs',
        'support' => 'url_support_document',
        'payroll' => 'url_payroll',
        'event'   => 'url_event',
    ];
    $envBadgeType = $environmentStatus['type'] ?? $type ?? 'invoice';

    if (isset($environmentStatus['environment_id'])) {
        $envBadgeId = (int) $environmentStatus['environment_id'];
    } elseif (isset($company)) {
        $envBadgeColumn = $envBadgeColumns[$envBadgeType] ?? 'type_environment_id';
        $envBadgeId = (int) ($company->{$envBadgeColumn} ?? 2);
    } else {
        $envBadgeId = 2;
    }
    $envBadgeIsProd = $envBadgeId === 1;

    $envBadgeMismatch = false;
    if (isset($company) && $company->software) {
        $envBadgeUrl = $company->software->{$envBadgeUrlColumns[$envBadgeType] ?? 'url'} ?? null;
        if ($envBadgeUrl) {
            $envBadgeUrlIsProd = strpos($envBadgeUrl, '-hab.dian.gov.co') === false;
            $envBadgeMismatch = $envBadgeUrlIsProd !== $envBadgeIsProd;
        }
    }
@endphp
{{-- bg-* (Bootstrap 5) + badge-* (Bootstrap 4) para cubrir ambas vistas del proyecto --}}
<span class="badge {{ $envBadgeIsProd ? 'bg-success badge-success' : 'bg-warning badge-warning' }} ms-2 ml-2" style="font-size:.7rem; vertical-align:middle;">
    {{ isset($label) ? $label.': ' : 'Ambiente: ' }}{{ $envBadgeIsProd ? 'Producción' : 'Habilitación' }}
</span>
@if($envBadgeMismatch)
    <span class="badge bg-danger badge-danger ms-1 ml-1" style="font-size:.7rem; vertical-align:middle;"
          title="La empresa está marcada en {{ $envBadgeIsProd ? 'Producción' : 'Habilitación' }} pero el software apunta a {{ $envBadgeUrlIsProd ? 'Producción' : 'Habilitación' }} ({{ $envBadgeUrl }}). Los documentos se envían a donde apunta el software.">
        <i class="fas fa-exclamation-triangle"></i> Envía a {{ $envBadgeUrlIsProd ? 'Producción' : 'Habilitación' }}
    </span>
@endif
