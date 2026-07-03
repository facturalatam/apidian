<!DOCTYPE html>
<html lang="es">
<body>
    {{-- Logo --}}
    @if(isset($imgLogo) && $imgLogo)
        <div class="text-center" style="margin-bottom: 5px;">
            <table cellpadding="0" cellspacing="0" style="width:170px; height:80px; margin:0 auto; border-collapse:collapse;">
                <tr>
                    <td style="width:170px; height:80px; overflow:hidden; vertical-align:middle; text-align:center; padding:0;">
                        <img src="{{$imgLogo}}" alt="logo" style="display:inline-block; max-width:170px; max-height:80px; width:auto; height:auto;">
                    </td>
                </tr>
            </table>
        </div>
    @endif

    {{-- Empresa y Establecimiento --}}
    <div class="text-center font-bold" style="font-size:13px;">
        {{ $user->name ?? '' }}
        @if(isset($request->establishment_name) && $request->establishment_name != 'Oficina Principal')
            <br>{{ $request->establishment_name }}
        @endif
    </div>
    <div class="text-center desc" style="line-height:1.2;">
        NIT: {{ $company->identification_number ?? '' }}{{ isset($company->dv) ? '-'.$company->dv : '' }}
        @if(isset($request->establishment_address) && $request->establishment_address)
            <br>Dirección: {{ $request->establishment_address }}
        @else
            <br>Dirección: {{ $company->address ?? '' }}
        @endif
        @inject('municipality', 'App\Municipality')
        @if(isset($request->establishment_municipality))
            <br>{{ $municipality->findOrFail($request->establishment_municipality)['name'] ?? '' }} - {{ $municipality->findOrFail($request->establishment_municipality)['department']['name'] ?? '' }} - {{ $company->country->name ?? '' }}
        @elseif(isset($company->municipality->name))
            <br>{{ $company->municipality->name ?? '' }} - {{ $company->municipality->department->name ?? '' }} - {{ $company->country->name ?? '' }}
        @endif
        @if(isset($request->establishment_phone) && $request->establishment_phone)
            <br>Tel: {{ $request->establishment_phone }}
        @elseif(isset($company->phone))
            <br>Tel: {{ $company->phone }}
        @endif
        @if(isset($request->establishment_email) && $request->establishment_email)
            <br>Correo: {{ $request->establishment_email }}
        @elseif(isset($user->email))
            <br>Correo: {{ $user->email }}
        @endif
    </div>
    <hr>

    {{-- Datos de la factura y resolución DIAN --}}
    <div class="text-center font-bold" style="font-size:12px;">
        FACTURA ELECTRÓNICA DE VENTA<br>
        {{$resolution->prefix}} - {{$request->number}}
    </div>
    <div class="text-center desc">
        Fecha Emisión: {{$date}}<br>
        Hora: {{$time}}
    </div>
    <div class="text-center desc" style="margin-top: 2px;">
        Resolución DIAN: {{$resolution->resolution}} de {{$resolution->resolution_date}}<br>
        Prefijo: {{$resolution->prefix}}, Rango {{$resolution->from}} al {{$resolution->to}}<br>
        Vigencia: {{$resolution->date_from}} a {{$resolution->date_to}}
    </div>
    <hr>
    {{-- Información adicional DIAN --}}
    <div class="desc-9 text-center" style="margin-bottom:2px; line-height:1.1; white-space:normal;">
        @php
            $infoDian = [];
            if(isset($request->ivaresponsable) && $request->ivaresponsable != $company->type_regime->name) {
                $infoDian[] = $company->type_regime->name.' - '.$request->ivaresponsable;
            }
            if(isset($request->nombretipodocid)) {
                $infoDian[] = 'Tipo Doc: '.$request->nombretipodocid;
            }
            if(isset($request->tarifaica) && $request->tarifaica != '100') {
                $infoDian[] = 'ICA: '.$request->tarifaica.'‰';
            }
            if(isset($request->actividadeconomica)) {
                $infoDian[] = 'Act. Econ.: '.$request->actividadeconomica;
            }
            echo implode(' | ', $infoDian);
        @endphp
    </div>
    {{-- Cliente --}}
    <table style="margin-bottom: 2px;">
        <tr>
            <td class="desc-9" style="padding:1px 2px; width: 32%;">Cliente:</td>
            <td class="desc-9" style="padding:1px 2px;">{{ $customer->name ?? '' }}</td>
        </tr>
        <tr>
            <td class="desc-9" style="padding:1px 2px;">NIT/CC:</td>
            <td class="desc-9" style="padding:1px 2px;">
                {{ $customer->company->identification_number ?? '' }}{{ isset($request->customer['dv']) ? '-'.$request->customer['dv'] : '' }}
            </td>
        </tr>
        <tr>
            <td class="desc-9" style="padding:1px 2px;">Dir./Ciudad:</td>
            <td class="desc-9" style="padding:1px 2px;">
                {{ $customer->company->address ?? '' }}
                @if(isset($customer->company->country) && isset($customer->company->country->id) && $customer->company->country->id == 46)
                    - {{ $customer->company->municipality->name ?? '' }} - {{ $customer->company->country->name ?? '' }}
                @else
                    - {{ $customer->company->municipality_name ?? '' }} - {{ $customer->company->state_name ?? '' }} - {{ $customer->company->country->name ?? '' }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="desc-9" style="padding:1px 2px;">Tel/Email:</td>
            <td class="desc-9" style="padding:1px 2px;">
                {{ $customer->company->phone ?? '' }}{{ isset($customer->email) && $customer->email ? ' / '.$customer->email : '' }}
            </td>
        </tr>
        @if(isset($customer->company->type_regime->name) || isset($customer->company->type_liability->name))
        <tr>
            <td class="desc-9" style="padding:1px 2px;">Rég/Obl.:</td>
            <td class="desc-9" style="padding:1px 2px;">
                {{ $customer->company->type_regime->name ?? '' }}
                @if(isset($customer->company->type_regime->name) && isset($customer->company->type_liability->name)) /
                @endif
                {{ $customer->company->type_liability->name ?? '' }}
            </td>
        </tr>
        @endif
    </table>

    {{-- Información de pedido y moneda --}}
    @if(isset($request['order_reference']['id_order']))
        <div class="desc-9">
            <strong>Número Pedido:</strong> {{$request['order_reference']['id_order']}}<br>
        </div>
    @endif
    @if(isset($request['order_reference']['issue_date_order']))
        <div class="desc-9">
            <strong>Fecha Pedido:</strong> {{$request['order_reference']['issue_date_order']}}<br>
        </div>
    @endif
    @if(isset($request['deliveryterms']))
        <div class="desc-9">
            <strong>Terminos de Entrega:</strong> {{$request['deliveryterms']['loss_risk_responsibility_code']}} - {{ $request['deliveryterms']['loss_risk'] }}<br>
            <strong>T.R.M:</strong> {{number_format($request['calculationrate'], 2)}}<br>
            <strong>Fecha T.R.M:</strong> {{$request['calculationratedate']}}<br>
            @inject('currency', 'App\TypeCurrency')
            <strong>Tipo Moneda:</strong> {{$currency->findOrFail($request['idcurrency'])['name']}}
        </div>
    @endif

    <hr>
    {{-- Información sector salud (healthfields) --}}
    @isset($healthfields)
        <table class="table" style="width: 100%; margin-bottom: 4px;">
            <thead>
                <tr>
                    <th class="text-center desc-9" colspan="3">INFORMACIÓN REFERENCIAL SECTOR SALUD</th>
                </tr>
                <tr>
                    <th class="desc-9 text-center">Cod Prestador</th>
                    <th class="desc-9 text-center">Info. Contratación</th>
                    <th class="desc-9 text-center">Info. de Pagos</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($healthfields->users_info as $item)
                <tr>
                    <td class="desc-9 text-center">{{$item->provider_code}}</td>
                    <td class="desc-9">
                        Modalidad Contratación: {{$item->health_contracting_payment_method()->name}}<br>
                        Nro Contrato: {{$item->contract_number}}<br>
                        Cobertura: {{$item->health_coverage()->name}}
                    </td>
                    <td class="desc-9">
                        Copago: {{number_format($item->co_payment, 2)}}<br>
                        Cuota Moderadora: {{number_format($item->moderating_fee, 2)}}<br>
                        Pagos Compartidos: {{number_format($item->shared_payment, 2)}}<br>
                        Anticipos: {{number_format($item->advance_payment, 2)}}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="desc-9" style="margin-bottom: 4px;">
            <strong>Inicio Periodo Facturación:</strong> {{$healthfields->invoice_period_start_date}}<br>
            <strong>Fin Periodo Facturación:</strong> {{$healthfields->invoice_period_end_date}}
        </div>
        <hr>
    @endisset

    {{-- Items --}}
    <table>
        <thead>
            <tr>
                <th class="border-top-bottom desc-9 text-left">Código</th>
                <th class="border-top-bottom desc-9 text-center">Cantidad</th>
                <th class="border-top-bottom desc-9 text-center">UM</th>
                <th class="border-top-bottom desc-9 text-left">Descripción</th>
                <th class="border-top-bottom desc-9 text-right">Val. Unit</th>
                <th class="border-top-bottom desc-9 text-right">Val. Item</th>
            </tr>
        </thead>
        <tbody>
            @foreach($request['invoice_lines'] as $item)
                @if(
                    isset($item['description']) &&
                    (
                        $item['description'] == 'Administración' ||
                        $item['description'] == 'Imprevisto' ||
                        $item['description'] == 'Utilidad'
                    )
                )
                    <tr>
                        <td class="desc-9">{{ $item['code'] ?? '' }}</td>
                        <td class="desc-9 text-center"></td>
                        <td class="desc-9 text-center"></td>
                        <td class="desc-9">{{ $item['description'] }}</td>
                        <td class="desc-9 text-right">
                            {{ isset($item['price_amount']) ? number_format($item['price_amount'], 2) : '' }}
                        </td>
                        <td class="desc-9 text-right">
                            {{ isset($item['invoiced_quantity']) && isset($item['price_amount']) ? number_format($item['invoiced_quantity'] * $item['price_amount'], 2) : '' }}
                        </td>
                    </tr>
                @else
                    <tr>
                        <td class="desc-9">{{ $item['code'] ?? '' }}</td>
                        <td class="desc-9 text-center">{{ isset($item['invoiced_quantity']) ? number_format($item['invoiced_quantity'], 2) : '' }}</td>
                        <td class="desc-9 text-center">
                            @inject('um', 'App\UnitMeasure')
                            @if(isset($item['unit_measure_id']))
                                {{ $um->findOrFail($item['unit_measure_id'])['name'] }}
                            @else
                                {{ $item['unit_measure'] ?? '' }}
                            @endif
                        </td>
                        <td class="desc-9">
                            {{ $item['description'] ?? '' }}
                            @if(isset($item['notes']))
                                <br><span style="font-style: italic;">{{ $item['notes'] }}</span>
                            @endif
                            @if(!empty($item['lot_code']) || !empty($item['date_of_due']))
                                <br><span style="font-size: 7px; font-style: italic;">
                                    @if(!empty($item['lot_code']))<strong>Lote:</strong> {{ $item['lot_code'] }}@endif
                                    @if(!empty($item['lot_code']) && !empty($item['date_of_due'])) | @endif
                                    @if(!empty($item['date_of_due']))<strong>Vence:</strong> {{ $item['date_of_due'] }}@endif
                                </span>
                            @endif
                        </td>
                        <td class="desc-9 text-right">
                            @php
                                $cantidad = isset($item['invoiced_quantity']) && $item['invoiced_quantity'] > 0 ? $item['invoiced_quantity'] : 1;
                                $precioUnidad = (isset($item['tax_totals']) && isset($item['tax_totals'][0]['tax_amount']))
                                    ? (($item['line_extension_amount'] ?? 0) + $item['tax_totals'][0]['tax_amount']) / $cantidad
                                    : ($item['line_extension_amount'] ?? 0) / $cantidad;
                            @endphp
                            {{ number_format($precioUnidad, 2) }}
                        </td>
                        <td class="desc-9 text-right">
                            @if(isset($item['tax_totals']) && isset($item['tax_totals'][0]['tax_amount']))
                                {{ number_format(($item['line_extension_amount'] ?? 0) + $item['tax_totals'][0]['tax_amount'], 2) }}
                            @else
                                {{ number_format($item['line_extension_amount'] ?? 0, 2) }}
                            @endif
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
    {{-- Impuestos y retenciones --}}
    <table style="width:100%; margin-top: 8px;">
        <tr>
            <td style="width: 50%; text-align: center;">
                <strong>IVA</strong><br>
                @if(isset($request->tax_totals))
                    @php $TotalImpuestos = 0; @endphp
                    @foreach($request->tax_totals as $item)
                        @php $TotalImpuestos += $item['tax_amount']; @endphp
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
            <td style="width: 50%; text-align: center;">
                <strong>Retenciones</strong><br>
                @if(isset($withHoldingTaxTotal))
                    @php $TotalRetenciones = 0; @endphp
                    @foreach($withHoldingTaxTotal as $item)
                        @php $TotalRetenciones += $item['tax_amount']; @endphp
                        @inject('tax', 'App\Tax')
                        <div>{{ isset($item['tax_name']) && $item['tax_name'] ? $item['tax_name'] : $tax->findOrFail($item['tax_id'])['name'] }}: {{number_format($item['tax_amount'], 2)}}</div>
                    @endforeach
                @endif
            </td>
        </tr>
    </table>

    {{-- Totales --}}
    @php
        $TotalImpuestos = 0;
        if(isset($request->tax_totals)){
            foreach($request->tax_totals as $item){
                $TotalImpuestos += $item['tax_amount'];
            }
        }
        $TotalRetenciones = 0;
        if(isset($withHoldingTaxTotal)){
            foreach($withHoldingTaxTotal as $item){
                $TotalRetenciones += $item['tax_amount'];
            }
        }
    @endphp
    <table class="totales-small">
        <tr>
            <td class="desc text-right font-bold">SUBTOTAL:</td>
            <td class="desc text-right font-bold">{{ number_format($request->legal_monetary_totals['line_extension_amount'], 2) }}</td>
        </tr>
        <tr>
            <td class="desc text-right font-bold">IMPUESTOS:</td>
            <td class="desc text-right font-bold">{{ number_format($TotalImpuestos, 2) }}</td>
        </tr>
        @if(isset($request->legal_monetary_totals['allowance_total_amount']))
        <tr>
            <td class="desc text-right font-bold">DESCUENTOS:</td>
            <td class="desc text-right font-bold">{{ number_format($request->legal_monetary_totals['allowance_total_amount'], 2) }}</td>
        </tr>
        @endif
        @if(isset($withHoldingTaxTotal))
        <tr>
            <td class="desc text-right font-bold">RETENCIONES:</td>
            <td class="desc text-right font-bold">{{ number_format($TotalRetenciones, 2) }}</td>
        </tr>
        @endif
        @if(isset($request->previous_balance) && $request->previous_balance > 0)
        <tr>
            <td class="desc text-right font-bold">Saldo Anterior:</td>
            <td class="desc text-right font-bold">{{ number_format($request->previous_balance, 2) }}</td>
        </tr>
        @endif
        <tr>
            <td class="desc text-right font-bold">TOTAL A PAGAR:</td>
            <td class="desc text-right font-bold">
                @php
                    $totalAmount = $request->legal_monetary_totals['payable_amount'];
                    if(isset($request->previous_balance)) $totalAmount += $request->previous_balance;
                    if(isset($TotalRetenciones)) $totalAmount -= $TotalRetenciones;
                @endphp
                {{ number_format($totalAmount, 2) }}
            </td>
        </tr>
    </table>
    <hr>

    {{-- Valor en letras --}}
    @inject('Varios', 'App\Custom\NumberSpellOut')
    <div class="desc-9 text-center son-small">
        <strong>SON:</strong> {{$Varios->convertir($totalAmount, $request->idcurrency ?? null)}} M/CTE
    </div>
    <hr>

    {{-- Pagos --}}
    @if(!empty($paymentForm) && $paymentForm->count() > 0)
        <div class="desc-9">
            <strong>Forma de Pago:</strong> {{$paymentForm[0]->name}}<br>
            <strong>Medios:</strong>
            @foreach ($paymentForm as $paymentF)
                {{$paymentF->nameMethod}}@if(!$loop->last), @endif
            @endforeach
            <br>
            <strong>Plazo:</strong> {{$paymentForm[0]->duration_measure}} días<br>
            <strong>Vence:</strong> {{$paymentForm[0]->payment_due_date}}
        </div>
    @endif

    {{-- QR y CUFE --}}
    <div class="text-center" style="margin: 5px 0;">
        <div class="desc-9" style="font-size: 6px;"><strong>CUFE:</strong> {{$cufecude}}</div>
        <img style="width: 50%;" src="{{$imageQr}}">
    </div>

    {{-- Notas --}}
    @if(isset($notes))
        <div class="desc-9" style="font-style: italic; text-align: center;">
            {{$notes}}
        </div>
    @endif

    @if(isset($request->foot_note))
        <div class="desc-9 text-center" style="font-size:6px;">
            {{$request->foot_note}}
        </div>
    @endif

    {{-- Footer --}}
    <div class="text-center desc-9">
        <h4>GRACIAS POR SU COMPRA</h4>
    </div>
</body>
</html>