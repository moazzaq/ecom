<?php

namespace App\Http\Controllers;

use App\Banner;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use RealRashid\SweetAlert\Facades\Alert;

class BannersController extends Controller
{
    public function banners(){
        $bannerDetails = Banner::all();
        return view('admin.banner.banners')->with(compact('bannerDetails'));
    }
    public function addBanner(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            $banner = new Banner();
            $banner->name = $data['banner_name'];
            $banner->text_style = $data['text_style'];
            $banner->sort_order = $data['sort_order'];
            $banner->content = $data['banner_content'];
            $banner->link = $data['link'];
            // Upload Image
            if($request->hasFile('image')){
                $image_tmp = $request->file('image');
                if ($image_tmp->isValid()){
                    // Upload Images after Resize
                    $extension = $image_tmp->getClientOriginalExtension();
                    $fileName = rand(111,99999).'.'.$extension;
                    $banner_path = 'uploads/banners/'.$fileName;
                    Image::make($image_tmp)->save($banner_path);
                    $banner->image = $fileName;
                }
            }
            $banner->save();
            return redirect('/admin/banners')->with('flash_message_success','Banners has been updated Successfully!!');
        }
        return view('admin.banner.add_banner');
    }
    public function editBanner(Request $request, $id=null){
        if($request->isMethod('post')){
            $data = $request->all();
            //echo "<pre>"; print_r($data); die;
            // Upload Image
            if($request->hasFile('image')){
                $image_tmp = $request->file('image');
                if ($image_tmp->isValid()){
                    // Upload Images after Resize
                    $extension = $image_tmp->getClientOriginalExtension();
                    $fileName = rand(111,99999).'.'.$extension;
                    $banner_path = 'uploads/banners/'.$fileName;
                    Image::make($image_tmp)->save($banner_path);
                }
            }else if(!empty($data['current_image'])){
                $fileName = $data['current_image'];
            }else{
                $fileName = '';
            }
            Banner::where('id',$id)->update(['name'=>$data['banner_name'],
                'text_style'=>$data['text_style'],'content'=>$data['banner_content'],'link'=>$data['link'],
                'sort_order'=>$data['sort_order'],'image'=>$fileName]);
            return redirect('/admin/banners')->with('flash_message_success','Banner has been edited Successfully');
        }
        $bannerDetails = Banner::where(['id'=>$id])->first();
        return view('admin.banner.edit_banner')->with(compact('bannerDetails'));
    }

    public function deleteProduct($id=null){
        Banner::where(['id'=>$id])->delete();
        Alert::success('Deleted Successfully', 'Success Message');
        return redirect()->back()->with('flash_message_error','Product Deleted');
    }

    public function updateStatus(Request $request){
//        $data = $request->all();
//        Banner::where('id',$data['id'])->update(['status'=>$data['status']]);
        Banner::where('id',$request->id)->update([
            'status' => $request->status
        ]);

    }
}
