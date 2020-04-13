<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;
use App\Restaurateur;
use App\HttpResponseCode;
use App\ProductCategory;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Create new product inside a category
     * The restaurateur must be logged in
     */
    public function create(Request $request, $categoryId) {
        $restaurateur = Auth::guard('restaurateur')->user();
        if(isset($restaurateur)) {
            $category = ProductCategory::find($categoryId);
            if(isset($category)) {
                $product = new Product;
                $product->name = $request->name;
                $product->price = $request->price;
                $product->details = $request->details;
                $product->productCategory()->associate($category);
                $product->save();
                $message = $product;
                $code = HttpResponseCode::CREATED;
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
    public function update(Request $request, $id) {

    }

    /**
     * Delete a product
     * The restaurateur must be logged in
     */
    public function delete($id) {

    }
}
