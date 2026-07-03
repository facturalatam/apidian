<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    /**
     * With default model.
     *
     * @var array
     */
    protected $with = [
        'user',
        'software',
        'certificate',
        'resolutions',
        'language',
        'tax',
        'country',
        'type_document_identification',
        'type_operation',
        'type_environment',
        'eqdocs_type_environment',
        'payroll_type_environment',
        'support_document_type_environment',
        'event_type_environment',
        'type_currency',
        'type_organization',
        'municipality',
        'type_liability',
        'type_regime',
        'send',
        'type_plan',
        'type_plan2',
        'type_plan3',
        'type_plan4',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $casts = [
        'type_liability_ids' => 'array',
    ];

    protected $fillable = [
        'user_id',
        'identification_number',
        'dv',
        'language_id',
        'tax_id',
        'type_environment_id',
        'payroll_type_environment_id',
        'eqdocs_type_environment_id',
        'support_document_type_environment_id',
        'event_type_environment_id',
        'type_operation_id',
        'type_document_identification_id',
        'country_id',
        'type_currency_id',
        'type_organization_id',
        'type_regime_id',
        'type_liability_id',
        'type_liability_ids',
        'municipality_id',
        'merchant_registration',
        'address',
        'phone',
        'password',
        'newpassword',
        'type_plan_id',
        'type_plan2_id',
        'type_plan3_id',
        'type_plan4_id',
        'start_plan_date',
        'start_plan_date2',
        'start_plan_date3',
        'start_plan_date4',
        'absolut_start_plan_date',
        'municipality_name',
        'state_name',
        'state',
        'absolut_plan_documents',
        'allow_seller_login',
        'graphic_representation_template',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'software',
        'certificate',
        'resolutions',
        'language',
        'tax',
        'country',
        'type_document_identification',
        'type_operation',
        'type_environment',
        'eqdocs_type_environment',
        'payroll_type_environment',
        'support_document_type_environment',
        'event_type_environment',
        'type_currency',
        'type_organization',
        'municipality',
        'type_liability',
        'type_regime',
        'type_plan',
        'type_plan2',
        'type_plan3',
        'type_plan4',
        'absolut_plan_documents',
    ];

    /**
     * Get the software record associated with the company.
     */
    public function software()
    {
        return $this->hasOne(Software::class);
    }

    /**
     * Get the certificate record associated with the company.
     */
    public function certificate()
    {
        return $this->hasOne(Certificate::class);
    }

    /**
     * Get the resolutions record associated with the company.
     */
    public function resolutions()
    {
        return $this->hasMany(Resolution::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'companies_id');
    }

    public function customers()
    {
        return $this->hasMany(Customer::class, 'companies_id');
    }

    /**
     * Get the language that owns the company.
     */
    public function language()
    {
        return $this->belongsTo(Language::class)
            ->withDefault([
                'id' => 79,
                'name' => 'Spanish; Castilian',
                'code' => 'es',
            ]);
    }

    /**
     * Get the tax that owns the company.
     */
    public function tax()
    {
        return $this->belongsTo(Tax::class)
            ->withDefault([
                'id' => 1,
                'name' => 'IVA',
                'description' => 'Impuesto sobre la Ventas',
                'code' => '01',
            ]);
    }

    /**
     * Get the country that owns the company.
     */
    public function country()
    {
        return $this->belongsTo(Country::class)
            ->withDefault([
                'id' => 46,
                'name' => 'Colombia',
                'code' => 'CO',
            ]);
    }

    /**
     * Get the type operation that owns the company.
     */
    public function type_operation()
    {
        return $this->belongsTo(TypeOperation::class);
    }

    /**
     * Get the type document identification that owns the company.
     */
    public function type_document_identification()
    {
        return $this->belongsTo(TypeDocumentIdentification::class)
            ->withDefault([
                'id' => 3,
                'name' => 'Cédula de ciudadanía',
                'code' => '13',
            ]);
    }

    /**
     * Get the type environment identification that owns the company.
     */
    public function type_environment()
    {
        return $this->belongsTo(TypeEnvironment::class);
    }

    /**
     * Get the payroll_type environment identification that owns the company.
     */
    public function payroll_type_environment()
    {
        return $this->belongsTo(TypeEnvironment::class);
    }

    /**
     * Get the type environment identification that owns the company.
     */
    public function eqdocs_type_environment()
    {
        return $this->belongsTo(TypeEnvironment::class);
    }

    /**
     * Get the support document type environment that owns the company.
     */
    public function support_document_type_environment()
    {
        return $this->belongsTo(TypeEnvironment::class);
    }

    /**
     * Get the event type environment that owns the company.
     */
    public function event_type_environment()
    {
        return $this->belongsTo(TypeEnvironment::class);
    }

    /**
     * Get the type currency identification that owns the company.
     */
    public function type_currency()
    {
        return $this->belongsTo(TypeCurrency::class);
    }

    /**
     * Get the type organization identification that owns the company.
     */
    public function type_organization()
    {
        return $this->belongsTo(TypeOrganization::class)
            ->withDefault([
                'id' => 2,
                'name' => 'Persona Natural y asimiladas',
                'code' => '2',
            ]);
    }

    /**
     * Get the municipality identification that owns the company.
     */
    public function municipality()
    {
        return $this->belongsTo(Municipality::class)
            ->withDefault([
                'id' => 1006,
                'department_id' => 31,
                'name' => 'Cali',
                'code' => '76001',
            ]);
    }

    /**
     * Get the type liability identification that owns the company.
     */
    public function type_liability()
    {
        return $this->belongsTo(TypeLiability::class)
            ->withDefault([
                'id' => 117,
                'name' => 'No aplica – Otros',
                'code' => 'R-99-PN',
            ]);
    }

    public function getTypeLiabilityCodesAttribute()
    {
        $additionalIds = $this->type_liability_ids;
        if (!empty($additionalIds)) {
            $primaryId = $this->type_liability_id;
            $allIds = $primaryId
                ? array_unique(array_merge([$primaryId], $additionalIds))
                : array_unique($additionalIds);
            return TypeLiability::whereIn('id', $allIds)->pluck('code')->implode(';');
        }
        return null;
    }

    public function getTypeLiabilityNamesAttribute()
    {
        $additionalIds = $this->type_liability_ids;
        if (!empty($additionalIds)) {
            $primaryId = $this->type_liability_id;
            $allIds = $primaryId
                ? array_unique(array_merge([$primaryId], $additionalIds))
                : array_unique($additionalIds);
            return TypeLiability::whereIn('id', $allIds)->pluck('name')->implode(', ');
        }
        return null;
    }

    /**
     * Get the type regime identification that owns the company.
     */
    public function type_regime()
    {
        return $this->belongsTo(TypeRegime::class)
            ->withDefault([
                'id' => 2,
                'name' => 'No Responsable de IVA',
                'code' => '49',
            ]);
    }

    /**
     * Get the send that owns the company.
     */
    public function send()
    {
        return $this->hasMany(Send::class)
            ->where('year', now()->format('Y'));
    }

     /**
     * Get the type operation that owns the company.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the type plan that owns the company.
     */
    public function type_plan()
    {
        return $this->belongsTo(TypePlan::class);
    }

    /**
     * Get the type plan 2 that owns the company.
     */
    public function type_plan2()
    {
        return $this->belongsTo(TypePlan::class);
    }

    /**
     * Get the type plan 3 that owns the company.
     */
    public function type_plan3()
    {
        return $this->belongsTo(TypePlan::class);
    }

    /**
     * Get the type plan 4 that owns the company.
     */
    public function type_plan4()
    {
        return $this->belongsTo(TypePlan::class);
    }

    /**
     * search by identification number
     */
    public function getRouteKeyName()
    {
        return 'identification_number';
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'company_user');
    }
}
