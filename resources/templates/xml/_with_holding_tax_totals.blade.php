@for($i = 1; $i <= 9; $i++)
    <?php $TotalImpuesto = 0; $CantItems = 0; ?>
    @foreach ($withHoldingTaxTotal as $key => $taxTotal)
        @if(preg_replace("/[\r\n|\n|\r]+/", "", $taxTotal->tax->code) === '0'.$i)
            <?php $TotalImpuesto += $taxTotal->tax_amount; $CantItems += 1; ?>
        @endif
    @endforeach
    @if($CantItems > 0)
            <cac:WithholdingTaxTotal>
                @if(isset($idcurrency))
                    <cbc:TaxAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $idcurrency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($TotalImpuesto, 3, '.', ''))}}</cbc:TaxAmount>
                @else
                    <cbc:TaxAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($TotalImpuesto, 3, '.', ''))}}</cbc:TaxAmount>
                @endif
        @foreach($withHoldingTaxTotal as $key => $taxTotal)
            @if(preg_replace("/[\r\n|\n|\r]+/", "", $taxTotal->tax->code) === '0'.$i)
                <cac:TaxSubtotal>
                    @if (!$taxTotal->is_fixed_value)
                        @if(isset($idcurrency))
                            <cbc:TaxableAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $idcurrency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($taxTotal->taxable_amount, 3, '.', ''))}}</cbc:TaxableAmount>
                        @else
                            <cbc:TaxableAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($taxTotal->taxable_amount, 3, '.', ''))}}</cbc:TaxableAmount>
                        @endif
                    @endif
                    @if ($taxTotal->is_fixed_value)
                        <cbc:BaseUnitMeasure unitCode="{{preg_replace("/[\r\n|\n|\r]+/", "", $taxTotal->unit_measure->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($taxTotal->base_unit_measure, 6, '.', ''))}}</cbc:BaseUnitMeasure>
                        @if(isset($idcurrency))
                            <cbc:PerUnitAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $idcurrency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($taxTotal->per_unit_amount, 3, '.', ''))}}</cbc:PerUnitAmount>
                        @else
                            <cbc:PerUnitAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($taxTotal->per_unit_amount, 3, '.', ''))}}</cbc:PerUnitAmount>
                        @endif
                    @endif
                        @if(isset($idcurrency))
                            <cbc:TaxAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $idcurrency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($taxTotal->tax_amount, 3, '.', ''))}}</cbc:TaxAmount>
                        @else
                            <cbc:TaxAmount currencyID="{{preg_replace("/[\r\n|\n|\r]+/", "", $company->type_currency->code)}}">{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($taxTotal->tax_amount, 3, '.', ''))}}</cbc:TaxAmount>
                        @endif
                    <cac:TaxCategory>
                        @if(isset($taxTotal->percent) && $taxTotal->percent !== null && $taxTotal->percent !== '')
                            <cbc:Percent>{{preg_replace("/[\r\n|\n|\r]+/", "", number_format($taxTotal->percent, 3, '.', ''))}}</cbc:Percent>
                        @endif
                        <cac:TaxScheme>
                            <cbc:ID>{{preg_replace("/[\r\n|\n|\r]+/", "", $taxTotal->tax->code)}}</cbc:ID>
                            <cbc:Name>{{preg_replace("/[\r\n|\n|\r]+/", "", $taxTotal->tax->name)}}</cbc:Name>
                        </cac:TaxScheme>
                    </cac:TaxCategory>
                </cac:TaxSubtotal>
            @endif
        @endforeach
            </cac:WithholdingTaxTotal>
    @endif
@endfor
