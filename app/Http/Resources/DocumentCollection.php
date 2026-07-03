<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DocumentCollection extends ResourceCollection
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
                'number' => $row->number,
                'client' => $row->client->name,
                'currency' => $row->currency->name,
                'date' => $row->date_issue,
                'sale' => $row->sale,
                'total_discount' => $row->total_discount,
                'total_tax' => $row->total_tax,
                'subtotal' => $row->subtotal,
                'total' => $row->total,
                'xml' => $row->xml,
                'pdf' => $row->pdf,
                'url_xml' => '',
                'url_pdf' => '',
            ];
        });
    }

}
