<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Comprobante;
use App\Models\ComprobanteItem;
use App\Notifications\RegistrarComprobante;
use Illuminate\Support\Facades\Notification;
/**
 * @OA\Info(
 *      version="1.0.0", 
 *      title="L5 OpenApi documentación de Enterprises",
 *      description="L5 Swagger OpenApi description para enterprises.",
 * )
 */
class ComprobanteController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/registrar-comprobante",
     *     summary="Registrar un comprobante",
     *     description="Registrar un comprobante utilizando un archivo XML",
     *     tags={"Comprobantes"},
     *     @OA\RequestBody(
     *         description="Archivo XML del comprobante",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="file_xml", type="file")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comprobante registrado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Se guardó correctamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Archivo XML no proporcionado",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="No se proporcionó un archivo XML")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al procesar el archivo XML",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Ocurrió un error al procesar el archivo XML")
     *         )
     *     )
     * )
     */
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
                
                auth()->user()->notify(new RegistrarComprobante($comprobante));
            
                return response()->json(
                    [
                        'msg' => 'Se guardo correctamente',
                    ]
                );
            } else {
                return response()->json(['msg' => 'No se proporcionó un archivo XML'], 400);
            }
        } catch (\Throwable $th) {
            return response()->json(['msg' => 'Ocurrió un error al procesar el archivo XML'], 500);
        }
    }
    /**
     * @OA\Get(
     *     path="/api/comprobantes/{id_comprobante}",
     *     summary="Obtener un comprobante por su ID",
     *     description="Obtener los detalles de un comprobante por su ID",
     *     tags={"Comprobantes"},
     *     @OA\Parameter(
     *         name="id_comprobante",
     *         in="path",
     *         description="ID del comprobante",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comprobante obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="fechaEmision", type="string"),
     *             @OA\Property(property="nameEmisor", type="string"),
     *             @OA\Property(property="rucEmisor", type="string"),
     *             @OA\Property(property="nameReceptor", type="string"),
     *             @OA\Property(property="rucReceptor", type="string"),
     *             @OA\Property(property="ventaTotal", type="string"),
     *             @OA\Property(property="ventaTotalImpuesto", type="string"),
     *             @OA\Property(property="otrosPagos", type="string"),
     *             @OA\Property(property="importeTotal", type="string"),
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="comprobante_id", type="integer"),
     *                 @OA\Property(property="productoName", type="string"),
     *                 @OA\Property(property="productoPrecio", type="string")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Comprobante no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="No se encontró ningún comprobante con ese id")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No tienes permiso para acceder a este comprobante",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="No tienes permiso para acceder a este comprobante")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al obtener el comprobante",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Ocurrió un error al obtener el comprobante")
     *         )
     *     )
     * )
     */
    public function comprobanteById($id_comprobante ,Request $request){
        try {
            $comprobante = DB::table('comprobantes')->find($id_comprobante);
            $comprobante->items = DB::table('comprobante_items')->where('comprobante_id',$comprobante->id)->get();
            if(!$comprobante){
                return response()->json(['msg' => 'No se encontro ningun comprobante con ese id'], 400);
            }
            if ($comprobante->user_id !== auth()->user()->id) {
                return response()->json(['msg' => 'No tienes permiso para acceder a este comprobante.'], 403);
            }
            return response()->json($comprobante);
        } catch (\Throwable $th) {
            return response()->json(['msg' => 'Ocurrió un error en comprobanteById'], 500);
        }
    } 
    public function deleteComprobanteById($id_comprobante ,Request $request){
        try {
            $comprobante = DB::table('comprobantes')->find($id_comprobante);
            if (!$comprobante) {
                return response()->json(['error' => 'No se encontró ningún comprobante con ese ID'], 404);
            }
            DB::table('comprobantes')->where('id', $id_comprobante)->delete();
            return response()->json([ 'msg' => 'Se elimino correctamente el comprobante con ID ' . $id_comprobante]); 
        } catch (\Throwable $th) {
            return response()->json(['msg' => 'Ocurrió un error en deleteComprobanteById'], 500);
        }
    }
    public function comprobanteAll(){
        try {
            $montoTotal = DB::table('comprobantes')->sum('importeTotal');
            return response()->json(['msg' => 'Success','montoTotal' => $montoTotal]);
        } catch (\Throwable $th) {
            return response()->json(['msg' => 'Ocurrió un error en comprobanteAll'], 500);
        }
    }
}
