<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use \App\User;
use \App\Customer;
use \App\Company;
use App\Traits\DocumentTrait;
use Validator;

class AppServiceProvider extends ServiceProvider
{
    use DocumentTrait;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        DB::setDefaultConnection('mysql');
        Schema::defaultStringLength(191);

        if (config('system_configuration.force_https')) URL::forceScheme('https');

        Validator::extend('passwordcustomer_verify', function($attribute, $value, $parameters, $validator) {
            $customer = Customer::where('identification_number', '=', $parameters[0])->get();
            if(count($customer) > 0)
                if (password_verify($value, $customer[0]->password))
                    return true;
                else
                    return false;
        });

        Validator::extend('passwordowner_verify', function($attribute, $value, $parameters, $validator) {
            if (file_exists(storage_path("filepassowner.api"))){
                $fp = fopen(storage_path("filepassowner.api"), "r");
                $password = fgets($fp);
                fclose($fp);
                $arraypasswordrecibido = explode("-", $parameters[0]);
                for($i=0; $i <= count($arraypasswordrecibido) - 2; $i++){
                    $arraypasswordrecibido[$i] = chr($arraypasswordrecibido[$i]);
                }
                $passwordrecibido = strrev(implode("", $arraypasswordrecibido));
                if($password == $passwordrecibido)
                    return true;
                else
                    return false;
            }
            else
                return false;
        });

        Validator::extend('passwordseller_verify', function($attribute, $value, $parameters, $validator) {
            $seller = Company::where('identification_number', '=', $parameters[0])->get();
            if(count($seller) > 0)
                if (password_verify($value, $seller[0]->password))
                    return true;
                else
                    return false;
        });

        Validator::extend('igual_a', function($attribute, $value, $parameters, $validator) {
            if($value == $parameters[0])
                return true;
            else
                return false;
        });

        Validator::extend('dian_dv', function($attribute, $value, $parameters, $validator) {
            if($this->validarDigVerifDIAN($parameters[0]) == $value)
                return true;
            else
                return false;
        });
    }
}
