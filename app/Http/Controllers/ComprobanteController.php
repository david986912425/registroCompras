<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

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

                foreach ($xml->xpath('//cac:InvoiceLine') as $invoiceLine) {
                    $productoName = (string) $invoiceLine->xpath('cac:Item/cbc:Description')[0]; 
                    $productoPrecio = (string) $invoiceLine->xpath('cac:Price/cbc:PriceAmount')[0]; 

                    $items[] = [
                        'productoName' => $productoName,
                        'productoPrecio' => $productoPrecio,
                    ];
                }



                return response()->json(
                    [
                        'FechaEmision' => $issueDate . " " . $issueTime,
                        'nameEmisor' => $nameEmisor,
                        'rucEmisor' => $rucEmisor,
                        'nameReceptor' => $nameReceptor,
                        'rucReceptor' => $rucReceptor,
                        'ventaTotal' => $ventaTotal,
                        'ventaTotalImpuesto' => $ventaTotalImpuesto,
                        'otrosPagos' => $otrosPagos,
                        'importeTotal' => $importeTotal,
                        'items' => $items,
                    ]
                );
            } else {
                return response()->json(['error' => 'No se proporcionó un archivo XML'], 400);
            }
        } catch (\Throwable $th) {
            // Puedes agregar manejo de errores aquí
            return response()->json(['error' => 'Ocurrió un error al procesar el archivo XML'], 500);
        }
    }
}
