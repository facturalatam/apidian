@extends('layouts.app')
@section('content')
<header class="page-header d-flex justify-content-between align-items-center">    
    <div>
        <h2>
            Listado de Empresas    
        </h2>
    </div>
    <div class="right-wrapper text-end mt-auto pb-1">
        @if(auth()->user() && method_exists(auth()->user(), 'isPlatformAdmin') && auth()->user()->isPlatformAdmin())
            <a href="{{ route('configuration_admin') }}" class="btn btn-primary btn-sm text-white mr-2">
                <i class="fas fa-plus"></i>
                Nueva empresa
            </a>
        @endif
    </div>
</header>
<div class="card mb-3">
    <div class="card-body">
        <div class="row align-items-end">
            <!-- Filtro primero -->
            <div class="col-md-4">
                <label><strong>Filtrar por</strong></label>
                <select id="filter-type" class="form-control" clearable>
                    <option value="nit">NIT</option>
                    <option value="email">Correo</option>
                    <option value="name">Nombre</option>
                </select>
            </div>
            <!-- Barra de búsqueda a la derecha -->
            <div class="col-md-8">
                <label><strong>Búsqueda</strong></label>
                <input type="text" id="filter-text" class="form-control" placeholder="Escribe para buscar...">
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Documentos generados</th>
                    <th>Nombre de usuario</th>
                    <th>Correo de usuario</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($companies as $row)
                    <tr class="table-light">
                        <td>{{ $row->id }}</td>
                        <td>{{ $row->identification_number }}-{{ $row->dv }}</td>
                        <td>{{ $row->total_documents }}</td>
                        <td>{{ $row->user->name }}</td>
                        <td>{{ $row->user->email }}</td>
                        <td class="text-right">
                            <el-dropdown trigger="click">
                                <el-button size="small" class="btn-dropdown-toggle">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-dots">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M5 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/>
                                        <path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/>
                                        <path d="M19 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/>
                                    </svg>
                                </el-button>
                                <el-dropdown-menu slot="dropdown">
                                    <el-dropdown-item class="d-flex align-items-center"
                                        @click.native="openEditModal({{ $row->id }}, '{{ $row->identification_number }}', '{{ $row->dv }}', {{ $row->type_document_identification_id }}, {{ $row->type_regime_id }}, {{ $row->type_liability_id }}, {{ $row->municipality_id }}, '{{ $row->merchant_registration }}', '{{ $row->address }}', '{{ $row->phone }}', '{{ $row->user->api_token }}')">
                                        <span class="dropdown-icon-left">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-edit text-muted">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"/>
                                                <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"/>
                                                <path d="M16 5l3 3"/>
                                            </svg>
                                        </span>
                                        <span class="ml-2">Editar Empresa</span>
                                    </el-dropdown-item>
                                    <el-dropdown-item divided></el-dropdown-item>
                                    <el-dropdown-item class="d-flex align-items-center" onclick="redirectTo('{{ route('company.production.index', $row->identification_number) }}')">
                                            <span class="dropdown-icon-left">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-file-description text-muted">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                    <path d="M14 3v4a1 1 0 0 0 1 1h4"/>
                                                    <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/>
                                                    <path d="M9 17h6"/>
                                                    <path d="M9 13h6"/>
                                                </svg>
                                            </span>
                                            <span class="ml-2">Documentos Electrónicos</span>
                                    </el-dropdown-item >
                                    <el-dropdown-item class="d-flex align-items-center">
                                            <span class="dropdown-icon-left">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-users-group text-muted">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                    <path d="M10 13a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/>
                                                    <path d="M8 21v-1a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v1"/>
                                                    <path d="M15 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/>
                                                    <path d="M17 10h2a2 2 0 0 1 2 2v1"/>
                                                    <path d="M5 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/>
                                                    <path d="M3 13v-1a2 2 0 0 1 2 -2h2"/>
                                                </svg>
                                            </span>
                                            <span class="ml-2">Usuarios</span>
                                            <span class="badge ml-2" style="background-color:#6f42c1;color:#fff;font-size:10px;font-weight:600;letter-spacing:.3px;">Enterprise</span>
                                    </el-dropdown-item>
                                    <el-dropdown-item class="d-flex align-items-center" onclick="redirectTo('{{ route('company.email.index', $row->id) }}')">
                                            <span class="dropdown-icon-left">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-mail text-muted">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                    <path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z"/>
                                                    <path d="M3 7l9 6l9 -6"/>
                                                </svg>
                                            </span>
                                            <span class="ml-2">Configurar Correo</span>
                                    </el-dropdown-item>
                                    <el-dropdown-item class="d-flex align-items-center">
                                        <span class="dropdown-icon-left">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-device-mobile text-muted">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M6 5a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v14a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2v-14z"/>
                                                <path d="M11 4h2"/>
                                                <path d="M12 17v.01"/>
                                            </svg>
                                        </span>
                                        <span class="ml-2">Acceso a la App</span>
                                        <span class="badge ml-2" style="background-color:#6f42c1;color:#fff;font-size:10px;font-weight:600;letter-spacing:.3px;">Enterprise</span>
                                    </el-dropdown-item>
                                    <el-dropdown-item divided></el-dropdown-item>
                                    <el-dropdown-item class="d-flex align-items-center">
                                        <span class="dropdown-icon-left">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-server text-muted">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M3 4m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z"/>
                                                <path d="M3 12m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z"/>
                                                <path d="M7 8l0 .01"/>
                                                <path d="M7 16l0 .01"/>
                                            </svg>
                                        </span>
                                        <span class="ml-2">Almacenamiento S3</span>
                                        <span class="badge ml-2" style="background-color:#6f42c1;color:#fff;font-size:10px;font-weight:600;letter-spacing:.3px;">Enterprise</span>
                                    </el-dropdown-item>
                                </el-dropdown-menu>
                            </el-dropdown>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="text-center">
                        <span class="text-muted">Cantidad de empresas registradas: {{ $companies->count() }}</span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Modal de Edición de Empresa -->
