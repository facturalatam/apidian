<template>
    <div class="card">
        <div class="card-body">
            <el-steps :active="active" finish-status="success">
                <el-step title="Empresa">
                    <template #icon>
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon icon-tabler-outline icon-tabler-building">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M3 21h18" />
                            <path d="M9 8h1" />
                            <path d="M9 12h1" />
                            <path d="M9 16h1" />
                            <path d="M14 8h1" />
                            <path d="M14 12h1" />
                            <path d="M14 16h1" />
                            <path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16" />
                        </svg>
                    </template>
                </el-step>
            
                <el-step title="Certificado">
                    <template #icon>
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon icon-tabler icons-tabler-outline icon-tabler-file-description">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                            <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                            <path d="M9 17h6" />
                            <path d="M9 13h6" />
                        </svg>
                    </template>
                </el-step>

                <el-step title="Software">
                    <template #icon>
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon icon-tabler icons-tabler-outline icon-tabler-server">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M3 4m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z" />
                            <path d="M3 14m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z" />
                            <path d="M7 8l0 .01" />
                            <path d="M7 18l0 .01" />
                        </svg>
                    </template>
                </el-step>

                <el-step title="Resolución">
                    <template #icon>
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon icon-tabler icons-tabler-outline icon-tabler-license">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M15 21h-9a3 3 0 0 1 -3 -3v-1h10v2a2 2 0 0 0 4 0v-14a2 2 0 1 1 2 2h-2m2 -4h-11a3 3 0 0 0 -3 3v11" />
                            <path d="M9 7l4 0" />
                            <path d="M9 11l4 0" />
                        </svg>
                    </template>
                </el-step>

                <el-step title="Factura Json(opcional)">
                    <template #icon>
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon icon-tabler icons-tabler-outline icon-tabler-send">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M10 14l11 -11" />
                            <path d="M21 3l-6.5 18a.55 .55 0 0 1 -1 0l-3.5 -8l-8 -3.5a.55 .55 0 0 1 0 -1l18 -6.5" />
                        </svg>
                    </template>
                </el-step>
            </el-steps>
            <br />
            <br />

            <!-- Empresa -->
            <div class="row" v-show="active == 0">
                <div class="col-md-4">
                    <div class="card-body card p-3 text-center border-dashed">
                        <h5 class="mb-3">Subir Ficha RUT (PDF)</h5>

                        <el-upload
                            ref="rutUpload"
                            drag
                            action="#"
                            :auto-upload="false"
                            :limit="1"
                            accept="application/pdf"
                            :on-change="onRutChange"
                            :show-file-list="false"
                            class="rut-drag"
                        >
                            <i class="el-icon-upload"></i>
                            <div class="el-upload__text">
                                Arrastra tu archivo aquí<br>
                                <em>o haz clic para seleccionar</em>
                            </div>
                            <div slot="tip" class="el-upload__tip">Solo archivos PDF</div>
                        </el-upload>

                        <div v-if="selectedRutName" class="uploaded-wrapper mt-3">

                            <!-- CABECERA: Icono + Nombre archivo + X -->
                            <div class="uploaded-header d-flex align-items-center justify-content-between">
                                
                                <!-- Icono + texto -->
                                <div class="d-flex align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="pdf-icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                    </svg>
                                    <span class="uploaded-name">{{ selectedRutName }}</span>
                                </div>

                                <!-- Botón X -->
                                <button type="button" class="close file-close" @click="resetRutUpload">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <!-- PIE: Barra de progreso -->
                            <div class="uploaded-footer mt-2">
                                <el-progress
                                    :percentage="rutProgress"
                                    :status="rutProgress == 100 ? 'success' : null"
                                    :stroke-width="8">
                                </el-progress>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FORMULARIO EMPRESA -->
                <div class="col-md-8">
                    <form autocomplete="off">
                        <div class="form-body">
                            <div class="row">

                                <!-- tipo de documento -->
                                <div class="col-md-6">
                                    <div class="form-group" :class="{'has-danger': errors.type_document_identification_id}">
                                        <label>Tipo de Documento</label>
                                        <el-select class="extend" v-model="form.type_document_identification_id" filterable>
                                            <el-option
                                                v-for="option in type_document_identification"
                                                :key="option.id"
                                                :value="option.id"
                                                :label="option.name"
                                            ></el-option>
                                        </el-select>
                                        <small v-if="errors.type_document_identification_id" class="form-control-feedback">
                                            {{ errors.type_document_identification_id[0] }}
                                        </small>
                                    </div>
                                </div>

                                <!-- nit -->
                                <div class="col-md-6">
                                    <div class="form-group" :class="{'has-danger': errors.nit}">
                                        <label>Número de documento</label>
                                        <el-input v-model="form.nit"></el-input>
                                        <small v-if="errors.nit" class="form-control-feedback">{{ errors.nit[0] }}</small>
                                    </div>
                                </div>

                                <!-- dv -->
                                <div class="col-md-3">
                                    <div class="form-group" :class="{'has-danger': errors.dv}">
                                        <label>DV</label>
                                        <el-input v-model="form.dv"></el-input>
                                        <small v-if="errors.dv" class="form-control-feedback">{{ errors.dv[0] }}</small>
                                    </div>
                                </div>

                                <!-- empresa -->
                                <div class="col-md-9">
                                    <div class="form-group" :class="{'has-danger': errors.business_name}">
                                        <label>Empresa</label>
                                        <el-input v-model="form.business_name"></el-input>
                                        <small v-if="errors.business_name" class="form-control-feedback">
                                            {{ errors.business_name[0] }}
                                        </small>
                                    </div>
                                </div>

                                <!-- resto de campos -->
                                <div class="col-md-6">
                                    <div class="form-group" :class="{'has-danger': errors.merchant_registration}">
                                        <label>Registro Mercantil</label>
                                        <el-input v-model="form.merchant_registration"></el-input>
                                        <small v-if="errors.merchant_registration" class="form-control-feedback">
                                            {{ errors.merchant_registration[0] }}
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group" :class="{'has-danger': errors.phone}">
                                        <label>Teléfono</label>
                                        <el-input v-model="form.phone"></el-input>
                                        <small v-if="errors.phone" class="form-control-feedback">
                                            {{ errors.phone[0] }}
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group" :class="{'has-danger': errors.email}">
                                        <label>Correo Electrónico</label>
                                        <el-input v-model="form.email"></el-input>
                                        <small v-if="errors.email" class="form-control-feedback">
                                            {{ errors.email[0] }}
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group" :class="{'has-danger': errors.address}">
                                        <label>Dirección</label>
                                        <el-input v-model="form.address"></el-input>
                                        <small v-if="errors.address" class="form-control-feedback">
                                            {{ errors.address[0] }}
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group" :class="{'has-danger': errors.department_id}">
                                        <label>Departamento</label>
                                        <el-select class="extend" @change="filterMunicipality" v-model="form.department_id" filterable>
                                            <el-option
                                                v-for="option in department"
                                                :key="option.id"
                                                :value="option.id"
                                                :label="option.name"
                                            ></el-option>
                                        </el-select>
                                        <small v-if="errors.department_id" class="form-control-feedback">
                                            {{ errors.department_id[0] }}
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group" :class="{'has-danger': errors.municipality_id}">
                                        <label>Municipio</label>
                                        <el-select class="extend" v-model="form.municipality_id" filterable>
                                            <el-option
                                                v-for="option in municipality_filter"
                                                :key="option.id"
                                                :value="option.id"
                                                :label="option.name"
                                            ></el-option>
                                        </el-select>
                                        <small v-if="errors.municipality_id" class="form-control-feedback">
                                            {{ errors.municipality_id[0] }}
                                        </small>
                                    </div>
                                </div>

                                <!-- Responsabilidad / Organización / Régimen -->
                                <div class="col-md-4">
                                    <div class="form-group" :class="{'has-danger': errors.type_liability_id}">
                                        <label>Tipo Responsabilidad</label>
                                        <el-select class="extend" v-model="form.type_liability_id" filterable>
                                            <el-option
                                                v-for="option in type_liability"
                                                :key="option.id"
                                                :value="option.id"
                                                :label="option.name"
                                            ></el-option>
                                        </el-select>
                                        <small v-if="errors.type_liability_id" class="form-control-feedback">
                                            {{ errors.type_liability_id[0] }}
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group" :class="{'has-danger': errors.type_organization_id}">
                                        <label>Organización</label>
                                        <el-select class="extend" v-model="form.type_organization_id" filterable>
                                            <el-option
                                                v-for="option in type_organization"
                                                :key="option.id"
                                                :value="option.id"
                                                :label="option.name"
                                            ></el-option>
                                        </el-select>
                                        <small v-if="errors.type_organization_id" class="form-control-feedback">
                                            {{ errors.type_organization_id[0] }}
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group" :class="{'has-danger': errors.type_regime_id}">
                                        <label>Régimen</label>
                                        <el-select class="extend" v-model="form.type_regime_id" filterable>
                                            <el-option
                                                v-for="option in type_regime"
                                                :key="option.id"
                                                :value="option.id"
                                                :label="option.name"
                                            ></el-option>
                                        </el-select>
                                        <small v-if="errors.type_regime_id" class="form-control-feedback">
                                            {{ errors.type_regime_id[0] }}
                                        </small>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </form>

                    <!-- <div class="text-center mt-4">
                        <el-button size="medium" type="primary" :loading="loading_submit" @click="saveCompany">
                            Siguiente
                        </el-button>
                    </div> -->
                </div>

                <div class="col-md-12 text-center mt-4">
                    <el-button size="medium" type="primary" :loading="loading_submit" @click="saveCompany">
                        Siguiente
                    </el-button>
                </div>
            </div>

            <!-- Certificado -->
            <div class="row" v-show="active == 1">
                <div class="col-md-8">
                    <form autocomplete="off">
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group" :class="{'has-danger': errors.password}">
                                        <label class="control-label">Password</label>
                                        <el-input type="password" v-model="form.password"></el-input>
                                        <small class="form-control-feedback" v-if="errors.password"
                                            v-text="errors.password"></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" :class="{'has-danger': errors.certificate}">
                                        <textarea hidden id="base64" rows="5"></textarea>
                                        <label class="control-label">File</label>
                                        <el-upload ref="fileCertificado" :auto-upload="false" width="100px"
                                            :on-change="handleChangeFileCertificado" :limit="1" drag action="''">
                                            <i class="el-icon-upload"></i>
                                            <div class="el-upload__text">
                                                Suelta tu archivo aquí o
                                                <em>haz clic para cargar</em>
                                            </div>
                                            <div slot="tip" class="el-upload__tip">Solo archivos .pfx</div>
                                        </el-upload>
                                        <small class="form-control-feedback" v-if="errors.certificate"
                                            v-text="errors.certificate"></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-4">
                    <div class="cert-help-box">
                        <i class="el-icon-document" style="font-size:22px;color:#409EFF;"></i>
                        <h5 class="cert-help-title">¿No tienes certificado digital?</h5>
                        <p class="cert-help-text">
                            Necesitas un <strong>.pfx / .p12</strong> autorizado para firmar ante la DIAN.
                        </p>
                        <a href="https://facturalatam.com/certificados-digitales/"
                           target="_blank" rel="noopener noreferrer"
                           class="el-button el-button--success el-button--mini">
                            <i class="el-icon-shopping-cart-2"></i> Adquirir certificado
                        </a>
                    </div>
                </div>
                <div class="col-md-12 text-center mt-4">
                    <el-button size="medium" type="primary" :loading="loading_submit" @click="saveCertificate">
                        Siguiente</el-button>
                </div>
            </div>

            <!-- Software -->
            <div class="row" v-show="active == 2">
                <div class="col-md-12">
                    <form autocomplete="off">
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group" :class="{'has-danger': errors.id}">
                                        <label class="control-label">Identificador del Software (ID)</label>
                                        <el-input v-model="software.id"></el-input>
                                        <small class="form-control-feedback" v-if="errors.id" v-text="errors.id[0]"></small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group" :class="{'has-danger': errors.pin}">
                                        <label class="control-label">PIN</label>
                                        <el-input v-model="software.pin"></el-input>
                                        <small class="form-control-feedback" v-if="errors.pin" v-text="errors.pin[0]"></small>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="alert alert-info" style="margin-top: 6px;">
                                        <strong><i class="el-icon-info"></i> ¿Dónde obtengo el ID y el PIN?</strong>
                                        <div class="mt-1">
                                            En el portal DIAN de Habilitación:
                                            <em>Participantes → Registro y habilitación de software → Agregar software</em>.
                                            Allí la DIAN genera el <strong>Identificador del Software (ID)</strong> y tú asignas el <strong>PIN</strong>.
                                            <a href="#" @click.prevent="softwarePreviewVisible = true"
                                               style="margin-left: 6px; text-decoration: underline; font-weight: 600;">
                                                🖼 Vista Previa
                                            </a>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-12 text-center mt-4">
                    <el-button size="medium" type="primary" :loading="loading_submit" @click="saveSoftware">
                        Siguiente
                    </el-button>
                </div>

                <!-- Dialogo Vista Previa: como crear el software en la DIAN -->
                <el-dialog title="Cómo crear el software y obtener el ID y PIN (portal DIAN)"
                    :visible.sync="softwarePreviewVisible" width="70%" append-to-body>
                    <el-carousel height="500px" :autoplay="false" indicator-position="outside" arrow="always">
                        <el-carousel-item v-for="(img, i) in softwareHelpImages" :key="i">
                            <img :src="img.src" :alt="img.caption"
                                style="max-width: 100%; max-height: calc(100% - 34px); display: block; margin: 0 auto; object-fit: contain;">
                            <div style="text-align: center; padding-top: 6px;">
                                <strong>{{ img.caption }}</strong>
                            </div>
                        </el-carousel-item>
                    </el-carousel>
                </el-dialog>
            </div>

            <!-- Resolución -->
            <div class="row" v-show="active == 3">
                <div class="col-md-12" v-if="existingResolution">
                    <div class="alert alert-success">
                        <strong><i class="el-icon-success"></i> Esta empresa ya tiene una resolución de factura registrada.</strong>
                        <div class="mt-1">
                            Prefijo: <strong>{{ existingResolution.prefix }}</strong> —
                            N° Resolución: <strong>{{ existingResolution.resolution }}</strong>.
                            No necesitas registrarla de nuevo.
                            <el-button size="mini" type="success" plain style="margin-left: 8px;" @click="skipResolutionStep">
                                Omitir este paso →
                            </el-button>
                        </div>
                    </div>
                </div>
                <div class="col-md-12" v-else>
                    <div class="alert alert-info">
                        <strong><i class="el-icon-info"></i> Valores por defecto de la DIAN (habilitación).</strong>
                        Los campos vienen precargados con la resolución de habilitación estándar
                        (<em>SETP — 18760000001</em>). Solo modifícalos si tu resolución es diferente.
                    </div>
                </div>
                <div class="col-md-12">
                    <form autocomplete="off">
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group" :class="{'has-danger': errors.type_document_id}">
                                        <label class="control-label">Tipo de Documento</label>
                                        <el-select class="extend" v-model="resolution.type_document_id" disabled>
                                            <el-option :value="1" label="Factura de Venta Nacional"></el-option>
                                        </el-select>
                                        <small class="form-control-feedback" v-if="errors.type_document_id" v-text="errors.type_document_id[0]"></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" :class="{'has-danger': errors.prefix}">
                                        <label class="control-label">Prefijo</label>
                                        <el-input v-model="resolution.prefix"></el-input>
                                        <small class="form-control-feedback" v-if="errors.prefix" v-text="errors.prefix[0]"></small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group" :class="{'has-danger': errors.resolution}">
                                        <label class="control-label">Número de Resolución</label>
                                        <el-input v-model="resolution.resolution"></el-input>
                                        <small class="form-control-feedback" v-if="errors.resolution" v-text="errors.resolution[0]"></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" :class="{'has-danger': errors.resolution_date}">
                                        <label class="control-label">Fecha Resolución</label>
                                        <el-input type="date" v-model="resolution.resolution_date"></el-input>
                                        <small class="form-control-feedback" v-if="errors.resolution_date" v-text="errors.resolution_date[0]"></small>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group" :class="{'has-danger': errors.technical_key}">
                                        <label class="control-label">Clave Técnica</label>
                                        <el-input v-model="resolution.technical_key"></el-input>
                                        <small class="form-control-feedback" v-if="errors.technical_key" v-text="errors.technical_key[0]"></small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group" :class="{'has-danger': errors.from}">
                                        <label class="control-label">Rango Inicial (desde)</label>
                                        <el-input type="number" v-model.number="resolution.from"></el-input>
                                        <small class="form-control-feedback" v-if="errors.from" v-text="errors.from[0]"></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" :class="{'has-danger': errors.to}">
                                        <label class="control-label">Rango Final (hasta)</label>
                                        <el-input type="number" v-model.number="resolution.to"></el-input>
                                        <small class="form-control-feedback" v-if="errors.to" v-text="errors.to[0]"></small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group" :class="{'has-danger': errors.date_from}">
                                        <label class="control-label">Vigencia desde</label>
                                        <el-input type="date" v-model="resolution.date_from"></el-input>
                                        <small class="form-control-feedback" v-if="errors.date_from" v-text="errors.date_from[0]"></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" :class="{'has-danger': errors.date_to}">
                                        <label class="control-label">Vigencia hasta</label>
                                        <el-input type="date" v-model="resolution.date_to"></el-input>
                                        <small class="form-control-feedback" v-if="errors.date_to" v-text="errors.date_to[0]"></small>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <el-checkbox v-model="createCreditNoteResolution">
                                            Crear también la resolución para <b>Nota Crédito</b> (configuración inicial necesaria para poder emitir NC)
                                        </el-checkbox>
                                        <div class="text-muted" style="font-size: 12px; margin-top: 2px;">
                                            La DIAN no exige numeración autorizada para NC: el número de resolución puede ser
                                            cualquiera y <b>no requiere clave técnica</b>.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12" v-if="createCreditNoteResolution">
                                    <div class="nc-resolution-box">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="control-label">Prefijo NC</label>
                                                    <el-input v-model="ncResolution.prefix"></el-input>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="control-label">Número de Resolución</label>
                                                    <el-input v-model="ncResolution.resolution"></el-input>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="control-label">Rango Inicial (desde)</label>
                                                    <el-input type="number" v-model.number="ncResolution.from"></el-input>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="control-label">Rango Final (hasta)</label>
                                                    <el-input type="number" v-model.number="ncResolution.to"></el-input>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group" style="margin-bottom: 0;">
                                                    <label class="control-label">Vigencia desde</label>
                                                    <el-input type="date" v-model="ncResolution.date_from"></el-input>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group" style="margin-bottom: 0;">
                                                    <label class="control-label">Vigencia hasta</label>
                                                    <el-input type="date" v-model="ncResolution.date_to"></el-input>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-12 text-center mt-4">
                    <el-button size="medium" type="primary" :loading="loading_submit" @click="saveResolution(false)">
                        Siguiente (factura JSON opcional)
                    </el-button>
                    <el-button size="medium" type="success" :loading="loading_submit" @click="saveResolution(true)">
                        <i class="el-icon-check"></i> Terminar
                    </el-button>
                </div>
            </div>

            <!-- Envío de Factura (JSON) - Paso opcional -->
            <div class="row" v-show="active == 4">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <!-- Ingrese el JSON para generar una Factura -->
                        <strong>Ingrese el JSON para generar una Factura Electronica (Opcional)</strong>
                        <!-- <span class="text-muted">Este paso es opcional.</span> -->
                        <div class="mt-2">
                            Puede consultar la
                            <el-button size="mini" type="primary" plain @click="openDocumentation">
                                <i class="el-icon-document"></i> documentación
                            </el-button>
                            y/o collection
                            <a href="https://documenter.getpostman.com/view/1431398/2sAY4uCido#intro"
                               target="_blank" rel="noopener noreferrer"
                               style="text-decoration: underline;">postman</a>.
                        </div>
                    </div>
                    <!-- Detalles de la petición -->
                    <div class="api-doc-card">
                        <div class="api-doc-title"><i class="el-icon-connection"></i> Detalles de la petición</div>
                        <div class="api-doc-row">
                            <span class="api-method-badge">POST</span>
                            <code class="api-url">{{ invoiceEndpointUrl }}</code>
                            <el-button size="mini" type="text" icon="el-icon-document-copy"
                                @click="copyText(invoiceEndpointUrl, 'URL copiada')">Copiar</el-button>
                        </div>
                        <div class="api-doc-row">
                            <span class="api-header-name">Authorization:</span>
                            <code class="api-url">Bearer {{ bearerToken }}</code>
                            <el-button size="mini" type="text" icon="el-icon-document-copy"
                                @click="copyText('Bearer ' + bearerToken, 'Token copiado')">Copiar</el-button>
                        </div>
                        <div class="api-doc-row">
                            <span class="api-header-name">Content-Type:</span>
                            <code class="api-url">application/json</code>
                            <span class="api-header-name" style="margin-left: 12px;">Accept:</span>
                            <code class="api-url">application/json</code>
                        </div>
                    </div>

                    <form autocomplete="off">
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">JSON de la Factura</label>
                                        <div class="json-editor">
                                            <pre class="json-doc json-editor-highlight" ref="editorHighlight" v-html="highlightedInvoiceJson"></pre>
                                            <textarea
                                                ref="editorInput"
                                                class="json-editor-input"
                                                v-model="invoice.json"
                                                spellcheck="false"
                                                wrap="off"
                                                placeholder="Pegue aquí el JSON de la factura"
                                                @scroll="syncEditorScroll"
                                                @input="syncEditorScroll"
                                            ></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 text-center" style="margin-bottom: 15px;">
                                    <el-button size="medium" type="primary" :loading="loading_submit" @click="sendInvoice">
                                        <i class="el-icon-s-promotion"></i> Enviar Factura
                                    </el-button>
                                </div>

                                <div class="col-md-12" v-if="invoice.response">
                                    <div class="form-group">
                                        <label class="control-label">Respuesta DIAN</label>
                                        <el-input type="textarea" :rows="10" :value="formattedInvoiceResponse" readonly></el-input>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Ejemplos documentados -->
                    <el-collapse v-model="openExamples" class="mt-3">
                        <el-collapse-item name="invoice">
                            <template slot="title">
                                <b><i class="el-icon-tickets"></i>&nbsp;Ejemplo documentado: Factura Electrónica de Venta</b>
                            </template>
                            <div class="json-doc-toolbar">
                                <span class="api-method-badge">POST</span>
                                <code class="api-url">{{ invoiceEndpointUrl }}</code>
                                <el-button size="mini" plain icon="el-icon-document-copy"
                                    @click="copyExample(documentedInvoiceExample())">Copiar JSON</el-button>
                            </div>
                            <pre class="json-doc" v-html="highlightedInvoiceExample"></pre>
                        </el-collapse-item>
                        <el-collapse-item name="credit-note">
                            <template slot="title">
                                <b><i class="el-icon-tickets"></i>&nbsp;Ejemplo documentado: Nota Crédito</b>
                            </template>
                            <div class="json-doc-toolbar">
                                <span class="api-method-badge">POST</span>
                                <code class="api-url">{{ creditNoteEndpointUrl }}</code>
                                <el-button size="mini" plain icon="el-icon-document-copy"
                                    @click="copyExample(documentedCreditNoteExample())">Copiar JSON</el-button>
                            </div>
                            <pre class="json-doc" v-html="highlightedCreditNoteExample"></pre>
                        </el-collapse-item>
                    </el-collapse>
                    <div class="text-muted mt-2" style="font-size: 12px;">
                        <i class="el-icon-info"></i> Los comentarios <code>//</code> de los ejemplos son solo informativos.
                        Use el botón <b>Copiar JSON</b> para obtener el JSON limpio, listo para enviar.
                    </div>

                    <!-- Más ejemplos en la documentación -->
                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="el-icon-notebook-2"></i>
                        <b>¿Necesita más ejemplos?</b> Nota Débito, Documento Soporte, Nómina Electrónica, Documento Equivalente,
                        facturas con descuentos, retenciones y más casos están disponibles en la
                        <a href="#" @click.prevent="openDocumentation" style="text-decoration: underline;">documentación completa</a>
                        y en la
                        <a href="https://documenter.getpostman.com/view/1431398/2sAY4uCido#intro"
                           target="_blank" rel="noopener noreferrer"
                           style="text-decoration: underline;">collection de Postman</a>.
                    </div>
                </div>
                <div class="col-md-12 text-center mt-4">
                    <el-button size="medium" type="success" @click="closeWizard">
                        Cerrar
                    </el-button>
                </div>
            </div>
        </div>
    </div>
