<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Comprobante;
use Illuminate\Support\Facades\Notification;
use App\Jobs\ProcesarXML;
/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Documentación Registro de Comprobantes",
 *      description="Documentación de la API para el manejo de comprobantes.",
 *      @OA\Contact(
 *          email="dm7659746@gmail.com",
 *          name="Equipo de Desarrollo"
 *      ),
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
            $files = $request->allFiles();
            foreach ($files as $fieldName => $fileArray) {
                $xmlFile = $request->file($fieldName);
                $xmlContent = file_get_contents($xmlFile->path());  
                ProcesarXML::dispatch($xmlContent)->onQueue('addXML');
            }
            
            return response()->json(
                [
                    'msg' => 'Se guardaron correctamente los archivos',
                ]
            );
            // if ($request->hasFile('file_xml')) {
            //     $xmlFile = $request->file('file_xml');
            // } else {
            //     return response()->json(['msg' => 'No se proporcionó un archivo XML'], 400);
            // }
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
    /**
     * @OA\Delete(
     *     path="/api/comprobantes/{id_comprobante}",
     *     summary="Eliminar un comprobante por ID",
     *     tags={"Comprobantes"},
     *     @OA\Parameter(
     *         name="id_comprobante",
     *         in="path",
     *         required=true,
     *         description="ID del comprobante a eliminar",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comprobante eliminado exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="msg",
     *                 type="string",
     *                 example="Se eliminó correctamente el comprobante con ID {id_comprobante}"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Comprobante no encontrado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="No se encontró ningún comprobante con ese ID"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="msg",
     *                 type="string",
     *                 example="Ocurrió un error en deleteComprobanteById"
     *             )
     *         )
     *     ),
     * )
     */
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
    /**
     * @OA\Get(
     *     path="/api/comprobantes/all",
     *     summary="Obtener el monto total de todos los comprobantes",
     *     tags={"Comprobantes"},
     *     @OA\Response(
     *         response=200,
     *         description="Monto total obtenido exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="msg",
     *                 type="string",
     *                 example="Success"
     *             ),
     *             @OA\Property(
     *                 property="montoTotal",
     *                 type="number",
     *                 format="float",
     *                 example=12345.67
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="msg",
     *                 type="string",
     *                 example="Ocurrió un error en comprobanteAll"
     *             )
     *         )
     *     ),
     * )
     */
    public function comprobanteAll(){
        try {
            $montoTotal = DB::table('comprobantes')
            ->where('user_id', auth()->user()->id)
            ->sum('importeTotal');
            return response()->json(['msg' => 'Success','montoTotal' => $montoTotal]);
        } catch (\Throwable $th) {
            return response()->json(['msg' => 'Ocurrió un error en comprobanteAll'], 500);
        }
    }
    public function itemsAll(){
        try {
            $id_comprobantes = DB::table('comprobantes')
            ->where('user_id', auth()->user()->id)
            ->pluck('id')
            ->toArray();
            
            $montoTotalItem = DB::table('comprobante_items')
            ->whereIn('comprobante_id', $id_comprobantes)
            ->sum('productoPrecio');
            return response()->json(['msg' => 'Success','montoTotalItems' => $montoTotalItem]);
        } catch (\Throwable $th) {
            return response()->json(['msg' => 'Ocurrió un error en itemsAll'], 500);
        }
    }
}
