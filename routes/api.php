<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\ClassificationController;
use App\Http\Controllers\ProductsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/



/////////////////////////////////////////////Auth
Route::middleware('auth:api')->group( function () {
    Route::post('/logout',[AuthenticationController::class,'logout']);
    
    Route::post('/resatPasswordEnternal',[AuthenticationController::class,'resatPasswordEnternal']);
});


Route::post('/register',[AuthenticationController::class,'register']);

Route::post('/verifyCode',[AuthenticationController::class,'verifyCode']);

Route::post('/loginUser',[AuthenticationController::class,'loginForUser']);

Route::post('/loginAdmin',[AuthenticationController::class,'loginForAdmin']);

Route::post('/forgetPassword',[AuthenticationController::class,'forgetPassword']);

Route::post('/verifyForgetPassword',[AuthenticationController::class,'verifyForgetPassword']);

Route::post('/resatPassword',[AuthenticationController::class,'resatPassword']);

//////////////////////////////////////////////////////////////////////////////// for products

Route::post('/AddProduct',[ProductsController::class,'AddProduct']);

Route::get('/sukerProductsAdmin', [ProductsController::class, 'sukerProductsAdmin']);
Route::get('/sukerProducts', [ProductsController::class, 'sukerProducts'])->middleware('auth:api');;

Route::get('/ExtraProductsAdmin', [ProductsController::class, 'ExtraProductsAdmin']);
Route::get('/ExtraProducts', [ProductsController::class, 'ExtraProducts'])->middleware('auth:api');

Route::get('/ProdctsDetails/{id}', [ProductsController::class, 'ProdctsDetails']);

Route::post('/productsUpdate/{id}', [ProductsController::class, 'updateProduct']);

Route::post('/onOffProduct/{id}', [ProductsController::class, 'onOffProduct']);

Route::delete('/productsDelete/{id}', [ProductsController::class, 'deleteProduct']);

/////////////////////////////////////////////////////////////////////////////////// classification

Route::post('/AddClassification',[ClassificationController::class,'AddClassification']);

Route::get('/allClassifications',[ClassificationController::class,'allClassifications']);

Route::delete('/deleteClassification/{classification_id}',[ClassificationController::class,'deleteClassification']);

//////////////////////////////////////////////////////////////////////////////////////