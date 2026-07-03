<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use DateTime;
use App\TypeDocument;


class ReceivedDocument extends Model
{
    use SoftDeletes;

    protected $with = ['type_document'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['identification_number', 'dv', 'name_seller', 'state_document_id', 'type_document_id', 'prefix', 'number', 'xml', 'cufe', 'date_issue', 'sale', 'total_discount', 'total_tax', 'subtotal', 'total', 'ambient_id', 'pdf', 'acu_recibo', 'cude_acu_recibo', 'payload_acu_recibo', 'rec_bienes', 'cude_rec_bienes', 'payload_rec_bienes', 'aceptacion', 'cude_aceptacion', 'payload_aceptacion', 'rechazo', 'cude_rechazo', 'payload_rechazo', 'request_api', 'customer_name'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];


    /**
    * Get the type document belongs to
    */
    public function type_document() {
        return $this->belongsTo(TypeDocument::class);
    }

    public function getClientDataAttribute()
    {
//        $model = json_decode($this->client);
//        return $model->name;
    }

    protected $appends = ['client_data'];
}