</template>
<style>
    .extend {
        width: 100%;
    }
    .cert-help-box {
        border: 1px dashed #dcdfe6;
        border-radius: 6px;
        padding: 12px 12px;
        text-align: center;
        background: #f9fafc;
        margin-top: 20px;
    }
    .cert-help-title {
        font-size: 12.5px;
        font-weight: 600;
        color: #2B323D;
        margin: 6px 0 4px;
    }
    .cert-help-text {
        font-size: 11px;
        color: #666;
        margin-bottom: 10px;
        line-height: 1.35;
    }
    .cert-help-box a.el-button {
        text-decoration: none;
    }
    .nc-resolution-box {
        border: 1px dashed #dcdfe6;
        border-radius: 6px;
        background: #f9fafc;
        padding: 12px 12px 8px;
        margin-top: 10px;
    }
    .api-doc-card {
        border: 1px solid #dcdfe6;
        border-radius: 6px;
        background: #f9fafc;
        padding: 12px 16px;
        margin-bottom: 15px;
    }
    .api-doc-title {
        font-weight: 600;
        color: #2B323D;
        margin-bottom: 8px;
    }
    .api-doc-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 6px;
        font-size: 13px;
    }
    .api-method-badge {
        background: #67c23a;
        color: #fff;
        font-weight: 700;
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 4px;
        letter-spacing: .5px;
    }
    .api-url {
        background: #eef1f6;
        padding: 2px 8px;
        border-radius: 4px;
        color: #409EFF;
        word-break: break-all;
    }
    .api-header-name {
        font-weight: 600;
        color: #606266;
    }
    .json-doc-toolbar {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 8px;
    }
    .json-doc {
        background: #282c34;
        color: #abb2bf;
        border-radius: 6px;
        padding: 16px;
        font-size: 12.5px;
        line-height: 1.55;
        overflow-x: auto;
        font-family: Consolas, 'Courier New', monospace;
        margin-bottom: 0;
    }
    .json-doc .json-key { color: #e06c75; }
    .json-doc .json-string { color: #98c379; }
    .json-doc .json-number { color: #d19a66; }
    .json-doc .json-boolean { color: #56b6c2; }
    .json-doc .json-comment { color: #7f848e; font-style: italic; }
    .json-editor {
        position: relative;
        height: 420px;
        border-radius: 6px;
        overflow: hidden;
        background: #282c34;
    }
    .json-editor-highlight {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        margin: 0;
        overflow: hidden;
        pointer-events: none;
    }
    .json-editor-input {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        padding: 16px;
        margin: 0;
        border: 0;
        resize: none;
        background: transparent;
        color: transparent;
        caret-color: #ffffff;
        font-family: Consolas, 'Courier New', monospace;
        font-size: 12.5px;
        line-height: 1.55;
        white-space: pre;
        overflow: auto;
        outline: none;
    }
    .json-editor-input::selection {
        background: rgba(64, 158, 255, .35);
    }
    .json-editor-input::placeholder {
        color: #7f848e;
    }
</style>
<script>
    function handleFileSelect(file) {
        var f = file; //evt.target.files[0]; // FileList object
        var reader = new FileReader();
        // Closure to capture the file information.
        reader.onload = (function (theFile) {
            return function (e) {
                var binaryData = e.target.result;
                //Converting Binary Data to base 64
                var base64String = window.btoa(binaryData);
                //showing file converted to base64
                document.getElementById("base64").value = base64String;
                console.log(
                    "File converted to base64 successfuly!\nCheck in Textarea hidden"
                );
                //return base64String;
            };
        })(f);
        // Read in the image file as a data URL.
        reader.readAsBinaryString(f);
    }
    import 'element-ui/lib/theme-chalk/index.css';
    export default {
        components: {},
        data() {
            return {
                hostname: window.location.hostname,
                loading_submit: false,
                active: 0,
                errors: [],
                resource: "configuration",
                resourceapi: "api/ubl2.1/config",
                type_document_identification: [],
                type_organization: [],
                type_regime: [],
                department: [],
                municipality: [],
                municipality_filter: [],
                type_document: [],
                type_liability: [],
                form: {},
                software: {},
                resolution: {},
                invoice: {},
                responseCompany: {},
                responseSoftware: {},
                responseCertificate: {},
                responseResolution: {},
                responseInvoice: {},
                resolution_type_documents: [
                    { id: 1,  name: 'Factura electrónica de Venta' },
                    { id: 2,  name: 'Factura electrónica de venta - exportación' },
                    { id: 3,  name: 'Instrumento electrónico de transmisión – tipo 03' },
                    { id: 4,  name: 'Nota Crédito' },
                    { id: 5,  name: 'Nota Débito' },
                    { id: 11, name: 'Documento Soporte Electrónico' },
                    { id: 13, name: 'Nota de Ajuste al Documento Soporte Electrónico' },
                ],
                selectedRutName: null,
                rutProgress: 0,
                simulatedInterval: null,
                simulatedDuration: 0,
                softwarePreviewVisible: false,
                softwareHelpImages: [
                    { src: '/img/help/software-paso1.png', caption: 'Paso 1' },
                    { src: '/img/help/software-paso2.png', caption: 'Paso 2' },
                    { src: '/img/help/software-paso3.png', caption: 'Paso 3' },
                    { src: '/img/help/software-paso4.png', caption: 'Paso 4' },
                    { src: '/img/help/dian-id-pin.png', caption: 'Resultado: en "Listado de modos de operación asociados" encuentras el ID del SW y el PIN' },
                ],
                existingResolution: null,
                openExamples: [],
                createCreditNoteResolution: true,
                // Resolución de Nota Crédito: la DIAN no exige numeración autorizada,
                // el número puede ser cualquiera y no requiere clave técnica
                ncResolution: {
                    type_document_id: 4,
                    prefix: 'NC',
                    resolution: '1',
                    from: 1,
                    to: 99999999,
                    date_from: '2019-01-19',
                    date_to: '2030-01-19',
                },
            };
        },
        computed: {
            invoiceEndpointUrl() {
                return window.location.origin + '/api/ubl2.1/invoice';
            },
            creditNoteEndpointUrl() {
                return window.location.origin + '/api/ubl2.1/credit-note';
            },
            bearerToken() {
                return (this.responseCompany && this.responseCompany.token)
                    ? this.responseCompany.token
                    : '{SU_TOKEN}';
            },
            highlightedInvoiceExample() {
                return this.highlightJson(this.documentedInvoiceExample());
            },
            highlightedCreditNoteExample() {
                return this.highlightJson(this.documentedCreditNoteExample());
            },
            highlightedInvoiceJson() {
                // \n final para que el alto del resaltado coincida con el textarea
                return this.highlightJson((this.invoice.json || '') + '\n');
            },
            formattedInvoiceResponse() {
                try {
                    return typeof this.invoice.response === 'string'
                        ? this.invoice.response
                        : JSON.stringify(this.invoice.response, null, 2);
                } catch (e) {
                    return String(this.invoice.response);
                }
            }
        },
        created() {
            this.getTables();
            this.initForm()
        },
        methods: {
            initForm() {
                this.form = {
                    generated_to_date: 0,
                    nit: null,
                    dv: null,
                    business_name: null,
                    merchant_registration: null,
                    phone: null,
                    email: null,
                    address: null,

                    department_id: null,
                    municipality_id: null,

                    type_document_identification_id: null,
                    type_liability_id: null,
                    type_organization_id: null,
                    type_regime_id: null,
                };
                this.software = {
                    id: null,
                    pin: null,
                    idsupportdocument: null,
                    pinsupportdocument: null,
                };
                // Precargada con la resolución de habilitación por defecto de la DIAN (SETP).
                // Solo se modifica si la resolución del cliente es diferente.
                this.resolution = {
                    type_document_id: 1,
                    prefix: 'SETP',
                    resolution: '18760000001',
                    resolution_date: '2019-01-19',
                    technical_key: 'fc8eac422eba16e22ffd8c6f94b3f40a6e38162c',
                    from: 990000000,
                    to: 995000000,
                    date_from: '2019-01-19',
                    date_to: '2030-01-19',
                };
                this.existingResolution = null;
                this.invoice = {
                    json: this.defaultInvoiceJson(),
                    response: null,
                };
                this.responseCompany = {};
                this.responseSoftware = {};
                this.responseCertificate = {};
                this.responseResolution = {};
                this.responseInvoice = {};
            },
            defaultInvoiceJson() {
                // fecha y hora actuales (zona horaria local) en el formato que exige la DIAN
                const now = new Date();
                const pad = (n) => String(n).padStart(2, '0');
                const today = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
                const time = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
                return `{
  "number": 994899975,
  "type_document_id": 1,
  "date": "${today}",
  "time": "${time}",
  "resolution_number": "18760000001",
  "prefix": "SETP",
  "sendmail": false,
  "customer": {
    "identification_number": "89008003",
    "name": "OBANDO LONDONO ALEXANDER",
    "email": "ejemplo@gmail.com"
  },
  "payment_form": {
    "payment_form_id": 2,
    "payment_method_id": 75,
    "payment_due_date": "2026-11-30",
    "duration_measure": "4"
  },
  "legal_monetary_totals": {
    "line_extension_amount": "3000.00",
    "tax_exclusive_amount": "3000.00",
    "tax_inclusive_amount": "3570.00",
    "payable_amount": "3570.00"
  },
  "tax_totals": [
    {
      "tax_id": 1,
      "tax_amount": "570.00",
      "percent": "19",
      "taxable_amount": "3000.00"
    }
  ],
  "invoice_lines": [
    {
      "unit_measure_id": 70,
      "invoiced_quantity": "1",
      "line_extension_amount": "1000.00",
      "free_of_charge_indicator": false,
      "description": "Producto de prueba 1",
      "code": "6455",
      "type_item_identification_id": 4,
      "price_amount": "1000.00",
      "base_quantity": "1",
      "tax_totals": [
        {
          "tax_id": 1,
          "tax_amount": "190.00",
          "taxable_amount": "1000.00",
          "percent": "19.00"
        }
      ]
    },
    {
      "unit_measure_id": 70,
      "invoiced_quantity": "1",
      "line_extension_amount": "1000.00",
      "free_of_charge_indicator": false,
      "description": "Producto de prueba 2",
      "code": "PRUEBA2",
      "type_item_identification_id": 4,
      "price_amount": "1000.00",
      "base_quantity": "1",
      "tax_totals": [
        {
          "tax_id": 1,
          "tax_amount": "190.00",
          "taxable_amount": "1000.00",
          "percent": "19.00"
        }
      ]
    },
    {
      "unit_measure_id": 70,
      "invoiced_quantity": "1",
      "line_extension_amount": "1000.00",
      "free_of_charge_indicator": false,
      "description": "Producto de prueba 3",
      "code": "PRUEBA3",
      "type_item_identification_id": 4,
      "price_amount": "1000.00",
      "base_quantity": "1",
      "tax_totals": [
        {
          "tax_id": 1,
          "tax_amount": "190.00",
          "taxable_amount": "1000.00",
          "percent": "19.00"
        }
      ]
    }
  ]
}`;
            },
            documentedInvoiceExample() {
                return `{
  "number": 994899975,                          // Consecutivo (dentro del rango de la resolución)
  "type_document_id": 1,                        // 1 = Factura electrónica de Venta
  "date": "2026-05-08",                         // Fecha de emisión (AAAA-MM-DD)
  "time": "08:40:00",                           // Hora de emisión (HH:MM:SS)
  "resolution_number": "18760000001",           // Número de la resolución DIAN
  "prefix": "SETP",                             // Prefijo de la resolución
  "sendmail": false,                            // true = envía la factura por correo al cliente
  "customer": {                                 // Datos del adquiriente (cliente)
    "identification_number": "89008003",        // NIT o cédula, sin dígito de verificación
    "name": "OBANDO LONDONO ALEXANDER",         // Razón social o nombre completo
    "email": "ejemplo@gmail.com"                // Correo donde el cliente recibe la factura
  },
  "payment_form": {                             // Forma de pago
    "payment_form_id": 2,                       // 1 = Contado, 2 = Crédito
    "payment_method_id": 75,                    // Medio de pago (10 = Efectivo, 75 = Consignación, ...)
    "payment_due_date": "2026-11-30",           // Fecha de vencimiento (solo crédito)
    "duration_measure": "4"                     // Plazo en días (solo crédito)
  },
  "legal_monetary_totals": {                    // Totales del documento
    "line_extension_amount": "1000.00",         // Subtotal antes de impuestos
    "tax_exclusive_amount": "1000.00",          // Base gravable
    "tax_inclusive_amount": "1190.00",          // Subtotal + impuestos
    "payable_amount": "1190.00"                 // Total a pagar
  },
  "tax_totals": [                               // Impuestos globales del documento
    {
      "tax_id": 1,                              // 1 = IVA
      "tax_amount": "190.00",                   // Valor del impuesto
      "percent": "19",                          // Tarifa aplicada (%)
      "taxable_amount": "1000.00"               // Base sobre la que se calcula
    }
  ],
  "invoice_lines": [                            // Detalle de productos / servicios
    {
      "unit_measure_id": 70,                    // 70 = Unidad
      "invoiced_quantity": "1",                 // Cantidad facturada
      "line_extension_amount": "1000.00",       // Total de la línea antes de impuestos
      "free_of_charge_indicator": false,        // true si la línea es a título gratuito (muestra)
      "description": "Producto de prueba 1",    // Descripción del producto o servicio
      "code": "6455",                           // Código interno del producto
      "type_item_identification_id": 4,         // 4 = Estándar de adopción del contribuyente
      "price_amount": "1000.00",                // Precio unitario
      "base_quantity": "1",                     // Cantidad base del precio
      "tax_totals": [                           // Impuestos de la línea
        {
          "tax_id": 1,
          "tax_amount": "190.00",
          "taxable_amount": "1000.00",
          "percent": "19.00"
        }
      ]
    }
  ]
}`;
            },
            documentedCreditNoteExample() {
                return `{
  "billing_reference": {                        // Factura que se está afectando
    "number": "SETP994899975",                  // Prefijo + consecutivo de la factura afectada
    "uuid": "941cf36af62dbbc06f105d2a80e9bfe683a90e84960eae4d351cc3afbe8f848c26c39bac4fbc80fa254824c6369ea694", // CUFE de la factura afectada (96 caracteres)
    "issue_date": "2026-05-08"                  // Fecha de emisión de la factura afectada
  },
  "discrepancyresponsecode": 2,                 // Motivo: 1=Devolución parcial, 2=Anulación, 3=Rebaja o descuento, 4=Ajuste de precio, 5=Otros
  "number": 1,                                  // Consecutivo de la NC (dentro del rango de la resolución NC)
  "type_document_id": 4,                        // 4 = Nota Crédito
  "prefix": "NC",                               // Prefijo de la resolución de Nota Crédito (creada en el paso Resolución)
  "date": "2026-05-09",                         // Fecha de emisión (AAAA-MM-DD)
  "time": "10:30:00",                           // Hora de emisión (HH:MM:SS)
  "sendmail": false,                            // true = envía la nota por correo al cliente
  "customer": {                                 // Mismo adquiriente de la factura afectada
    "identification_number": "89008003",
    "name": "OBANDO LONDONO ALEXANDER",
    "email": "ejemplo@gmail.com"
  },
  "legal_monetary_totals": {                    // Totales de la nota crédito
    "line_extension_amount": "1000.00",
    "tax_exclusive_amount": "1000.00",
    "tax_inclusive_amount": "1190.00",
    "payable_amount": "1190.00"
  },
  "tax_totals": [
    {
      "tax_id": 1,                              // 1 = IVA
      "tax_amount": "190.00",
      "percent": "19",
      "taxable_amount": "1000.00"
    }
  ],
  "credit_note_lines": [                        // Detalle de las líneas que se acreditan
    {
      "unit_measure_id": 70,                    // 70 = Unidad
      "invoiced_quantity": "1",                 // Cantidad a acreditar
      "line_extension_amount": "1000.00",
      "free_of_charge_indicator": false,
      "description": "Producto de prueba 1",
      "code": "6455",
      "type_item_identification_id": 4,
      "price_amount": "1000.00",
      "base_quantity": "1",
      "tax_totals": [
        {
          "tax_id": 1,
          "tax_amount": "190.00",
          "taxable_amount": "1000.00",
          "percent": "19.00"
        }
      ]
    }
  ]
}`;
            },
            highlightJson(code) {
                const escaped = code
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
                return escaped.replace(
                    /("(?:\\.|[^"\\])*")(\s*:)?|\b(true|false|null)\b|(-?\d+(?:\.\d+)?(?:[eE][+\-]?\d+)?)|(\/\/[^\n]*)/g,
                    (match, str, colon, bool, num, comment) => {
                        if (comment) return '<span class="json-comment">' + comment + '</span>';
                        if (str !== undefined) {
                            if (colon) return '<span class="json-key">' + str + '</span>' + colon;
                            return '<span class="json-string">' + str + '</span>';
                        }
                        if (bool) return '<span class="json-boolean">' + bool + '</span>';
                        if (num !== undefined) return '<span class="json-number">' + num + '</span>';
                        return match;
                    }
                );
            },
            copyText(text, message) {
                const done = () => this.$message.success(message || 'Copiado al portapapeles');
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(done).catch(() => this.fallbackCopy(text, done));
                } else {
                    this.fallbackCopy(text, done);
                }
            },
            fallbackCopy(text, done) {
                const el = document.createElement('textarea');
                el.value = text;
                el.style.position = 'fixed';
                el.style.opacity = '0';
                document.body.appendChild(el);
                el.select();
                document.execCommand('copy');
                document.body.removeChild(el);
                done();
            },
            copyExample(code) {
                // quitar los comentarios informativos para entregar un JSON válido
                const clean = code.replace(/[ \t]*\/\/[^\n]*/g, '');
                this.copyText(clean, 'JSON copiado (sin comentarios, listo para enviar)');
            },
            syncEditorScroll() {
                const ta = this.$refs.editorInput;
                const hl = this.$refs.editorHighlight;
                if (ta && hl) {
                    hl.scrollTop = ta.scrollTop;
                    hl.scrollLeft = ta.scrollLeft;
                }
            },
            saveCreditNoteResolution() {
                if (!this.createCreditNoteResolution) return Promise.resolve();
                return this.$http
                    .put(`/${this.resourceapi}/resolution`, this.ncResolution, this.getHeaderConfig())
                    .then(() => {
                        this.$message.success(`Resolución de Nota Crédito (prefijo ${this.ncResolution.prefix}) creada.`);
                    })
                    .catch(error => {
                        let detail = '';
                        if (error.response && error.response.data && error.response.data.errors) {
                            detail = ': ' + Object.values(error.response.data.errors).flat().join(' ');
                        }
                        this.$message.warning('No se pudo crear la resolución de Nota Crédito' + detail + '. Puede registrarla luego vía PUT /api/ubl2.1/config/resolution.');
                    });
            },
            validateRequiredFields() {
                const requiredFields = [
                    "nit",
                    "dv",
                    "business_name",
                    "merchant_registration",
                    "phone",
                    "email",
                    "address",
                    "department_id",
                    "municipality_id",
                    "type_document_identification_id",
                    "type_liability_id",
                    "type_organization_id",
                    "type_regime_id"
                ];
                this.errors = {};
                let valid = true;
                requiredFields.forEach(field => {
                    if (!this.form[field]) {
                        this.$set(this.errors, field, ["Campo obligatorio."]);
                        valid = false;
                    }
                });
                return valid;
            },
            getHeaderConfig() {
                let token = this.responseCompany.token;
                let axiosConfig = {
                    headers: {
                        "Content-Type": "application/json;charset=UTF-8",
                        Accept: "application/json",
                        Authorization: `Bearer ${token}`
                    }
                };
                return axiosConfig;
            },
            async saveCompany() {
                if (!this.validateRequiredFields()) {
                    this.$message.warning("Por favor complete todos los campos obligatorios antes de continuar.");
                    return;
                }
                this.loading_submit = true;
                try {
                    const response = await this.$http.post(
                        `/${this.resourceapi}/${this.form.nit}/${this.form.dv}`,
                        this.form
                    );
                    if (response.data.success) {
                        // Mostrar mensaje de éxito
                        this.$message.success(response.data.message);
                        // Guardar datos de la empresa y token para el siguiente paso
                        this.responseCompany = response.data;
                        // Avanzar al siguiente paso
                        this.next();
                    } else {
                        if (response.data.error) {
                            this.$message.error(response.data.error);
                        } else if (response.data.message) {
                            this.$message.error(response.data.message);
                        } else {
                            this.$message.error("Error al crear la empresa.");
                        }
                    }
                } catch (error) {
                    if (error.response && error.response.status === 422) {
                        this.errors = error.response.data.errors;
                        let allErrors = Object.values(this.errors).flat().join('\n');
                        this.$message.error(allErrors);
                    } else if (error.response && error.response.data && error.response.data.error) {
                        this.$message.error(error.response.data.error);
                    } else if (error.response && error.response.data && error.response.data.message) {
                        this.$message.error(error.response.data.message);
                    } else {
                        this.$message.error("Error inesperado al guardar la empresa.");
                    }
                } finally {
                    this.loading_submit = false;
                }
            },
            saveCertificate() {
                this.loading_submit = true;
                return new Promise((resolve, reject) => {
                    this.form.certificate = document.getElementById("base64").value;
                    this.$http
                        .put(
                            `/${this.resourceapi}/certificate`,
                            this.form,
                            this.getHeaderConfig()
                        )
                        .then(response => {
                            if (response.data.success) {
                                this.responseCertificate = response.data;
                                this.$message.success(response.data.message);
                                this.errors = {};
                                this.next();
                            } else {
                                this.$message.error(response.data.message);
                            }
                        })
                        .catch(error => {
                            if (error.response.status === 422) {
                                this.errors = error.response.data.errors;
                            } else {
                                this.$message.error(error.response.data.message);
                            }
                        })
                        .then(() => {
                            this.loading_submit = false;
                        });
                });
            },
            filterMunicipality() {
                this.municipality_filter = [];
                let id = this.form.department_id;
                this.municipality_filter = this.municipality.filter(
                    x => x.department_id == id
                );
                //this.form.municipality_id = ''
            },
            handleChangeFileCertificado(file) {
                // this.fileCertificado = file.raw;
                handleFileSelect(file.raw);
                //console.log(dato)
            },
            next() {
                if (this.active < 5) this.active++;
            },
            closeWizard() {
                window.location.href = '/home';
            },
            openDocumentation() {
                window.open('/tools', '_blank', 'noopener,noreferrer');
            },
            saveSoftware() {
                this.errors = {};
                const hasAtLeastOne =
                    this.software.id && String(this.software.id).trim() !== '' &&
                    this.software.pin && String(this.software.pin).trim() !== '';
                if (!hasAtLeastOne) {
                    this.$set(this.errors, 'id', ['Debe configurar el software con su PIN.']);
                    this.$set(this.errors, 'pin', ['Debe configurar el software con su PIN.']);
                    this.$message.warning('Debe configurar el software con su PIN.');
                    return;
                }
                this.loading_submit = true;
                this.$http
                    .put(`/${this.resourceapi}/software`, this.software, this.getHeaderConfig())
                    .then(response => {
                        if (response.data.success) {
                            this.responseSoftware = response.data;
                            this.$message.success(response.data.message);
                            this.checkExistingResolution();
                            this.next();
                        } else {
                            this.$message.error(response.data.message || 'Error al guardar el software.');
                        }
                    })
                    .catch(error => {
                        if (error.response && error.response.status === 422) {
                            this.errors = error.response.data.errors;
                            let allErrors = Object.values(this.errors).flat().join('\n');
                            this.$message.error(allErrors);
                        } else if (error.response && error.response.data && error.response.data.message) {
                            this.$message.error(error.response.data.message);
                        } else {
                            this.$message.error('Error inesperado al guardar el software.');
                        }
                    })
                    .then(() => {
                        this.loading_submit = false;
                    });
            },
            // Consulta si la empresa ya tiene resolución de factura (type_document_id = 1)
            // para ofrecer omitir el paso de resolución.
            checkExistingResolution() {
                const nit = this.form.nit;
                if (!nit) return;
                this.$http
                    .get(`/api/ubl2.1/table/resolutions/${nit}/1`, this.getHeaderConfig())
                    .then(response => {
                        const list = (response.data && response.data.resolutions) || [];
                        const found = list.find(r => Number(r.type_document_id) === 1) || list[0];
                        this.existingResolution = found || null;
                    })
                    .catch(() => {
                        this.existingResolution = null;
                    });
            },
            skipResolutionStep() {
                this.responseResolution = { success: true, skipped: true };
                this.$message.info('Paso de resolución omitido: la empresa ya tiene resolución registrada.');
                this.next();
            },
            validateResolutionFields() {
                this.errors = {};
                const r = this.resolution || {};
                const setErr = (k, msg) => this.$set(this.errors, k, [msg]);
                const isYMD = (v) => /^\d{4}-\d{2}-\d{2}$/.test(String(v || ''));
                const isInt = (v) => v !== null && v !== undefined && v !== '' && Number.isInteger(Number(v));

                // Siempre
                if (!r.type_document_id) setErr('type_document_id', 'El tipo de documento es obligatorio.');
                if (r.prefix && String(r.prefix).length > 4) setErr('prefix', 'El prefijo no puede tener más de 4 caracteres.');
                if (!isInt(r.from)) setErr('from', 'El rango inicial es obligatorio y debe ser un entero.');
                if (!isInt(r.to)) setErr('to', 'El rango final es obligatorio y debe ser un entero.');
                if (isInt(r.from) && isInt(r.to) && Number(r.to) <= Number(r.from)) {
                    setErr('to', 'El rango final debe ser mayor al rango inicial.');
                }

                // Solo para Factura Electrónica (type_document_id == 1)
                if (Number(r.type_document_id) === 1) {
                    if (!r.resolution) setErr('resolution', 'El número de resolución es obligatorio.');
                    if (!isYMD(r.resolution_date)) setErr('resolution_date', 'La fecha de resolución es obligatoria (YYYY-MM-DD).');
                    if (!r.technical_key) setErr('technical_key', 'La clave técnica es obligatoria.');
                    if (!isYMD(r.date_from)) setErr('date_from', 'La vigencia desde es obligatoria (YYYY-MM-DD).');
                    if (!isYMD(r.date_to)) setErr('date_to', 'La vigencia hasta es obligatoria (YYYY-MM-DD).');
                    if (isYMD(r.date_from) && isYMD(r.date_to) && r.date_to <= r.date_from) {
                        setErr('date_to', 'La vigencia hasta debe ser posterior a la vigencia desde.');
                    }
                }

                return Object.keys(this.errors).length === 0;
            },
            saveResolution(finish = false) {
                if (!this.validateResolutionFields()) {
                    const allErrors = Object.values(this.errors).flat().join('\n');
                    this.$message.warning(allErrors || 'Complete los campos obligatorios.');
                    return;
                }
                this.loading_submit = true;
                this.$http
                    .put(`/${this.resourceapi}/resolution`, this.resolution, this.getHeaderConfig())
                    .then(response => {
                        if (response.data.success) {
                            this.responseResolution = response.data;
                            this.$message.success(response.data.message);
                            if (finish) {
                                // Crear la resolución NC (si está marcada) antes de cerrar el wizard
                                this.saveCreditNoteResolution().then(() => {
                                    this.active = 5;
                                    setTimeout(() => this.closeWizard(), 800);
                                });
                                return;
                            }
                            this.saveCreditNoteResolution();
                            // Pre-rellenar prefix y resolution_number en el JSON de factura
                            try {
                                const parsed = JSON.parse(this.invoice.json || '{}');
                                if (this.resolution.prefix) parsed.prefix = this.resolution.prefix;
                                if (this.resolution.resolution) parsed.resolution_number = this.resolution.resolution;
                                if (this.resolution.from) parsed.number = this.resolution.from;
                                this.invoice.json = JSON.stringify(parsed, null, 2);
                            } catch (e) { /* ignore */ }
                            this.next();
                        } else {
                            this.$message.error(response.data.message || 'Error al guardar la resolución.');
                        }
                    })
                    .catch(error => {
                        if (error.response && error.response.status === 422) {
                            this.errors = error.response.data.errors;
                            let allErrors = Object.values(this.errors).flat().join('\n');
                            this.$message.error(allErrors);
                        } else if (error.response && error.response.data && error.response.data.message) {
                            this.$message.error(error.response.data.message);
                        } else {
                            this.$message.error('Error inesperado al guardar la resolución.');
                        }
                    })
                    .then(() => {
                        this.loading_submit = false;
                    });
            },
            parseInvoiceJson(text) {
                // 1) Intento estricto
                try {
                    return { ok: true, data: JSON.parse(text) };
                } catch (strictErr) {
                    // 2) Intento tolerante: corrige errores típicos al pegar
                    //    (comentarios // y /* */, claves sin comillas,
                    //     comillas simples, comas finales)
                    try {
                        let s = String(text);
                        // Quitar comentarios de bloque /* ... */
                        s = s.replace(/\/\*[\s\S]*?\*\//g, '');
                        // Quitar comentarios de línea //... (preservando "://" en URLs)
                        s = s.replace(/(^|[^:"'\\])\/\/[^\n\r]*/g, '$1');
                        // Quitar comas finales antes de } o ]
                        s = s.replace(/,(\s*[}\]])/g, '$1');
                        // Encerrar entre comillas dobles las claves sin comillas
                        s = s.replace(/([{,]\s*)([A-Za-z_$][A-Za-z0-9_$\-]*)\s*:/g, '$1"$2":');
                        // Convertir strings con comillas simples a dobles
                        s = s.replace(/'((?:[^'\\]|\\.)*)'/g, '"$1"');
                        return { ok: true, data: JSON.parse(s) };
                    } catch (e) {
                        return { ok: false, error: strictErr.message };
                    }
                }
            },
            sendInvoice() {
                const parsed = this.parseInvoiceJson(this.invoice.json);
                if (!parsed.ok) {
                    this.$message.error(
                        'El JSON de la factura no es válido: ' + parsed.error +
                        '. Verifica que las claves tengan comillas dobles, sin comas finales.'
                    );
                    return;
                }
                const payload = parsed.data;

                this.loading_submit = true;
                this.errors = {};
                this.invoice.response = null;

                this.$http
                    .post(`/api/ubl2.1/invoice`, payload, this.getHeaderConfig())
                    .then(response => {
                        this.responseInvoice = response.data;
                        this.invoice.response = response.data;
                        if (response.data && response.data.message) {
                            this.$message.success(response.data.message);
                        } else {
                            this.$message.success('Factura enviada.');
                        }
                    })
                    .catch(error => {
                        if (error.response && error.response.data) {
                            this.invoice.response = error.response.data;
                            if (error.response.status === 422 && error.response.data.errors) {
                                this.errors = error.response.data.errors;
                                let allErrors = Object.values(this.errors).flat().join('\n');
                                this.$message.error(allErrors);
                            } else if (error.response.data.message) {
                                this.$message.error(error.response.data.message);
                            } else {
                                this.$message.error('Error al enviar la factura.');
                            }
                        } else {
                            this.$message.error('Error inesperado al enviar la factura.');
                        }
                    })
                    .then(() => {
                        this.loading_submit = false;
                    });
            },
            getTables() {
                return new Promise((resolve, reject) => {
                    this.$http
                        .get(`/${this.resource}/tables`)
                        .then(response => {
                            this.type_document_identification =
                                response.data.type_document_identification;
                            this.type_organization = response.data.type_organization;
                            this.type_regime = response.data.type_regime;
                            this.department = response.data.department;
                            this.municipality = response.data.municipality;
                            this.type_document = response.data.type_document;
                            this.type_liability = response.data.type_liability;
                        })
                        .catch(error => {})
                        .then(() => {});
                });
            },
            onRutChange(file) {
                const realFile = file.raw;
                this.selectedRutName = realFile.name;
                this.rutProgress = 0;

                if (!realFile) {
                    this.$message.error("No se pudo leer el archivo");
                    return;
                }

                this.initForm();

                // detener intervalos previos por si acaso
                if (this.simulatedInterval) clearInterval(this.simulatedInterval);

                // --- SIMULACIÓN DE PROGRESO LENTO ---
                const steps = 100;
                const stepTime = this.simulatedDuration / steps;

                this.simulatedInterval = setInterval(() => {
                    if (this.rutProgress < 100) {
                        this.rutProgress++;
                    } else {
                        clearInterval(this.simulatedInterval);
                        this.uploadRut(realFile); // cuando termina, ahora sí subimos el archivo
                    }
                }, stepTime);
            },
            resetRutUpload() {
                this.selectedRutName = null;
                this.rutProgress = 0;
                this.initForm();

                if (this.simulatedInterval) {
                    clearInterval(this.simulatedInterval);
                    this.simulatedInterval = null;
                }

                if (this.$refs.rutUpload) {
                    this.$refs.rutUpload.clearFiles();
                }
            },
            uploadRut(realFile) {
                const formData = new FormData();
                formData.append("rut", realFile);

                this.$http.post("/configuration/extract-rut", formData, {
                    headers: { "Content-Type": "multipart/form-data" }
                })
                .then(({ data }) => {

                    if (!data.success) {
                        this.$message.error(data.message);
                        return;
                    }

                    // Llenar formulario con datos reales del backend
                    Object.assign(this.form, data.fields);

                    if (data.fields.department_id) {
                        this.form.department_id = data.fields.department_id;
                        this.filterMunicipality();
                    }

                    if (data.fields.municipality_id) {
                        this.form.municipality_id = data.fields.municipality_id;
                    }

                    this.$message.success("Datos del RUT cargados correctamente");
                    this.validateRutFields();
                })
                .catch(err => {
                    console.error(err);
                    this.$message.error("Error procesando el RUT");
                });
            },
            validateRutFields() {
                this.errors = {}; // limpiar errores previos

                // Campos críticos obligatorios del RUT
                const requiredCritical = [
                    "type_liability_id",
                    "type_regime_id",
                    "type_document_identification_id",
                ];

                requiredCritical.forEach(field => {
                    if (!this.form[field]) {
                        this.$set(this.errors, field, ["Campo obligatorio."]);
                    }
                });

                // Campos generales del formulario que deberían venir del RUT
                const generalFields = [
                    "nit",
                    "dv",
                    "business_name",
                    "merchant_registration",
                    "phone",
                    "email",
                    "address",
                    "department_id",
                    "municipality_id"
                ];

                generalFields.forEach(field => {
                    if (!this.form[field]) {
                        this.$set(this.errors, field, ["Campo no detectado."]);
                    }
                });
            }
        }
    };
</script>
<style>
    .form-control-feedback {
        color: red;
    }
    .el-step__icon.is-text{
        border: none !important;
    }
    .uploaded-file {
        background: #f8f9fa;
        padding: 8px 12px;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
    }

    .uploaded-file .file-name {
        font-size: 14px;
        color: #333;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .file-close {
        font-size: 20px;
        line-height: 1;
        opacity: 0.6;
        cursor: pointer;
        border: none;
        background: transparent;
    }

    .file-close:hover {
        opacity: 1;
        color: #dc3545;
    }

    /* Contenedor general */
    .uploaded-wrapper {
        border: 1px solid #dcdcdc;
        border-radius: 8px;
        padding: 12px;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    /* Cabecera */
    .uploaded-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Icono PDF */
    .pdf-icon {
        color: #1a1919;
        margin-right: 8px;
    }

    /* Nombre del archivo */
    .uploaded-name {
        font-size: 14px;
        font-weight: 600;
        color: #444;
        max-width: 200px;
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }

    /* Botón X */
    .file-close {
        font-size: 22px;
        opacity: 0.7;
        cursor: pointer;
        border: none;
        background: transparent;
    }

    .file-close:hover {
        opacity: 1;
        color: #dc3545;
    }

    /* Pie */
    .uploaded-footer {
        padding-top: 6px;
    }
    .rut-drag .el-upload-dragger {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 100% !important;
        padding: 20px !important;
    }

    /* Vista móvil */
    @media (max-width: 576px) {
        .rut-drag .el-upload-dragger {
            padding: 16px !important;
            min-height: 120px !important;
        }

        .rut-drag .el-upload__text {
            font-size: 13px !important;
            line-height: 1.3;
        }

        .rut-drag i.el-icon-upload {
            font-size: 28px !important;
        }
    }
</style>
