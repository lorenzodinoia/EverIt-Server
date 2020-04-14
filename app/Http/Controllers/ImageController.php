<?php

namespace App\Http\Controllers;

use App\HttpResponseCode;
use App\Restaurateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class ImageController extends Controller
{
    /**
     * Salva l'immagine inviata nel sistema
     * @param request: richiesta contenente l'immagine da caricare
     * @return: Restituisce il nome del file salvat ocon l'estensione
     */
    public function saveImage(Request $request){

        $extension = $request->image->getClientOriginalExtension();
        $fileName = time().'_'.rand(1, 100000).'.'.$extension;
        $path = $request->file('image')->move(public_path("/"), $fileName);

        return $fileName;
    }

    /**
     * Elimina l'immagine indicata
     * @param percorso: percorso dell'immagine da eliminare
     * @return: Restituisce un messaggio di avvenuta cancellazione in caso di esito positivo
     * @return: Restituisce un messaggio di errore in caso di esito negativo
     */
    public function deleteImage(String $path){

        if(File::exists($path)){
            File::delete($path);
            $message = ['message', 'Image deleted'];
            $code = 200;
        }
        else{
            $message = ['messaggio', "Can't find image"];
            $code = 400;
        }

        return response()->json($message, $code);
    }

    public function saveImageRestaurateur(Request $request){
        $restaurateur = Auth::guard('restaurateur')->user();
        if(isset($restaurateur)) {
            $fileName = $this->saveImage($request);
            $restaurateur->image_path = $fileName;
            $restaurateur->save();
            $message = "Saved";
            $code = HttpResponseCode::OK;
        }
        else{
            $message = "Unauthorized";
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    public function  deleteImageRestaurateur(Request $request){
        $restaurateur = Auth::guard('restaurateur')->user();
        if(isset($restaurateur)){
            $this->deleteImage($restaurateur->image_path);
            $restaurateur->image_path = null;
            $restaurateur->save();
        }
        else{
            $message = "Unauthorized";
            $code = HttpResponseCode::UNAUTHORIZED;
            return response()->json($message, $code);
        }
    }
}
