@foreach ($debitNoteLines as $key => $debitNoteLine)
    <cac:DebitNoteLine>
        <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", ($key + 1))}}</cbc:ID>
        @if(isset($debitNoteLine->notes))
            <cbc:Note>{{preg_replace("/[\r\n|\n|\r]+/", "", $debitNoteLine->notes)}}</cbc:Note>
        @endif
        <cbc:DebitedQuantity unitCode="{{preg_replace("/[\r\n|\n|\r]+/", "", $debitNoteLine->unit_measure->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($debitNoteLine->invoiced_quantity, 6, '.', ''))}}</cbc:DebitedQuantity>
        @if(isset($idcurrency))
            <cbc:LineExtensionAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $idcurrency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($debitNoteLine->line_extension_amount, 2, '.', ''))}}</cbc:LineExtensionAmount>
        @else
            <cbc:LineExtensionAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($debitNoteLine->line_extension_amount, 2, '.', ''))}}</cbc:LineExtensionAmount>
        @endif
        @if ($debitNoteLine->free_of_charge_indicator === 'true')
            <cac:PricingReference>
                <cac:AlternativeConditionPrice>
                    <cbc:PriceAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($debitNoteLine->price_amount, 2, '.', ''))}}</cbc:PriceAmount>
                    <cbc:PriceTypeCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $debitNoteLine->reference_price->code)}}</cbc:PriceTypeCode>
                </cac:AlternativeConditionPrice>
            </cac:PricingReference>
        @endif
        {{-- TaxTotals line --}}
        @include('xml._tax_totals', ['taxTotals' => $debitNoteLine->tax_totals])
        {{-- AllowanceCharges line  --}}
        @include('xml._allowance_charges', ['allowanceCharges' => $debitNoteLine->allowance_charges])
        <cac:Item>
            <cbc:Description>{{preg_replace("/[\r\n|\n|\r]+/", "", $debitNoteLine->description)}}</cbc:Description>
            <cac:StandardItemIdentification>
                <cbc:ID schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $debitNoteLine->type_item_identification->code)}}" schemeName="{{preg_replace("/[\r\n|\n|\r]+/", "", $debitNoteLine->type_item_identification->code)}}" schemeAgencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $debitNoteLine->type_item_identification->code_agency)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $debitNoteLine->code)}}</cbc:ID>
            </cac:StandardItemIdentification>
            @if($request->isTransport)
                @if($debitNoteLine->is_RNDC)
                    <cac:AdditionalItemProperty>
                        <cbc:Name>01</cbc:Name>
                        <cbc:Value>{{preg_replace("/[\r\n|\n|\r]+/", "", $debitNoteLine->RNDC_consignment_number)}}</cbc:Value>
                    </cac:AdditionalItemProperty>
                    <cac:AdditionalItemProperty>
                        <cbc:Name>02</cbc:Name>
                        <cbc:Value>{{preg_replace("/[\r\n|\n|\r]+/", "", $debitNoteLine->internal_consignment_number)}}</cbc:Value>
                    </cac:AdditionalItemProperty>
                    <cac:AdditionalItemProperty>
                        <cbc:Name>03</cbc:Name>
                        <cbc:Value>{{preg_replace("/[\r\n|\n|\r]+/", "", $debitNoteLine->value_consignment)}}</cbc:Value>
                        <cbc:ValueQuantity unitCode="{{preg_replace("/[\r\n|\n|\r]+/", "", $debitNoteLine->unit_measure_consignment->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $debitNoteLine->quantity_consignment)}}</cbc:ValueQuantity>
                    </cac:AdditionalItemProperty>
                @endif
            @endif
        </cac:Item>
        <cac:Price>
            @if(isset($idcurrency))
                <cbc:PriceAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $idcurrency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format(($debitNoteLine->free_of_charge_indicator === 'true') ? 0 : $debitNoteLine->price_amount, 2, '.', ''))}}</cbc:PriceAmount>
            @else
                <cbc:PriceAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format(($debitNoteLine->free_of_charge_indicator === 'true') ? 0 : $debitNoteLine->price_amount, 2, '.', ''))}}</cbc:PriceAmount>
            @endif
            <cbc:BaseQuantity unitCode="{{preg_replace("/[\r\n|\n|\r]+/", "", $debitNoteLine->unit_measure->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($debitNoteLine->base_quantity, 6, '.', ''))}}</cbc:BaseQuantity>
        </cac:Price>
    </cac:DebitNoteLine>
@endforeach
