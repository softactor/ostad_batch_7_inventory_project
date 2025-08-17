<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\Web\PageController;
use App\Http\Middleware\JwtTokenVerify;
use Illuminate\Support\Facades\Route;

// Backend
Route::group(['prefix'=>'backend'], function(){

    Route::post('register',[RegisterController::class,'register']);
    Route::post('login',[LoginController::class,'login']);
    Route::post('password/reset/send/otp',[ResetPasswordController::class,'sendOtp']);
    Route::post('password/reset/verify/otp',[ResetPasswordController::class,'verifyOtp']);
    Route::post('password/reset',[ResetPasswordController::class,'resetPassword']);

    Route::group(['middleware'=> JwtTokenVerify::class], function(){
        Route::get('profile',[ProfileController::class,'profile']);
        Route::post('profile-update',[ProfileController::class,'profileUpdate']);
        Route::post('logout',[LogoutController::class,'logout']);
    });

    Route::group(['prefix'=>'admin/products'], function(){
        Route::get('/list', [ProductController::class, 'adminProductList'])->name('admin.products.adminProductList');
        Route::get('/edit/{product}', [ProductController::class, 'adminProductEdit'])->name('admin.products.adminProductEdit');
        Route::put('/update/{product_id}', [ProductController::class, 'update'])->name('admin.products.update');
    })->middleware(JwtTokenVerify::class);



    Route::group(['prefix'=>'products'], function(){
        Route::get('/', [ProductController::class, 'index']);
        Route::post('store', [ProductController::class, 'store']);
        Route::put('update/{product}', [ProductController::class, 'update']);

        Route::get('{product}', [ProductController::class, 'show']);
    })->middleware(JwtTokenVerify::class);


    Route::group(['prefix'=>'invoices'], function(){
        Route::get('/', [InvoiceController::class, 'index']);
        Route::post('store', [InvoiceController::class, 'store']);

        Route::get('show/{invoice}', [InvoiceController::class, 'show']);
        Route::get('print/{invoice}', [InvoiceController::class, 'print']);
    })->middleware(JwtTokenVerify::class);

});


// Frontend Routes
Route::get('/', [PageController::class, 'index']);
Route::get('/register', [PageController::class, 'registration'])->name('register');
Route::get('/login', [PageController::class, 'login'])->name('login');
Route::get('/reset-password', [PageController::class, 'resetPassword'])->name('reset-password');
Route::get('/send-otp', [PageController::class, 'sendOtp'])->name('forgot-password.send-otp');
Route::get('/verify-otp', [PageController::class, 'verifyOtp'])->name('forgot-password.verify-otp');

Route::group(['middleware'=> JwtTokenVerify::class], function(){
    Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [PageController::class, 'profile'])->name('profile');
});

