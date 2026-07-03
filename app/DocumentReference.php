<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocumentReference extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'prefix', 'number', 'uuid', 'document_type_code',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'scheme_name',
    ];

    /**
     * Set the prefix document reference.
     *
     * @param string $value
     */
    public function setPrefixAttribute($data)
    {
        return $this->attributes['prefix'] = $data;
    }

    /**
     * Get the prefix document reference.
     *
     * @return string
     */
    public function getPrefixAttribute()
    {
        return $this->attributes['prefix'] ?? [];
    }

    /**
     * Set the number document reference.
     *
     * @param string $value
     */
    public function setNumberAttribute($data)
    {
        return $this->attributes['number'] = $data;
    }

    /**
     * Get the numer document reference.
     *
     * @return string
     */
    public function getNumberAttribute()
    {
        return $this->attributes['number'] ?? [];
    }

    /**
     * Set the document type code document reference.
     *
     * @param string $value
     */
    public function setDocumentTypeCodeAttribute($data)
    {
        return $this->attributes['document_type_code'] = $data;
    }

    /**
     * Get the document type code document reference.
     *
     * @return string
     */
    public function getDocumentTypeCodeAttribute()
    {
        return $this->attributes['document_type_code'] ?? [];
    }

    /**
     * Set the scheme name document reference.
     *
     * @return string
     */
    public function setSchemeNameAttribute($data)
    {
        return $this->attributes['scheme_name'] = $data;
    }

    /**
     * Get the scheme name document reference.
     *
     * @return string
     */
    public function getSchemeNameAttribute()
    {
        return $this->attributes['scheme_name'] ?? 'CUFE-SHA384';
    }

    /**
     * Set the uuid allowance document reference.
     *
     * @param string $value
     */
    public function setUuidAttribute($data)
    {
        return $this->attributes['uuid'] = $data;
    }

    /**
     * Get the uuid document reference.
     *
     * @return string
     */
    public function getUuidAttribute()
    {
        return $this->attributes['uuid'] ?? [];
    }
}
