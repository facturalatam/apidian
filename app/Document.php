<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use DateTime;
use App\TypeDocument;
use App\Customer;
use App\TypeCurrency;


class Document extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'client' => 'object',
        'taxes' => 'object',
        'date_issue' => 'datetime',
    ];

    protected $with = ['type_document', 'currency', 'customer_document'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'identification_number',
        'state_document_id',
        'type_document_id',
        'prefix',
        'number',
        'xml',
        'cufe',
        'acknowledgment_received',
        'type_invoice_id',
        'client_id',
        'client',
        'currency_id',
        'date_issue',
        'date_expiration',
        'observation',
        'reference_id',
        'note_concept_id',
        'sale',
        'total_discount',
        'taxes',
        'total_tax',
        'subtotal',
        'total',
        'version_ubl_id',
        'ambient_id',
        'response_api',
        'payment_form_id',
        'payment_method_id',
        'time_days_credit',
        'response_api_status',
        'correlative_api',
        'request_api',
        'response_api',
        'pdf',
        'customer',
        'aceptacion',
        'cude_aceptacion',
        'payload_aceptacion',
        'send_email_success',
        'send_email_date_time',
        'response_dian'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];


    /**
    * Get the state document belongs to
    */
    public function customer_document() {
        return $this->belongsTo(Customer::class, 'customer', 'identification_number');
    }

    /**
    * Get the state document belongs to
    */
    public function state_document() {
        //return $this->belongsTo(StateDocument::class);
    }

    /**
    * Get the detail documents has many
    */
    public function detail_documents() {
       // return $this->hasMany(DetailDocument::class);
    }

    /**
    * Get the currency belongs to
    */
    public function currency() {
        return $this->belongsTo(TypeCurrency::class);
    }

    /**
    * Get the reference belongs to
    */
    public function reference() {
        //return $this->belongsTo(Document::class, 'reference_id');
    }

    /**
    * Get the type document belongs to
    */
    public function type_document() {
        return $this->belongsTo(TypeDocument::class);
    }

    /**
    * Get the country client belongs to
    */
    public function country_client() {
        //return $this->belongsTo(Country::class, 'client->country_id');
    }

    /**
    * Get the departament client belongs to
    */
    public function departament_client() {
       // return $this->belongsTo(Department::class, 'client->department_id');
    }

    /**
    * Get the city client belongs to
    */
    public function city_client() {
        //return $this->belongsTo(City::class, 'client->city_id');
    }

    /**
    * Get the log documents has many
    */
    public function log_documents() {
       // return $this->hasMany(LogDocument::class)->latest();
    }

    /**
     * Get the taxes collect
     *
     * @return string
     */
    public function getTaxesCollectAttribute() {
        //return collect($this->taxes);
    }

    public function payment_form()
    {
         //return $this->belongsTo(PaymentForm::class);
    }

    public function payment_method()
    {
        //return $this->belongsTo(PaymentMethod::class);
    }

    public function getPlazoAttribute()
    {
        /*$ini = $this->created_at;
        $fin  = new DateTime($this->date_expiration);
        $dif =  $ini->diff($fin);
        return $dif->days;*/
    }

    public function getResponseApiInvoiceAttribute()
    {
        //$model = json_decode($this->response_api);
        //return $model;
    }

    public function getResponseApiInvoiceStatusAttribute()
    {
        //$model = json_decode($this->response_api_status);
       // return $model;
    }

    public function getResponseApiInvoiceStatusDateValidAttribute()
    {
       /* $date_valid = '';
        $model = json_decode($this->response_api_status);
        $status =  (bool)$model->ResponseDian->Envelope->Body->GetStatusZipResponse->GetStatusZipResult->DianResponse->IsValid;
        if($status)
        {
            $date_valid = $this->created_at;
        }

        return $date_valid;*/
    }

    public function getClientDataAttribute()
    {
//        $model = json_decode($this->client);
//        return $model->name;
    }

    protected $appends = ['client_data'];

    public function scopeFilter($query, $search = null) {
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->orWhere('number', 'LIKE', "%$search%")
                    ->orWhere('prefix', 'LIKE', "%$search%")
                    ->orWhereRaw("CONCAT(COALESCE(prefix, ''), COALESCE(number, '')) LIKE ?", ["%$search%"])
                    ->orWhere('total', 'LIKE', "%$search%")
                    ->orWhere('date_issue', 'LIKE', "%$search%")
                    ->orWhereRaw("JSON_UNQUOTE(client->'$.name') LIKE ?", ["%$search%"])
                    ->orWhereRaw("JSON_UNQUOTE(client->'$.email') LIKE ?", ["%$search%"])
                    ->orWhereRaw("JSON_UNQUOTE(client->'$.phone') LIKE ?", ["%$search%"])
                    ->orWhereRaw("JSON_UNQUOTE(client->'$.identification_number') LIKE ?", ["%$search%"]);
                });
        }
        return $query;
    }

    public function scopeFilterByRangeDate($query, $date = null) {
        if (!empty($date)) {
            $now = now();
            switch ($date) {
                case 'current_month':
                    $query->whereBetween('date_issue', [
                        $now->copy()->startOfMonth(),
                        $now->endOfMonth(),
                    ]);
                    break;
                case 'last_month':
                    $lastMonthStart = $now->copy()->subMonthNoOverflow()->startOfMonth();
                    $lastMonthEnd = $lastMonthStart->copy()->endOfMonth();
                    $query->whereBetween('date_issue', [
                        $lastMonthStart,
                        $lastMonthEnd,
                    ]);
                    break;
                case 'last_ninety':
                    $lastNinety = $now->copy()->subDays(90)->startOfDay();
                    $query->whereBetween('date_issue', [
                        $lastNinety,
                        $now,
                    ]);
                    break;
                case 'all':
                default:
                    // No se aplica filtro
                    break;
            }
        }
        return $query;
    }
}
