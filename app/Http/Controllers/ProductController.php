<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Get details for a given product
     */
    public function read($id) {

    }

    /**
     * Get all product for a restaurateur
     */
    public function readAll($restaurateurId) {

    }

    /**
     * Update data of a product
     * The restaurateur must be logged in
     */
    public function update($id, Request $newData) {

    }

    /**
     * Delete a product
     * The restaurateur must be logged in
     */
    public function delete($id) {
        
    }
}
