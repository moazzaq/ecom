<?php

namespace App\Http\Controllers;

use App\Attribute;
use App\Cart;
use App\Category;
use App\Country;
use App\Coupon;
use App\DeliveryAddress;
use App\Order;
use App\OrderProduct;
use App\Product;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use RealRashid\SweetAlert\Facades\Alert;

class ProductController extends Controller
{
    public function viewProducts()
    {
        $products = Product::all();
        return view('admin.products.view_products',compact('products'));
    }

    public function addProduct(Request $request)
    {
        if ($request->isMethod('post')){
            $product = new Product();
            $product->name = $request->product_name;
            $product->category_id = $request->category_id;
            $product->code = $request->product_code;
            $product->color = $request->product_color;
            $product->price = $request->product_price;
            if(!empty($data['product_description'])){
                $product->description = $request->product_description;

            }else{
                $product->description = '';
            }
            //Upload image
            if($request->hasfile('image')){
                echo $img_tmp = $request->file('image');
                if($img_tmp->isValid()){

                    //image path code
                    $extension = $img_tmp->getClientOriginalExtension();
                    $filename = rand(111,99999).'.'.$extension;
                    $img_path = 'uploads/products/'.$filename;

                    //image resize
                    Image::make($img_tmp)->resize(500,500)->save($img_path);

                    $product->image = $filename;
                }
            }
            $product->save();
            return redirect('admin/view-products')->with('flash_message_success','Product has been added successfully!!');

        }else{
            $categories = Category::whereParentId(0)->get();
//            dd($categories);
            return view('admin.products.add_product',compact('categories'));
        }
    }

    public function editProduct(Request $request,$id)
    {
        $productDetails = Product::where(['id'=>$id])->first();
        if ($request->isMethod('post')){
            $data = $request->all();
            if(!empty($request->product_description)){
                $data['description'] = $request->product_description;

            }else{
                $productDetails->description = '';
            }
        }

        if ($request->isMethod('post')){
            $product = new Product();
            $product->name = $request->product_name;
            $product->category_id = $request->category_id;
            $product->code = $request->product_code;
            $product->color = $request->product_color;
            $product->price = $request->product_price;
            if(!empty($data['product_description'])){
                $product->description = $request->product_description;

            }else{
                $product->description = '';
            }
            //Upload image
            if($request->hasfile('image')){
                echo $img_tmp =$request->file('image');
                if($img_tmp->isValid()){

                    //image path code
                    $extension = $img_tmp->getClientOriginalExtension();
                    $filename = rand(111,99999).'.'.$extension;
                    $img_path = 'uploads/products/'.$filename;

                    //image resize
                    Image::make($img_tmp)->resize(500,500)->save($img_path);
//                    unlink(public_path('uploads/products/'.$productDetails->image));
                    File::delete(public_path('uploads/products/').$productDetails->image);

                }
            }else{
                $filename = $data['current_image'];
            }
            $productDetails->update(['name'=>$data['product_name'],
                'category_id'=>$data['category_id'],'code'=>$data['product_code'],'color'=>$data['product_color'],
                'description'=>$data['product_description'],'price'=>$data['product_price'],
                'image'=>$filename]);
            return redirect('admin/view-products')->with('flash_message_success','Product has been added successfully!!');

        }else{
            $productDetails = Product::where(['id'=>$id])->first();
            $categories = Category::whereParentId(0)->get();
//            dd($categories);
            return view('admin.products.edit_product',compact('categories','productDetails'));
        }
    }

    public function DeleteProduct($id)
    {
        $product = Product::find($id);
        if ($product->delete()){
            File::delete(public_path('uploads/products/').$product->image);
        }

        Alert::success('Deleted Successfully', 'Success Message');
        return redirect()->back()->with('flash_message_error','Product Deleted');
    }

    public function updateStatus(Request $request)
    {
        $product = Product::where('id',$request->id)->update([
           'status' => $request->status
        ]);
        //return response()->json(['success'=>'Product status change successfully.']);
    }

