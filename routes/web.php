<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/', function () {
//    return view('welcome');
//});

Route::match(['get','post'],'/', 'IndexController@index');
Route::get('/products/{id}','ProductController@products');
Route::get('/categories/{category_id}','IndexController@categories');
Route::get('/get-product-price','ProductController@getPrice');
//Apply Coupon Code
Route::post('/cart/apply-coupon','ProductController@applyCoupon');

//Route for login-register
Route::get('/login-register','UsersController@userLoginRegister');
//Route for login-User
Route::post('/user-login','UsersController@login');
//Route for add users registration
Route::post('/user-register','UsersController@register');
//Route for add users registration
Route::get('/user-logout','UsersController@logout');
//Confirm Email
Route::get('/confirm/{code}','UsersController@confirmAccount');

//Route for middleware after front login
Route::group(['middleware' => ['frontLogin']],function(){
//Route for users account
    Route::match(['get','post'],'/account','UsersController@account');
    Route::match(['get','post'],'/change-password','UsersController@changePassword');
    Route::match(['get','post'],'/change-address','UsersController@changeAddress');
    Route::match(['get','post'],'/checkout','ProductController@checkout');
    Route::match(['get','post'],'/order-review','ProductController@orderReview');
    Route::match(['get','post'],'/place-order','ProductController@placeOrder');
    Route::get('/thanks','ProductController@thanks');
    Route::match(['get','post'],'/stripe','ProductController@stripe');
    Route::get('/orders','ProductController@userOrders');
    Route::get('/orders/{id}','ProductController@userOrderDetails');
});




Route::match(['get','post'],'/admin', 'AdminController@login');
Route::group(['middleware'=>['AdminLogin']],function (){
    Route::match(['get','post'],'/admin/dashboard', 'AdminController@dashboard')->name('admin.dashboard');
    Route::match(['get','post'],'/admin/user-profile','AdminController@changePassword');
    Route::get('/logout','AdminController@logout');

    //Category Route
    Route::match(['get','post'],'/admin/add-category','CategoryController@addCategory');
    Route::match(['get','post'],'/admin/view-categories','CategoryController@viewCategories');
    Route::match(['get','post'],'/admin/edit-category/{id}','CategoryController@editCategory');
    Route::match(['get','post'],'/admin/delete-category/{id}','CategoryController@deleteCategory');
    Route::post('/admin/update-category-status','CategoryController@updateStatus');

    //Product Route
    Route::match(['get','post'],'/admin/add-product','ProductController@addProduct');
    Route::match(['get','post'],'/admin/view-products','ProductController@viewProducts');
    Route::match(['get','post'],'/admin/edit-product/{id}','ProductController@editProduct');
    Route::match(['get','post'],'/admin/delete-product/{id}','ProductController@DeleteProduct');
    Route::post('/admin/update-product-status','ProductController@updateStatus');
    Route::post('/admin/update-featured-product-status','ProductController@updateFeatured');
    //Products Attributes
    Route::match(['get','post'],'/admin/add-attributes/{id}','ProductController@addAttributes');
    Route::get('/admin/delete-attribute/{id}', 'ProductController@deleteAttribute');
    Route::match(['get','post'],'/admin/edit-attributes/{id}','ProductController@editAttributes');
    Route::match(['get','post'],'/admin/add-images/{id}','ProductController@addImages');
    Route::get('/admin/delete-alt-image/{id}','ProductController@deleteAltImage');

    //Banners Route
    Route::match(['get','post'],'/admin/banners','BannersController@banners');
    Route::match(['get','post'],'/admin/add-banner','BannersController@addBanner');
    Route::match(['get','post'],'/admin/edit-banner/{id}','BannersController@editBanner');
    Route::match(['get','post'],'/admin/delete-banner/{id}','BannersController@deleteBanner');
    Route::post('/admin/update-banner-status','BannersController@updateStatus');

    //Coupons Route
    Route::match(['get','post'],'/admin/add-coupon','CouponsController@addCoupon');
    Route::match(['get','post'],'/admin/view-coupons','CouponsController@viewCoupons');
    Route::match(['get','post'],'/admin/edit-coupon/{id}','CouponsController@editCoupon');
    Route::get('/admin/delete-coupon/{id}','CouponsController@deleteCoupon');
    Route::post('/admin/update-coupon-status','CouponsController@updateStatus');

    //Orders Route
    Route::get('/admin/orders','ProductController@viewOrders');
    Route::get('/admin/orders/{id}','ProductController@viewOrderDetails');
    Route::post('/admin/update-order-status','ProductsController@updateOrderStatus');
});


Auth::routes(['verify' => true]);

//Route::get('/home', 'HomeController@index')->name('home');
Route::any('/home','IndexController@home');


// Route for add to cart
Route::match(['get','post'],'add-cart','ProductController@addtoCart');
Route::match(['get','post'],'/cart','ProductController@cart')->middleware('verified');
Route::get('/cart/delete-product/{id}','ProductController@deleteCartProduct');
Route::get('/cart/update-quantity/{id}/{quantity}','ProductController@updateCartQuantity');
