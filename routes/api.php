<?php

use App\Http\Controllers\AttributeController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\ClassificationController;
use App\Http\Controllers\ComplaintsAndSuggestionController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\PointsProductController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RateAndReviewController;
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

Route::get('/userInfo', [AuthenticationController::class, 'userInfo'])->middleware('auth:api');
//////////////////////////////////////////////////////////////////////////////// for products

Route::post('/AddProduct',[ProductController::class,'AddProduct']);
Route::get('/ProdctsDetails/{id}', [ProductController::class, 'ProdctsDetails']);
Route::post('/productsUpdate/{id}', [ProductController::class, 'updateProduct']);
Route::post('/onOffProduct/{id}', [ProductController::class, 'onOffProduct']);
Route::delete('/productsDelete/{id}', [ProductController::class, 'deleteProduct']);


Route::post('/AddPointsProduct',[PointsProductController::class,'AddPointsProduct']);
Route::get('/PointsProductDetails/{id}', [PointsProductController::class, 'PointsProductDetails']);
Route::post('/updatePointsProduct/{id}', [PointsProductController::class, 'updatePointsProduct']);
Route::post('/onOffPointsProduct/{id}', [PointsProductController::class, 'onOffPointsProduct']);
Route::delete('/deletePointsProduct/{id}', [PointsProductController::class, 'deletePointsProduct']);

Route::get('/productsAdmin/{type}', [ProductController::class, 'productsAdmin']);
Route::get('/Products/{type}', [ProductController::class, 'Products'])->middleware('auth:api');


/////////////////////////////////////////////////////////////////////////////////// classification

Route::post('/AddClassification',[ClassificationController::class,'AddClassification']);
Route::get('/allClassifications',[ClassificationController::class,'allClassifications']);
Route::delete('/deleteClassification/{classification_id}',[ClassificationController::class,'deleteClassification']);

//////////////////////////////////////////////////////////////////////////////////////ATTRIBUTES
Route::post('/updateWorkTime', [AttributeController::class, 'updateWorkTime']);
Route::get('/getWorkTime', [AttributeController::class, 'getWorkTime']);

Route::post('/updateStorePrice', [AttributeController::class, 'updateStorePrice']);
Route::post('/updateUrgentPrice', [AttributeController::class, 'updateUrgentPrice']);
Route::get('/getPrices', [AttributeController::class, 'getPrices']);

Route::post('/updateAboutUs', [AttributeController::class, 'updateAboutUs']);
Route::get('/getAboutUs', [AttributeController::class, 'getAboutUs']);

Route::post('/updatePhoneNumbers', [AttributeController::class, 'updatePhoneNumbers']);
Route::get('/getPhoneNumbers', [AttributeController::class, 'getPhoneNumbers']);

/////////////////////////////////////////////////// ORDER ///////////////////////////////

Route::post('/createEssentialOrder', [OrdersController::class, 'createEssentialOrder'])->middleware('auth:api');
Route::get('/orderDetails/{order_id}', [OrdersController::class, 'orderDetails']);
Route::delete('/deleteOrder/{order_id}', [OrdersController::class, 'deleteOrder']);
Route::post('/updateEssentialOrder/{order_id}', [OrdersController::class, 'updateEssentialOrder']);
Route::post('/updateExtraOrder/{order_id}', [OrdersController::class, 'updateExtraOrder']);

Route::post('/createExtraOrder', [OrdersController::class, 'createExtraOrder'])->middleware('auth:api');


Route::post('/preparingOrder/{order_id}', [OrdersController::class, 'preparingOrder']);
Route::post('/sentOrder/{order_id}', [OrdersController::class, 'sentOrder']);
Route::post('/receivedOrder/{order_id}', [OrdersController::class, 'receivedOrder']);

Route::get('/ordresOfuser', [OrdersController::class, 'ordresOfuser'])->middleware('auth:api');

////////////////////////////////////////////////////////////// Complaints Or Suggestion //////////////////// 

Route::post('/createComplaintsOrSuggestion', [ComplaintsAndSuggestionController::class, 'createComplaintsOrSuggestion'])->middleware('auth:api');
Route::get('/ComplaintsOrSuggestionDetails/{ComplaintsOrSuggestion_id}', [ComplaintsAndSuggestionController::class, 'ComplaintsOrSuggestionDetails']);

Route::post('/createRateAndReview', [RateAndReviewController::class, 'createRateAndReview'])->middleware('auth:api');
Route::get('/RateAndReviewDetails/{RateAndReview_id}', [RateAndReviewController::class, 'RateAndReviewDetails']);

Route::get('/getReviewsUseer', [RateAndReviewController::class, 'getReviewsUseer']);
Route::get('/getReviewsAdmin', [RateAndReviewController::class, 'getReviewsAdmin']);

Route::post('/displayRateOrNot/{rateAndReview_id}', [RateAndReviewController::class, 'displayRateOrNot']);
Route::get('/reportUserOrders', [OrdersController::class, 'reportUserOrders'])->middleware('auth:api');