    public function products(Request $request,$id)
    {
        $productDetails = Product::where('id',$id)->first();
        $ProductsAltImages = \App\Image::where('product_id',$id)->get();
        $featuredProducts = Product::where(['featured_products'=>1])->get();
        // echo $productDetails;die;
        return view('wayshop.product_details')->with(compact('productDetails','featuredProducts','ProductsAltImages'));
    }

    public function addAttributes(Request $request,$id)
    {
        $productDetails = Product::with('attributes')->where('id',$id)->first();

        if ($request->isMethod('post')){
            $data = $request->all();
            foreach ($data['sku'] as $key=>$val) {
                if (!empty($val)) {
                    //Prevent duplicate SKU Record
                    $attrCountSKU = Attribute::where('sku', $val)->count();
                    if ($attrCountSKU > 0) {
                        return redirect('/admin/add-attributes/' . $id)->with('flash_message_error', 'SKU is already exist please select another sku');
                    }
                    //Prevent duplicate Size Record
                    $attrCountSizes = Attribute::where(['product_id' => $id, 'size' => $data['size']
                    [$key]])->count();
                    if ($attrCountSizes > 0) {
                        return redirect('/admin/add-attributes/' . $id)->with('flash_message_error', '' . $data['size'][$key] . 'Size is already exist please select another size');
                    }
                    $attribute = new Attribute();
                    $attribute->product_id = $id;
                    $attribute->sku = $data['sku'][$key];
                    $attribute->size = $data['size'][$key];
                    $attribute->price = $data['price'][$key];
                    $attribute->stock = $data['stock'][$key];
                    $attribute->save();
                }
            }
            return redirect('/admin/add-attributes/'.$id)->with('flash_message_success','Products attributes added successfully!');
        }else{
            return view('admin.products.add_attributes')->with(compact('productDetails'));
        }
    }

    public function deleteAttribute($id)
    {
        Attribute::where(['id'=>$id])->delete();
        return redirect()->back()->with('flash_message_error','Product Attribute is deleted!');

    }

    public function editAttributes(Request $request)
    {
        if($request->isMethod('post')){
            $data = $request->all();
            foreach($data['attr'] as $key=>$attr){
                Attribute::where(['id'=>$data['attr'][$key]])->update(['sku'=>$data['sku'][$key],
                    'size'=>$data['size'][$key],'price'=>$data['price'][$key],'stock'=>$data['stock'][$key]]);
            }
            return redirect()->back()->with('flash_message_success','Products Attributes Updated!!!');
        }
    }

    public function addImages(Request $request,$id=null)
    {
        $productDetails = Product::where(['id'=>$id])->first();
        if($request->isMethod('post')){
            $data = $request->all();
            if($request->hasfile('image')){
                $files = $request->file('image');
                foreach($files as $file){
                    $image = new \App\Image();
                    $extension = $file->getClientOriginalExtension();
                    $filename = rand(111,9999).'.'.$extension;
                    $image_path = 'uploads/products/'.$filename;
                    Image::make($file)->save($image_path);
                    $image->image = $filename;
                    $image->product_id = $data['product_id'];
                    $image->save();
                }
            }
            return redirect('/admin/add-images/'.$id)->with('flash_message_success','Image has been updated');
        }
        $productImages = \App\Image::where(['product_id'=>$id])->get();
        return view('admin.products.add_images')->with(compact('productDetails','productImages'));
    }
    public function deleteAltImage($id=null)
    {
        $productImage = \App\Image::where(['id'=>$id])->first();

        $image_path = 'uploads/products/';
        if(file_exists($image_path.$productImage->image)){
            unlink($image_path.$productImage->image);
        }
        \App\Image::where(['id'=>$id])->delete();
        Alert::success('Deleted','Success Message');
        return redirect()->back();
    }

