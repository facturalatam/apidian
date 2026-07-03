<?php

namespace App\Http\Controllers\Api;

use App\Tax;
use App\Country;
use App\CreditNoteDiscrepancyResponse;
use App\DebitNoteDiscrepancyResponse;
use App\Discount;
use App\Language;
use App\Department;
use App\Event;
use App\HealthTypeDocumentIdentification;
use App\TypeRegime;
use App\PaymentForm;
use App\UnitMeasure;
use Illuminate\Support\Arr;
use App\Municipality;
use App\TypeCurrency;
use App\TypeDocument;
use Illuminate\Http\Request;
use App\PaymentMethod;
use App\TypeLiability;
use App\TypeOperation;
use App\ReferencePrice;
use App\TypeEnvironment;
use App\TypeOrganization;
use App\Http\Controllers\Controller;
use App\Incoterm;
use App\TypeItemIdentification;
use App\TypeDocumentIdentification;
use App\TypeDiscount;
use App\TypeGenerationTransmition;
use App\TypePlan;
use App\TypeRejection;

class ListingController extends Controller
{
    /**
     * Models
     * @var array
     */
    private $models = [
        'Country' => Country::class,
        'CreditNoteDiscrepancyResponse' => CreditNoteDiscrepancyResponse::class,
        'DebitNoteDiscrepancyResponse' => DebitNoteDiscrepancyResponse::class,
        'Department' => Department::class,
        'Discount' => Discount::class,
        'Event' => Event::class,
        'HealthTypeDocumentIdentification' => HealthTypeDocumentIdentification::class,
        'Incoterm' => Incoterm::class,
        'Language' => Language::class,
        'Municipality' => Municipality::class,
        'PaymentForm' => PaymentForm::class,
        'PaymentMethod' => PaymentMethod::class,
        'ReferencePrice' => ReferencePrice::class,
        'Tax' => Tax::class,
        'TypeCurrency' => TypeCurrency::class,
        'TypeDiscount' => TypeDiscount::class,
        'TypeDocumentIdentification' => TypeDocumentIdentification::class,
        'TypeDocument' => TypeDocument::class,
        'TypeEnvironment' => TypeEnvironment::class,
        'TypeGenerationTransmition' => TypeGenerationTransmition::class,
        'TypeItemIdentification' => TypeItemIdentification::class,
        'TypeLiability' => TypeLiability::class,
        'TypeOperation' => TypeOperation::class,
        'TypeOrganization' => TypeOrganization::class,
        'TypeRegime' => TypeRegime::class,
        'TypeRejection' => TypeRejection::class,
        'UnitMeasure' => UnitMeasure::class,
    ];

    /**
     * Get all models
     * @param  Request $request
     * @return \Illuminate\Support\Collection
     */
    public function all(Request $request)
    {
        $request->validate([
            'models' => 'nullable|string'
        ]);

        $modelNames = $request->has('models') 
            ? explode(',', str_replace(' ', '', $request->models)) 
            : array_keys($this->models);

        $modelNames = array_intersect($modelNames, array_keys($this->models));

        $allListing = collect();

        foreach ($modelNames as $modelName) {
            $class = $this->models[$modelName];
            $allListing->put($modelName, $class::all());
        }

        return $allListing;
    }
}
