<?php

namespace App\Http\Controllers;

use App\Category;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use RealRashid\SweetAlert\Facades\Alert;

class CategoryController extends Controller
{
    public function viewCategories()
    {
        $categories = Category::all();
        return view('admin.category.view_category',compact('categories'));
    }

    public function addCategory(Request $request)
    {
        if ($request->isMethod('post')){
            $category = new Category();
            $category->name = $request->category_name;
            $category->parent_id = $request->parent_id;
            $category->url = $request->category_url;
            $category->description = $request->category_description;
            $category->save();
            return redirect('/admin/view-categories')->with('flash_message_success','Category Added Successfully!!');
        }else{
            $levels = Category::where('parent_id',0)->get();
            return view('admin.category.add_category')->with(compact('levels'));
        }
    }

    public function editCategory(Request $request,$id)
    {
        if ($request->isMethod('post')){
            $category = Category::find($id);
            $category->name = $request->category_name;
            $category->parent_id = $request->parent_id;
            $category->url = $request->category_url;
            $category->description = $request->category_description;
            $category->save();
            return redirect('/admin/view-categories')->with('flash_message_success','Category Added Successfully!!');
        }else{
            $levels = Category::where('parent_id',0)->get();
            $categoryDetails = Category::find($id);
            return view('admin.category.edit_category')->with(compact('levels','categoryDetails'));
        }
    }

    public function deleteCategory($id)
    {
        $category = Category::find($id);
        $category->delete();

        Alert::success('Deleted Successfully', 'Success Message');
        return redirect()->back()->with('flash_message_error','Category Deleted');
    }

    public function updateStatus(Request $request)
    {
        $category = Category::where('id',$request->id)->update([
            'status' => $request->status
        ]);
        //return response()->json(['success'=>'Category status change successfully.']);
    }
}