<div class="modal fade" id="editCompanyModal" tabindex="-1" role="dialog" aria-labelledby="editCompanyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCompanyModalLabel">Editar Empresa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" id="companyTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="company-info-tab" data-toggle="tab" href="#company-info" role="tab" aria-controls="company-info" aria-selected="true">
                            <i class="fas fa-building mr-2"></i>Información de la Empresa
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="company-logo-tab" data-toggle="tab" href="#company-logo" role="tab" aria-controls="company-logo" aria-selected="false">
                            <i class="fas fa-image mr-2"></i>Logo de la Empresa
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="company-token-tab" data-toggle="tab" href="#company-token" role="tab" aria-controls="company-token" aria-selected="false">
                            <i class="fas fa-key mr-2"></i>API Token
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="company-certificate-tab" data-toggle="tab" href="#company-certificate" role="tab" aria-controls="company-certificate" aria-selected="false">
                            <i class="fas fa-shield-alt mr-2"></i>Certificado
                        </a>
                    </li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content" id="companyTabContent">
                    <!-- Tab de Información de la Empresa -->
                    <div class="tab-pane fade show active" id="company-info" role="tabpanel" aria-labelledby="company-info-tab">
                        <form id="editCompanyForm" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="form-group col-6">
                                    <label for="identification_number">Número de identificación</label>
                                    <input type="text" name="identification_number" id="identification_number" class="form-control" required>
                                    <div class="invalid-feedback"></div>
                                    <small class="form-text text-muted">Solo números. El DV se calculará automáticamente.</small>
                                </div>

                                <div class="form-group col-6">
                                    <label for="dv">Dígito de verificación</label>
                                    <input type="text" name="dv" id="dv" class="form-control" readonly style="background-color: #f8f9fa;">
                                    <div class="invalid-feedback"></div>
                                    <small class="form-text text-muted">Se calcula automáticamente</small>
                                </div>

                                <div class="form-group col-6">
                                    <label for="type_document_identification_id">Tipo de documento</label>
                                    <select name="type_document_identification_id" id="type_document_identification_id" class="form-control" required>
                                        @foreach ($type_document_identifications as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="form-group col-6">
                                    <label for="type_regime_id">Tipo de régimen</label>
                                    <select name="type_regime_id" id="type_regime_id" class="form-control" required>
                                        @foreach ($type_regimes as $regime)
                                            <option value="{{ $regime->id }}">{{ $regime->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="form-group col-6">
                                    <label for="type_liability_id">Tipo de responsabilidad</label>
                                    <select name="type_liability_id" id="type_liability_id" class="form-control" required>
                                        @foreach ($type_liabilities as $liability)
                                            <option value="{{ $liability->id }}">{{ $liability->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="form-group col-6">
                                    <label for="municipality_id">Municipio</label>
                                    <select name="municipality_id" id="municipality_id" class="form-control" required>
                                        @foreach ($municipalities as $municipality)
                                            <option value="{{ $municipality->id }}">{{ $municipality->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="form-group col-6">
                                    <label for="merchant_registration">Matrícula mercantil</label>
                                    <input type="text" name="merchant_registration" id="merchant_registration" class="form-control">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="form-group col-6">
                                    <label for="phone">Teléfono</label>
                                    <input type="text" name="phone" id="phone" class="form-control">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="form-group col-12">
                                    <label for="address">Dirección</label>
                                    <input type="text" name="address" id="address" class="form-control">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Tab de Logo de la Empresa -->
                    <div class="tab-pane fade" id="company-logo" role="tabpanel" aria-labelledby="company-logo-tab">
                        <form id="editLogoForm" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="logo-upload">Logo de la Empresa</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="logo-upload" accept="image/jpeg">
                                            <label class="custom-file-label" for="logo-upload">Seleccionar archivo...</label>
                                        </div>
                                        <div class="invalid-feedback"></div>
                                        <small class="form-text text-muted">Formato permitido: JPG únicamente. Tamaño máximo: 2MB</small>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div id="logo-preview" class="text-center" style="display: none;">
                                        <h6>Vista previa:</h6>
                                        <img id="logo-preview-img" src="" alt="Vista previa del logo" class="img-thumbnail" style="max-width: 300px; max-height: 200px;">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- Tab de API Token -->
                    <div class="tab-pane fade" id="company-token" role="tabpanel" aria-labelledby="company-token-tab">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="company-token-input">Token de la Empresa</label>
                                    <div class="input-group">
                                        <input type="text" id="company-token-input" class="form-control" readonly placeholder="Sin token disponible">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" id="copyTokenBtn" title="Copiar token">
                                                <i class="far fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Este token es usado para llamadas a la API.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab de Certificado -->
                    <div class="tab-pane fade" id="company-certificate" role="tabpanel" aria-labelledby="company-certificate-tab">
                        <div class="row mt-3">
                            <div class="col-12" id="cert-current-info">
                                <!-- Información del certificado actual cargada por JS -->
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cert-password-input">Contraseña del certificado</label>
                                    <input type="password" id="cert-password-input" class="form-control" placeholder="Ingresa la contraseña">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Archivo de certificado (.pfx / .p12)</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="cert-file-input" accept=".pfx,.p12">
                                        <label class="custom-file-label" for="cert-file-input">Seleccionar archivo...</label>
                                    </div>
                                    <small class="form-text text-muted">Formatos permitidos: .pfx, .p12</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" id="saveCompanyBtn" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Información
                </button>
                <button type="button" id="saveLogoBtn" class="btn btn-success" style="display: none;">
                    <i class="fas fa-image"></i> Actualizar Logo
                </button>
                <button type="button" id="saveCertificateBtn" class="btn btn-warning" style="display: none;">
                    <i class="fas fa-shield-alt"></i> Actualizar Certificado
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.btn-lg:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(61, 220, 132, 0.4) !important;
}

.text-danger {
    color: #dc3545 !important;
}

.form-group {
    margin-bottom: 1rem;
}

.invalid-feedback {
    display: none;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875em;
    color: #dc3545;
}

.form-control.is-invalid {
    border-color: #dc3545;
}

.form-control.is-invalid:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.is-invalid .invalid-feedback {
    display: block;
}

.nav-tabs .nav-link {
    color: #495057;
    border: 1px solid transparent;
    border-top-left-radius: .25rem;
    border-top-right-radius: .25rem;
}

.nav-tabs .nav-link:hover {
    border-color: #e9ecef #e9ecef #dee2e6;
}

.nav-tabs .nav-link.active {
    color: #495057;
    background-color: #fff;
    border-color: #dee2e6 #dee2e6 #fff;
}

.tab-content {
    border: 1px solid #dee2e6;
    border-top: none;
    padding: 1rem;
    border-bottom-left-radius: .25rem;
    border-bottom-right-radius: .25rem;
}

.custom-file-label::after {
    content: "Buscar";
}

/* Estilos para el dropdown de Element UI */
.el-dropdown-menu__item a {
    text-decoration: none;
    color: #606266;
    display: flex;
    align-items: center;
    width: 100%;
}

.el-dropdown-menu__item a:hover {
    color: #409EFF;
}

.el-dropdown-menu__item a i {
    margin-right: 8px;
}
</style>

@endsection

@push('scripts')
<script>
window.redirectTo = function(url) {
    window.location.href = url;
}
document.addEventListener("DOMContentLoaded", function () {

    const filterText = document.getElementById("filter-text");
    const filterType = document.getElementById("filter-type");

    const tableRows = document.querySelectorAll("table tbody tr");

    function applyFilter() {
        const value = filterText.value.toLowerCase().trim();
        const type = filterType.value;

        tableRows.forEach(row => {
            let columnText = "";

            if (type === "nit") {
                columnText = row.children[1].textContent.toLowerCase(); // NIT
            } else if (type === "email") {
                columnText = row.children[4].textContent.toLowerCase(); // email
            } else if (type === "name") {
                columnText = row.children[3].textContent.toLowerCase(); // nombre
            }

            if (columnText.includes(value)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    filterText.addEventListener("keyup", applyFilter);
    filterType.addEventListener("change", applyFilter);
});
// Datos de certificado por empresa (generados server-side)
window.companyCertificateData = {
    @foreach($companies as $row)
    {{ $row->id }}: {
        name: '{{ $row->certificate ? $row->certificate->name : '' }}',
        expiry: '{{ $row->certificate && $row->certificate->expiration_date ? \Carbon\Carbon::parse($row->certificate->expiration_date)->format('d/m/Y') : '' }}'
    }{{ !$loop->last ? ',' : '' }}
    @endforeach
};

$(document).ready(function () {
    let currentCompanyId = null;
    let currentApiToken = null;

    // Limpiar formulario cuando se abre el modal
    $('#editCompanyModal').on('show.bs.modal', function () {
        clearFormErrors();
        clearLogoForm();
        clearCertificateForm();
        // Asegurar que el primer tab esté activo
        $('#company-info-tab').tab('show');
        // Cargar token en el campo correspondiente si está disponible
        try {
            var tokenVal = window.currentApiToken || currentApiToken || '';
            $('#company-token-input').val(tokenVal);
        } catch (e) {
            // ignore
        }
    });

    // Manejar cambio de tabs
    $('#companyTabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href");

        if (target === '#company-info') {
            $('#saveCompanyBtn').show();
            $('#saveLogoBtn').hide();
            $('#saveCertificateBtn').hide();
        } else if (target === '#company-logo') {
            $('#saveCompanyBtn').hide();
            $('#saveLogoBtn').show();
            $('#saveCertificateBtn').hide();
        } else if (target === '#company-token') {
            $('#saveCompanyBtn').hide();
            $('#saveLogoBtn').hide();
            $('#saveCertificateBtn').hide();
        } else if (target === '#company-certificate') {
            $('#saveCompanyBtn').hide();
            $('#saveLogoBtn').hide();
            $('#saveCertificateBtn').show();
            loadCertificateDisplay();
        }
    });

    // Manejar subida de archivo de logo
    $('#logo-upload').on('change', function(e) {
        const file = e.target.files[0];
        const label = $(this).next('.custom-file-label');

        if (file) {
            // Validar tipo de archivo
            const allowedTypes = ['image/jpg', 'image/jpeg'];
            if (!allowedTypes.includes(file.type)) {
                new PNotify({
                    text: 'Por favor selecciona un archivo JPG válido',
                    type: 'error',
                    addclass: 'notification-danger',
                    delay: 4000
                });
                $(this).val('');
                label.text('Seleccionar archivo...');
                $('#logo-preview').hide();
                return;
            }

            // Validar tamaño (2MB máximo)
            if (file.size > 2 * 1024 * 1024) {
                new PNotify({
                    text: 'El archivo es demasiado grande. Tamaño máximo: 2MB',
                    type: 'error',
                    addclass: 'notification-danger',
                    delay: 4000
                });
                $(this).val('');
                label.text('Seleccionar archivo...');
                $('#logo-preview').hide();
                return;
            }

            // Actualizar label
            label.text(file.name);

            // Mostrar vista previa
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#logo-preview-img').attr('src', e.target.result);
                $('#logo-preview').show();
            };
            reader.readAsDataURL(file);
        } else {
            label.text('Seleccionar archivo...');
            $('#logo-preview').hide();
        }
    });

    // Manejar envío del formulario de información de empresa
    $('#saveCompanyBtn').on('click', function(e) {
        e.preventDefault();

        const submitBtn = $(this);
        const originalText = submitBtn.html();

        // Deshabilitar botón y mostrar estado de carga
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        // Limpiar errores previos
        clearFormErrors();

        // Preparar datos del formulario
        const formData = $('#editCompanyForm').serialize();

        // Realizar petición AJAX
        $.ajax({
            url: `/companies/${window.currentCompanyId || currentCompanyId}`,
            method: 'PUT',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Mostrar notificación de éxito
                    new PNotify({
                        text: response.message || 'Empresa actualizada exitosamente',
                        type: 'success',
                        addclass: 'notification-success',
                        delay: 3000
                    });

                    // Cerrar modal
                    $('#editCompanyModal').modal('hide');

                    // Recargar página para mostrar los cambios
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    // Mostrar notificación de error
                    new PNotify({
                        text: response.message || 'Error al actualizar la empresa',
                        type: 'error',
                        addclass: 'notification-danger',
                        delay: 5000
                    });
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Errores de validación
                    const errors = xhr.responseJSON.errors;
                    displayFormErrors(errors);

                    // Cambiar al tab de información si hay errores
                    $('#company-info-tab').tab('show');

                    new PNotify({
                        text: 'Por favor corrige los errores en el formulario',
                        type: 'error',
                        addclass: 'notification-danger',
                        delay: 5000
                    });
                } else {
                    // Otros errores
                    const message = xhr.responseJSON?.message || 'Error interno del servidor';
                    new PNotify({
                        text: message,
                        type: 'error',
                        addclass: 'notification-danger',
                        delay: 5000
                    });
                }
            },
            complete: function() {
                // Restaurar botón
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Manejar envío del logo
    $('#saveLogoBtn').on('click', function(e) {
        e.preventDefault();

        const file = $('#logo-upload')[0].files[0];
        if (!file) {
            new PNotify({
                text: 'Por favor selecciona una imagen',
                type: 'error',
                addclass: 'notification-danger',
                delay: 3000
            });
            return;
        }

        const submitBtn = $(this);
        const originalText = submitBtn.html();

        // Deshabilitar botón y mostrar estado de carga
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Subiendo logo...');

        // Convertir archivo a base64
        const reader = new FileReader();
        reader.onload = function(e) {
            const base64Data = e.target.result.split(',')[1]; // Remover el prefijo data:image/...;base64,

            // Realizar petición AJAX al API
            $.ajax({
                url: '/api/ubl2.1/config/logo',
                method: 'PUT',
                headers: {
                    'Authorization': 'Bearer ' + (window.currentApiToken || currentApiToken),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                data: JSON.stringify({
                    logo: base64Data
                }),
                success: function(response) {
                    new PNotify({
                        text: 'Logo actualizado exitosamente',
                        type: 'success',
                        addclass: 'notification-success',
                        delay: 3000
                    });

                    // Limpiar el formulario de logo
                    clearLogoForm();
                },
                error: function(xhr) {
                    let message = 'Error al actualizar el logo';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }

                    new PNotify({
                        text: message,
                        type: 'error',
                        addclass: 'notification-danger',
                        delay: 5000
                    });
                },
                complete: function() {
                    // Restaurar botón
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        };
        reader.readAsDataURL(file);
    });

    // Mostrar mensaje de éxito
    @if (session('success'))
        new PNotify({
            text: '{{ session('success') }}',
            type: 'success',
            addclass: 'notification-success',
            delay: 3000
        });
    @endif

    // Mostrar mensaje de error
    @if (session('error'))
        new PNotify({
            text: '{{ session('error') }}',
            type: 'error',
            addclass: 'notification-danger',
            delay: 3000
        });
    @endif

    // Función para limpiar errores del formulario
    function clearFormErrors() {
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('').hide();
    }

    // Función para mostrar errores de validación
    function displayFormErrors(errors) {
        $.each(errors, function(field, messages) {
            const input = $(`#${field}`);
            const feedback = input.siblings('.invalid-feedback');

            input.addClass('is-invalid');
            feedback.text(messages[0]).show();
        });
    }

    // Función para limpiar el formulario de logo
    function clearLogoForm() {
        $('#logo-upload').val('');
        $('.custom-file-label').text('Seleccionar archivo...');
        $('#logo-preview').hide();
    }

    // Función para calcular el dígito de verificación
    function calculateDV(nit) {
        if (!nit || nit.length === 0) return '';

        var sequence = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];
        var sum = 0;
        var nitArray = nit.toString().split('').reverse();

        for (var i = 0; i < nitArray.length && i < sequence.length; i++) {
            sum += parseInt(nitArray[i]) * sequence[i];
        }

        var remainder = sum % 11;
        var dv = remainder > 1 ? 11 - remainder : remainder;

        return dv.toString();
    }

    // Calcular DV automáticamente cuando se escriba el número de identificación
    $('#identification_number').on('input', function() {
        var nit = $(this).val().replace(/\D/g, ''); // Solo números
        if (nit.length > 0) {
            var dv = calculateDV(nit);
            $('#dv').val(dv);
        } else {
            $('#dv').val('');
        }
    });

    // También permitir solo números en el campo de identificación
    $('#identification_number').on('keypress', function(e) {
        var char = String.fromCharCode(e.which);
        if (!/[0-9]/.test(char)) {
            e.preventDefault();
        }
    });

    // Copiar token al portapapeles
    $('#copyTokenBtn').on('click', function() {
        var token = $('#company-token-input').val();
        if (!token) {
            new PNotify({
                text: 'No hay token disponible para copiar',
                type: 'error',
                addclass: 'notification-danger',
                delay: 2500
            });
            return;
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(token).then(function() {
                new PNotify({
                    text: 'Token copiado al portapapeles',
                    type: 'success',
                    addclass: 'notification-success',
                    delay: 2000
                });
            }).catch(function() {
                fallbackCopy(token);
            });
        } else {
            fallbackCopy(token);
        }

        function fallbackCopy(text) {
            var $temp = $('<input>');
            $('body').append($temp);
            $temp.val(text).select();
            document.execCommand('copy');
            $temp.remove();
            new PNotify({
                text: 'Token copiado al portapapeles',
                type: 'success',
                addclass: 'notification-success',
                delay: 2000
            });
        }
    });

    // Solo permitir números en el campo DV
    $('#dv').on('keypress', function(e) {
        var char = String.fromCharCode(e.which);
        if (!/[0-9]/.test(char)) {
            e.preventDefault();
        }
    });

    // Actualizar label del input de certificado
    $('#cert-file-input').on('change', function() {
        var file = this.files[0];
        $(this).next('.custom-file-label').text(file ? file.name : 'Seleccionar archivo...');
    });

    // Guardar / Actualizar certificado
    $('#saveCertificateBtn').on('click', function(e) {
        e.preventDefault();

        var file = $('#cert-file-input')[0].files[0];
        var password = $('#cert-password-input').val().trim();

        if (!file) {
            new PNotify({
                text: 'Por favor selecciona el archivo de certificado (.pfx o .p12)',
                type: 'error',
                addclass: 'notification-danger',
                delay: 3000
            });
            return;
        }
        if (!password) {
            new PNotify({
                text: 'Por favor ingresa la contraseña del certificado',
                type: 'error',
                addclass: 'notification-danger',
                delay: 3000
            });
            return;
        }

        var submitBtn = $(this);
        var originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');

        var reader = new FileReader();
        reader.onload = function(e) {
            var base64String = e.target.result.split(',')[1];

            $.ajax({
                url: '/api/ubl2.1/config/certificate',
                method: 'PUT',
                headers: {
                    'Authorization': 'Bearer ' + (window.currentApiToken || currentApiToken),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                data: JSON.stringify({
                    certificate: base64String,
                    password: password
                }),
                success: function(response) {
                    new PNotify({
                        text: response.message || 'Certificado actualizado exitosamente',
                        type: 'success',
                        addclass: 'notification-success',
                        delay: 3000
                    });
                    clearCertificateForm();
                    setTimeout(function() { location.reload(); }, 1500);
                },
                error: function(xhr) {
                    var message = 'Error al actualizar el certificado';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    new PNotify({
                        text: message,
                        type: 'error',
                        addclass: 'notification-danger',
                        delay: 5000
                    });
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        };
        reader.readAsDataURL(file);
    });

    // Función para mostrar la info del certificado actual en el tab
    function loadCertificateDisplay() {
        var companyId = window.currentCompanyId;
        var certData = window.companyCertificateData && window.companyCertificateData[companyId];

        if (certData && certData.name) {
            var expiryText = certData.expiry
                ? ' &mdash; Vence: <strong>' + certData.expiry + '</strong>'
                : '';
            $('#cert-current-info').html(
                '<div class="alert alert-info py-2">' +
                '<i class="fas fa-check-circle mr-2"></i>' +
                '<strong>Certificado cargado:</strong> ' + certData.name + expiryText +
                '</div>'
            );
        } else {
            $('#cert-current-info').html(
                '<div class="alert alert-warning py-2">' +
                '<i class="fas fa-exclamation-triangle mr-2"></i>' +
                'No hay certificado cargado para esta empresa.' +
                '</div>'
            );
        }
    }

    // Función para limpiar el formulario de certificado
    function clearCertificateForm() {
        $('#cert-password-input').val('');
        $('#cert-file-input').val('');
        $('#cert-file-input').next('.custom-file-label').text('Seleccionar archivo...');
        $('#cert-current-info').html('');
    }

});
</script>
@endpush