    public function getPrice(Request $request)
    {
        $data = $request->all();
        $proArr = explode("-",$data['idSize']);

        $proAttr = Attribute::where(['product_id'=>$proArr[0],'size'=>$proArr[1]])->first();
        echo $proAttr->price;
    }

    public function addtoCart(Request $request){
//        Session::forget('CouponAmount');
//        Session::forget('CouponCode');
        $data = $request->all();
        // echo "<pre>";print_r($data);die;
        if(empty(Auth::user()->email)){
            $data['user_email'] = '';
        }else{
            $data['user_email'] = Auth::user()->email;
        }
        $session_id = Session::get('session_id');
        if(empty($session_id)){
            $session_id = Str::random(40);
            Session::put('session_id',$session_id);
        }

        $sizeArr = explode('-',$data['size']);
        $countProducts =Cart::where(['product_id'=>$data['product_id'],'product_color'=>$data['color'],'price'=>$data['price'],
            'size'=>$sizeArr[1],'session_id'=>$session_id])->count();
        if($countProducts>0){
            return redirect()->back()->with('flash_message_error','Product already exists in cart');
        }else{
            Cart::create(['product_id'=>$data['product_id'],'product_name'=>$data['product_name'],
                'product_code'=>$data['product_code'],'product_color'=>$data['color'],'price'=>$data['price'],
                'size'=>$sizeArr[1],'quantity'=>$data['quantity'],'user_email'=>$data['user_email'],
                'session_id'=>$session_id]);
        }
        return redirect('/cart')->with('flash_message_success','Product has been added in cart');
    }

    public function cart()
    {
        if (Auth::check()){
            $userCart = Cart::where('user_email',Auth::user()->email)->get();
        }else{
            $session_id = Session::get('session_id');
            $userCart = Cart::where('session_id',$session_id)->get();
        }
        foreach ($userCart as $key=>$item){
            $productDetails = Product::where('id',$item->product_id)->first();
            $userCart[$key]->image = $productDetails->image;
        }

        return view('wayshop.products.cart',compact('userCart'));
    }

    public function deleteCartProduct($id)
    {
        Session::forget('CouponAmount');
        Session::forget('CouponCode');
        Cart::where('id',$id)->delete();
        return redirect('/cart')->with('flash_message_error','Product has been deleted!');
    }

    public function updateCartQuantity($id,$quantity)
    {
        Session::forget('CouponAmount');
        Session::forget('CouponCode');
        Cart::where('id',$id)->increment('quantity' , $quantity);
        return redirect('/cart')->with('flash_message_success','Product Quantity has been updated Successfully');
    }

    public function applyCoupon(Request $request)
    {
        Session::forget('CouponAmount');
        Session::forget('CouponCode');
        if($request->isMethod('post')) {
            $data = $request->all();
            $couponCount = Coupon::where('coupon_code', $data['coupon_code'])->first();
            if (!$couponCount) {
                return redirect()->back()->with('flash_message_error', 'Coupon code does not exists');
            } else {
                $couponDetails = Coupon::where('coupon_code',$data['coupon_code'])->first();
                //Coupon code status
                if($couponDetails->status==0){
                    return redirect()->back()->with('flash_message_error','Coupon code is not active');
                }
                //Check coupon expiry date
                $expire_date = $couponDetails->expire_date;
                $current_date = date('Y-m-d');
                if ($expire_date < $current_date){
                    return redirect()->back()->with('flash_message_error','Coupon Code is Expired');
                }

                //Coupon is ready for discount
                $session_id = Session::get('session_id');

                if(Auth::check()){
                    $user_email = Auth::user()->email;
                    $userCart =Cart::where(['user_email'=>$user_email])->get();
                }else{
                    $session_id = Session::get('session_id');
                    $userCart = Cart::where(['session_id'=>$session_id])->get();
                }
                $total_amount = 0;
                foreach ($userCart as $item){
                    $total_amount = $total_amount + ($item->price*$item->quantity);
                }
                //Check if coupon amount is fixed or percentage
                if($couponDetails->amount_type=="Fixed"){
                    $coupon = $couponDetails->amount;
                }else{
                    $couponAmount = $total_amount * ($couponDetails->amount/100);
                    $coupon = intval($couponAmount);
                }
                //Add Coupon code in session
                Session::put('CouponAmount',$coupon);
                Session::put('CouponCode',$data['coupon_code']);
                return redirect()->back()->with('flash_message_success','Coupon Code is Successffully Applied.You are Availing Discount');
            }
        }
    }

