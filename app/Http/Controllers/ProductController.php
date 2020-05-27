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
            $category = $restaurateur->productCategories()->where("id", $categoryId)->get();
            if(isset($category[0])) {
                $validator = Product::checkCreateRequest($request);
                if(!$validator->fails()) {
                    $product = new Product;
                    $product->name = $request->name;
                    $product->price = $request->price;
                    if(isset($request->details)) {
                        $product->details = $request->details;
                    }
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
    public function update(Request $request, $id) {
        $restaurateur = Auth::guard('restaurateur')->user();
        if(isset($restaurateur)){
            $product = $restaurateur->products()->where("products.id", $id)->get();
            if(isset($product[0])) {
                $category = $restaurateur->productCategories()->where("product_categories.id", $request->product_category_id)->get();
                if (isset($category[0])) {
                    $validator = Product::checkUpdateRequest($request);
                    if (!$validator->fails()) {
                        $product[0]->name = $request->name;
                        $product[0]->price = $request->price;
                        $product[0]->details = $request->details;
                        $product[0]->productCategory()->associate($request->product_category_id);
                        $product[0]->save();
                        $message = Product::find($id);
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
    public function delete($id) {

        $restaurateur = Auth::guard('restaurateur')->user();
        if(isset($restaurateur)) {
            $product = $restaurateur->products()->where("products.id", $id)->get();
            if (isset($product[0])) {
                $deleted = $product[0]->delete();
                if ($deleted) {
                    $message = ["message" => "Product deleted"];
                    $code = HttpResponseCode::OK;
                } else {
                    $message = "Can't delete product";
                    $code = HttpResponseCode::SERVER_ERROR;
                }
            } else {
                $message = "Product not found";
                $code = HttpResponseCode::BAD_REQUEST;
            }
        }
        else{
            $message = "Unauthorized";
            $code = HttpResponseCode::UNAUTHORIZED;
        }

        return response()->json($message, $code);
    }
}
