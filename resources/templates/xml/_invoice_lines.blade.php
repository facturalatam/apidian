@foreach ($invoiceLines as $key => $invoiceLine)
    <cac:InvoiceLine>
        @if($request->isMandate)
            <cbc:ID schemeID="1">{{preg_replace("/[\r\n|\n|\r]+/", "", ($key + 1))}}</cbc:ID>
        @else
            @if($request->isTransport)
                @if($invoiceLine->is_RNDC)
                    <cbc:ID schemeID="1">{{preg_replace("/[\r\n|\n|\r]+/", "", ($key + 1))}}</cbc:ID>
                @else
                    <cbc:ID schemeID="0">{{preg_replace("/[\r\n|\n|\r]+/", "", ($key + 1))}}</cbc:ID>
                @endif
            @else
                <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", ($key + 1))}}</cbc:ID>
            @endif
        @endif
        @if (preg_replace("/[\r\n|\n|\r]+/", "", $invoiceLine->description) == 'Administración')
            @isset($noteAIU)
                <cbc:Note>{{"Contrato de servicios AIU por concepto de: ".preg_replace("/[\r\n|\n|\r]+/", "", $noteAIU)}}</cbc:Note>
            @endisset
        @endif
        @if(isset($invoiceLine->notes))
            <cbc:Note>{{preg_replace("/[\r\n|\n|\r]+/", "", $invoiceLine->notes)}}</cbc:Note>
        @endif
        <cbc:InvoicedQuantity unitCode="{{preg_replace("/[\r\n|\n|\r]+/", "", $invoiceLine->unit_measure->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($invoiceLine->invoiced_quantity, 6, '.', ''))}}</cbc:InvoicedQuantity>
{{--        @if(isset($idcurrency))
            <cbc:LineExtensionAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $idcurrency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($invoiceLine->line_extension_amount, 2, '.', ''))}}</cbc:LineExtensionAmount>
        @else   --}}
            <cbc:LineExtensionAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($invoiceLine->line_extension_amount, 2, '.', ''))}}</cbc:LineExtensionAmount>
{{--        @endif  --}}
        @if($typeDocument->id == '24')
    		<cbc:AccountingCostCode>{{preg_replace("/[\r\n|\n|\r]+/", "", ($key + 1))}}</cbc:AccountingCostCode>
        @endif
        @if(isset($invoiceLine->type_generation_transmition))
            <cac:InvoicePeriod>
                <cbc:StartDate>{{preg_replace("/[\r\n|\n|\r]+/", "", $invoiceLine->start_date)}}</cbc:StartDate>
                <cbc:DescriptionCode>{{preg_replace("/[\r\n|\n|\r]+/", "", $invoiceLine->type_generation_transmition->id)}}</cbc:DescriptionCode>
                <cbc:Description>{{preg_replace("/[\r\n|\n|\r]+/", "", $invoiceLine->type_generation_transmition->name)}}</cbc:Description>
            </cac:InvoicePeriod>
        @endif
        @if($typeDocument->id != '11')
            <cbc:FreeOfChargeIndicator>{{preg_replace("/[\r\n|\n|\r]+/", "", $invoiceLine->free_of_charge_indicator)}}</cbc:FreeOfChargeIndicator>
        @endif
        @if (preg_replace("/[\r\n|\n|\r]+/", "", $invoiceLine->free_of_charge_indicator) === 'true')
            <cac:PricingReference>
                <cac:AlternativeConditionPrice>
{{--                @if(isset($idcurrency))
                    <cbc:PriceAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $idcurrency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($invoiceLine->price_amount, 2, '.', ''))}}</cbc:PriceAmount>
                @else   --}}
                    <cbc:PriceAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($invoiceLine->price_amount, 2, '.', ''))}}</cbc:PriceAmount>
{{--                @endif  --}}
                </cac:AlternativeConditionPrice>
            </cac:PricingReference>
        @endif
        {{-- AllowanceCharges line  --}}
        @include('xml._allowance_charges', ['allowanceCharges' => $invoiceLine->allowance_charges])
        {{-- TaxTotals line --}}
        @include('xml._tax_totals', ['taxTotals' => $invoiceLine->tax_totals])
        <cac:Item>
            <cbc:Description>{{preg_replace("/[\r\n|\n|\r]+/", "", $invoiceLine->description)}}</cbc:Description>
            @if($typeDocument->id == '24')
    			<cbc:PackSizeNumeric>0</cbc:PackSizeNumeric>
    			<cac:SellersItemIdentification>
	    			<cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $invoiceLine->code)}}</cbc:ID>
		    		<cbc:ExtendedID>{{preg_replace("/[\r\n|\n|\r]+/", "", $invoiceLine->seller_code)}}</cbc:ExtendedID>
    			</cac:SellersItemIdentification>
            @endif
            @if(isset($invoiceLine->brandname))
                <cbc:BrandName>{{preg_replace("/[\r\n|\n|\r]+/", "", $invoiceLine->brandname)}}</cbc:BrandName>
            @endif
            @if(isset($invoiceLine->modelname))
                <cbc:ModelName>{{preg_replace("/[\r\n|\n|\r]+/", "", $invoiceLine->modelname)}}</cbc:ModelName>
            @endif
            <cac:StandardItemIdentification>
                @if($typeDocument->id == '24')
    				<cbc:ID schemeAgencyID="195" schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $invoiceLine->type_item_identification->code)}}" schemeName="EAN13">{{preg_replace("/[\r\n|\n|\r]+/", "", $invoiceLine->code)}}</cbc:ID>
                @else
                    <cbc:ID schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $invoiceLine->type_item_identification->code)}}" schemeName="{{preg_replace("/[\r\n|\n|\r]+/", "", $invoiceLine->type_item_identification->name)}}" schemeAgencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $invoiceLine->type_item_identification->code_agency)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $invoiceLine->code)}}</cbc:ID>
                @endif
            </cac:StandardItemIdentification>
            @if(isset($invoiceLine->agentparty))
                <cac:InformationContentProviderParty>
                    <cac:PowerOfAttorney>
                       <cac:AgentParty>
                          <cac:PartyIdentification>
                             <cbc:ID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="{{preg_replace("/[\r\n|\n|\r]+/", "", $invoiceLine->agentparty_dv)}}" schemeName="31">{{preg_replace("/[\r\n|\n|\r]+/", "", $invoiceLine->agentparty)}}</cbc:ID>
                          </cac:PartyIdentification>
                       </cac:AgentParty>
                    </cac:PowerOfAttorney>
                </cac:InformationContentProviderParty>
            @endif
            @if($request->isTransport)
                @if($invoiceLine->is_RNDC)
                    <cac:AdditionalItemProperty>
                        <cbc:Name>01</cbc:Name>
                        <cbc:Value>{{preg_replace("/[\r\n|\n|\r]+/", "", $invoiceLine->RNDC_consignment_number)}}</cbc:Value>
                    </cac:AdditionalItemProperty>
                    <cac:AdditionalItemProperty>
                        <cbc:Name>02</cbc:Name>
                        <cbc:Value>{{preg_replace("/[\r\n|\n|\r]+/", "", $invoiceLine->internal_consignment_number)}}</cbc:Value>
                    </cac:AdditionalItemProperty>
                    <cac:AdditionalItemProperty>
                        <cbc:Name>03</cbc:Name>
                        <cbc:Value>{{preg_replace("/[\r\n|\n|\r]+/", "", $invoiceLine->value_consignment)}}</cbc:Value>
                        <cbc:ValueQuantity unitCode="{{preg_replace("/[\r\n|\n|\r]+/", "", $invoiceLine->unit_measure_consignment->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", $invoiceLine->quantity_consignment)}}</cbc:ValueQuantity>
                    </cac:AdditionalItemProperty>
                @endif
            @endif
        </cac:Item>
        <cac:Price>
            <cbc:PriceAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}">
                {{preg_replace("/[\r\n|\n|\r]+/", "", number_format($invoiceLine->price_amount, 2, '.', ''))}}
            </cbc:PriceAmount>
            <cbc:BaseQuantity unitCode="{{preg_replace("/[\r\n|\n|\r]+/", "", $invoiceLine->unit_measure->code)}}">
                {{preg_replace("/[\r\n|\n|\r]+/", "", number_format($invoiceLine->base_quantity, 6, '.', ''))}}
            </cbc:BaseQuantity>
        </cac:Price>
    </cac:InvoiceLine>
@endforeach
