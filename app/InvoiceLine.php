<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoiceLine extends Model
{
    /**
     * With default model.
     *
     * @var array
     */
    protected $with = [
        'unit_measure', 'type_item_identification', 'reference_price', 'type_generation_transmition',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'unit_measure_id', 'type_item_identification_id', 'reference_price_id', 'type_generation_transmition_id', 'start_date', 'invoiced_quantity', 'line_extension_amount', 'free_of_charge_indicator', 'notes', 'description', 'agentparty', 'agentparty_dv', 'brandname', 'modelname', 'code', 'price_amount', 'base_quantity', 'allowance_charges', 'tax_totals', 'unit_measure_consignment_id', 'value_consignment', 'quantity_consignment', 'internal_consignment_number', 'RNDC_consignment_number', 'is_RNDC', 'seller_code', 'seller_code_extended','purchase_order_number',
    ];

    /**
     * Get the unit measure that owns the invoice line.
     */
    public function unit_measure()
    {
        return $this->belongsTo(UnitMeasure::class);
    }

    /**
     * Get the unit measure that owns the invoice line.
     */
    public function unit_measure_consignment()
    {
        return $this->belongsTo(UnitMeasure::class);
    }

    /**
     * Get the type item identification that owns the invoice line.
     */
    public function type_item_identification()
    {
        return $this->belongsTo(TypeItemIdentification::class);
    }

    /**
     * Get the reference price that owns the invoice line.
     */
    public function reference_price()
    {
        return $this->belongsTo(ReferencePrice::class);
    }

    /**
     * Get the type generation transmition that owns the invoice line.
     */
    public function type_generation_transmition()
    {
        return $this->belongsTo(TypeGenerationTransmition::class);
    }

    /**
     * Get the invoice line allowance charges.
     *
     * @return string
     */
    public function getAllowanceChargesAttribute()
    {
        return $this->attributes['allowance_charges'] ?? [];
    }

    /**
     * Set the invoice line allowance charges.
     *
     * @param string $value
     */
    public function setAllowanceChargesAttribute(array $data = [])
    {
        $allowanceCharges = collect();

        foreach ($data as $value) {
            $allowanceCharges->push(new AllowanceCharge($value));
        }

        $this->attributes['allowance_charges'] = $allowanceCharges;
    }

    /**
     * Get the invoice line tax totals.
     *
     * @return string
     */
    public function getTaxTotalsAttribute()
    {
        return $this->attributes['tax_totals'] ?? [];
    }

    /**
     * Set the invoice line tax totals.
     *
     * @param string $value
     */
    public function setTaxTotalsAttribute(array $data = [])
    {
        $taxTotals = collect();

        foreach ($data as $value) {
            $taxTotals->push(new TaxTotal($value));
        }

        $this->attributes['tax_totals'] = $taxTotals;
    }

    /**
     * Get the free of charge indicator.
     *
     * @return string
     */
    public function getFreeOfChargeIndicatorAttribute()
    {
        return ($this->attributes['free_of_charge_indicator']) ? 'true' : 'false';
    }
}
