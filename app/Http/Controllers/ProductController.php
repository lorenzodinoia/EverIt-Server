<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;
use App\Restaurateur;
use App\HttpResponseCode;
use App\ProductCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    /**
     * Create new product inside a category
     * The restaurateur must be logged in
     */
    public function create(Request $request, $categoryId) {
        $restaurateur = Auth::guard('restaurateur')->user();
        if(isset($restaurateur)) {
            $category = $restaurateur->productCategories()->where("id", $categoryId)->first()->get();
            if(isset($category[0])) {
                $validator = Product::checkCreateRequest($request);
                if(!$validator->fails()) {
                    $product = new Product;
                    $product->name = $request->name;
                    $product->price = $request->price;
                    $product->details = $request->details;
                    $product->productCategory()->associate($categoryId);
                    $product->save();
                    $message = $product;
                    $code = HttpResponseCode::CREATED;
                }
                else{
                    $message = $validator->errors();
                    $code = HttpResponseCode::BAD_REQUEST;
                }
            }
            else {
                $message = ["message" => "Category not found"];
                $code = HttpResponseCode::NOT_FOUND;
            }
        }
        else {
            $message = ["message" => "User not authorized"];
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }

    /**
     * Get all product of a restaurateur's category
     */
    public function readAllByCategory($categoryId) {
        $category = ProductCategory::find($categoryId);
        if(isset($category)) {
            $products = $category->products()->get();
            $message = $products;
            $code = HttpResponseCode::OK;
        }
        else {
            $message = ["message" => "Category not found"];
            $code = HttpResponseCode::NOT_FOUND;
        }

        return response()->json($message, $code);
    }

    /**
     * Get all products of a restaurateur
     */
    public function readAllByRestaurateur($restaurateurId) {
        $restaurateur = Restaurateur::find($restaurateurId);
        if(isset($restaurateur)) {
            $products = $restaurateur->products()->get();
            $message = $products;
            $code = HttpResponseCode::OK;
        }
        else {
            $message = ["message" => "Restaurateur not found"];
            $code = HttpResponseCode::NOT_FOUND;
        }

        return response()->json($message, $code);
    }

    /**
     * Update data of a product
     * The restaurateur must be logged in
     */
    //TODO effettuare appartenenza del prodotto al ristoratore?
    public function update(Request $request, $idCategory, $id) {
        $restaurateur = Auth::guard('restaurateur')->user();
        if(isset($restaurateur)){
            $product = Product::find($id);
            if(isset($product)) {
                $category = $restaurateur->productCategories()->where("id", $idCategory)->first()->get();
                if (isset($category[0])) {
                    $validator = Product::checkCreateRequest($request);
                    if ($validator->fails()) {
                        $product->name = $request->name;
                        $product->price = $request->price;
                        $product->details = $request->details;
                        $product->productCategory()->associate($category);
                        $product->save();
                        $message = Product::find($product->id);
                        $code = HttpResponseCode::OK;
                    }
                    else{
                        $message = $validator->errors();
                        $code = HttpResponseCode::BAD_REQUEST;
                    }
                }
                else{
                    $message = "Product category doesn't exist";
                    $code = HttpResponseCode::NOT_FOUND;
                }
            }
            else{
                $message = "Product doesn't exist";
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
     * Delete a product
     * The restaurateur must be logged in
     */
    //TODO effettuare appartenenza del prodotto al ristoratore?
    public function delete($id) {

        $product = Product::find($id);
        if(isset($product)){
            $deleted = $product->delete();
            if($deleted){
                $message = "Product deleted";
                $code = HttpResponseCode::OK;
            }
            else{
                $message = "Can't delete product";
                $code = HttpResponseCode::SERVER_ERROR;
            }
        }
        else{
            //$message = "I"
        }
    }
}
