@for($i = 1; $i <= 23; $i++)
    <?php $TotalImpuesto = 0; $CantItems = 0; ?>
    @foreach ($taxTotals as $key => $taxTotal)
        @if(intval(preg_replace("/[\r\n|\n|\r]+/", "", $taxTotal->tax->id)) === $i)
            <?php $TotalImpuesto += $taxTotal->tax_amount; $CantItems += 1; ?>
        @endif
    @endforeach
    @if($CantItems > 0)
            <cac:TaxTotal>
{{--                @if(isset($idcurrency))
                    <cbc:TaxAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $idcurrency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($TotalImpuesto, 2, '.', ''))}}</cbc:TaxAmount>
                    <cbc:RoundingAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $idcurrency->code)}}">0.00</cbc:RoundingAmount>
                @else   --}}
                    <cbc:TaxAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($TotalImpuesto, 2, '.', ''))}}</cbc:TaxAmount>
                    <cbc:RoundingAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}">0.00</cbc:RoundingAmount>
{{--                @endif  --}}
        @foreach ($taxTotals as $key => $taxTotal)
            @if(intval(preg_replace("/[\r\n|\n|\r]+/", "", $taxTotal->tax->id)) === $i)
                <cac:TaxSubtotal>
{{--                        @if(isset($idcurrency))
                            <cbc:TaxableAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $idcurrency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($taxTotal->taxable_amount, 2, '.', ''))}}</cbc:TaxableAmount>
                        @else   --}}
                            <cbc:TaxableAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($taxTotal->taxable_amount, 2, '.', ''))}}</cbc:TaxableAmount>
{{--                        @endif  --}}
{{--                    @if(isset($idcurrency))
                        <cbc:TaxAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $idcurrency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($taxTotal->tax_amount, 2, '.', ''))}}</cbc:TaxAmount>
                    @else   --}}
                        <cbc:TaxAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($taxTotal->tax_amount, 2, '.', ''))}}</cbc:TaxAmount>
{{--                    @endif  --}}
                    @if ($taxTotal->is_fixed_value && $taxTotal->base_unit_measure)

                        <cbc:BaseUnitMeasure unitCode="{{preg_replace("/[\r\n|\n|\r]+/", "", $taxTotal->unit_measure->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($taxTotal->base_unit_measure, 6, '.', ''))}}</cbc:BaseUnitMeasure>
{{--                        @if(isset($idcurrency))
                            <cbc:PerUnitAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $idcurrency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($taxTotal->per_unit_amount, 2, '.', ''))}}</cbc:PerUnitAmount>
                        @else   --}}
                            <cbc:PerUnitAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($taxTotal->per_unit_amount, 2, '.', ''))}}</cbc:PerUnitAmount>
{{--                        @endif  --}}
                    @endif
                    <cac:TaxCategory>
                        @if(isset($taxTotal->percent) && $taxTotal->percent !== null && $taxTotal->percent !== '')
                            <cbc:Percent>{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($taxTotal->percent, 2, '.', ''))}}</cbc:Percent>
                        @endif
                        <cac:TaxScheme>
                            <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $taxTotal->tax->code)}}</cbc:ID>
                            @if($taxTotal->tax_id == 15)
                                <cbc:Name>{{preg_replace("/[\r\n|\n|\r]+/", "", $taxTotal->tax_name)}}</cbc:Name>
                            @else
                                <cbc:Name>{{preg_replace("/[\r\n|\n|\r]+/", "", $taxTotal->tax->name)}}</cbc:Name>
                            @endif
                        </cac:TaxScheme>
                    </cac:TaxCategory>
                </cac:TaxSubtotal>
            @endif
        @endforeach
            </cac:TaxTotal>
    @endif
@endfor
