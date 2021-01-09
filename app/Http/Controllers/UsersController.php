<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Country;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class UsersController extends Controller
{
    public function userLoginRegister()
    {
        return view('wayshop.users.login_register');
    }

    public function register(Request $request)
    {
        if($request->isMethod('post')){
            $data = $request->all();
            // echo "<pre>";print_r($data);die;
            $userCount = User::where('email',$data['email'])->count();
            if($userCount>0){
                return redirect()->back()->with('flash_message_error','Email is already exist');
            }else{
                //adding user in table
                $user = new User;
                $user->name = $data['name'];
                $user->email = $data['email'];
                $user->password = bcrypt($data['password']);
                $user->save();
                //Confirmation Email
//                $email = $data['email'];
//                $messageData = ['email'=>$data['email'],'name'=>$data['name'],
//                    'code'=>base64_encode($data['email'])];
//                Mail::send('wayshop.email.confirm',$messageData,function($message) use($email){
//                    $message->to($email)->subject('Account Activation For Wayshop');
//                });
                //  return redirect()->back()->with('flash_message_error','Please Confirm Your Email To Activate Your Account !');

                if(Auth::attempt(['email'=>$data['email'],'password'=>$data['password']])){
                    Session::put('frontSession',$data['email']);
                    if(!empty(Session::get('session_id'))){
                        $session_id = Session::get('session_id');
                        Cart::where('session_id',$session_id)->update(['user_email'=>$data['email']]);
                    }
                    return redirect('/cart');
                }
            }
        }
    }

    public function logout(){
        Session::forget('frontSession');
        Auth::logout();
        return redirect('/');
    }

    public function login(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            //    echo "<pre>";print_r($data);die;
            if(Auth::attempt(['email'=>$data['email'],'password'=>$data['password']])){
                //  $userStatus = User::where(['email'=>$data['email']])->first();
//                if($userStatus->status == 0){
//                    return redirect()->back()->with('flash_message_error','Your Account is not activated ! Please confirm your email to activate your account.');
//                }
                Session::put('frontSession',$data['email']);
                if(!empty(Session::get('session_id'))){
                    $session_id = Session::get('session_id');
                    Cart::where('session_id',$session_id)->update(['user_email'=>$data['email']]);
                }
                return redirect('/cart');
            }else{
                return redirect()->back()->with('flash_message_error','Invalid username and password!');
            }
        }
    }

    public function confirmAccount($email){
        $email = base64_decode($email);
        $userCount = User::where(['email'=>$email])->count();
        if($userCount > 0){
            $userDetails = User::where(['email'=>$email])->first();
            if($userDetails->status == 1){
                return redirect('login-register')->with('flash_message_error','Your Account is already activated. You can simply login now.');
            }else{
                User::where(['email'=>$email])->update(['status'=>1]);
                //Send Welcome to Users
                $messageData = ['email'=>$email,'name'=>$userDetails->name];
                Mail::send('wayshop.email.welcome',$messageData,function($message) use($email){
                    $message->to($email)->subject('Welcome To Wayshop Website');
                });
                return redirect('login-register')->with('flash_message_success','Congrats! Your Account is now Activated');
            }
        }else{
            abort(404);
        }
    }

    public function account(Request $request){
        return view('wayshop.users.account');
    }

    public function changePassword(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            $old_pwd = User::where('id',Auth::User()->id)->first();
            $current_password = $data['current_password'];
            if(Hash::check($current_password,$old_pwd->password)){
                $new_pwd = bcrypt($data['new_pwd']);
                User::where('id',Auth::User()->id)->update(['password'=>$new_pwd]);
                return redirect()->back()->with('flash_message_success','Yours Password is Changed Now!');
            }else{
                return redirect()->back()->with('flash_message_error','Old Password is Incorrect!');
            }
        }
        return view('wayshop.users.change_password');
    }
    public function changeAddress(Request $request){
        $user_id = Auth::user()->id;
        $userDetails = User::find($user_id);
        //    echo "<pre>";print_r($userDetails);die;
        if($request->isMethod('post')){
            $data = $request->all();
            $user = User::find($user_id);
            $user->name = $data['name'];
            $user->address = $data['address'];
            $user->city = $data['city'];
            $user->state = $data['state'];
            $user->country = $data['country'];
            $user->pincode = $data['pincode'];
            $user->mobile = $data['mobile'];
            $user->save();
            return redirect()->back()->with('flash_message_success','Account Details Has Been Updated!');

        }
        $countries = Country::get();
        return view('wayshop.users.change_address')->with(compact('countries','userDetails'));
    }
}
