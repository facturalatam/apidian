@extends('layouts.app')

@push('styles')
<style>
/* Estilos para las tabs principales */
#documentTabs .nav-item {
    display: flex;
    flex: 1;
}
#documentTabs .nav-link .tab-icon {
    height: 24px;
    width: 24px;
}

/* Estilos para las sub-tabs */
.sub-tabs .nav-item {
    width: 50%;
    display: flex;
}
.sub-tabs .nav-link {
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
    background: #fff;
}

/* Estilos para el header */
.page-header h2 {
    color: #2B323D;
    font-weight: 600;
    margin-bottom: 0;
}

/* Estilos para el subtítulo */
.sub-title {
    font-size: 22px;
    font-weight: bold;
    line-height: 30px;
    text-align: left;
    color: #262944;
    margin: 20px 0;
}

/* Mejorar el hr */
hr {
    border: none;
    height: 2px;
    background: linear-gradient(90deg, transparent, #ddd, transparent);
    margin: 30px 0;
}

/* Container principal */
.RadianContainerBegin {
    background: #f8f9fa;
    border-radius: 15px;
    padding: 30px;
    margin: 20px 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
#globalLoader {
    position: fixed;
    inset: 0;
    /* Fondo anterior: #ffffff */
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(4px);
    z-index: 3000;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 14px;
    transition: opacity .35s ease;
    font-family: system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;
}
#globalLoader.fade-out {
    opacity: 0;
    pointer-events: none;
}
#globalLoader .loader-text {
    font-size: 14px;
    color: #555;
    letter-spacing: .5px;
}

