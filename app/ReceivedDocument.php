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

    /**
     * Búsqueda de eventos RADIAN (panel de empresa > Eventos RADIAN).
     *
     * @param  string|null  $search    Valor (en fecha es el "desde").
     * @param  string|null  $field     Columna: number, prefix, nit, name, date.
     * @param  string|null  $searchTo  "Hasta" del rango de fechas.
     */
    public function scopeFilter($query, $search = null, $field = null, $searchTo = null) {
        $hasValue = !empty($search) || ($field === 'date' && !empty($searchTo));
        if (!$hasValue) {
            return $query;
        }

        if (!empty($field)) {
            switch ($field) {
                case 'number':
                    $query->where(function ($q) use ($search) {
                        $q->where('number', 'LIKE', "%$search%")
                            ->orWhereRaw("CONCAT(COALESCE(prefix, ''), COALESCE(number, '')) LIKE ?", ["%$search%"]);
                    });
                    break;
                case 'prefix':
                    $query->where('prefix', 'LIKE', "%$search%");
                    break;
                case 'nit':
                    $query->where('identification_number', 'LIKE', "%$search%");
                    break;
                case 'name':
                    $query->where('name_seller', 'LIKE', "%$search%");
                    break;
                case 'date':
                    if (!empty($search) && !empty($searchTo)) {
                        $query->whereDate('date_issue', '>=', $search)->whereDate('date_issue', '<=', $searchTo);
                    } elseif (!empty($search)) {
                        $query->whereDate('date_issue', $search);
                    } elseif (!empty($searchTo)) {
                        $query->whereDate('date_issue', $searchTo);
                    }
                    break;
            }
            return $query;
        }

        // Búsqueda global (todos los campos).
        $query->where(function ($q) use ($search) {
            $q->orWhere('number', 'LIKE', "%$search%")
                ->orWhere('prefix', 'LIKE', "%$search%")
                ->orWhereRaw("CONCAT(COALESCE(prefix, ''), COALESCE(number, '')) LIKE ?", ["%$search%"])
                ->orWhere('identification_number', 'LIKE', "%$search%")
                ->orWhere('name_seller', 'LIKE', "%$search%")
                ->orWhere('date_issue', 'LIKE', "%$search%");
        });
        return $query;
    }
}
