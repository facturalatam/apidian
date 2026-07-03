@foreach ($creditNoteLines as $key => $creditNoteLine)
    <cac:CreditNoteLine>
        <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", ($key + 1))}}</cbc:ID>
        @if(isset($creditNoteLine->notes))
            <cbc:Note>{{preg_replace("/[\r\n|\n|\r]+/", "", $creditNoteLine->notes)}}</cbc:Note>
        @endif
        <cbc:CreditedQuantity unitCode="{{preg_replace("/[\r\n|\n|\r]+/", "", $creditNoteLine->unit_measure->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($creditNoteLine->invoiced_quantity, 6, '.', ''))}}</cbc:CreditedQuantity>
        @if(isset($idcurrency))
            <cbc:LineExtensionAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $idcurrency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($creditNoteLine->line_extension_amount, 2, '.', ''))}}</cbc:LineExtensionAmount>
        @else
            <cbc:LineExtensionAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($creditNoteLine->line_extension_amount, 2, '.', ''))}}</cbc:LineExtensionAmount>
        @endif
        <cbc:FreeOfChargeIndicator>{{preg_replace("/[\r\n|\n|\r]+/", "", $creditNoteLine->free_of_charge_indicator)}}</cbc:FreeOfChargeIndicator>
        @if ($creditNoteLine->free_of_charge_indicator === 'true')
            <cac:PricingReference>
                <cac:AlternativeConditionPrice>
                    <cbc:PriceAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($creditNoteLine->price_amount, 2, '.', ''))}}</cbc:PriceAmount>
                    <cbc:PriceTypeCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $creditNoteLine->reference_price->code)}}</cbc:PriceTypeCode>
                </cac:AlternativeConditionPrice>
            </cac:PricingReference>
        @endif
        {{-- TaxTotals line --}}
        @include('xml._tax_totals', ['taxTotals' => $creditNoteLine->tax_totals])
        {{-- AllowanceCharges line  --}}
        @include('xml._allowance_charges', ['allowanceCharges' => $creditNoteLine->allowance_charges])
        <cac:Item>
            <cbc:Description>{{preg_replace("/[\r\n|\n|\r]+/", "", $creditNoteLine->description)}}</cbc:Description>
            @if(isset($creditNoteLine->brandname))
                <cbc:BrandName>{{preg_replace("/[\r\n|\n|\r]+/", "", $creditNoteLine->brandname)}}</cbc:BrandName>
            @endif
            @if(isset($creditNoteLine->modelname))
                <cbc:ModelName>{{preg_replace("/[\r\n|\n|\r]+/", "", $creditNoteLine->modelname)}}</cbc:ModelName>
            @endif
            <cac:StandardItemIdentification>
                <cbc:ID schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $creditNoteLine->type_item_identification->code)}}" schemeName="{{preg_replace("/[\r\n|\n|\r]+/", "", $creditNoteLine->type_item_identification->name)}}" schemeAgencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $creditNoteLine->type_item_identification->code_agency)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $creditNoteLine->code)}}</cbc:ID>
            </cac:StandardItemIdentification>
            @if($request->isTransport)
                @if($creditNoteLine->is_RNDC)
                    <cac:AdditionalItemProperty>
                        <cbc:Name>01</cbc:Name>
                        <cbc:Value>{{preg_replace("/[\r\n|\n|\r]+/", "", $creditNoteLine->RNDC_consignment_number)}}</cbc:Value>
                    </cac:AdditionalItemProperty>
                    <cac:AdditionalItemProperty>
                        <cbc:Name>02</cbc:Name>
                        <cbc:Value>{{preg_replace("/[\r\n|\n|\r]+/", "", $creditNoteLine->internal_consignment_number)}}</cbc:Value>
                    </cac:AdditionalItemProperty>
                    <cac:AdditionalItemProperty>
                        <cbc:Name>03</cbc:Name>
                        <cbc:Value>{{preg_replace("/[\r\n|\n|\r]+/", "", $creditNoteLine->value_consignment)}}</cbc:Value>
                        <cbc:ValueQuantity unitCode="{{preg_replace("/[\r\n|\n|\r]+/", "", $creditNoteLine->unit_measure_consignment->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $creditNoteLine->quantity_consignment)}}</cbc:ValueQuantity>
                    </cac:AdditionalItemProperty>
                @endif
            @endif
        </cac:Item>
        <cac:Price>
            @if(isset($idcurrency))
                <cbc:PriceAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $idcurrency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format(($creditNoteLine->free_of_charge_indicator === 'true') ? 0 : $creditNoteLine->price_amount, 2, '.', ''))}}</cbc:PriceAmount>
            @else
                <cbc:PriceAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format(($creditNoteLine->free_of_charge_indicator === 'true') ? 0 : $creditNoteLine->price_amount, 2, '.', ''))}}</cbc:PriceAmount>
            @endif
            <cbc:BaseQuantity unitCode="{{preg_replace("/[\r\n|\n|\r]+/", "", $creditNoteLine->unit_measure->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($creditNoteLine->base_quantity, 6, '.', ''))}}</cbc:BaseQuantity>
        </cac:Price>
    </cac:CreditNoteLine>
@endforeach