    public function checkout(Request $request)
    {
        $user_id = Auth::user()->id;
        $user_email = Auth::user()->email;
//        $shippingDetails = DeliveryAddress::where('user_id',$user_id)->first();
        $userDetails = User::find($user_id);
        $countries = Country::get();
        //check if shipping address exists
        $shippingCount = DeliveryAddress::where('user_id',$user_id)->count();
        $shippingDetails = array();
        if($shippingCount > 0){
            $shippingDetails = DeliveryAddress::where('user_id',$user_id)->first();
        }
        //Update Cart Table With Email
         $session_id = Session::get('session_id');
         Cart::where(['session_id'=>$session_id])->update(['user_email'=>$user_email]);

        if($request->isMethod('post')){
            $data = $request->all();
            // echo "<pre>";print_r($data);die;
            //Update Users Details
            User::where('id',$user_id)->update(['name'=>$data['billing_name'],'address'=>$data['billing_address'],
                'city'=>$data['billing_city'],'state'=>$data['billing_state'],'pincode'=>$data['billing_pincode'],
                'country'=>$data['billing_country'],'mobile'=>$data['billing_mobile']]);
            if($shippingCount > 0){
                //update Shipping Address
                DeliveryAddress::where('user_id',$user_id)->update(['name'=>$data['shipping_name'],'address'=>$data['shipping_address'],
                    'city'=>$data['shipping_city'],'state'=>$data['shipping_state'],'pincode'=>$data['shipping_pincode'],
                    'country'=>$data['shipping_country'],'mobile'=>$data['shipping_mobile']]);
            }else{
                //New Shipping Address
                $shipping = new DeliveryAddress;
                $shipping->user_id = $user_id;
                $shipping->user_email = $user_email;
                $shipping->name = $data['shipping_name'];
                $shipping->address = $data['shipping_address'];
                $shipping->city = $data['shipping_city'];
                $shipping->state= $data['shipping_state'];
                $shipping->country =$data['shipping_country'];
                $shipping->pincode =$data['shipping_pincode'];
                $shipping->mobile = $data['shipping_mobile'];
                $shipping->save();
            }
//            return 'redirect to order page';
              return redirect()->action('ProductController@orderReview');
        }
        return view('wayshop.products.checkout')->with(compact('userDetails','countries','shippingDetails'));
    }

    public function orderReview()
    {
        $user_id = Auth::user()->id;
        $user_email = Auth::user()->email;
        $shippingDetails = DeliveryAddress::where('user_id',$user_id)->first();
        $userDetails = User::find($user_id);
        $userCart = Cart::where('user_email',$user_email)->get();

        foreach($userCart as $key=>$product){
            $productDetails = Product::where('id',$product->product_id)->first();
            $userCart[$key]->image = $productDetails->image;
        }
        return view('wayshop.products.order_review')->with(compact('userDetails','shippingDetails','userCart'));
    }

