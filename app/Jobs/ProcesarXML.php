<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Notifications\RegistrarComprobante;
use App\Models\ComprobanteItem;
use App\Models\Comprobante;

class ProcesarXML implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $xmlContent;
    
    public function __construct($xml)
    {
        
        $this->xmlContent = $xml;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // $xmlContent = $this->xml;
      
        $xml = simplexml_load_string($this->xmlContent);
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
        
        
        auth()->user()->notify(new RegistrarComprobante($comprobante,$items));
            
    }
}
