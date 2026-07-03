<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Certificate;
use App\Services\StorageService;

class AddExpirationDateFieldToCertificates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dateTime('expiration_date')->after('password')->nullable();
        });

        try {
            $certs = Certificate::where('id', '>', 0)->get();
            if(count($certs) > 0){
                foreach($certs as $cert){
                    // Certificates are always stored locally
                    $pfxContent = StorageService::getLocal("certificates/".$cert->name);
                    if (!openssl_pkcs12_read($pfxContent, $x509certdata, $cert->password)) {
                        throw new Exception('The certificate could not be read.');
                    }
                    else{
                        $CertPriv   = array();
                        $CertPriv   = openssl_x509_parse(openssl_x509_read($x509certdata['cert']));
                        $PrivateKey = $x509certdata['pkey'];
                        $pub_key = openssl_pkey_get_public($x509certdata['cert']);
                        $keyData = openssl_pkey_get_details($pub_key);
                        $PublicKey  = $keyData['key'];
                        $cert->expiration_date = date('Y/m/d H:i:s', $CertPriv['validTo_time_t']);
                    }
                    $cert->update();
                }
            }
        } catch (Exception $e) {
            if (false == ($error = openssl_error_string())) {
                return response([
                    'message' => $e->getMessage(),
                    'errors' => [
                        'certificate' => 'The base64 encoding is not valid.',
                    ],
                ], 422);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn('expiration_date');
        });
    }

}
