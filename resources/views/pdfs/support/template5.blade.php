<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>

<body margin-top:50px>
    <hr style="height: 3px; background-color: rgb(6, 103, 194) ; border: none;">
    @if(isset($request->head_note))
    <div class="row">
        <div class="col-sm-12">
            <table class="table table-bordered table-condensed table-striped table-responsive">
                <thead>
                    <tr>
                        <th class="text-center"><p><strong>{{$request->head_note}}<br/>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    @endif
    <table style="font-size: 9px">
        <tr>
            <td class="vertical-align-top" style="width: 45%;">
                <table>
                    <tr>
                        <td style="padding: 0; width: 40%;">Proveedor:</td>
                        <td style="padding: 0;">{{$customer->name}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0; width: 40%;">CC o NIT:</td>
                        <td style="padding: 0;">{{$customer->company->identification_number}}-{{$request->customer['dv'] ?? NULL}} </td>
                    </tr>
                    <tr>
                        <td style="padding: 0; width: 40%;">Régimen:</td>
                        <td style="padding: 0;">{{$customer->company->type_regime->name}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0; width: 40%;">Obligación:</td>
                        <td style="padding: 0;">{{$customer->company->type_liability_names ?? $customer->company->type_liability->name}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0; width: 40%;">Email:</td>
                        <td style="padding: 0;">{{$customer->email}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0; width: 40%;">Forma de Pago:</td>
                        <td style="padding: 0;">{{$paymentForm[0]->name}}</td>
                    </tr>
                </table>
            </td>
            <td class="vertical-align-top" style="width: 35%; padding-left: 1rem">
                <table>
                    <tr>
                        <td style="padding: 0; width: 50%;">Dirección:</td>
                        <td style="padding: 0;">{{$customer->company->address}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0; width: 50%;">Ciudad:</td>
                        @if($customer->company->country->id == 46)
                            <td style="padding: 0;">{{$customer->company->municipality->name}} - {{$customer->company->country->name}} </td>
                        @else
                            <td style="padding: 0;">{{$customer->company->municipality_name}} - {{$customer->company->state_name}} - {{$customer->company->country->name}} </td>
                        @endif
                    </tr>
                    <tr>
                        <td style="padding: 0; width: 50%;">Teléfono:</td>
                        <td style="padding: 0;">{{$customer->company->phone}}</td>
                    </tr>
                    <br>
                    <tr>
                        <td style="padding: 0; width: 50%;">Medios de Pago:</td>
                        <td style="padding: 0;">
                            @foreach ($paymentForm as $paymentF)
                                {{$paymentF->nameMethod}}<br>
                            @endforeach
                        </td>
                    </tr>
                </table>
            </td>
            <td class="vertical-align-top" style="width: 25%; text-align: right">
                <table>
                    @if(isset($request['order_reference']['id_order']))
                    <tr>
                        <td style="padding: 0; width: 50%;">Número Pedido:</td>
                        <td style="padding: 0;">{{$request['order_reference']['id_order']}}</td>
                    </tr>
                    @endif
                    @if(isset($request['order_reference']['issue_date_order']))
                    <tr>
                        <td style="padding: 0; width: 50%;">Fecha Pedido:</td>
                        <td style="padding: 0;">{{$request['order_reference']['issue_date_order']}}</td>
                    </tr>
                    @endif
                    @if(isset($request['deliveryterms']))
                    <tr>
                        <td style="padding: 0; width: 50%;">Terminos de Entrega:</td>
                        <td style="padding: 0;">{{$request['deliveryterms']['loss_risk_responsibility_code']}} - {{ $request['deliveryterms']['loss_risk'] }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0; width: 50%;">T.R.M:</td>
                        <td style="padding: 0;">{{number_format($request['k_supplement']['FctConvCop'], 2)}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0; width: 50%;">Destino</td>
                        <td style="padding: 0;">{{$request['k_supplement']['destination']}}</td>
                    </tr>
                    <tr>
                        @inject('currency', 'App\TypeCurrency')
                        <td style="padding: 0; width: 50%;">Tipo Moneda:</td>
                        <td style="padding: 0;">{{$currency->where('code', 'like', '%'.$request['k_supplement']['MonedaCop'].'%')->firstOrFail()['name']}}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="padding: 0; width: 50%;">Plazo Para Pagar:</td>
                        <td style="padding: 0;">{{$paymentForm[0]->duration_measure}} Dias</td>
                    </tr>
                    <tr>
                        <td style="padding: 0; width: 50%;">Fecha Vencimiento:</td>
                        <td style="padding: 0;">{{$paymentForm[0]->payment_due_date}}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <br>
    @isset($healthfields)
        <table class="table" style="width: 100%;">
            <thead>
                <tr>
                    <th class="text-center" style="width: 100%;">INFORMACION REFERENCIAL SECTOR SALUD</th>
                </tr>
            </thead>
        </table>
        <table class="table" style="width: 100%;">
            <thead>
                <tr>
                    <th class="text-center" style="width: 12%;">Cod Prestador</th>
                    <th class="text-center" style="width: 25%;">Datos Usuario</th>
                    <th class="text-center" style="width: 25%;">Info. Contrat./Cobertura</th>
                    <th class="text-center" style="width: 20%;">Nros. Autoriz./MIPRES</th>
                    <th class="text-center" style="width: 18%;">Info. de Pagos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($healthfields->users_info as $item)
                    <tr>
                        <td>
                            <p style="font-size: 8px">{{$item->provider_code}}</p>
                        </td>
                        <td>
                            <p style="font-size: 8px">Modalidad Contratación: {{$item->health_contracting_payment_method()->name}}</p>
                            <p style="font-size: 8px">Nro. Contrato: {{$item->contract_number}}</p>
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
    <table class="table" style="width: 100%;">
        <thead>
            <tr>
                <th class="text-center" style="background-color: rgb(103, 237, 88); color: black;">#</th>
                <th class="text-center" style="background-color: rgb(103, 237, 88); color: black;">Código</th>
                <th class="text-center" style="background-color: rgb(103, 237, 88); color: black;">Descripción</th>
                <th class="text-center" style="background-color: rgb(103, 237, 88); color: black;">Cantidad</th>
                <th class="text-center" style="background-color: rgb(103, 237, 88); color: black;">UM</th>
                <th class="text-center" style="background-color: rgb(103, 237, 88); color: black;">Val. Unit</th>
                <th class="text-center" style="background-color: rgb(103, 237, 88); color: black;">IVA/IC</th>
                <th class="text-center" style="background-color: rgb(103, 237, 88); color: black;">Dcto</th>
                <th class="text-center" style="background-color: rgb(103, 237, 88); color: black;">%</th>
                <th class="text-center" style="background-color: rgb(103, 237, 88); color: black;">Val. Item</th>
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
                        @else
                            <td class="text-right">{{number_format("0", 2)}}</td>
                        @endif
                        <td class="text-right">{{number_format($item['invoiced_quantity'] * $item['price_amount'], 2)}}</td>
                    @else
                        <td>{{$ItemNro}}</td>
                        <td>{{$item['code']}}</td>
                        <td>
                            @if(isset($item['notes']))
                                {{$item['description']}}
                                <p style="font-style: italic; font-size: 7px"><strong>Nota: {{$item['notes']}}</strong></p>
                            @else
                                {{$item['description']}}
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
                                <td class="text-right">{{number_format(($item['line_extension_amount'] + $item['tax_totals'][0]['tax_amount']), 2)}}</td>
                            @else
                                <td class="text-right">{{number_format(($item['line_extension_amount']), 2)}}</td>
                            @endif
                        @else
                            <td class="text-right">{{number_format("0", 2)}}</td>
                            <td class="text-right">{{number_format("0", 2)}}</td>
                            <td class="text-right">{{number_format($item['invoiced_quantity'] * $item['line_extension_amount'], 2)}}</td>
                        @endif
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <br>

    <table class="table" style="width: 100%">
        <thead>
            <tr>
                <th class="text-center" style="border: none;"></th>
                <th class="text-center">Impuestos - Retenciones</th>
                <th class="text-center">Totales</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center" style="width: 30%;">
                    <p style="color: black; font-weight: bold; font-size: 7px; margin-bottom: 2px; padding: 0px 0px 0px 0px;">Resolución de Facturación Electrónica<br>
                                                                                                            Nro. {{$resolution->resolution}} de {{$resolution->resolution_date}}<br>
                                                                                                            Prefijo: {{$resolution->prefix}}, Rango {{$resolution->from}} Al {{$resolution->to}}<br>
                                                                                                            Vigencia Desde: {{$resolution->date_from}} Hasta: {{$resolution->date_to}}</p>
                    <img style="width: 180px;" src="{{$imageQr}}">
                </td>
                <td style="width: 33%;">
                    <table class="table" style="width: 100%">
                        <thead>
                            <tr>
                                <th class="text-center" style="background-color: rgb(103, 237, 88); color: black;">Tipo</th>
                                <th class="text-center" style="background-color: rgb(103, 237, 88); color: black;">Base</th>
                                <th class="text-center" style="background-color: rgb(103, 237, 88); color: black;">Porcentaje</th>
                                <th class="text-center" style="background-color: rgb(103, 237, 88); color: black;">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($request->tax_totals))
                                <?php $TotalImpuestos = 0; ?>
                                @foreach($request->tax_totals as $item)
                                    <tr>
                                        <?php $TotalImpuestos = $TotalImpuestos + $item['tax_amount'] ?>
                                        @inject('tax', 'App\Tax')
                                        <td>{{ isset($item['tax_name']) && $item['tax_name'] ? $item['tax_name'] : $tax->findOrFail($item['tax_id'])['name'] }}</td>
                                        <td class="text-right">{{number_format($item['taxable_amount'], 2)}}</td>
                                        <td class="text-right">{{number_format($item['percent'], 2)}}%</td>
                                        <td class="text-right">{{number_format($item['tax_amount'], 2)}}</td>
                                    </tr>
                                @endforeach
                            @else
                                <?php $TotalImpuestos = 0; ?>
                            @endif
                            @if(isset($withHoldingTaxTotal))
                                <?php $TotalRetenciones = 0; ?>
                                @foreach($withHoldingTaxTotal as $item)
                                    <tr>
                                        <?php $TotalRetenciones = $TotalRetenciones + $item['tax_amount'] ?>
                                        @inject('tax', 'App\Tax')
                                        <td>{{ isset($item['tax_name']) && $item['tax_name'] ? $item['tax_name'] : $tax->findOrFail($item['tax_id'])['name'] }}</td>
                                        <td class="text-right">{{number_format($item['taxable_amount'], 2)}}</td>
                                        <td class="text-right">{{number_format($item['percent'], 2)}}%</td>
                                        <td class="text-right">{{number_format($item['tax_amount'], 2)}}</td>
                                    </tr>
                                @endforeach
                            @else
                                <?php $TotalRetenciones = 0; ?>
                            @endif
                        </tbody>
                    </table>
                </td>
                <td style="width: 34%;">
                    <table class="table" style="width: 100%">
                        <thead>
                            <tr>
                                <th class="text-center" style="background-color: rgb(103, 237, 88); color: black;">Concepto</th>
                                <th class="text-center" style="background-color: rgb(103, 237, 88); color: black;">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Nro Lineas:</td>
                                <td class="text-right">{{$ItemNro}}</td>
                            </tr>
                            <tr>
                                <td>Base:</td>
                                <td class="text-right">{{number_format($request->legal_monetary_totals['line_extension_amount'], 2)}}</td>
                            </tr>
                            <tr>
                                <td>Impuestos:</td>
                                <td class="text-right">{{number_format($TotalImpuestos, 2)}}</td>
                            </tr>
                            <tr>
                                <td>Retenciones:</td>
                                <td class="text-right">{{number_format($TotalRetenciones, 2)}}</td>
                            </tr>
                            <tr>
                                <td>Descuentos:</td>
                                @if(isset($request->legal_monetary_totals['allowance_total_amount']))
                                    <td class="text-right">{{number_format($request->legal_monetary_totals['allowance_total_amount'], 2)}}</td>
                                @else
                                    <td class="text-right">{{number_format(0, 2)}}</td>
                                @endif
                            </tr>
                            <tr>
                                <td>Total Factura:</td>
                                @if(isset($request->tarifaica))
                                    @if(isset($request->legal_monetary_totals['allowance_total_amount']))
                                        <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + $request->legal_monetary_totals['allowance_total_amount'], 2)}}</td>
                                    @else
                                        <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + 0, 2)}}</td>
                                    @endif
                                @else
                                    <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'], 2)}}</td>
                                @endif
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
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
            </p>
        </div>
    </div>
    <p><strong>PRECIO EN LETRAS SON</strong>: {{$Varios->convertir($totalAmount, $idcurrency)}} M/CTE*********.</p>

    @if(isset($notes))
        <div class="summarys">
            <div class="text-word" id="note">
                <p><strong>OBSERVACIONES:</strong></p>
                <p style="font-style: italic; font-size: 9px">{{$notes}}</p>
            </div>
        </div>
    @endif

    <div class="summary" >
        <div class="text-word" id="note">
            @if(isset($request->disable_confirmation_text))
                @if(!$request->disable_confirmation_text)
                    <p style="font-style: italic;">INFORME EL PAGO AL TELEFONO {{$company->phone}} o al e-mail {{$user->email}}<br>
                        <br>
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
</body>
</html>