    public function placeOrder(Request $request)
    {
        if($request->isMethod('post')){
            $user_id = Auth::user()->id;
            $user_email = Auth::user()->email;
            $data = $request->all();

            //Get Shipping Details of Users
            $shippingDetails = DeliveryAddress::where(['user_email'=>$user_email])->first();
            if(empty(Session::get('CouponCode'))){
                $coupon_code = 'Not Used';
            }else{
                $coupon_code = Session::get('CouponCode');
            }
            if(empty(Session::get('CouponAmount'))){
                $coupon_amount = '0';
            }else{
                $coupon_amount = Session::get('CouponAmount');
            }
            $order = new Order();
            $order->user_id = $user_id;
            $order->user_email = $user_email;
            $order->name = $shippingDetails->name;
            $order->address = $shippingDetails->address;
            $order->city = $shippingDetails->city;
            $order->state = $shippingDetails->state;
            $order->pincode = $shippingDetails->pincode;
            $order->country = $shippingDetails->country;
            $order->mobile = $shippingDetails->mobile;
            $order->coupon_code = $coupon_code;
            $order->coupon_amount = $coupon_amount;
            $order->order_status = "New";
            $order->payment_method = $data['payment_method'];
            $order->grand_total = $data['grand_total'];
            $order->Save();

            $order_id = DB::getPdo()->lastinsertID();
//            dd($order_id);
            $catProducts = Cart::where(['user_email'=>$user_email])->get();

            foreach($catProducts as $pro){
                $cartPro = new OrderProduct();
                $cartPro->order_id = $order_id;
                $cartPro->user_id = $user_id;
                $cartPro->product_id = $pro->product_id;
                $cartPro->product_code = $pro->product_code;
                $cartPro->product_name = $pro->product_name;
                $cartPro->product_color = $pro->product_color;
                $cartPro->product_size = $pro->size;
                $cartPro->product_price = $pro->price;
                $cartPro->product_qty = $pro->quantity;
                $cartPro->save();

            }
            Session::put('order_id',$order_id);
            Session::put('grand_total',$data['grand_total']);
            if($data['payment_method']=="cod"){
                return redirect('/thanks');
            }else{
                return redirect('/stripe');
            }
        }
    }

    public function thanks()
    {
        $user_email = Auth::user()->email;
        Cart::where('user_email',$user_email)->delete();
        return view('wayshop.orders.thanks');
    }

    public function userOrders()
    {
        $user_id = Auth::user()->id;
        $orders = Order::with('orders')->where('user_id',$user_id)->orderBy('id','DESC')->get();

//       dd($orders);
        return view('wayshop.orders.user_orders')->with(compact('orders'));
    }

    public function userOrderDetails($id)
    {
        $orderDetails = Order::with('orders')->where('id',$id)->first();
        $user_id = $orderDetails->user_id;
        $userDetails = User::where('id',$user_id)->first();
        return view('wayshop.orders.user_order_details')->with(compact('orderDetails','userDetails'));
    }

    public function viewOrders(){
        $orders = Order::with('orders')->orderBy('id','DESC')->get();
        return view('admin.orders.view_orders')->with(compact('orders'));
    }
    public function viewOrderDetails($order_id){
        $orderDetails = Order::with('orders')->where('id',$order_id)->first();
//        dd($orderDetails);
        $user_id = $orderDetails->user_id;
        $userDetails = User::where('id',$user_id)->first();
        return view('admin.orders.order_details')->with(compact('orderDetails','userDetails'));
    }

    public function updateOrderStatus(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();

        }
        Order::where('id',$data['order_id'])->update(['order_status'=>$data['order_status']]);
        return redirect()->back()->with('flash_message_success','Order Status has been updated successfully!');
    }

    public function stripe(Request $request)
    {
        $user_email = Auth::user()->email;

        if($request->isMethod('post')){
            Cart::where('user_email',$user_email)->delete();
            $data = $request->all();
            \Stripe\Stripe::setApiKey('sk_test_BTfVVuRmscOUdmh2E68BplVk006uUbwdfj');

            $token = $_POST['stripeToken'];
            $charge = \Stripe\charge::Create([

                'amount' => $request->input('total_amount')*100,
                'currency' => 'pkr',
                'description' => $request->input('name'),
                'source' => $token,
            ]);
            return redirect()->back()->with('flash_message_success','Your Payment Successfully Done!');
        }
        return view('wayShop.orders.stripe');
    }
}
