<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CompaniesCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->transform(function($row, $key) {
            
            return [
                'key' => $key + 1,
                'id' => $row->id,
                'identification_number' => $row->company['identification_number'],
                'name' => $row->name,
                'email' => $row->email,
                'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                'token' => $row->api_token
            ];
        });
    }
    
}
