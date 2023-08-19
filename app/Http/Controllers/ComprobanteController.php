<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Comprobante;
use App\Models\ComprobanteItem;

class ComprobanteController extends Controller
{
    public function registrarComprobante(Request $request){
        try {
            if ($request->hasFile('file_xml')) {
                $xmlFile = $request->file('file_xml');
                $xmlContent = file_get_contents($xmlFile->path());
                
                $xml = simplexml_load_string($xmlContent);
    
                $issueDate = (string) $xml->xpath('//cbc:IssueDate')[0];
                $issueTime = (string) $xml->xpath('//cbc:IssueTime')[0]; 
                $nameEmisor = (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName')[0];
                $rucEmisor = (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID')[0]; 
                $nameReceptor = (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName')[0];
                $rucReceptor = (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID')[0]; 
                $importeTotal = (string) $xml->xpath('//cac:LegalMonetaryTotal/cbc:PayableAmount')[0];
                $otrosPagos = (string) $xml->xpath('//cac:LegalMonetaryTotal/cbc:ChargeTotalAmount')[0];
                $ventaTotalImpuesto = (string) $xml->xpath('//cac:LegalMonetaryTotal/cbc:TaxInclusiveAmount')[0];
                $ventaTotal = (string) $xml->xpath('//cac:LegalMonetaryTotal/cbc:LineExtensionAmount')[0];
                $items = [];

                $comprobante = new Comprobante();
                $comprobante->user_id = auth()->user()->id;
                $comprobante->fechaEmision = $issueDate . " " . $issueTime; 
                $comprobante->nameEmisor = $nameEmisor;  
                $comprobante->rucEmisor = $rucEmisor;  
                $comprobante->nameReceptor = $nameReceptor;  
                $comprobante->rucReceptor = $rucReceptor;  
                $comprobante->ventaTotal = $ventaTotal;  
                $comprobante->ventaTotalImpuesto = $ventaTotalImpuesto;  
                $comprobante->otrosPagos = $otrosPagos;  
                $comprobante->importeTotal = $importeTotal;
                $comprobante->save();

                foreach ($xml->xpath('//cac:InvoiceLine') as $invoiceLine) {
                    $productoName = (string) $invoiceLine->xpath('cac:Item/cbc:Description')[0]; 
                    $productoPrecio = (string) $invoiceLine->xpath('cac:Price/cbc:PriceAmount')[0]; 

                    $items[] = [
                        'productoName' => $productoName,
                        'productoPrecio' => $productoPrecio,
                    ];
                    $comprobanteItem = new ComprobanteItem();
                    $comprobanteItem->comprobante_id = $comprobante->id;
                    $comprobanteItem->productoName = $productoName;
                    $comprobanteItem->productoPrecio = $productoPrecio;
                    $comprobanteItem->save();
                }
            
                return response()->json(
                    [
                        'msg' => 'Se guardo correctamente',
                    ]
                );
            } else {
                return response()->json(['msg' => 'No se proporcionó un archivo XML'], 400);
            }
        } catch (\Throwable $th) {
            // Puedes agregar manejo de msges aquí
            return response()->json(['msg' => 'Ocurrió un msg al procesar el archivo XML'], 500);
        }
    }
    public function comprobanteById($id_comprobante ,Request $request){
        try {
            $comprobante = DB::table('comprobantes')->find($id_comprobante);
            if(!$comprobante){
                return response()->json(['msg' => 'No se encontro ningun comprobante con ese id'], 400);
            }
            if ($comprobante->user_id !== auth()->user()->id) {
                return response()->json(['msg' => 'No tienes permiso para acceder a este comprobante.'], 403);
            }
            return $comprobante;
        } catch (\Throwable $th) {
            // Puedes agregar manejo de msges aquí
            return response()->json(['msg' => 'Ocurrió un msg en comprobanteById'], 500);
        }
    } 
}
