<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Session;
use App\Admin;
class AdminController extends Controller
{
    public function login(Request $request){
        if($request->isMethod('post')){
            $data = $request->input();
            $adminCount = Admin::where(['username'=>$data['username'],'password'=>'$2y$10$I.oMtxE5pRaYDrpckT3vpu9f2lUWNdbpPXmUdSLopHaxJZ8//UyA2','status'=>1])->count();
//            dd($adminCount);
            if($adminCount > 0){
                Session::put('adminSession',$data['username']);
                return redirect('admin/dashboard');
            }else{

                return redirect('/admin')->with('flash_message_error','Invalid Username or Password');
            }
        }
        return view('admin.admin_login');
    }
    public function dashboard(){
        return view('admin.dashboard');
    }
    public function logout(){
        Session::forget('adminSession');
        Session::forget('session_id');
        return redirect('/admin')->with('flash_message_success','loged out Successfully!');
    }
    public function changePassword(Request $request){
        $userDetail = Admin::where(['status'=>1])->first();
        if($request->isMethod('post')){
            $data = $request->all();
            $adminCount = Admin::where(['username'=>Session::get('adminSession'),
                'password'=>md5($data['current_pwd']),'status'=>1])->count();
            if($adminCount == 1){
                $new_pwd = md5($data['new_pwd']);
                Admin::where('username',Session::get('adminSession'))->update(['password'=>$new_pwd,
                    'username'=>$data['username']]);
                return redirect()->back()->with('flash_message_success','Password is Updated Successfully!!');
            }else{
                return redirect()->back()->with('flash_message_error','Current Password is incorrect!');
            }
        }
        return view('admin.user_profile')->with(compact('userDetail'));
    }
}
