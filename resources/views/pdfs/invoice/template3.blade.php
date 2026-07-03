<!DOCTYPE html>
<html lang="es">
{{-- <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>FACTURA ELECTRONICA Nro: {{$resolution->prefix}} - {{$request->number}}</title>
</head> --}}

{{-- Header incluido en el template--}}

<table style="width: 100%; font-size: 8px;">
    <!-- Logo en la parte superior -->
    <tr>
        <td style="text-align: center;">
            <img style="max-width: 170px; height: auto; margin-bottom: 5px;" src="{{$imgLogo}}" alt="logo">
        </td>
    </tr>

    <!-- Información de la Empresa -->
    <tr>
        <td style="text-align: center;">
            <strong>{{$user->name}}</strong><br>
            @if(isset($request->establishment_name) && $request->establishment_name != 'Oficina Principal')
                <strong>{{$request->establishment_name}}</strong><br>
            @endif
            NIT: {{$company->identification_number}}-{{$company->dv}} - Dirección: {{$company->address}}<br>
            Tel: {{$company->phone}} - Correo: {{$user->email}}<br>
        </td>
    </tr>

    <!-- Detalles de la Factura -->
    <tr>
        <td style="text-align: center;">
            <strong>FACTURA ELECTRONICA DE VENTA {{$resolution->prefix}} - {{$request->number}}</strong><br>
            Fecha Emisión: {{$date}} - Fecha Validación DIAN: {{$date}}<br>
            Hora Validación DIAN: {{$time}}<br>
        </td>
    </tr>

    <!-- Información Adicional y Condiciones -->
    <tr>
        <td style="text-align: center;">
            @if(isset($request->ivaresponsable) && $request->ivaresponsable != $company->type_regime->name)
                {{$company->type_regime->name}} - {{$request->ivaresponsable}}<br>
            @endif
            @if(isset($request->nombretipodocid))
                Tipo Documento ID: {{$request->nombretipodocid}}<br>
            @endif
            @if(isset($request->tarifaica) && $request->tarifaica != '100')
                TARIFA ICA: {{$request->tarifaica}}‰<br>
            @endif
            @if(isset($request->actividadeconomica))
                ACTIVIDAD ECONOMICA: {{$request->actividadeconomica}}<br>
            @endif
            @if(isset($request->seze))
                <?php
                    $aseze = substr($request->seze, 0, strpos($request->seze, '-', 0));
                    $asociedad = substr($request->seze, strpos($request->seze, '-', 0) + 1);
                ?>
                Regimen ZESE Año: {{$aseze}} Constitución Sociedad Año: {{$asociedad}}<br>
            @endif
            Resolución de Facturación Electrónica No. {{$resolution->resolution}} de {{$resolution->resolution_date}}<br>
            Prefijo: {{$resolution->prefix}}, Rango {{$resolution->from}} al {{$resolution->to}}<br>
            Vigencia Desde: {{$resolution->date_from}} Hasta: {{$resolution->date_to}}<br>
            @if (isset($request->seze))
                FAVOR ABSTENERSE DE PRACTICAR RETENCION EN LA FUENTE REGIMEN ESPECIAL DECRETO 2112 DE 2019<br>
            @endif
        </td>
    </tr>

    <!-- Información de Contacto del Establecimiento -->
    <tr>
        <td style="text-align: center;">
            @if(isset($request->establishment_address))
                {{$request->establishment_address}} -
            @else
                {{$company->address}} -
            @endif
            @inject('municipality', 'App\Municipality')
            @if(isset($request->establishment_municipality))
                {{$municipality->findOrFail($request->establishment_municipality)['name']}} - {{$municipality->findOrFail($request->establishment_municipality)['department']['name']}} -
            @else
                {{$company->municipality->name}} - {{$municipality->findOrFail($company->municipality->id)['department']['name']}} -
            @endif
            {{$company->country->name}}<br>
            @if(isset($request->establishment_phone))
                Teléfono: {{$request->establishment_phone}}<br>
            @else
                Teléfono: {{$company->phone}}<br>
            @endif
            @if(isset($request->establishment_email))
                E-mail: {{$request->establishment_email}}<br>
            @else
                E-mail: {{$user->email}}<br>
            @endif
        </td>
    </tr>
