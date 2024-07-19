<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Brand;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        $categories = Category::all();
        $subcategories = Subcategory::all();
        $brands = Brand::all();
        return view('estoque.products.index', compact('products', 'categories', 'subcategories', 'brands'));
    }

    public function search(Request $request)
    {
        $query = Product::query();

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('subcategory')) {
            $query->where('subcategory_id', $request->subcategory);
        }

        if ($request->filled('brand')) {
            $query->where('brand_id', $request->brand);
        }

        if ($request->filled('name')) {
            $query->where('title', 'like', '%' . $request->name . '%');
        }

        $products = $query->get();
        $categories = Category::all();
        $subcategories = Subcategory::all();
        $brands = Brand::all();

        return view('estoque.products.index', compact('products', 'categories', 'subcategories', 'brands'));
    }
}
