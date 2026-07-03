@extends('layouts.app')

@section('content')
@php
    // El tab "Resoluciones" aplica a todos los tipos excepto 'event' (radian).
    $showResolutionsTab = isset($type) && $type !== 'event';
@endphp
<div class="container-fluid px-0 mt-2">
    <ul class="nav nav-tabs nav-justified mb-0" id="invoiceTabs" role="tablist" style="background: #fff;">
        <li class="nav-item d-flex" role="presentation">
            <button class="nav-link w-100 d-flex justify-content-center align-items-center px-3 py-2 fw-bold"
                id="list-tab" data-bs-toggle="tab" data-bs-target="#list" type="button" role="tab"
                aria-controls="list" aria-selected="true">
                <i class="fas fa-list me-2"></i>
                Listado
            </button>
        </li>
        <li class="nav-item d-flex" role="presentation">
            <button class="nav-link w-100 d-flex justify-content-center align-items-center px-3 py-2 fw-bold"
                id="production-tab" data-bs-toggle="tab" data-bs-target="#production" type="button" role="tab"
                aria-controls="production" aria-selected="false">
                <i class="fas fa-cogs me-2"></i>
                Paso a Producción
            </button>
        </li>
        @if ($showResolutionsTab)
        <li class="nav-item d-flex" role="presentation">
            <button class="nav-link w-100 d-flex justify-content-center align-items-center px-3 py-2 fw-bold"
                id="resolutions-tab" data-bs-toggle="tab" data-bs-target="#resolutions" type="button" role="tab"
                aria-controls="resolutions" aria-selected="false">
                <i class="fas fa-file-invoice me-2"></i>
                Resoluciones
            </button>
        </li>
        @endif
    </ul>
    <div class="tab-content" id="invoiceTabsContent">
        <div class="tab-pane fade" id="list" role="tabpanel" aria-labelledby="list-tab">
            @include($listView, [
                'documents' => $documents,
                'resolution_credit_notes' => $resolution_credit_notes,
                'company' => $company,
                'company_idnumber' => $company_idnumber,
                'token_company' => $token_company,
                'type' => $type
            ])
        </div>
        <div class="tab-pane fade" id="production" role="tabpanel" aria-labelledby="production-tab">
            @include($indexView, [
                'company' => $company,
                'environmentStatus' => $environmentStatus
            ])
        </div>
        @if ($showResolutionsTab)
        <div class="tab-pane fade" id="resolutions" role="tabpanel" aria-labelledby="resolutions-tab">
            @include('company.production._resolutions_tab', [
                'company' => $company,
                'type' => $type,
                'resolutionsHab' => $resolutionsHab ?? collect(),
                'resolutionsProd' => $resolutionsProd ?? collect(),
                'resolutionTypeDocuments' => $resolutionTypeDocuments ?? collect(),
            ])
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.nav-tabs .nav-item {
    flex: 1 1 0;
    display: flex;
}
.nav-tabs .nav-link {
    border-radius: 0;
    font-size: 17px;
    text-align: center;
    width: 100%;
    height: 48px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 6px;
}
.tab-content {
    min-height: 350px;
    padding: 0;
}
@media (max-width: 768px) {
    .nav-tabs .nav-link {
        font-size: 15px;
        height: 40px;
        padding: 8px 4px;
    }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Recuperar la pestaña activa de localStorage
    var activeTab = localStorage.getItem('invoiceTabActive') || '#list';
    var tabTrigger = document.querySelector('[data-bs-target="' + activeTab + '"]');
    if (tabTrigger) {
        var tab = new bootstrap.Tab(tabTrigger);
        tab.show();
    }

    // Guardar la pestaña activa al cambiar
    var tabButtons = document.querySelectorAll('#invoiceTabs button[data-bs-toggle="tab"]');
    tabButtons.forEach(function(btn) {
        btn.addEventListener('shown.bs.tab', function(event) {
            localStorage.setItem('invoiceTabActive', event.target.getAttribute('data-bs-target'));
        });
    });
});
</script>
@endpush