<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'items';

    protected $fillable = [
        'name',
        'second_name',
        'description',
        'item_type_id',
        'internal_id',
        'date_of_due',
        'sale_unit_price',
        'purchase_unit_price',
        'commission_amount',
        'commission_type',
        'amount_plastic_bag_taxes',
        'calculate_quantity',
        'sale_unit_price_set',
        'is_set',
        'model',
        'image',
        'image_medium',
        'image_small',
        'lot_code',
        'lots_enabled',
        'series_enabled',
        'percentage_of_profit',
        'has_perception',
        'percentage_perception',
        'attributes',
        'active',
        'status',
        'apply_store',
        'companies_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'companies_id');
    }
}
