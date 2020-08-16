<?php

namespace App\Http\Controllers;

use App\Restaurateur;
use Illuminate\Http\Request;
use App\HttpResponseCode;
use App\ProductCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

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
        if(isset($restaurateur)){
            $category = $restaurateur->productCategories()->where("id", $id)->get();
            if(isset($category[0])){
                $newCategory = $category[0];
                $newCategory->name = $request->name;
                $newCategory->save();
                $message = ProductCategory::find($id);
                $code = HttpResponseCode::OK;
            }
            else{
                $message = ["message" => "Category not found"];
                $code = HttpResponseCode::BAD_REQUEST;
            }
        }
        else{
            $message = ["message" => "Unauthorized"];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    /**
     *
     */
    public function delete(Request $request, $id) {
        $restaurateur = Auth::guard('restaurateur')->user();
        if(isset($restaurateur)){
            $category = $restaurateur->productCategories()->where("id", $id)->get();
            //return response()->json($category, 200);
            if(isset($category[0])){
                $categoryTarget = $category[0];
                $deleted = $categoryTarget->delete();
                if($deleted) {
                    $message = ["message" => "Deleted"];
                    $code = HttpResponseCode::OK;
                }
                else{
                    $message = ["message" => "Can't delete product category"];
                    $code = HttpResponseCode::SERVER_ERROR;
                }
            }
            else{
                $message = ["message" => "Category not found"];
                $code = HttpResponseCode::BAD_REQUEST;
            }
        }
        else{
            $message = ["message" => "Unauthorized"];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }
}
