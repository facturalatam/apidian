<?php

namespace App\Http\Controllers;

use App\Company;
use App\Document;
use App\ReceivedDocument;
use App\Resolution;
use App\Software;
use App\TypeDocument;
use App\User;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class ProductionController extends Controller
{
    public function index($company)
    {
        $company = Company::where('identification_number', $company)->firstOrFail();
        // Preparar datos para cada tipo de documento
        $environmentStatuses = [
            'invoice' => $this->getEnvironmentStatus($company, 'invoice'),
            'support' => $this->getEnvironmentStatus($company, 'support'),
            'event' => $this->getEnvironmentStatus($company, 'event'),
        ];

        $typeDocuments = TypeDocument::all();

        // Cargar documentos para cada tipo
        // IDs de type_document por tipo de vista, para resoluciones (más amplios
        // que los de documentos, para no perder resoluciones existentes).
        $invoiceResolutionIds = [1, 2, 3, 4, 5, 12];
        $supportResolutionIds = [11, 13];

        $invoiceData = array_merge([
            'documents' => Document::where('identification_number', $company->identification_number)
                ->whereIn('type_document_id', [1,2,3,4,5])
                ->filter(request('invoice_search'), request('invoice_search_field'), request('invoice_search_to'))
                ->orderBy('id', 'DESC')
                ->paginate(20, ['*'], 'invoice_page')
                ->withQueryString(),
            'resolution_credit_notes' => Resolution::where('type_document_id', 4)
                ->where('company_id', $company->id)
                ->get()
        ], $this->buildResolutionData($company, $invoiceResolutionIds));

        $supportData = array_merge([
            'documents' => Document::where('identification_number', $company->identification_number)
                ->whereIn('type_document_id', [11,13])
                ->filter(request('support_search'), request('support_search_field'), request('support_search_to'))
                ->orderBy('id', 'DESC')
                ->paginate(20, ['*'], 'support_page')
                ->withQueryString()
        ], $this->buildResolutionData($company, $supportResolutionIds));

        $eventData = [
            'documents' => ReceivedDocument::where('customer', $company->identification_number)
                ->where('state_document_id', 1)
                ->filter(request('event_search'), request('event_search_field'), request('event_search_to'))
                ->orderBy('id', 'DESC')
                ->paginate(10, ['*'], 'event_page')
                ->withQueryString()
        ];

        return view('company.production.index', compact(
            'company',
            'environmentStatuses',
            'typeDocuments',
            'invoiceData',
            'supportData',
            'eventData'
        ));
    }

    /**
     * Carga resoluciones por ambiente (hab/prod) y los type_documents
     * permitidos para los IDs indicados.
     */
    private function buildResolutionData($company, array $typeDocumentIds)
    {
        return [
            'resolutionsHab' => Resolution::where('company_id', $company->id)
                ->whereIn('type_document_id', $typeDocumentIds)
                ->where('type_environment_id', 2)
                ->with(['type_document', 'type_environment'])
                ->orderBy('id', 'DESC')
                ->get(),
            'resolutionsProd' => Resolution::where('company_id', $company->id)
                ->whereIn('type_document_id', $typeDocumentIds)
                ->where('type_environment_id', 1)
                ->with(['type_document', 'type_environment'])
                ->orderBy('id', 'DESC')
                ->get(),
            'resolutionTypeDocuments' => TypeDocument::whereIn('id', $typeDocumentIds)->get(),
        ];
    }

    public function documentsTabs($company, $type = 'invoice')
    {
        $company = Company::where('identification_number', $company)->firstOrFail();
        $company_idnumber = $company->identification_number;
        $environmentStatus = $this->getEnvironmentStatus($company, $type);

        $resolution_credit_notes = Resolution::where('type_document_id', 4)
            ->where('company_id', $company->id)
            ->get();

        $token_company = $company->user->api_token ?? null;
        $typeDocuments = TypeDocument::all();

        // Mapeo de IDs de type_document por tipo de vista, para filtrar resoluciones.
        $resolutionTypeIdsByView = [
            'invoice' => [1, 2, 3, 4, 5],
            'support' => [11, 13],
        ];

        switch ($type) {
            case 'invoice':
                $typeDocumentIds = [1,2,3,4,5];
                $documents = Document::where('identification_number', $company->identification_number)
                    ->whereIn('type_document_id', $typeDocumentIds)
                    ->filter(request('invoice_search'), request('invoice_search_field'), request('invoice_search_to'))
                    ->orderBy('id', 'DESC')
                    ->paginate(20)
                    ->withQueryString();
                $listView = 'company.documents';
                $indexView = 'company.production.invoice.index';
                break;
            case 'support':
                $typeDocumentIds = [11,13];
                $documents = Document::where('identification_number', $company->identification_number)
                    ->whereIn('type_document_id', $typeDocumentIds)
                    ->filter(request('support_search'), request('support_search_field'), request('support_search_to'))
                    ->orderBy('id', 'DESC')
                    ->paginate(20)
                    ->withQueryString();
                $listView = 'company.documents';
                $indexView = 'company.production.support.index';
                break;
            case 'event':
                $documents = ReceivedDocument::where('customer', $company->identification_number)
                    ->where('state_document_id', 1)
                    ->filter(request('event_search'), request('event_search_field'), request('event_search_to'))
                    ->paginate(10)
                    ->withQueryString();
                $listView = 'company.events';
                $indexView = 'company.production.event.index';
                break;
            default:
                $documents = collect();
                $listView = 'company.documents';
                $indexView = 'company.production.invoice.index';
        }

        // Cargar resoluciones por ambiente y filtradas por los type_document_ids de este tipo.
        $resolutionsHab = collect();
        $resolutionsProd = collect();
        $resolutionTypeDocuments = collect();

        if (isset($resolutionTypeIdsByView[$type])) {
            $ids = $resolutionTypeIdsByView[$type];
            $resolutionsHab = Resolution::where('company_id', $company->id)
                ->whereIn('type_document_id', $ids)
                ->where('type_environment_id', 2)
                ->with(['type_document', 'type_environment'])
                ->orderBy('id', 'DESC')
                ->get();
            $resolutionsProd = Resolution::where('company_id', $company->id)
                ->whereIn('type_document_id', $ids)
                ->where('type_environment_id', 1)
                ->with(['type_document', 'type_environment'])
                ->orderBy('id', 'DESC')
                ->get();
            $resolutionTypeDocuments = TypeDocument::whereIn('id', $ids)->get();
        }

        return view('company.production.tabs', compact(
            'documents',
            'resolution_credit_notes',
            'company',
            'company_idnumber',
            'environmentStatus',
            'token_company',
            'type',
            'listView',
            'indexView',
            'typeDocuments',
            'resolutionsHab',
            'resolutionsProd',
            'resolutionTypeDocuments'
        ));
    }

    public function productionInvoice($company)
    {
        $company = Company::where('identification_number', $company)->firstOrFail();
        $environmentStatus = $this->getEnvironmentStatus($company, 'invoice');
        $typeDocuments = TypeDocument::all();

        // Resolución de factura existente (para omitir el paso 2 del wizard).
        $invoiceResolution = Resolution::where('company_id', $company->id)
            ->where('type_document_id', 1)
            ->orderBy('id', 'DESC')
            ->first();

        return view('company.production.invoice.index', compact('company', 'environmentStatus', 'typeDocuments', 'invoiceResolution'));
    }

    public function productionSupport($company)
    {
        $company = Company::where('identification_number', $company)->firstOrFail();
        $environmentStatus = $this->getEnvironmentStatus($company, 'support');

        return view('company.production.support.index', compact('company', 'environmentStatus'));
    }

    public function productionEvent($company)
    {
        $company = Company::where('identification_number', $company)->firstOrFail();
        $environmentStatus = $this->getEnvironmentStatus($company, 'event');

        return view('company.production.event.index', compact('company', 'environmentStatus'));
    }

    /**
     * Método genérico para configurar software de cualquier tipo de documento
     */
    public function storeSoftware(Request $request, $company, $type = 'invoice')
    {
        $company = Company::where('identification_number', $company)->firstOrFail();

        $request->validate([
            'id' => ['required'],
            'pin' => ['required', 'digits:5']
        ], [
            'pin.digits' => 'El PIN debe contener exactamente 5 dígitos numéricos.'
        ]);

        $software = $company->software ?: new Software();
        $isNewSoftware = !$software->exists;

        // Asegurar company_id siempre
        $software->company_id = $company->id;

        // La tabla `software` fue creciendo con campos para nómina/eqdocs/etc.
        // En MySQL con modo estricto, si esos campos son NOT NULL y no tienen default,
        // insertar solo `identifier`/`pin` (factura) falla.
        // Inicializamos los campos requeridos cuando el registro es nuevo.
        if ($isNewSoftware) {
            $defaultUrl = (($company->type_environment_id ?? 2) == 1)
                ? 'https://vpfe.dian.gov.co/WcfDianCustomerServices.svc'
                : 'https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc';

            $software->identifier = '';
            $software->pin = '';
            $software->url = $defaultUrl;

            $software->identifier_payroll = '';
            $software->pin_payroll = '';
            $software->url_payroll = $defaultUrl;

            $software->identifier_eqdocs = '';
            $software->pin_eqdocs = '';
            $software->url_eqdocs = $defaultUrl;
        }

        switch ($type) {
            case 'invoice':
                $software->identifier = $request->id;
                $software->pin = $request->pin;
                if (empty($software->url)) {
                    $software->url = (($company->type_environment_id ?? 2) == 1)
                        ? 'https://vpfe.dian.gov.co/WcfDianCustomerServices.svc'
                        : 'https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc';
                }
                break;
            case 'support':
                $software->identifier_support_document = $request->id;
                $software->pin_support_document = $request->pin;
                break;
            default:
                return back()->with('error', 'Tipo de documento no válido.');
        }
        $software->save();

        return back()->with('success', 'Software configurado correctamente.');
    }

    private function getEnvironmentStatus($company, $type)
    {
        $company->load('software');

        $environmentId = 2;

        switch ($type) {
            case 'invoice':
                $environmentId = $company->type_environment_id ?? 2;
                break;
            case 'support':
                $environmentId = $company->support_document_type_environment_id ?? 2;
                break;
            case 'event':
                $environmentId = $company->event_type_environment_id ?? 2;
                break;
        }

        $hasSoftware = false;
        $softwareInfo = null;

        if ($company->software) {
            switch ($type) {
                case 'invoice':
                    if ($company->software->identifier && $company->software->pin) {
                        $hasSoftware = true;
                        $softwareInfo = [
                            'identifier' => $company->software->identifier,
                            'pin' => $company->software->pin,
                            'name' => $company->software->name ?? 'Software DIAN Facturas'
                        ];
                    }
                    break;
                case 'support':
                    if ($company->software->identifier_support_document && $company->software->pin_support_document) {
                        $hasSoftware = true;
                        $softwareInfo = [
                            'identifier' => $company->software->identifier_support_document,
                            'pin' => $company->software->pin_support_document,
                            'name' => $company->software->name ?? 'Software DIAN Documentos Soporte'
                        ];
                    }
                    break;
            }
        }

        return [
            // El tipo viaja en el arreglo para que el badge sepa contra qué URL del
            // software comparar; sin él caía a la de facturas para todos los tipos.
            'type' => $type,
            'environment_id' => $environmentId,
            'has_software' => $hasSoftware,
            'software_info' => $softwareInfo
        ];
    }

    public function updateEnvironment(Request $request, $company, $type)
    {
        $company = Company::where('identification_number', $company)->firstOrFail();

        $environmentId = $request->input('environment_id');

        if (!in_array($environmentId, [1, 2])) {
            return back()->with('error', 'Ambiente no válido');
        }

        // Sin software solo se movería la columna y la URL de envío quedaría como está,
        // que es exactamente la desincronización que hace mentir al badge.
        if (!$company->software) {
            return back()->with('error', 'La empresa no tiene software configurado. Configure el software antes de cambiar el ambiente.');
        }

        switch ($type) {
            case 'invoice':
                $company->type_environment_id = $environmentId;
                if ($company->software) {
                    $company->software->url = ($environmentId == 1)
                        ? 'https://vpfe.dian.gov.co/WcfDianCustomerServices.svc'
                        : 'https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc';
                    $company->software->save();
                }
                break;
            case 'support':
                $company->support_document_type_environment_id = $environmentId;
                if ($company->software) {
                    $company->software->url_support_document = ($environmentId == 1)
                        ? 'https://vpfe.dian.gov.co/WcfDianCustomerServices.svc'
                        : 'https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc';
                    $company->software->save();
                }
                break;
            case 'event':
                $company->event_type_environment_id = $environmentId;
                if ($company->software) {
                    $company->software->url_event = ($environmentId == 1)
                        ? 'https://vpfe.dian.gov.co/WcfDianCustomerServices.svc'
                        : 'https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc';
                    $company->software->save();
                }
                break;
            default:
                return back()->with('error', 'Tipo de documento no válido');
        }

        $company->save();

        $environmentName = $environmentId == 1 ? 'Producción' : 'Habilitación';
        return back()->with('success', "Ambiente actualizado a {$environmentName} correctamente");
    }

    public function process(Request $request, $company)
    {
        if ($request->ajax()) {
            $step = $request->input('step', 1);
            $testSetId = trim($request->input('test_set_id'));
            $zipkey = $request->input('zipkey');
            $type = $request->input('type', 'invoice');
            // Nomina y Documentos equivalentes (POS) no disponibles en la version Community.
            if ($type === 'pos' || $type === 'payroll') {
                return response()->json(['error' => 'Nomina y Documentos equivalentes no estan disponibles en la version Community.']);
            }
            // \Log::info('Paso a producción iniciado', [
            //     'step' => $step,
            //     'testSetId' => $testSetId,
            //     'zipkey' => $zipkey,
            //     'company' => $company
            // ]);
            $company = Company::with('software', 'user')->where('identification_number', $company)->first();
            if (!$company) {
                // \Log::error('Empresa no encontrada', ['company' => $company]);
                return response()->json(['error' => 'Empresa no encontrada.']);
            }
            $token = $company->user->api_token ?? null;
            if (!$token) {
                // \Log::error('Token de usuario principal no encontrado', ['company_id' => $company->id]);
                return response()->json(['error' => 'No se encontró el token del usuario principal.']);
            }
            $baseUrl = rtrim(config('app.url'), '/');
            $client = new Client(['base_uri' => $baseUrl]);
            $headers = [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json',
            ];
            if ($step == 1 && $type === 'payroll') {
                $payroll_zipkeys = [];
                $payroll_cunes = [];
                $ajuste_zipkeys = [];
                $errors = [];

                $firstConsecutivePayroll = 100301;
                try {
                    $bodyConsecutive = [
                        'type_document_id' => 9,
                        'prefix' => 'NI'
                    ];
                    $responseConsecutive = $client->post('/api/ubl2.1/next-consecutive', [
                        'headers' => $headers,
                        'json' => $bodyConsecutive
                    ]);
                    $dataConsecutive = json_decode($responseConsecutive->getBody(), true);
                    if (isset($dataConsecutive['number'])) {
                        $firstConsecutivePayroll = (int)$dataConsecutive['number'];
                    }
                } catch (\Exception $e) {
                }

                // 2. Enviar 4 nóminas y guardar ZipKey y CUNE
                for ($i = 0; $i < 4; $i++) {
                    // Consecutivo nómina incremental en memoria
                    $consecutivePayroll = $firstConsecutivePayroll + $i;

                    // Enviar nómina
                    $jsonPayroll = [
                        "type_document_id" => 9,
                        "establishment_name" => "TORRE SOFTWARE",
                        "establishment_address" => "CLL 11 NRO 21-73 BRR LA CABAÑA",
                        "establishment_phone" => "3226563672",
                        "establishment_municipality" => 600,
                        "establishment_email" => "alternate_email@alternate.com",
                        "head_note" => "PRUEBA DE TEXTO LIBRE QUE DEBE POSICIONARSE EN EL ENCABEZADO DE PAGINA DE LA REPRESENTACION GRAFICA DE LA FACTURA ELECTRONICA VALIDACION PREVIA DIAN",
                        "foot_note" => "PRUEBA DE TEXTO LIBRE QUE DEBE POSICIONARSE EN EL PIE DE PAGINA DE LA REPRESENTACION GRAFICA DE LA FACTURA ELECTRONICA VALIDACION PREVIA DIAN",
                        "novelty" => [
                            "novelty" => false,
                            "uuidnov" => ""
                        ],
                        "period" => [
                            "admision_date" => "2018-10-10",
                            "settlement_start_date" => "2021-07-01",
                            "settlement_end_date" => "2021-07-31",
                            "worked_time" => "785.00",
                            "issue_date" => "2021-07-28"
                        ],
                        "sendmail" => false,
                        "sendmailtome" => false,
                        "worker_code" => "41946692",
                        "prefix" => "NI",
                        "resolution_number" => 9,
                        "consecutive" => $consecutivePayroll,
                        "payroll_period_id" => 4,
                        "notes" => "PRUEBA DE ENVIO DE NOMINA ELECTRONICA",
                        "worker" => [
                            "type_worker_id" => 1,
                            "sub_type_worker_id" => 1,
                            "payroll_type_document_identification_id" => 3,
                            "municipality_id" => 822,
                            "type_contract_id" => 1,
                            "high_risk_pension" => false,
                            "identification_number" => 41946692,
                            "surname" => "CARDONA",
                            "second_surname" => "VILLADA",
                            "first_name" => "ELIZABETH",
                            "middle_name" => null,
                            "address" => "BRR LIMONAR MZ 6 CS 3 ET 1",
                            "integral_salarary" => false,
                            "salary" => "1500000.00",
                            "email" => "prueba@somehost.com"
                        ],
                        "payment" => [
                            "payment_method_id" => 10,
                            "bank_name" => "BANCO DAVIVIENDA",
                            "account_type" => "AHORROS",
                            "account_number" => "12607060328"
                        ],
                        "payment_dates" => [
                            [
                                "payment_date" => "2021-03-10"
                            ]
                        ],
                        "accrued" => [
                            "worked_days" => 30,
                            "salary" => "750000.00",
                            "transportation_allowance" => "115000.00",
                            "accrued_total" => "859000.00"
                        ],
                        "deductions" => [
                            "eps_type_law_deductions_id" => 1,
                            "eps_deduction" => "60000.00",
                            "pension_type_law_deductions_id" => 5,
                            "pension_deduction" => "60000.00",
                            "deductions_total" => "120000.00"
                        ]
                    ];
                    $jsonPayroll['consecutive'] = $consecutivePayroll;
                    \Log::info("payroll_envio", $jsonPayroll);
                    try {
                        $response = $client->post("/api/ubl2.1/payroll/{$testSetId}", [
                            'headers' => $headers,
                            'json' => $jsonPayroll
                        ]);
                        $result = json_decode($response->getBody(), true);
                        $zipkey = $result['ResponseDian']['Envelope']['Body']['SendTestSetAsyncResponse']['SendTestSetAsyncResult']['ZipKey'] ?? null;
                        if ($zipkey) {
                            $payroll_zipkeys[] = [
                                'zipkey' => $zipkey,
                                'consecutive' => $consecutivePayroll
                            ];
                        } else {
                            $errors[] = "Nómina $i: No se obtuvo ZipKey.";
                            continue;
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Nómina $i: " . $e->getMessage();
                        continue;
                    }
                }

                // 2. Consultar los 4 ZipKey de nómina y obtener CUNE
                foreach ($payroll_zipkeys as $idx => $item) {
                    try {
                        $bodyStatus = ["is_payroll" => true];
                        $responseStatus = $client->post("/api/ubl2.1/status/zip/{$item['zipkey']}", [
                            'headers' => $headers,
                            'json' => $bodyStatus
                        ]);
                        $statusResult = json_decode($responseStatus->getBody(), true);
                        \Log::info("payroll",  $statusResult);
                        $dianResponse = $statusResult['ResponseDian']['Envelope']['Body']['GetStatusZipResponse']['GetStatusZipResult']['DianResponse'] ?? null;
                        $cune = $dianResponse['XmlDocumentKey'] ?? null;
                        // Mejor validación y mensaje de error real
                        if (!$dianResponse || $dianResponse['IsValid'] !== "true" || !$cune) {
                            $desc = $dianResponse['StatusDescription'] ?? 'No fue autorizada por la DIAN.';
                            $errors[] = "Nómina " . ($idx + 1) . ": " . $desc;
                            $payroll_cunes[] = null;
                        } else {
                            $payroll_cunes[] = $cune;
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Nómina " . ($idx + 1) . ": Error consultando ZipKey - " . $e->getMessage();
                        $payroll_cunes[] = null;
                    }
                }
                sleep(30);
                // 3. Enviar 4 notas de ajuste usando consecutivo y CUNE de cada nómina
                $firstConsecutiveAdjust = 100301;
                try {
                    $bodyConsecutive = [
                        'type_document_id' => 10,
                        'prefix' => 'NA'
                    ];
                    $responseConsecutive = $client->post('/api/ubl2.1/next-consecutive', [
                        'headers' => $headers,
                        'json' => $bodyConsecutive
                    ]);
                    $dataConsecutive = json_decode($responseConsecutive->getBody(), true);
                    if (isset($dataConsecutive['number'])) {
                        $firstConsecutiveAdjust = (int)$dataConsecutive['number'];
                    }
                } catch (\Exception $e) {
                }

                for ($i = 0; $i < 4; $i++) {
                    if (!$payroll_cunes[$i]) {
                        $errors[] = "Ajuste " . ($i + 1) . ": No se puede enviar porque la nómina no fue autorizada.";
                        continue;
                    }
                    // Consecutivo incremental en memoria
                    $consecutiveAdjust = $firstConsecutiveAdjust + $i;
                    $jsonAdjust = [
                        "type_document_id" => 10,
                        "type_note" => 1,
                        "establishment_name" => "TORRE SOFTWARE",
                        "establishment_address" => "CLL 11 NRO 21-73 BRR LA CABAÑA",
                        "establishment_phone" => "3226563672",
                        "establishment_municipality" => 600,
                        "establishment_email" => "alternate_email@alternate.com",
                        "head_note" => "PRUEBA DE TEXTO LIBRE QUE DEBE POSICIONARSE EN EL ENCABEZADO DE PAGINA DE LA REPRESENTACION GRAFICA DE LA FACTURA ELECTRONICA VALIDACION PREVIA DIAN",
                        "foot_note" => "PRUEBA DE TEXTO LIBRE QUE DEBE POSICIONARSE EN EL PIE DE PAGINA DE LA FACTURA ELECTRONICA VALIDACION PREVIA DIAN",
                        "novelty" => [
                            "novelty" => false,
                            "uuidnov" => ""
                        ],
                        "period" => [
                            "admision_date" => "2018-10-10",
                            "settlement_start_date" => "2021-07-01",
                            "settlement_end_date" => "2021-07-31",
                            "worked_time" => "785.00",
                            "issue_date" => "2021-07-28"
                        ],
                        "sendmail" => false,
                        "sendmailtome" => false,
                        "worker_code" => "41946692",
                        "prefix" => "NA",
                        "resolution_number" => 10,
                        "consecutive" => $consecutiveAdjust,
                        "payroll_period_id" => 4,
                        "notes" => "PRUEBA DE ENVIO DE AJUSTE DE NOMINA ELECTRONICA",
                        "worker" => [
                            "type_worker_id" => 1,
                            "sub_type_worker_id" => 1,
                            "payroll_type_document_identification_id" => 3,
                            "municipality_id" => 822,
                            "type_contract_id" => 1,
                            "high_risk_pension" => false,
                            "identification_number" => 41946692,
                            "surname" => "CARDONA",
                            "second_surname" => "VILLADA",
                            "first_name" => "ELIZABETH",
                            "middle_name" => null,
                            "address" => "BRR LIMONAR MZ 6 CS 3 ET 1",
                            "integral_salarary" => false,
                            "salary" => "1500000.00",
                            "email" => "prueba@gmail.com"
                        ],
                        "payment" => [
                            "payment_method_id" => 10,
                            "bank_name" => "BANCO DAVIVIENDA",
                            "account_type" => "AHORROS",
                            "account_number" => "1111111111"
                        ],
                        "payment_dates" => [
                            [
                                "payment_date" => "2021-03-10"
                            ]
                        ],
                        "accrued" => [
                            "worked_days" => 30,
                            "salary" => "750000.00",
                            "transportation_allowance" => "115000.00",
                            "accrued_total" => "859000.00"
                        ],
                        "deductions" => [
                            "eps_type_law_deductions_id" => 1,
                            "eps_deduction" => "60000.00",
                            "pension_type_law_deductions_id" => 5,
                            "pension_deduction" => "60000.00",
                            "deductions_total" => "120000.00"
                        ]
                    ];
                    $jsonAdjust['consecutive'] = $consecutiveAdjust;
                    $jsonAdjust['predecessor'] = [
                        "predecessor_number" => $payroll_zipkeys[$i]['consecutive'],
                        "predecessor_cune" => $payroll_cunes[$i],
                        "predecessor_issue_date" => date('Y-m-d')
                    ];
                    \Log::info("ajuste_envio", $jsonAdjust);
                    try {
                        $response = $client->post("/api/ubl2.1/payroll-adjust-note/{$testSetId}", [
                            'headers' => $headers,
                            'json' => $jsonAdjust
                        ]);
                        $result = json_decode($response->getBody(), true);
                        $zipkeyAjuste = $result['ResponseDian']['Envelope']['Body']['SendTestSetAsyncResponse']['SendTestSetAsyncResult']['ZipKey'] ?? null;
                        if ($zipkeyAjuste) {
                            $ajuste_zipkeys[] = $zipkeyAjuste;
                        } else {
                            $errors[] = "Ajuste " . ($i + 1) . ": No se obtuvo ZipKey.";
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Ajuste " . ($i + 1) . ": " . $e->getMessage();
                    }
                }

                // 4. Consultar los 4 ZipKey de las notas de ajuste
                $ajuste_statuses = [];
                foreach ($ajuste_zipkeys as $idx => $zipkey) {
                    try {
                        $body = ["is_payroll" => true];
                        $response = $client->post("/api/ubl2.1/status/zip/{$zipkey}", [
                            'headers' => $headers,
                            'json' => $body
                        ]);
                        $ajuste_statuses[] = json_decode($response->getBody(), true);
                        \Log::info("ajuste", $ajuste_statuses);
                    } catch (\Exception $e) {
                        $ajuste_statuses[] = ['error' => $e->getMessage()];
                    }
                }

                // Respuesta final
                if ($errors) {
                    return response()->json([
                        'error' => implode('<br>', $errors),
                        'zipkeys' => array_column($payroll_zipkeys, 'zipkey'),
                        'ajuste_zipkeys' => $ajuste_zipkeys,
                        'ajuste_statuses' => $ajuste_statuses
                    ]);
                }
                return response()->json([
                    'success' => true,
                    'zipkeys' => array_column($payroll_zipkeys, 'zipkey'),
                    'ajuste_zipkeys' => $ajuste_zipkeys,
                    'ajuste_statuses' => $ajuste_statuses
                ]);
            }
            if ($step == 3) {
                // Solo se mueve el tipo que se está migrando. Antes se enviaba el ambiente
                // de facturas en cada paso a producción, así que habilitar otro tipo bajaba
                // las facturas a habilitación, y soporte/eventos no se tocaban nunca porque
                // no tenían rama en este paso.
                $envColumnByType = [
                    'invoice' => 'type_environment_id',
                    'support' => 'support_document_type_environment_id',
                    'event'   => 'event_type_environment_id',
                ];

                if (!isset($envColumnByType[$type])) {
                    return response()->json(['error' => 'Tipo de documento no válido para el cambio de ambiente.']);
                }

                try {
                    $envData = [$envColumnByType[$type] => 1];
                    $envResponse = $client->put('/api/ubl2.1/config/environment', [
                        'headers' => $headers,
                        'json' => $envData
                    ]);
                    $envResult = json_decode($envResponse->getBody(), true);
                    return response()->json(['success' => true, 'env_result' => $envResult]);
                } catch (\Exception $e) {
                    return response()->json(['error' => 'Error cambiando ambiente: ' . $e->getMessage()]);
                }
            }
            if ($step == 1) {
                if (empty($testSetId)) {
                    return response()->json(['error' => 'Debe ingresar el TestSetId entregado por la DIAN.']);
                }

                $bodyConsecutive = [
                    'type_document_id' => 1,
                    'prefix' => 'SETP'
                ];
                try {
                    $responseConsecutive = $client->post('/api/ubl2.1/next-consecutive', [
                        'headers' => $headers,
                        'json' => $bodyConsecutive
                    ]);
                    $dataConsecutive = json_decode($responseConsecutive->getBody(), true);
                    $consecutive = isset($dataConsecutive['number']) ? (int)$dataConsecutive['number'] : 990000001;
                } catch (\Exception $e) {
                    $consecutive = 990000001;
                }
                if ($type === 'pos') {
                    $json = [
                        "number" => $consecutive,
                        "type_document_id" => 15,
                        "date" => now()->format('Y-m-d'),
                        "time" => now()->format('H:i:s'),
                        "postal_zone_code" => "630003",
                        "resolution_number" => "18760000001",
                        "prefix" => "EPOS",
                        "notes" => "ESTA ES UNA NOTA DE PRUEBA, ESTA ES UNA NOTA DE PRUEBA, ...",
                        "sendmail" => true,
                        "sendmailtome" => false,
                        "foot_note" => "PRUEBA DE TEXTO LIBRE QUE DEBE POSICIONARSE EN EL PIE DE PAGINA DE LA REPRESENTACION GRAFICA DE LA FACTURA ELECTRONICA VALIDACION PREVIA DIAN",
                        "software_manufacturer" => [
                            "name" => "ALEXANDER OBANDO LONDONO",
                            "business_name" => "TORRE SOFTWARE",
                            "software_name" => "BABEL"
                        ],
                        "buyer_benefits" => [
                            "code" => "89008003",
                            "name" => "INVERSIONES DAVAL SAS",
                            "points" => "100"
                        ],
                        "cash_information" => [
                            "plate_number" => "DF-000-12345",
                            "location" => "HOTEL OVERLOOK RECEPCION",
                            "cashier" => "JACK TORRANCE",
                            "cash_type" => "CAJA PRINCIPAL",
                            "sales_code" => "EPOS1",
                            "subtotal" => "1000000.00"
                        ],
                        "customer" => [
                            "identification_number" => 89008003,
                            "dv" => 2,
                            "name" => "INVERSIONES DAVAL SAS",
                            "phone" => "3103891693",
                            "address" => "CLL 4 NRO 33-90",
                            "email" => "alexanderobandolondono@gmail.com",
                            "merchant_registration" => "0000000-00",
                            "type_document_identification_id" => 6,
                            "type_organization_id" => 1,
                            "type_liability_id" => 7,
                            "municipality_id" => 822,
                            "type_regime_id" => 1
                        ],
                        "payment_form" => [
                            "payment_form_id" => 1,
                            "payment_method_id" => 30,
                            "payment_due_date" => now()->format('Y-m-d'),
                            "duration_measure" => "0"
                        ],
                        "legal_monetary_totals" => [
                            "line_extension_amount" => "840336.134",
                            "tax_exclusive_amount" => "840336.134",
                            "tax_inclusive_amount" => "1000000.00",
                            "payable_amount" => "1000000.00"
                        ],
                        "tax_totals" => [
                            [
                                "tax_id" => 1,
                                "tax_amount" => "159663.865",
                                "percent" => "19.00",
                                "taxable_amount" => "840336.134"
                            ]
                        ],
                        "invoice_lines" => [
                            [
                                "unit_measure_id" => 70,
                                "invoiced_quantity" => "1",
                                "line_extension_amount" => "840336.134",
                                "free_of_charge_indicator" => false,
                                "tax_totals" => [
                                    [
                                        "tax_id" => 1,
                                        "tax_amount" => "159663.865",
                                        "taxable_amount" => "840336.134",
                                        "percent" => "19.00"
                                    ]
                                ],
                                "description" => "COMISION POR SERVICIOS",
                                "notes" => "ESTA ES UNA PRUEBA DE NOTA DE DETALLE DE LINEA.",
                                "code" => "COMISION",
                                "type_item_identification_id" => 4,
                                "price_amount" => "1000000.00",
                                "base_quantity" => "1"
                            ]
                        ]
                    ];
                    $endpoint = "/api/ubl2.1/eqdoc/{$testSetId}";
                } else {
                    $json = [
                        "number" => $consecutive,
                        "type_document_id" => 1,
                        "date" => now()->format('Y-m-d'),
                        "time" => now()->format('H:i:s'),
                        "resolution_number" => 18760000001,
                        "prefix" => "SETP",
                        "customer" => [
                            "identification_number" => "900428042",
                            "name" => "TAMPAC TECNOLOGÍA EN AUTOMATIZACIÓN SAS"
                        ],
                        "payment_form" => [
                            "payment_form_id" => 1,
                            "payment_method_id" => 30,
                            "payment_due_date" => now()->format('Y-m-d'),
                            "duration_measure" => "30"
                        ],
                        "legal_monetary_totals" => [
                            "line_extension_amount" => "2000.00",
                            "tax_exclusive_amount" => "2000.00",
                            "tax_inclusive_amount" => "2380.00",
                            "payable_amount" => "2380.00"
                        ],
                        "tax_totals" => [
                            [
                                "tax_id" => 1,
                                "tax_amount" => "380.00",
                                "percent" => "19",
                                "taxable_amount" => "2000.00"
                            ]
                        ],
                        "invoice_lines" => [
                            [
                                "unit_measure_id" => 70,
                                "invoiced_quantity" => "1",
                                "line_extension_amount" => "1000.00",
                                "free_of_charge_indicator" => false,
                                "description" => "Producto de prueba 1",
                                "code" => "PRUEBA1",
                                "type_item_identification_id" => 4,
                                "price_amount" => "1000.00",
                                "base_quantity" => "1",
                                "tax_totals" => [
                                    [
                                        "tax_id" => 1,
                                        "tax_amount" => "190.00",
                                        "taxable_amount" => "1000.00",
                                        "percent" => "19.00"
                                    ]
                                ]
                            ],
                            [
                                "unit_measure_id" => 70,
                                "invoiced_quantity" => "1",
                                "line_extension_amount" => "1000.00",
                                "free_of_charge_indicator" => false,
                                "description" => "Producto de prueba 2",
                                "code" => "PRUEBA2",
                                "type_item_identification_id" => 4,
                                "price_amount" => "1000.00",
                                "base_quantity" => "1",
                                "tax_totals" => [
                                    [
                                        "tax_id" => 1,
                                        "tax_amount" => "190.00",
                                        "taxable_amount" => "1000.00",
                                        "percent" => "19.00"
                                    ]
                                ]
                            ]
                        ]
                    ];
                    $endpoint = "/api/ubl2.1/invoice/{$testSetId}";
                }
                try {
                    $response = $client->post($endpoint, [
                        'headers' => $headers,
                        'json' => $json
                    ]);
                    $result = json_decode($response->getBody(), true);
                    // \Log::info('Respuesta envío factura de prueba', ['result' => $result]);
                    $zipkey = $result['ResponseDian']['Envelope']['Body']['SendTestSetAsyncResponse']['SendTestSetAsyncResult']['ZipKey'] ?? null;

                    if (!$zipkey) {
                        $mensaje = $result['message'] ?? 'No se obtuvo ZipKey.';
                        // \Log::error('No se obtuvo ZipKey', ['result' => $result]);
                        return response()->json(['error' => $mensaje]);
                    }
                    return response()->json(['success' => true, 'zipkey' => $zipkey]);
                } catch (\Exception $e) {
                    // \Log::error('Error al enviar el documento', ['exception' => $e]);
                    return response()->json(['error' => 'Error al enviar el documento: ' . $e->getMessage()]);
                }
            }

            if ($step == 2) {
                // \Log::info('Paso 2: Consultar ZipKey', ['zipkey' => $zipkey]);
                if (!$zipkey) {
                    // \Log::warning('ZipKey vacío');
                    return response()->json(['error' => 'No se recibió ZipKey.']);
                }
                try {
                    $body = [
                        "sendmail" => false,
                        "sendmailtome" => false,
                        "is_payroll" => false,
                        "is_eqdoc" => false
                    ];
                    $response = $client->post("/api/ubl2.1/status/zip/{$zipkey}", [
                        'headers' => $headers,
                        'json' => $body
                    ]);
                    // \Log::info('Respuesta RAW de la DIAN al consultar ZipKey', [
                    //     'raw_response' => (string) $response->getBody()
                    // ]);
                    $data = json_decode($response->getBody(), true);
                    // \Log::info('Respuesta ARRAY consulta ZipKey', [
                    //     'data' => $data
                    // ]);

                    $dianResponse = $data['ResponseDian']['Envelope']['Body']['GetStatusZipResponse']['GetStatusZipResult']['DianResponse'] ?? null;
                    if ($dianResponse && isset($dianResponse['IsValid']) && $dianResponse['IsValid'] === "false") {
                        $desc = $dianResponse['StatusDescription'] ?? 'Error desconocido';
                        $statusCode = $dianResponse['StatusCode'] ?? '';
                        $statusMsg = $dianResponse['StatusMessage'] ?? '';
                        $errorMsg = '';
                        if (stripos($desc, 'proceso de validación') !== false) {
                            return response()->json([
                                'error' => 'El documento está en proceso de validación en la DIAN. Por favor, espera unos minutos y vuelve a consultar el ZipKey.'
                            ]);
                        }
                        if (is_array($statusCode)) {
                            $statusCode = json_encode($statusCode);
                        }
                        if (is_array($statusMsg)) {
                            $statusMsg = json_encode($statusMsg);
                        }

                        if (isset($dianResponse['ErrorMessage']['string'])) {
                            if (is_array($dianResponse['ErrorMessage']['string'])) {
                                $errorMsg = implode('<br>', $dianResponse['ErrorMessage']['string']);
                            } else {
                                $errorMsg = $dianResponse['ErrorMessage']['string'];
                            }
                        }

                        $fullMsg = $desc;
                        if ($statusMsg) {
                            $fullMsg .= '<br>' . $statusMsg;
                        }
                        if ($errorMsg) {
                            $fullMsg .= '<br>' . $errorMsg;
                        }

                        return response()->json(['error' => $fullMsg]);
                    }
                    return response()->json(['success' => true, 'zipkey_status' => $data]);
                } catch (\Exception $e) {
                    // \Log::error('Error consultando ZipKey', ['exception' => $e]);
                    return response()->json(['error' => 'Error consultando ZipKey: ' . $e->getMessage()]);
                }
            }

        }

        return back()->with('error', 'Petición inválida.');
    }

    private function consultarZipKey($zipKey, $token)
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $client = new Client(['base_uri' => $baseUrl]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];
        $body = [
            "sendmail" => false,
            "sendmailtome" => false,
            "is_payroll" => false,
            "is_eqdoc" => false
        ];

        try {
            $response = $client->post("/api/ubl2.1/status/zip/{$zipKey}", [
                'headers' => $headers,
                'json' => $body
            ]);
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            // \Log::error('Error consultando ZipKey', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    public function consultarResoluciones(Request $request, $company)
    {
        $type = $request->input('type', 'invoice');
        $company = Company::with(['software', 'user'])->where('identification_number', $company)->first();
        if (!$company || !$company->software) {
            return response()->json(['error' => 'Empresa o software no encontrado'], 404);
        }

        $token = $company->user->api_token ?? null;
        $IDSoftware = $company->software->identifier ?? null;

        if (!$token || !$IDSoftware) {
            return response()->json(['error' => 'Token o IDSoftware no disponible'], 400);
        }
        $baseUrl = rtrim(config('app.url'), '/');
        $client = new Client(['base_uri' => $baseUrl]);

        try {
            $response = $client->post('/api/ubl2.1/numbering-range', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'IDSoftware' => $IDSoftware
                ]
            ]);
            $data = json_decode($response->getBody(), true);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