</table>


{{--Fin del Header--}}

<hr>

<body>
    <table style="font-size: 12px">
        <tr>
            <td class="vertical-align-top" style="width: 60%;">
                <table>
                    <tr>
                        <td>CC o NIT:</td>
                        <td>{{$customer->company->identification_number}}-{{$request->customer['dv'] ?? NULL}} </td>
                    </tr>
                    <tr>
                        <td>Cliente:</td>
                        <td>{{$customer->name}}</td>
                    </tr>
                    <tr>
                        <td>Régimen:</td>
                        <td>{{$customer->company->type_regime->name}}</td>
                    </tr>
                    <tr>
                        <td>Obligación:</td>
                        <td>{{$customer->company->type_liability_names ?? $customer->company->type_liability->name}}</td>
                    </tr>
                    <tr>
                        <td>Dirección:</td>
                        <td>{{$customer->company->address}}</td>
                    </tr>
                    <tr>
                        <td>Ciudad:</td>
                        @if($customer->company->country->id == 46)
                            <td>{{$customer->company->municipality->name}} - {{$customer->company->country->name}} </td>
                        @else
                            <td>{{$customer->company->municipality_name}} - {{$customer->company->state_name}} - {{$customer->company->country->name}} </td>
                        @endif
                    </tr>
                    <tr>
                        <td>Teléfono:</td>
                        <td>{{$customer->company->phone}}</td>
                    </tr>
                    <tr>
                        <td>Email:</td>
                        <td>{{$customer->email}}</td>
                    </tr>
                </table>
            </td>
            <td class="vertical-align-top" style="width: 40%; padding-left: 1rem">
                <table>
                    @if(!empty($paymentForm)&& $paymentForm->count() > 0)
                    <tr>
                        <td>Forma de Pago:</td>
                        <td>{{$paymentForm[0]->name}}</td>
                    </tr>
                    <tr>
                        <td>Medios de Pago:</td>
                        <td>
                            @foreach ($paymentForm as $paymentF)
                                {{$paymentF->nameMethod}}<br>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <td>Plazo Para Pagar:</td>
                        <td>{{$paymentForm[0]->duration_measure}} Dias</td>
                    </tr>
                    <tr>
                        <td>Fecha Vencimiento:</td>
                        <td>{{$paymentForm[0]->payment_due_date}}</td>
                    </tr>
                    @endif
                    @if(isset($request['seller']) && isset($request['seller']['name']))
                    <tr>
                        <td>Vendedor:</td>
                        <td>{{$request['seller']['name']}}</td>
                    </tr>
                    @endif
                    @if(isset($request['order_reference']['id_order']))
                    <tr>
                        <td>Número Pedido:</td>
                        <td>{{$request['order_reference']['id_order']}}</td>
                    </tr>
                    @endif
                    @if(isset($request['order_reference']['issue_date_order']))
                    <tr>
                        <td>Fecha Pedido:</td>
                        <td>{{$request['order_reference']['issue_date_order']}}</td>
                    </tr>
                    @endif
                    @if(isset($healthfields))
                    <tr>
                        <td>Inicio Periodo Facturación:</td>
                        <td>{{$healthfields->invoice_period_start_date}}</td>
                    </tr>
                    <tr>
                        <td>Fin Periodo Facturación:</td>
                        <td>{{$healthfields->invoice_period_end_date}}</td>
                    </tr>
                    @endif
                    @if(isset($request['number_account']))
                    <tr>
                        <td>Número de cuenta:</td>
                        <td>{{$request['number_account'] }}</td>
                    </tr>
                    @endif
                    @if(isset($request['deliveryterms']))
                    <tr>
                        <td>Terminos de Entrega:</td>
                        <td>
                            {{ isset($request['deliveryterms']['loss_risk_responsibility_code']) ? $request['deliveryterms']['loss_risk_responsibility_code'] : '' }}
                            -
                            {{ isset($request['deliveryterms']['loss_risk']) ? $request['deliveryterms']['loss_risk'] : '' }}
                        </td>
                    </tr>
                    <tr>
                        <td>T.R.M:</td>
                        <td>{{ isset($request['k_supplement']['FctConvCop']) ? number_format($request['k_supplement']['FctConvCop'], 2) : '0.00' }}</td>
                    </tr>
                    <tr>
                        <td>Destino</td>
                        <td>{{ isset($request['k_supplement']['destination']) ? $request['k_supplement']['destination'] : '' }}</td>
                    </tr>
                    <tr>
                        @inject('currency', 'App\TypeCurrency')
                        <td>Tipo Moneda:</td>
                        <td>
                            {{ isset($request['k_supplement']['MonedaCop']) ? ($currency->where('code', 'like', '%'.$request['k_supplement']['MonedaCop'].'%')->first()['name'] ?? '') : '' }}
                        </td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <hr>

    @isset($healthfields)
        <table class="table" style="width: 100%;">
            <thead>
                <tr>
                    <th class="text-center" style="width: 100%;">INFORMACION REFERENCIAL SECTOR SALUD</th>
                </tr>
            </thead>
        </table>
        <table class="table" style="width: 100%">
    <thead>
        <th class="text-center" style="width: 12%;">Cod Prestador</th>
        <th class="text-center" style="width: 29%;">Info. Contrat.</th>
        <th class="text-center" style="width: 18%;">Info. de Pagos</th>
    </thead>
    <tbody>
        @foreach ($healthfields->users_info as $item)
        <tr>
            <td style="font-size: 8px;">{{$item->provider_code}}</td>
            <td>
                <p style="font-size: 8px">Modalidad Contratacion: {{$item->health_contracting_payment_method()->name}}</p>
                <p style="font-size: 8px">Nro Contrato: {{$item->contract_number}}</p>
                <p style="font-size: 8px">Cobertura: {{$item->health_coverage()->name}}</p>
            </td>
            <td>
                <p style="font-size: 8px">Copago: {{number_format($item->co_payment, 2)}}</p>
                <p style="font-size: 8px">Cuota Moderardora: {{number_format($item->moderating_fee, 2)}}</p>
                <p style="font-size: 8px">Pagos Compartidos: {{number_format($item->shared_payment, 2)}}</p>
                <p style="font-size: 8px">Anticipos: {{number_format($item->advance_payment, 2)}}</p>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

        <br>
    @endisset

    <?php
        $showPurchaseOrderColumn = false;
        foreach($request['invoice_lines'] as $line) {
            if(isset($line['purchase_order_number']) && $line['purchase_order_number']) {
                $showPurchaseOrderColumn = true;
                break;
            }
        }
    ?>
        <table class="tabla-items">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Código</th>
                    <th class="desc">Descripción</th>
                    @if($showPurchaseOrderColumn)
                        <th>Orden de compra</th>
                    @endif
                    <th>Cant.</th>
                    <th>UM</th>
                    <th>Val. Unit</th>
                    <th>IVA/IC</th>
                    <th>Dcto</th>
                    <th>%</th>
                    <th>Val. Item</th>
                </tr>
            </thead>
            <tbody>
                <?php $ItemNro = 0; ?>
                @foreach($request['invoice_lines'] as $item)
                    <?php $ItemNro = $ItemNro + 1; ?>
                    <tr>
                        @inject('um', 'App\UnitMeasure')
                        @if($item['description'] == 'Administración' or $item['description'] == 'Imprevisto' or $item['description'] == 'Utilidad')
                            <td>{{$ItemNro}}</td>
                            <td class="text-right">
                                {{$item['code']}}
                            </td>
                            <td>{{$item['description']}}</td>
                            <td class="text-right"></td>
                            <td class="text-right"></td>
                            <td class="text-right">{{number_format($item['price_amount'], 2)}}</td>
                            <td class="text-right">{{number_format($item['tax_totals'][0]['tax_amount'], 2)}}</td>
                            @if(isset($item['allowance_charges']))
                                <td class="text-right">{{number_format($item['allowance_charges'][0]['amount'], 2)}}</td>
                                <td class="text-right">{{number_format(($item['allowance_charges'][0]['amount'] * 100) / $item['allowance_charges'][0]['base_amount'], 2)}}</td>
                            @else
                                <td class="text-right">{{number_format("0", 2)}}</td>
                                <td class="text-right">{{number_format("0", 2)}}</td>
                            @endif
                            <td class="text-right">{{number_format($item['invoiced_quantity'] * $item['price_amount'], 2)}}</td>
                        @else
                            <td>{{$ItemNro}}</td>
                            <td>{{$item['code']}}</td>
                            <td>
                                @if(isset($item['notes']))
                                    {{$item['description']}}
                                    <p style="font-style: italic; font-size: 90%">{{$item['notes']}}</p>
                                @else
                                    {{$item['description']}}
                                @endif
                                @if(!empty($item['lot_code']) || !empty($item['date_of_due']))
                                    <p style="font-style: italic; font-size: 60%">
                                        @if(!empty($item['lot_code']))<strong>Lote:</strong> {{$item['lot_code']}}@endif
                                        @if(!empty($item['lot_code']) && !empty($item['date_of_due'])) | @endif
                                        @if(!empty($item['date_of_due']))<strong>Vence:</strong> {{$item['date_of_due']}}@endif
                                    </p>
                                @endif
                        @if($showPurchaseOrderColumn)
                            <td>
                                @if(isset($item['purchase_order_number']) && $item['purchase_order_number'])
                                    {{$item['purchase_order_number']}}
                                @endif
                            </td>
                        @endif
                            </td>
                            <td class="text-right">{{number_format($item['invoiced_quantity'], 2)}}</td>
                            <td class="text-right">{{$um->findOrFail($item['unit_measure_id'])['name']}}</td>

                            @if(isset($item['tax_totals']))
                                @if(isset($item['allowance_charges']))
                                    <td class="text-right">{{number_format(($item['line_extension_amount'] + $item['allowance_charges'][0]['amount']) / $item['invoiced_quantity'], 2)}}</td>
                                @else
                                    <td class="text-right">{{number_format($item['line_extension_amount'] / $item['invoiced_quantity'], 2)}}</td>
                                @endif
                            @else
                                @if(isset($item['allowance_charges']))
                                    <td class="text-right">{{number_format(($item['line_extension_amount'] + $item['allowance_charges'][0]['amount']) / $item['invoiced_quantity'], 2)}}</td>
                                @else
                                    <td class="text-right">{{number_format($item['line_extension_amount'] / $item['invoiced_quantity'], 2)}}</td>
                                @endif
                            @endif

                            @if(isset($item['tax_totals']))
                                @if(isset($item['tax_totals'][0]['tax_amount']))
                                    <td class="text-right">{{number_format($item['tax_totals'][0]['tax_amount'] / $item['invoiced_quantity'], 2)}}</td>
                                @else
                                    <td class="text-right">{{number_format(0, 2)}}</td>
                                @endif
                            @else
                                <td class="text-right">E</td>
                            @endif

                            @if(isset($item['allowance_charges']))
                                <td class="text-right">{{number_format($item['allowance_charges'][0]['amount'] / $item['invoiced_quantity'], 2)}}</td>
                                <td class="text-right">{{number_format(($item['allowance_charges'][0]['amount'] * 100) / $item['allowance_charges'][0]['base_amount'], 2)}}</td>
                                @if(isset($item['tax_totals']))
                                    <td class="text-right">{{number_format(($item['line_extension_amount'] + ($item['tax_totals'][0]['tax_amount'])), 2)}}</td>
                                @else
                                    <td class="text-right">{{number_format(($item['line_extension_amount']), 2)}}</td>
                                @endif
                            @else
                                <td class="text-right">{{number_format("0", 2)}}</td>
                                <td class="text-right">{{number_format("0", 2)}}</td>
                                <td class="text-right">{{number_format($item['invoiced_quantity'] * ($item['line_extension_amount'] / $item['invoiced_quantity']), 2)}}</td>
                            @endif
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>


    {{--seccion de immpuestos --}}

            <!-- Tabla para IVA y Retenciones -->
            <table class="tabla-impuestos">
                <tr>
                    <!-- Columna de IVA -->
                    <td style="width: 50%; text-align: center;">
                        <strong>IVA</strong><br>
                        @if(isset($request->tax_totals))
                            <?php $TotalImpuestos = 0; ?>
                            @foreach($request->tax_totals as $item)
                                <?php $TotalImpuestos += $item['tax_amount']; ?>
                                @inject('tax', 'App\Tax')
                                <div>{{ isset($item['tax_name']) && $item['tax_name'] ? $item['tax_name'] : $tax->findOrFail($item['tax_id'])['name'] }} 
                                    @if(isset($item['percent']))
                                        {{ number_format($item['percent'], 2) }}%
                                    @elseif(isset($item['per_unit_amount']) && isset($item['base_unit_measure']))
                                        {{ number_format($item['per_unit_amount'], 2) }} x {{ number_format($item['base_unit_measure'], 2) }}
                                    @else
                                        -
                                    @endif:
                                    {{number_format($item['tax_amount'], 2)}}
                                </div>
                            @endforeach
                        @endif
                    </td>

                    <!-- Columna de Retenciones -->
                    <td style="width: 50%; text-align: center;">
                        <strong>Retenciones</strong><br>
                        @if(isset($withHoldingTaxTotal))
                            <?php $TotalRetenciones = 0; ?>
                            @foreach($withHoldingTaxTotal as $item)
                                <?php $TotalRetenciones += $item['tax_amount']; ?>
                                @inject('tax', 'App\Tax')
                                <div>{{ isset($item['tax_name']) && $item['tax_name'] ? $item['tax_name'] : $tax->findOrFail($item['tax_id'])['name'] }}: {{number_format($item['tax_amount'], 2)}}</div>
                            @endforeach
                        @endif
                    </td>
                </tr>
            </table>

            <!-- Tabla para Totales -->
            <!-- Tabla para Totales, incluyendo la información adicional -->
            <table class="tabla-totales" style="margin-top: 8px;">
                <tr>
                    <th>Nro Lineas</th>
                    <td>{{$ItemNro}}</td>
                </tr>
                <tr>
                    <th>Base</th>
                    <td>{{number_format($request->legal_monetary_totals['line_extension_amount'], 2)}}</td>
                </tr>
                <tr>
                    <th>Impuestos</th>
                    <td>{{number_format($TotalImpuestos, 2)}}</td>
                </tr>
                <tr>
                    <th>Retenciones</th>
                    <td>{{number_format($TotalRetenciones, 2)}}</td>
                </tr>
                @if(isset($request->legal_monetary_totals['allowance_total_amount']))
                    <tr>
                        <th>Descuentos</th>
                        <td>{{number_format($request->legal_monetary_totals['allowance_total_amount'], 2)}}</td>
                    </tr>
                @endif
                @if(isset($request->previous_balance) && $request->previous_balance > 0)
                    <tr>
                        <th>Saldo Anterior</th>
                        <td>{{number_format($request->previous_balance, 2)}}</td>
                    </tr>
                @endif
                <!-- Calculo de Total Factura - Descuentos -->
                <tr>
                    <td><b>Total Factura - Descuentos:</b></td>
                    @if(isset($request->tarifaica))
                        @if(isset($request->legal_monetary_totals['allowance_total_amount']))
                            @if(isset($request->previous_balance))
                                <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + $request->previous_balance - $TotalRetenciones, 2)}}</td>
                            @else
                                <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] - $TotalRetenciones, 2)}}</td>
                            @endif
                        @else
                            @if(isset($request->previous_balance))
                                <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + 0 + $request->previous_balance - $TotalRetenciones, 2)}}</td>
                            @else
                                <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + 0 - $TotalRetenciones, 2)}}</td>
                            @endif
                        @endif
                    @else
                        @if(isset($request->previous_balance))
                            <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + $request->previous_balance - $TotalRetenciones, 2)}}</td>
                        @else
                            <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] - $TotalRetenciones, 2)}}</td>
                        @endif
                    @endif
                </tr>

                <tr>
                    <td><b>Total a Pagar</b></td>
                    @if(isset($request->tarifaica))
                        @if(isset($request->legal_monetary_totals['allowance_total_amount']))
                            @if(isset($request->previous_balance))
                                <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + $request->previous_balance - $TotalRetenciones, 2)}}</td>
                            @else
                                <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] - $TotalRetenciones, 2)}}</td>
                            @endif
                        @else
                            @if(isset($request->previous_balance))
                                <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + 0 + $request->previous_balance - $TotalRetenciones, 2)}}</td>
                            @else
                                <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + 0 - $TotalRetenciones, 2)}}</td>
                            @endif
                        @endif
                    @else
                        @if(isset($request->previous_balance))
                            <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + $request->previous_balance - $TotalRetenciones, 2)}}</td>
                        @else
                            <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] - $TotalRetenciones, 2)}}</td>
                        @endif
                    @endif
                </tr>
            </table>


            @inject('Varios', 'App\Custom\NumberSpellOut')
            <div class="text-right" style="margin-top: -25px;">
                <div>
                    <p style="font-size: 12pt">
                        @php
                            // Inicializamos con payable_amount
                            $totalAmount = $request->legal_monetary_totals['payable_amount'];

                            // Verificamos si existe previous_balance
                            if (isset($request->previous_balance)) {
                                $totalAmount += $request->previous_balance;
                            }

                            // Verificamos si existen retenciones y las restamos
                            if (isset($TotalRetenciones)) {
                                $totalAmount -= $TotalRetenciones;
                            }

                            // Finalmente, redondeamos el total a dos decimales
                            $totalAmount = round($totalAmount, 2);

                            // Definimos la moneda
                            $idcurrency = $request->idcurrency ?? null;
                        @endphp
                        <p><strong>SON</strong>: {{$Varios->convertir($totalAmount, $idcurrency)}} M/CTE*********.</p>
                    </p>
                </div>
            </div>


        @if(isset($notes))
        <div class="summarys">
            <div class="text-word" id="note">
                <p><strong>NOTAS:</strong></p>
                <p style="font-style: italic; font-size: 9px">{{$notes}}</p>
            </div>
        </div>
        @endif

    {{--
    <div class="summary" >
        <div class="text-word" id="note">
            @if(isset($request->disable_confirmation_text))
                @if(!$request->disable_confirmation_text)
                    <p style="font-style: italic;">INFORME EL PAGO AL TELEFONO {{$company->phone}} o al e-mail {{$user->email}}<br>
                        {{-- <br>
                        <div id="firma">
                            <p><strong>FIRMA ACEPTACIÓN:</strong></p><br>
                            <p><strong>CC:</strong></p><br>
                            <p><strong>FECHA:</strong></p><br>
                        </div>
                    </p>
                @endif
            @endif
        </div>
        @if(isset($firma_facturacion) and !is_null($firma_facturacion))
            <table style="font-size: 10px">
                <tr>
                    <td class="vertical-align-top" style="width: 50%; text-align: right">
                        <img style="width: 250px;" src="{{$firma_facturacion}}">
                    </td>
                </tr>
            </table>
        @endif
    </div>

    --}}

    <!-- Footer -->
<div id="footer" style="font-size: 8px; text-align: center; margin-top: 10px;">
    <hr style="margin-bottom: 4px;">
    <p id='mi-texto'>
        Factura No: {{$resolution->prefix}} - {{$request->number}}<br>
        Fecha y Hora de Generación: {{$date}} - {{$time}}<br>
        <strong> CUFE: {{$cufecude}}</strong>
    </p>

    <div style="text-align: center;">
        <img style="width: 70%;" src="{{$imageQr}}">
    </div>

    @isset($request->foot_note)
        <p id='mi-texto-1'>{{$request->foot_note}}</p>
    @endisset

    <h3> GRACIAS POR SU COMPRA</h3>
</div>
</body>
</html>
