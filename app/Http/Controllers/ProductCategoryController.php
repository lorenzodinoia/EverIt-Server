<?php

namespace App\Http\Controllers;

use App\HttpResponseCode;
use App\ProductCategory;

class ProductCategoryController extends Controller
{
    /**
     * Get details for a given product category
     */
    public function read($id) {
        $productCategory = ProductCategory::find($id);
        if(isset($productCategory)){
            $messaggio = $productCategory;
            $code = HttpResponseCode::OK;
        }
        else{
            $messaggio = "Product Category not found";
            $code = HttpResponseCode::NOT_FOUND;
        }

        return response()->json($messaggio, $code);
    }

    /**
     * Get all product categories
     */
    public function readAll() {
        return response()->json(ProductCategory::all(), HttpResponseCode::OK);
    }
}
