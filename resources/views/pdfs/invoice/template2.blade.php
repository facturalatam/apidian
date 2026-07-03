<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>FACTURA ELECTRONICA DE VENTA Nro: {{$resolution->prefix}} - {{$request->number}}</title>
</head>

<body>
    <hr>
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
    <table style="font-size: 10px">
        <tr>
            <td class="vertical-align-top" style="width: 30%;">
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
            <td class="vertical-align-top" style="width: 30%; text-align: right">
                <img style="width: 150px;" src="{{$imageQr}}">
            </td>
        </tr>
    </table>
    <br>
    @isset($healthfields)
        @if($healthfields->print_users_info_to_pdf)
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
                            @if($item->identification_number && $item->first_name && $item->surname && $item->health_type_document_identification_id)
                                <td>
                                    <p style="font-size: 8px">Nro ID: {{$item->identification_number}}</p>
                                    <p style="font-size: 8px">Nombre: {{$item->first_name}} {{$item->middle_name}} {{$item->surname}} {{$item->second_surname}}</p>
                                    <p style="font-size: 8px">Tipo Documento: {{$item->health_type_document_identification()->name}}</p>
                                    <p style="font-size: 8px">Tipo Usuario: {{$item->health_type_user()->name}}</p>
                                </td>
                            @else
                                <td>
                                    <p style="font-size: 8px">Nro ID: </p>
                                    <p style="font-size: 8px">Nombre: </p>
                                    <p style="font-size: 8px">Tipo Documento: </p>
                                    <p style="font-size: 8px">Tipo Usuario: </p>
                                </td>
                            @endif
                            <td>
                                <p style="font-size: 8px">Modalidad Contratación: {{$item->health_contracting_payment_method()->name}}</p>
                                <p style="font-size: 8px">Nro. Contrato: {{$item->contract_number}}</p>
                                <p style="font-size: 8px">Cobertura: {{$item->health_coverage()->name}}</p>
                            </td>
                            <td>
                                <p style="font-size: 8px">Nros Autorización: {{$item->autorization_numbers}}</p>
                                <p style="font-size: 8px">Nro MIPRES: {{$item->mipres}}</p>
                                <p style="font-size: 8px">Entrega MIPRES: {{$item->mipres_delivery}}</p>
                                <p style="font-size: 8px">Nro Poliza: {{$item->policy_number}}</p>
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
        @endif
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
    <table class="table" style="width: 100%;">
        <thead>
            <tr>
                <th class="text-center">#</th>
                <th class="text-center">Código</th>
                <th class="text-center">Descripción</th>
                @if($showPurchaseOrderColumn)
                    <th class="text-center">Orden de compra</th>
                @endif
                <th class="text-center">Cantidad</th>
                <th class="text-center">UM</th>
                <th class="text-center">Val. Unit</th>
                @if(isset($request['deliveryterms']))
                    <th class="text-center">Val. Unit USD</th>
                @endif
                <th class="text-center">IVA/IC</th>
                @if(isset($request['deliveryterms']))
                    <th class="text-center">IVA/IC USD</th>
                @endif
                <th class="text-center">Dcto</th>
                @if(isset($request['deliveryterms']))
                    <th class="text-center">Dcto USD</th>
                @endif
                <th class="text-center">%</th>
                <th class="text-center">Val. Item</th>
                @if(isset($request['deliveryterms']))
                    <th class="text-center">Val. Item USD</th>
                @endif
            </tr>
        </thead>
        <tbody>
            <?php 
                $ItemNro = 0; 
                $TotalDescuentosEnLineas = 0;
                $trmValue = isset($request['k_supplement']['FctConvCop']) 
                    ? $request['k_supplement']['FctConvCop'] 
                    : (isset($request['deliveryterms']) ? app('App\Http\Controllers\Api\TrmController')->getCurrentTRM() : 1);
            ?>
            @foreach($request['invoice_lines'] as $item)
                <?php $ItemNro = $ItemNro + 1; ?>
                <tr>
                    @inject('um', 'App\UnitMeasure')
                    @if($item['description'] == 'Administración' or $item['description'] == 'Imprevisto' or $item['description'] == 'Utilidad')
                        <td>{{$ItemNro}}</td>
                        <td class="text-right">{{$item['code']}}</td>
                        <td>{{$item['description']}}</td>
                        <td class="text-right"></td>
                        <td class="text-right"></td>
                        <td class="text-right">{{number_format($item['price_amount'], 2)}}</td>
                        @if(isset($request['deliveryterms']))
                            <td class="text-right" style="background-color:rgb(194, 241, 194);">{{number_format($item['price_amount'] / $trmValue, 2)}}</td>
                        @endif
                        <td class="text-right">{{isset($item['tax_totals'][0]['tax_amount']) ? number_format($item['tax_totals'][0]['tax_amount'], 2) : '0.00'}}</td>
                        @if(isset($request['deliveryterms']))
                            <td class="text-right" style="background-color: rgb(194, 241, 194);">{{isset($item['tax_totals'][0]['tax_amount']) ? number_format($item['tax_totals'][0]['tax_amount'] / $trmValue, 2) : '0.00'}}</td>
                        @endif
                        @if(isset($item['allowance_charges']))
                            <?php $TotalDescuentosEnLineas = $TotalDescuentosEnLineas + $item['allowance_charges'][0]['amount'] ?>
                            <td class="text-right">{{number_format($item['allowance_charges'][0]['amount'], 2)}}</td>
                            @if(isset($request['deliveryterms']))
                                <td class="text-right" style="background-color: rgb(194, 241, 194);">{{number_format($item['allowance_charges'][0]['amount'] / $trmValue, 2)}}</td>
                            @endif
                        @else
                            <td class="text-right">0.00</td>
                            @if(isset($request['deliveryterms']))
                                <td class="text-right" style="background-color: rgb(194, 241, 194);">0.00</td>
                            @endif
                        @endif
                        <td class="text-right">
                            <td class="text-right">
                            @if(isset($item['allowance_charges']) && floatval($item['allowance_charges'][0]['base_amount']) != 0)
                                {{ number_format(($item['allowance_charges'][0]['amount'] * 100) / $item['allowance_charges'][0]['base_amount'], 2) }}
                            @else
                                0.00
                            @endif
                            </td>
                        </td>
                        <td class="text-right">{{number_format($item['invoiced_quantity'] * $item['price_amount'], 2)}}</td>
                        @if(isset($request['deliveryterms']))
                            <td class="text-right" style="background-color: rgb(194, 241, 194);">{{number_format(($item['invoiced_quantity'] * $item['price_amount']) / $trmValue, 2)}}</td>
                        @endif
                    @else
                        <td>{{$ItemNro}}</td>
                        <td>{{$item['code']}}</td>
                        <td>
                            @if(isset($item['notes']))
                                {{$item['description']}}
                                <p style="font-style: italic; font-size: 10px"><strong> {{$item['notes']}}</strong></p>
                            @else
                                {{$item['description']}}
                            @endif
                            @if(!empty($item['lot_code']) || !empty($item['date_of_due']))
                                <p style="font-size: 8px; margin: 0; font-style: italic;">
                                    @if(!empty($item['lot_code']))<strong>Lote:</strong> {{$item['lot_code']}}@endif
                                    @if(!empty($item['lot_code']) && !empty($item['date_of_due'])) | @endif
                                    @if(!empty($item['date_of_due']))<strong>Vence:</strong> {{$item['date_of_due']}}@endif
                                </p>
                            @endif
                        </td>
                        @if($showPurchaseOrderColumn)
                            <td>
                                @if(isset($item['purchase_order_number']) && $item['purchase_order_number'])
                                    {{$item['purchase_order_number']}}
                                @endif
                            </td>
                        @endif
                        <td class="text-right">{{number_format($item['invoiced_quantity'], 2)}}</td>
                        <td class="text-right">{{$um->findOrFail($item['unit_measure_id'])['name']}}</td>
                        <td class="text-right">{{number_format(($item['line_extension_amount'] / $item['invoiced_quantity']), 2)}}</td>
                        @if(isset($request['deliveryterms']))
                            <td class="text-right" style="background-color: rgb(194, 241, 194);">{{number_format(($item['line_extension_amount'] / $item['invoiced_quantity']) / $trmValue, 2)}}</td>
                        @endif
                        @if(isset($item['tax_totals']))
                            <td class="text-right">{{number_format($item['tax_totals'][0]['tax_amount'] / $item['invoiced_quantity'], 2)}}</td>
                            @if(isset($request['deliveryterms']))
                                <td class="text-right" style="background-color: rgb(194, 241, 194);">{{number_format(($item['tax_totals'][0]['tax_amount'] / $item['invoiced_quantity']) / $trmValue, 2)}}</td>
                            @endif
                        @else
                            <td class="text-right">E</td>
                            @if(isset($request['deliveryterms']))
                                <td class="text-right" style="background-color: rgb(194, 241, 194);">E</td>
                            @endif
                        @endif
                        @if(isset($item['allowance_charges']))
                            <?php $TotalDescuentosEnLineas = $TotalDescuentosEnLineas + $item['allowance_charges'][0]['amount'] ?>
                            <td class="text-right">{{number_format($item['allowance_charges'][0]['amount'], 2)}}</td>
                            @if(isset($request['deliveryterms']))
                                <td class="text-right" style="background-color: rgb(194, 241, 194);">{{number_format($item['allowance_charges'][0]['amount'] / $trmValue, 2)}}</td>
                            @endif
                        @else
                            <td class="text-right">0.00</td>
                            @if(isset($request['deliveryterms']))
                                <td class="text-right" style="background-color: rgb(194, 241, 194);">0.00</td>
                            @endif
                        @endif
                        <td class="text-right">
                            @if(isset($item['allowance_charges']) && floatval($item['allowance_charges'][0]['base_amount']) != 0)
                                {{ number_format(($item['allowance_charges'][0]['amount'] * 100) / $item['allowance_charges'][0]['base_amount'], 2) }}
                            @else
                                0.00
                            @endif
                            </td>
                        <td class="text-right">{{number_format($item['line_extension_amount'], 2)}}</td>
                        @if(isset($request['deliveryterms']))
                            <td class="text-right" style="background-color: rgb(194, 241, 194);">{{number_format($item['line_extension_amount'] / $trmValue, 2)}}</td>
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
                <th class="text-center">Impuestos</th>
                <th class="text-center">Retenciones</th>
                <th class="text-center">Totales</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="width: 40%;">
                    <table class="table" style="width: 100%">
                        <thead>
                            <tr>
                                <th class="text-center">Tipo</th>
                                <th class="text-center">Base</th>
                                @if(isset($request['deliveryterms']))
                                    <th class="text-center">Base USD</th>
                                @endif
                                <th class="text-center">Porcentaje</th>
                                <th class="text-center">Valor</th>
                                @if(isset($request['deliveryterms']))
                                    <th class="text-center">Valor USD</th>
                                @endif
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
                                        @if(isset($request['deliveryterms']))
                                            <td class="text-right" style="background-color: rgb(194, 241, 194);">{{number_format($item['taxable_amount'] / $trmValue, 2)}}</td>
                                        @endif
                                        <td class="text-right">
                                            @if(isset($item['percent']))
                                                {{ number_format($item['percent'], 2) }}%
                                            @elseif(isset($item['per_unit_amount']) && isset($item['base_unit_measure']))
                                                {{ number_format($item['per_unit_amount'], 2) }} x {{ number_format($item['base_unit_measure'], 2) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-right">{{number_format($item['tax_amount'], 2)}}</td>
                                        @if(isset($request['deliveryterms']))
                                            <td class="text-right" style="background-color: rgb(194, 241, 194);">{{number_format($item['tax_amount'] / $trmValue, 2)}}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            @else
                                <?php $TotalImpuestos = 0; ?>
                            @endif
                        </tbody>
                    </table>
                </td>
                <td style="width: 30%;">
                    <table class="table" style="width: 100%">
                        <thead>
                            <tr>
                                <th class="text-center">Tipo</th>
                                <th class="text-center">Base</th>
                                @if(isset($request['deliveryterms']))
                                    <th class="text-center">Base USD</th>
                                @endif
                                <th class="text-center">Porcentaje</th>
                                <th class="text-center">Valor</th>
                                @if(isset($request['deliveryterms']))
                                    <th class="text-center">Valor USD</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($withHoldingTaxTotal))
                                <?php $TotalRetenciones = 0; ?>
                                @foreach($withHoldingTaxTotal as $item)
                                    <tr>
                                        <?php $TotalRetenciones = $TotalRetenciones + $item['tax_amount'] ?>
                                        @inject('tax', 'App\Tax')
                                        <td>{{ isset($item['tax_name']) && $item['tax_name'] ? $item['tax_name'] : $tax->findOrFail($item['tax_id'])['name'] }}</td>
                                        <td class="text-right">{{number_format($item['taxable_amount'], 2)}}</td>
                                        @if(isset($request['deliveryterms']))
                                            <td class="text-right" style="background-color: rgb(194, 241, 194);">{{number_format($item['taxable_amount'] / $trmValue, 2)}}</td>
                                        @endif
                                        <td class="text-right">
                                            @if(isset($item['percent']))
                                                {{ number_format($item['percent'], 2) }}%
                                            @elseif(isset($item['per_unit_amount']) && isset($item['base_unit_measure']))
                                                {{ number_format($item['per_unit_amount'], 2) }} x {{ number_format($item['base_unit_measure'], 2) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-right">{{number_format($item['tax_amount'], 2)}}</td>
                                        @if(isset($request['deliveryterms']))
                                            <td class="text-right" style="background-color: rgb(194, 241, 194);">{{number_format($item['tax_amount'] / $trmValue, 2)}}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </td>
                <td style="width: 30%;">
                    <table class="table" style="width: 100%">
                        <thead>
                            <tr>
                                <th class="text-center">Concepto</th>
                                <th class="text-center">Valor</th>
                                @if(isset($request['deliveryterms']))
                                    <th class="text-center">Valor USD</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Nro Lineas:</td>
                                <td class="text-right">{{$ItemNro}}</td>
                                @if(isset($request['deliveryterms']))
                                    <td class="text-right">{{$ItemNro}}</td>
                                @endif
                            </tr>
                            <tr>
                                <td>Base:</td>
                                <td class="text-right">{{number_format($request->legal_monetary_totals['line_extension_amount'], 2)}}</td>
                                @if(isset($request['deliveryterms']))
                                    <td class="text-right" style="background-color: rgb(194, 241, 194);">{{number_format($request->legal_monetary_totals['line_extension_amount'] / $trmValue, 2)}}</td>
                                @endif
                            </tr>
                            <tr>
                                <td>Impuestos:</td>
                                <td class="text-right">{{number_format($TotalImpuestos, 2)}}</td>
                                @if(isset($request['deliveryterms']))
                                    <td class="text-right" style="background-color: rgb(194, 241, 194);">{{number_format($TotalImpuestos / $trmValue, 2)}}</td>
                                @endif
                            </tr>
                            <tr>
                                <td>Retenciones:</td>
                                <td class="text-right">{{number_format($TotalRetenciones, 2)}}</td>
                                @if(isset($request['deliveryterms']))
                                    <td class="text-right" style="background-color: rgb(194, 241, 194);">{{number_format($TotalRetenciones / $trmValue, 2)}}</td>
                                @endif
                            </tr>
                            <tr>
                                <td>Descuentos En Lineas:</td>
                                <td class="text-right">{{number_format($TotalDescuentosEnLineas, 2)}}</td>
                                @if(isset($request['deliveryterms']))
                                    <td class="text-right" style="background-color: rgb(194, 241, 194);">{{number_format($TotalDescuentosEnLineas / $trmValue, 2)}}</td>
                                @endif
                            </tr>
                            <tr>
                                <td>Descuentos Globales:</td>
                                @if(isset($request->legal_monetary_totals['allowance_total_amount']))
                                    <td class="text-right">{{number_format($request->legal_monetary_totals['allowance_total_amount'], 2)}}</td>
                                    @if(isset($request['deliveryterms']))
                                        <td class="text-right" style="background-color: rgb(194, 241, 194);">{{number_format($request->legal_monetary_totals['allowance_total_amount'] / $trmValue, 2)}}</td>
                                    @endif
                                @else
                                    <td class="text-right">{{number_format(0, 2)}}</td>
                                    @if(isset($request['deliveryterms']))
                                        <td class="text-right" style="background-color: rgb(194, 241, 194);">{{number_format(0, 2)}}</td>
                                    @endif
                                @endif
                            </tr>

                            @if(isset($request->legal_monetary_totals['charge_total_amount']))
                                @if($request->legal_monetary_totals['charge_total_amount'] > 0)
                                    <?php $charge_number = 0; ?>
                                    @foreach($request['allowance_charges'] as $allowance_charge)
                                        @if(isset($allowance_charge))
                                            @if($allowance_charge['charge_indicator'] == true)
                                                <?php $charge_number++; ?>
                                                <tr>
                                                    <td>{{$allowance_charge['allowance_charge_reason'] ?? "Cargo Global Nro: ".$charge_number}}</td>
                                                    <td class="text-right">{{number_format($allowance_charge['amount'], 2)}}</td>
                                                    @if(isset($request['deliveryterms']))
                                                        <td class="text-right" style="background-color: rgb(194, 241, 194);">{{number_format($allowance_charge['amount'] / $trmValue, 2)}}</td>
                                                    @endif
                                                </tr>
                                            @endif
                                        @endif
                                    @endforeach
                                @endif
                            @endif

                            @if(isset($request->previous_balance))
                                @if($request->previous_balance > 0)
                                    <tr>
                                        <td>Saldo Anterior:</td>
                                        <td class="text-right">{{number_format($request->previous_balance, 2)}}</td>
                                        @if(isset($request['deliveryterms']))
                                            <td class="text-right" style="background-color: rgb(194, 241, 194);">{{number_format($request->previous_balance / $trmValue, 2)}}</td>
                                        @endif
                                    </tr>
                                @endif
                            @endif
                            <tr>
                                <td>Total Factura - Descuentos:</td>
                                <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + ($request->previous_balance ?? 0) - $TotalRetenciones, 2)}}</td>
                                @if(isset($request['deliveryterms']))
                                    <td class="text-right" style="background-color: rgb(194, 241, 194);">{{number_format(($request->legal_monetary_totals['payable_amount'] + ($request->previous_balance ?? 0) - $TotalRetenciones) / $trmValue, 2)}}</td>
                                @endif
                            </tr>

                            <tr>
                                <td>Total a Pagar</td>
                                <td class="text-right">{{number_format($request->legal_monetary_totals['payable_amount'] + ($request->previous_balance ?? 0) - $TotalRetenciones, 2)}}</td>
                                @if(isset($request['deliveryterms']))
                                    <td class="text-right" style="background-color: rgb(194, 241, 194);">{{number_format(($request->legal_monetary_totals['payable_amount'] + ($request->previous_balance ?? 0) - $TotalRetenciones) / $trmValue, 2)}}</td>
                                @endif
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    <br>

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
                    $totalAmountUSD = isset($request['deliveryterms']) ? round($totalAmount / $trmValue, 2) : 0;

                    // Definimos la moneda
                    $idcurrency = $request->idcurrency ?? null;
                @endphp
                @if(isset($request['deliveryterms']))
                    <p><strong>SON</strong>: {{$Varios->convertir($totalAmountUSD, $idcurrency)}} M/CTE*********.</p>
                @else
                    <p><strong>SON</strong>: {{$Varios->convertir($totalAmount, $idcurrency)}} M/CTE*********.</p>
                @endif
            </p>
        </div>
    </div>
    @if(isset($request['bank_accounts']) && count($request['bank_accounts']) > 0)
        <br>
        <table class="table" style="width: 100%; font-size: 11px; border: 1px solid #ccc;">
            <thead>
                <tr>
                    <th colspan="2" class="text-center" style="background: #f3f3f3;"><strong>Cuentas bancarias para pago</strong></th>
                </tr>
                <tr>
                    <th>Banco</th>
                    <th>Número de Cuenta</th>
                </tr>
            </thead>
            <tbody>
                @foreach($request['bank_accounts'] as $cuenta)
                    <tr>
                        <td>{{$cuenta['description']}}</td>
                        <td>{{$cuenta['number']}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        El pago debe realizarse únicamente a la cuenta bancaria indicada, no asumimos responsabilidad por consignaciones a otras cuentas.
        <br>
    @endif
    @if(isset($notes))
        <div class="summarys">
            <div class="text-word" id="note">
                <p><strong>NOTAS:</strong></p>
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
