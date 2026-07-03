<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Tax;






class TaxController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index()
    {   
       // $list =  new CompaniesCollection(User::all());
        //return json_encode($list);
        return view('taxes.index') ; 
    }

    public function records(Request $request)
    {
        $records = Tax::all();
        return $records;
        //return new CompaniesCollection($records);
    }
   




 

    


}