/* Responsive */
@media (max-width: 768px) {
    #documentTabs .nav-link {
        font-size: 13px;
        height: 40px;
        padding: 8px 4px;
    }
    #documentTabs .nav-link .tab-icon {
        height: 20px;
        width: 20px;
    }
    .sub-tabs .nav-link {
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
    // Loader helpers
function hideGlobalLoader() {
    const el = document.getElementById('globalLoader');
    if (!el) return;
    el.classList.add('fade-out');
    setTimeout(()=> el.remove(), 400);
}
function showGlobalLoader() {
    let el = document.getElementById('globalLoader');
    if (el) {
        el.classList.remove('fade-out');
        return;
    }
    el = document.createElement('div');
    el.id = 'globalLoader';
    el.innerHTML = `
        <div class="spinner-border text-primary" style="width:3rem;height:3rem;" role="status">
            <span class="visually-hidden"></span>
        </div>
        <div class="loader-text">Cargando información...</div>
    `;
    document.body.appendChild(el);
}

// Ocultar cuando todo terminó de preparar (incluye los setTimeout internos)
window.addEventListener('load', () => {
    // Dar un pequeño margen para que se ejecuten scripts diferidos
    setTimeout(hideGlobalLoader, 600);
});

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('#documentTabs button[data-bs-toggle="tab"]').forEach(btn => {
        btn.addEventListener('show.bs.tab', () => {
            showGlobalLoader();
            // Ocultarlo luego de estabilizar la vista
            setTimeout(hideGlobalLoader, 700);
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Recuperar la pestaña activa de localStorage
    var activeTab = localStorage.getItem('documentTabActive') || '#invoice';
    var tabTrigger = document.querySelector('[data-bs-target="' + activeTab + '"]');
    if (tabTrigger) {
        var tab = new bootstrap.Tab(tabTrigger);
        tab.show();
    }

    // Guardar la pestaña activa al cambiar
    var tabButtons = document.querySelectorAll('#documentTabs button[data-bs-toggle="tab"]');
    tabButtons.forEach(function(btn) {
        btn.addEventListener('shown.bs.tab', function(event) {
            localStorage.setItem('documentTabActive', event.target.getAttribute('data-bs-target'));
        });
    });
    
    // Solucionar IDs duplicados y scripts conflictivos en los wizards
    function fixDuplicateWizardIds() {
        const wizardContainers = [
            { selector: '#payroll-production', prefix: 'payroll' },
            { selector: '#support-production', prefix: 'support' },
            { selector: '#pos-production', prefix: 'pos' }
        ];
        
        wizardContainers.forEach(function(config) {
            const container = document.querySelector(config.selector);
            if (!container) return;
            
            const prefix = config.prefix;
            
            // Actualizar wizard-steps
            const wizardSteps = container.querySelector('#wizard-steps');
            if (wizardSteps) wizardSteps.id = prefix + '-wizard-steps';
            
            // Actualizar wizard-content
            const wizardContent = container.querySelector('#wizard-content');
            if (wizardContent) wizardContent.id = prefix + '-wizard-content';
            
            // Actualizar stepper items
            const steppers = container.querySelectorAll('[id^="stepper-"]');
            steppers.forEach(function(el) {
                if (!el.id.startsWith(prefix + '-')) {
                    el.id = prefix + '-' + el.id;
                }
            });
            
            // Actualizar wizard-step divs
            const wizardStepDivs = container.querySelectorAll('[id^="wizard-step-"]');
            wizardStepDivs.forEach(function(el) {
                if (!el.id.startsWith(prefix + '-')) {
                    el.id = prefix + '-' + el.id;
                }
            });
            
            // Actualizar botones
            const btnPrev = container.querySelector('#btnPrevStep');
            if (btnPrev && !btnPrev.id.startsWith(prefix + '-')) {
                btnPrev.id = prefix + '-btnPrevStep';
            }
            
            const btnNext = container.querySelector('#btnNextStep');
            if (btnNext && !btnNext.id.startsWith(prefix + '-')) {
                btnNext.id = prefix + '-btnNextStep';
            }
            
            // Remover scripts originales para evitar conflictos
            const scripts = container.querySelectorAll('script');
            scripts.forEach(function(script) {
                script.remove();
            });
            
            // Crear la funcionalidad del wizard manualmente con los IDs correctos
            initWizard(prefix, container);
        });
    }
    
    // Función para inicializar un wizard con IDs prefijados
    function initWizard(prefix, container) {
        let currentStep = 1;
        const totalSteps = container.querySelectorAll('[id^="' + prefix + '-wizard-step-"]').length;
        
        function updateWizard() {
            for (let i = 1; i <= totalSteps; i++) {
                const stepEl = document.getElementById(prefix + '-wizard-step-' + i);
                if (stepEl) {
                    stepEl.classList.toggle('active', i === currentStep);
                    stepEl.classList.toggle('d-none', i !== currentStep);
                }
            }
            
            const btnPrev = document.getElementById(prefix + '-btnPrevStep');
            const btnNext = document.getElementById(prefix + '-btnNextStep');
            
            if (btnPrev) {
                btnPrev.style.display = currentStep === 1 ? 'none' : '';
            }
            
            if (btnNext) {
                if (currentStep === totalSteps) {
                    btnNext.innerText = 'Finalizar';
                } else {
                    btnNext.innerText = 'Siguiente';
                }
            }
        }
        
        function updateStepper() {
            for (let i = 1; i <= totalSteps; i++) {
                const item = document.getElementById(prefix + '-stepper-' + i);
                if (item) {
                    item.classList.remove('active', 'completed');
                    if (i < currentStep) {
                        item.classList.add('completed');
                    } else if (i === currentStep) {
                        item.classList.add('active');
                    }
                }
            }
        }
        
        // Event listeners
        const btnPrev = document.getElementById(prefix + '-btnPrevStep');
        const btnNext = document.getElementById(prefix + '-btnNextStep');
        
        if (btnPrev) {
            btnPrev.addEventListener('click', function() {
                if (currentStep > 1) {
                    currentStep--;
                    updateWizard();
                    updateStepper();
                }
            });
        }
        
        if (btnNext) {
            btnNext.addEventListener('click', function() {
                if (currentStep < totalSteps) {
                    currentStep++;
                    updateWizard();
                    updateStepper();
                } else if (currentStep === totalSteps) {
                    window.location.href = "{{ route('company.production.index', ['company' => $company->identification_number]) }}";
                }
            });
        }
        
        // Inicializar
        updateWizard();
        updateStepper();
    }
    
    // Función para activar el primer paso de cada wizard
    function activateFirstWizardSteps() {
        // Buscar todos los wizard-step que NO tienen d-none
        const allWizardSteps = document.querySelectorAll('.wizard-step');
        allWizardSteps.forEach(function(step) {
            // Si no tiene d-none, es el primer paso y debe tener active
            if (!step.classList.contains('d-none')) {
                step.classList.add('active');
            }
        });
        
        // Activar el primer stepper de cada wizard
        const allSteppers = document.querySelectorAll('.stepper-item[id$="-1"]');
        allSteppers.forEach(function(stepper) {
            if (!stepper.classList.contains('active') && !stepper.classList.contains('completed')) {
                stepper.classList.add('active');
            }
        });
    }
    
    // Ejecutar al cargar
    setTimeout(function() {
        fixDuplicateWizardIds();
        activateFirstWizardSteps();
    }, 100);
    
    // Ejecutar cuando se cambie de pestaña
    document.querySelectorAll('#documentTabs button[data-bs-toggle="tab"]').forEach(function(btn) {
        btn.addEventListener('shown.bs.tab', function() {
            setTimeout(function() {
                fixDuplicateWizardIds();
                activateFirstWizardSteps();
            }, 200);
        });
    });
});
</script>
@endpush

@section('content')
{{-- <header class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>
            Seleccione el tipo de documento
        </h2>
        <br>
        <span class="text-muted">{{ $company->user->name }} - {{ $company->identification_number }}</span>
    </div>
    <div class="right-wrapper text-right mt-auto">
        <a href="{{ route('home') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-2"></i> Volverss
        </a>
    </div>
</header> --}}
<div id="globalLoader">
    <div class="spinner-border text-primary" style="width:3rem;height:3rem;" role="status">
        <span class="visually-hidden"></span>
    </div>
    <div class="loader-text">Cargando información...</div>
</div>

<div class="container-fluid px-0 mt-3">
    <ul class="nav nav-tabs nav-justified mb-0" id="documentTabs" role="tablist" style="background: #fff;">
        <li class="nav-item d-flex" role="presentation">
            <button class="nav-link w-100 d-flex justify-content-center align-items-center px-3 py-2 fw-bold"
                id="invoice-tab" data-bs-toggle="tab" data-bs-target="#invoice" type="button" role="tab"
                aria-controls="invoice" aria-selected="true">
                <img src="{{ asset('production/factura-electronica-icon.svg') }}" alt="Factura electrónica" class="tab-icon me-2">
                Factura electrónica
            </button>
        </li>
        <li class="nav-item d-flex" role="presentation">
            <button class="nav-link w-100 d-flex justify-content-center align-items-center px-3 py-2 fw-bold disabled"
                id="payroll-tab" type="button" role="tab"
                aria-controls="payroll" aria-selected="false" disabled>
                <img src="{{ asset('production/nomina-electronica-icon.svg') }}" alt="Nómina electrónica" class="tab-icon me-2">
                Nómina electrónica
                <span class="badge ms-2" style="background-color:#6f42c1;color:#fff;font-size:10px;font-weight:600;letter-spacing:.3px;">Enterprise</span>
            </button>
        </li>
        <li class="nav-item d-flex" role="presentation">
            <button class="nav-link w-100 d-flex justify-content-center align-items-center px-3 py-2 fw-bold"
                id="support-tab" data-bs-toggle="tab" data-bs-target="#support" type="button" role="tab"
                aria-controls="support" aria-selected="false">
                <img src="{{ asset('production/documento-soporte-icon.svg') }}" alt="Documento soporte" class="tab-icon me-2">
                Documento soporte
            </button>
        </li>
        <li class="nav-item d-flex" role="presentation">
            <button class="nav-link w-100 d-flex justify-content-center align-items-center px-3 py-2 fw-bold"
                id="event-tab" data-bs-toggle="tab" data-bs-target="#event" type="button" role="tab"
                aria-controls="event" aria-selected="false">
                <img src="{{ asset('production/eventos-radian-icon.svg') }}" alt="Eventos RADIAN" class="tab-icon me-2">
                Eventos RADIAN
            </button>
        </li>
        <li class="nav-item d-flex" role="presentation">
            <button class="nav-link w-100 d-flex justify-content-center align-items-center px-3 py-2 fw-bold disabled"
                id="pos-tab" type="button" role="tab"
                aria-controls="pos" aria-selected="false" disabled>
                <img src="{{ asset('production/documentos-equivalentes-icon.svg') }}" alt="Documentos equivalentes" class="tab-icon me-2">
                Documentos equivalentes
                <span class="badge ms-2" style="background-color:#6f42c1;color:#fff;font-size:10px;font-weight:600;letter-spacing:.3px;">Enterprise</span>
            </button>
        </li>
    </ul>
    <div class="tab-content" id="documentTabsContent">
        <!-- Factura Electrónica -->
        <div class="tab-pane fade mt-2" id="invoice" role="tabpanel" aria-labelledby="invoice-tab">
            <header class="page-header d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2>{{ $company->user->name }} - {{ $company->identification_number }}</h2>
                    <br>
                    <span class="text-muted">Factura Electrónica</span>
                </div>
                <div class="mt-auto pb-1">
                    <a href="{{ route('home') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-2"></i> Volver
                    </a>
                </div>
            </header>
            <ul class="nav nav-tabs nav-justified mb-0 sub-tabs" id="invoiceSubTabs" role="tablist" style="background: #fff;">
                <li class="nav-item d-flex" role="presentation">
                    <button class="nav-link w-100 d-flex justify-content-center align-items-center px-3 py-2 fw-bold active"
                        id="invoice-list-tab" data-bs-toggle="tab" data-bs-target="#invoice-list" type="button" role="tab"
                        aria-controls="invoice-list" aria-selected="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-list"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 6l11 0" /><path d="M9 12l11 0" /><path d="M9 18l11 0" /><path d="M5 6l0 .01" /><path d="M5 12l0 .01" /><path d="M5 18l0 .01" /></svg>
                        Listado
                    </button>
                </li>
                <li class="nav-item d-flex" role="presentation">
                    <button class="nav-link w-100 d-flex justify-content-center align-items-center px-3 py-2 fw-bold"
                        id="invoice-production-tab" data-bs-toggle="tab" data-bs-target="#invoice-production" type="button" role="tab"
                        aria-controls="invoice-production" aria-selected="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-settings-cog"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12.003 21c-.732 .001 -1.465 -.438 -1.678 -1.317a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c.886 .215 1.325 .957 1.318 1.694" /><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /><path d="M19.001 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M19.001 15.5v1.5" /><path d="M19.001 21v1.5" /><path d="M22.032 17.25l-1.299 .75" /><path d="M17.27 20l-1.3 .75" /><path d="M15.97 17.25l1.3 .75" /><path d="M20.733 20l1.3 .75" /></svg>
                        Paso a Producción
                    </button>
                </li>
                <li class="nav-item d-flex" role="presentation">
                    <button class="nav-link w-100 d-flex justify-content-center align-items-center px-3 py-2 fw-bold"
                        id="invoice-resolutions-tab" data-bs-toggle="tab" data-bs-target="#invoice-resolutions" type="button" role="tab"
                        aria-controls="invoice-resolutions" aria-selected="false">
                        <i class="fas fa-file-invoice me-2"></i>
                        Resoluciones
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="invoiceSubTabsContent">
                <div class="tab-pane fade show active" id="invoice-list" role="tabpanel" aria-labelledby="invoice-list-tab">
                    @include('company.documents', [
                        'documents' => $invoiceData['documents'] ?? collect(),
                        'resolution_credit_notes' => $invoiceData['resolution_credit_notes'] ?? collect(),
                        'company' => $company,
                        'company_idnumber' => $company->identification_number,
                        'token_company' => $company->user->api_token ?? null,
                        'type' => 'invoice'
                    ])
                </div>
                <div class="tab-pane fade" id="invoice-production" role="tabpanel" aria-labelledby="invoice-production-tab">
                    @include('company.production.invoice.index', [
                        'company' => $company,
                        'environmentStatus' => $environmentStatuses['invoice'],
                        'typeDocuments' => $typeDocuments
                    ])
                </div>
                <div class="tab-pane fade" id="invoice-resolutions" role="tabpanel" aria-labelledby="invoice-resolutions-tab">
                    @include('company.production._resolutions_tab', [
                        'company' => $company,
                        'type' => 'invoice',
                        'resolutionsHab' => $invoiceData['resolutionsHab'] ?? collect(),
                        'resolutionsProd' => $invoiceData['resolutionsProd'] ?? collect(),
                        'resolutionTypeDocuments' => $invoiceData['resolutionTypeDocuments'] ?? collect(),
                    ])
                </div>
            </div>
        </div>

        <!-- Documento Soporte -->
        <div class="tab-pane fade mt-2" id="support" role="tabpanel" aria-labelledby="support-tab">
            <header class="page-header d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2>{{ $company->user->name }} - {{ $company->identification_number }}</h2>
                    <br>
                    <span class="text-muted">Documento Soporte</span>
                </div>
                <div class="mt-auto pb-1">
                    <a href="{{ route('home') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-2"></i> Volver
                    </a>
                </div>
            </header>
            <ul class="nav nav-tabs nav-justified mb-0 sub-tabs" id="supportSubTabs" role="tablist" style="background: #fff;">
                <li class="nav-item d-flex" role="presentation">
                    <button class="nav-link w-100 d-flex justify-content-center align-items-center px-3 py-2 fw-bold active"
                        id="support-list-tab" data-bs-toggle="tab" data-bs-target="#support-list" type="button" role="tab"
                        aria-controls="support-list" aria-selected="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-list"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 6l11 0" /><path d="M9 12l11 0" /><path d="M9 18l11 0" /><path d="M5 6l0 .01" /><path d="M5 12l0 .01" /><path d="M5 18l0 .01" /></svg>
                        Listado
                    </button>
                </li>
                <li class="nav-item d-flex" role="presentation">
                    <button class="nav-link w-100 d-flex justify-content-center align-items-center px-3 py-2 fw-bold"
                        id="support-production-tab" data-bs-toggle="tab" data-bs-target="#support-production" type="button" role="tab"
                        aria-controls="support-production" aria-selected="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-settings-cog"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12.003 21c-.732 .001 -1.465 -.438 -1.678 -1.317a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c.886 .215 1.325 .957 1.318 1.694" /><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /><path d="M19.001 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M19.001 15.5v1.5" /><path d="M19.001 21v1.5" /><path d="M22.032 17.25l-1.299 .75" /><path d="M17.27 20l-1.3 .75" /><path d="M15.97 17.25l1.3 .75" /><path d="M20.733 20l1.3 .75" /></svg>
                        Paso a Producción
                    </button>
                </li>
                <li class="nav-item d-flex" role="presentation">
                    <button class="nav-link w-100 d-flex justify-content-center align-items-center px-3 py-2 fw-bold"
                        id="support-resolutions-tab" data-bs-toggle="tab" data-bs-target="#support-resolutions" type="button" role="tab"
                        aria-controls="support-resolutions" aria-selected="false">
                        <i class="fas fa-file-invoice me-2"></i>
                        Resoluciones
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="supportSubTabsContent">
                <div class="tab-pane fade show active" id="support-list" role="tabpanel" aria-labelledby="support-list-tab">
                    @include('company.documents', [
                        'documents' => $supportData['documents'] ?? collect(),
                        'resolution_credit_notes' => collect(),
                        'company' => $company,
                        'company_idnumber' => $company->identification_number,
                        'token_company' => $company->token_company ?? null,
                        'type' => 'support'
                    ])
                </div>
                <div class="tab-pane fade" id="support-production" role="tabpanel" aria-labelledby="support-production-tab">
                    @include('company.production.support.index', [
                        'company' => $company,
                        'environmentStatus' => $environmentStatuses['support'],
                        'typeDocuments' => $typeDocuments
                    ])
                </div>
                <div class="tab-pane fade" id="support-resolutions" role="tabpanel" aria-labelledby="support-resolutions-tab">
                    @include('company.production._resolutions_tab', [
                        'company' => $company,
                        'type' => 'support',
                        'resolutionsHab' => $supportData['resolutionsHab'] ?? collect(),
                        'resolutionsProd' => $supportData['resolutionsProd'] ?? collect(),
                        'resolutionTypeDocuments' => $supportData['resolutionTypeDocuments'] ?? collect(),
                    ])
                </div>
            </div>
        </div>

        <!-- Eventos RADIAN -->
        <div class="tab-pane fade mt-2" id="event" role="tabpanel" aria-labelledby="event-tab">
            <header class="page-header d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2>{{ $company->user->name }} - {{ $company->identification_number }}</h2>
                    <br>
                    <span class="text-muted">Eventos RADIAN</span>
                </div>
                <div class="mt-auto pb-1">
                    <a href="{{ route('home') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-2"></i> Volver
                    </a>
                </div>
            </header>
            <ul class="nav nav-tabs nav-justified mb-0 sub-tabs" id="eventSubTabs" role="tablist" style="background: #fff;">
                <li class="nav-item d-flex" role="presentation">
                    <button class="nav-link w-100 d-flex justify-content-center align-items-center px-3 py-2 fw-bold active"
                        id="event-list-tab" data-bs-toggle="tab" data-bs-target="#event-list" type="button" role="tab"
                        aria-controls="event-list" aria-selected="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-list"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 6l11 0" /><path d="M9 12l11 0" /><path d="M9 18l11 0" /><path d="M5 6l0 .01" /><path d="M5 12l0 .01" /><path d="M5 18l0 .01" /></svg>
                        Listado
                    </button>
                </li>
                <li class="nav-item d-flex" role="presentation">
                    <button class="nav-link w-100 d-flex justify-content-center align-items-center px-3 py-2 fw-bold"
                        id="event-production-tab" data-bs-toggle="tab" data-bs-target="#event-production" type="button" role="tab"
                        aria-controls="event-production" aria-selected="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-settings-cog"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12.003 21c-.732 .001 -1.465 -.438 -1.678 -1.317a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c.886 .215 1.325 .957 1.318 1.694" /><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /><path d="M19.001 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M19.001 15.5v1.5" /><path d="M19.001 21v1.5" /><path d="M22.032 17.25l-1.299 .75" /><path d="M17.27 20l-1.3 .75" /><path d="M15.97 17.25l1.3 .75" /><path d="M20.733 20l1.3 .75" /></svg>
                        Paso a Producción
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="eventSubTabsContent">
                <div class="tab-pane fade show active" id="event-list" role="tabpanel" aria-labelledby="event-list-tab">
                    @include('company.events', [
                        'documents' => $eventData['documents'] ?? collect(),
                        'company' => $company,
                        'company_idnumber' => $company->identification_number,
                        'type' => 'event'
                    ])
                </div>
                <div class="tab-pane fade" id="event-production" role="tabpanel" aria-labelledby="event-production-tab">
                    @include('company.production.event.index', [
                        'company' => $company,
                        'environmentStatus' => $environmentStatuses['event']
                    ])
                </div>
            </div>
        </div>

    </div>
</div>
@include('company.modals', [
    'resolution_credit_notes' => $invoiceData['resolution_credit_notes'] ?? collect()
])
@endsection