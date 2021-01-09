<?php

namespace App\Http\Controllers;

use App\Banner;
use App\Category;
use App\Product;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index()
    {
        $banners = Banner::where('status','1')->orderby('sort_order','asc')->get();
        $categories = Category::with('categories')->where(['parent_id'=>0])->get();
        $products = Product::paginate(3);
        return view('wayshop.index',compact('banners','categories','products'));
    }

    public function categories($category_id)
    {
        $categories = Category::with(['categories','products'])->where(['parent_id'=>0])->get();
        $products = Product::where('category_id',$category_id)->get();
        $product_name = Product::where('category_id',$category_id)->first();

        return view('wayshop.category')->with(compact('categories','product_name','products'));
    }

    public function home()
    {
        $banners = Banner::where('status','1')->orderby('sort_order','asc')->get();
        $categories = Category::with('categories')->where(['parent_id'=>0])->get();
        $products = Product::paginate(3);
        return view('wayshop.index',compact('banners','categories','products'));
    }
}
