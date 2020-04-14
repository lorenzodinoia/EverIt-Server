<?php

namespace App\Http\Controllers;

use App\Restaurateur;
use Illuminate\Http\Request;
use App\HttpResponseCode;
use App\ProductCategory;
use Illuminate\Support\Facades\Auth;

class ProductCategoryController extends Controller
{
    /**
     * Create a new product category for a restaurateur
     * Restaurateur must be logged in
     */
    public function create(Request $request) {
        $restaurateur = Auth::guard('restaurateur')->user();
        if(isset($restaurateur)) {
            if(isset($request->name)) {
                $category = new ProductCategory;
                $category->name = $request->name;
                $category->restaurateur()->associate($restaurateur);
                $category->save();
                $message = $category;
                $code = HttpResponseCode::CREATED;
            }
            else {
                $message = ["message" => "Category name not provided"];
                $code = HttpResponseCode::BAD_REQUEST;
            }
        }
        else {
            $message = ["message" => "User not authorized"];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    /**
     * Get all product categories
     */
    public function readAll($restaurateurId) {
        $restaurateur = Restaurateur::find($restaurateurId);
        if(isset($restaurateur)) {
            $categories = $restaurateur->productCategories()->get();
            $message = $categories;
            $code = HttpResponseCode::OK;
        }
        else {
            $message = ['message' => "Restaurateur not found"];
            $code = HttpResponseCode::NOT_FOUND;
        }

        return response()->json($message, $code);
    }

    /**
     *
     */
    public function update(Request $request, $id) {
        $restaurateur = Auth::guard('restaurateur')->user();
        //TODO aggiungere controllo se categoria appartiene a ristoratore
        if(isset($restaurateur)){
            $category = ProductCategory::find($id);
            if(isset($category)){
                $category->name = $request->name;
                $category->save();
                $message = ProductCategory::find($id);
                $code = HttpResponseCode::OK;
            }
            else{
                $message = "Category not found";
                $code = HttpResponseCode::NOT_FOUND;
            }
        }
        else{
            $message = "Unauthorized";
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    /**
     *
     */
    public function delete(Request $request, $id) {
        //TODO controllare appartenenza categoria prodotto a ristoratore
        $restaurateur = Auth::guard('restaurateur')->user();
        if(isset($restaurateur)){

        }
        else{
            $message = "Unauthorized";
            $code = HttpResponseCode::UNAUTHORIZED;
        }
    }
}
