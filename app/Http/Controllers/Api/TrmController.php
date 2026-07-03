<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Carbon\Carbon;

class TrmController extends Controller
{
    private function getLastDianId()
    {
        try {
            $client = new Client([
                'verify' => false
            ]);
            
            $response = $client->get('https://www.dian.gov.co/dian/cifras/scripts/HEconomics', [
                'query' => [
                    'type' => 'All',
                    '_' => time() . rand(100,999)
                ]
            ]);
            
            $data = json_decode($response->getBody()->getContents());
            
            if (isset($data->results[0]->id)) {
                return $data->results[0]->id;
            }
            
            throw new \Exception('No se pudo obtener el ID de la DIAN');
            
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getAllCurrencies()
    {
        try {
            $dianId = $this->getLastDianId();
            $client = new Client([
                'verify' => false
            ]);
                        
            $response = $client->get('https://www.dian.gov.co/dian/cifras/Paginas/DetalleTRM.aspx', [
                'query' => [
                    'DianId' => $dianId,
                    'IsDlg' => '1'
                ]
            ]);

            $html = $response->getBody()->getContents();

            // Extraer otras monedas primero
            preg_match_all('/<td class="col-sm-6">\s*([^<]+)\s*<\/td>.*?<span[^>]*currencyValue">\s*([\d,\.]+)\s*<\/span>/s', $html, $matches, PREG_SET_ORDER);

            $currencies = [];

            // Agregar otras monedas
            foreach ($matches as $match) {
                $moneda = trim($match[1]); // Captura el nombre de la moneda
                $valor = str_replace(',', '.', $match[2]);
                
                // Verificar si es una moneda válida y no es el dólar americano principal
                if (!empty($moneda) && is_numeric($valor) && strpos($moneda, 'Dólar Americano') === false) {
                    $currencies[] = [
                        'moneda' => $moneda,
                        'valor' => floatval($valor),
                        'fecha' => Carbon::now()->format('Y-m-d')
                    ];
                }
            }

            // Extraer y agregar el dólar principal al inicio del array
            preg_match('/<span class="col-sm-12 dollarValue">\s*([\d,\.]+)\s*<\/span>/', $html, $dollarMatch);
            if (isset($dollarMatch[1])) {
                $dolarValue = str_replace(',', '', $dollarMatch[1]);
                $dolarValue = floatval($dolarValue) / 100; // Dividir por 100 para obtener el formato correcto
                array_unshift($currencies, [
                    'moneda' => 'Dólar Americano',
                    'valor' => $dolarValue,
                    'fecha' => Carbon::now()->format('Y-m-d')
                ]);
            }

            return response()->json([
                'success' => true,
                'currencies' => $currencies
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las tasas de cambio: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getHtmlTrm()
    {
        try {
            $dianId = $this->getLastDianId();
            
            return response()->json([
                'success' => true,
                'data' => $dianId
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos económicos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCurrentTRM()
    {
        try {
            $response = $this->getAllCurrencies();
            $data = json_decode($response->getContent(), true);
            
            if ($data['success'] && !empty($data['currencies'])) {
                // Buscar el dólar americano (que siempre es el primero en el array)
                foreach($data['currencies'] as $currency) {
                    if ($currency['moneda'] === 'Dólar Americano') {
                        return $currency['valor'];
                    }
                }
            }
            throw new \Exception('No se pudo obtener el valor del dólar');
        }
        catch(\Exception $e) {
            return 4000.00; // Valor por defecto
        }
    }
}
