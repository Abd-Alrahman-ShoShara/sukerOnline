<?php

use App\Http\Controllers\AttributeController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\ClassificationController;
use App\Http\Controllers\ComplaintsAndSuggestionController;
use App\Http\Controllers\MaintenanceModeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\PointsOrderController;
use App\Http\Controllers\PointsProductController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RateAndReviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/



/////////////////////////////////////////////Auth



Route::post('/firstAccount', [AuthenticationController::class, 'firstAccount']);
Route::post('/register', [AuthenticationController::class, 'register']);

Route::post('/verifyCode', [AuthenticationController::class, 'verifyCode']);

Route::post('/login', [AuthenticationController::class, 'login']);

Route::post('/forgetPassword', [AuthenticationController::class, 'forgetPassword']);

Route::post('/verifyForgetPassword', [AuthenticationController::class, 'verifyForgetPassword']);
Route::post('/resendCode', [AuthenticationController::class, 'resendCode']);

Route::post('/resatPassword', [AuthenticationController::class, 'resatPassword']);
Route::get('/allClassifications', [ClassificationController::class, 'allClassifications']);





Route::middleware('auth:api')->group(function () {

    Route::post('/logout', [AuthenticationController::class, 'logout']);
    Route::get('/userInfo', [AuthenticationController::class, 'userInfo'])->middleware('auth:api');
    Route::post('/allUsers', [AuthenticationController::class, 'allUsers']);

    Route::post('/resatPasswordEnternal', [AuthenticationController::class, 'resatPasswordEnternal']);


    //////////////////////////////////////////////////////////////////////////////// for products

    Route::post('/AddProduct', [ProductController::class, 'AddProduct']);
    Route::get('/ProdctsDetails/{id}', [ProductController::class, 'ProdctsDetails']);
    Route::post('/productsUpdate/{id}', [ProductController::class, 'updateProduct']);
    Route::post('/onOffProduct/{id}', [ProductController::class, 'onOffProduct']);
    Route::delete('/productsDelete/{id}', [ProductController::class, 'deleteProduct']);


    Route::post('/AddPointsProduct', [PointsProductController::class, 'AddPointsProduct']);
    Route::get('/PointsProductDetails/{id}', [PointsProductController::class, 'PointsProductDetails']);
    Route::get('/PointsProducts', [PointsProductController::class, 'PointsProducts']);
    Route::get('/PointsProductsAdmin', [PointsProductController::class, 'PointsProductsAdmin']);
    Route::post('/updatePointsProduct/{id}', [PointsProductController::class, 'updatePointsProduct']);
    Route::post('/onOffPointsProduct/{id}', [PointsProductController::class, 'onOffPointsProduct']);
    Route::delete('/deletePointsProduct/{id}', [PointsProductController::class, 'deletePointsProduct']);

    Route::get('/productsAdmin/{type}', [ProductController::class, 'productsAdmin']);
    Route::get('/Products/{type}', [ProductController::class, 'Products'])->middleware('auth:api');


    /////////////////////////////////////////////////////////////////////////////////// classification

    Route::post('/AddClassification', [ClassificationController::class, 'AddClassification']);
    Route::delete('/deleteClassification/{classification_id}', [ClassificationController::class, 'deleteClassification']);

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

    Route::post('/createEssentialOrder', [OrdersController::class, 'createEssentialOrder'])->middleware('auth:api')->middleware('OnOffApp:api');
    Route::get('/orderDetails/{order_id}', [OrdersController::class, 'orderDetails']);
    Route::delete('/deleteOrder/{order_id}', [OrdersController::class, 'deleteOrder'])->middleware('OnOffApp:api');

    Route::post('/updateOrder/{order_id}', [OrdersController::class, 'updateOrder'])->middleware('OnOffApp:api');

    Route::post('/createExtraOrder', [OrdersController::class, 'createExtraOrder'])->middleware('auth:api')->middleware('OnOffApp:api');
    Route::post('/allOrdersUser', [OrdersController::class, 'allOrdersUser'])->middleware('auth:api');

    ////////////////////////////////////////////////////////////admin
    Route::post('/editStateOfOrder/{order_id}', [OrdersController::class, 'editStateOfOrder']);

    Route::post('/userOrders/{user_id}', [OrdersController::class, 'userOrders']);
    Route::post('/allOrders', [OrdersController::class, 'allOrders']);
    Route::get('/allPointsOrders', [PointsOrderController::class, 'allPointsOrders']);
    ////////////////////////////////////////////////////////////// PointsOrder ////////////////////

    Route::post('/createPointsOrder', [PointsOrderController::class, 'createPointsOrder'])->middleware('auth:api')->middleware('OnOffApp:api');
    Route::get('/pointsOrderDetails/{pointsOrder_id}', [PointsOrderController::class, 'pointsOrderDetails']);
    Route::delete('/deletePointsOrder/{pointsOrder_id}', [PointsOrderController::class, 'deletePointsOrder'])->middleware('auth:api')->middleware('OnOffApp:api');
    Route::post('/updatePointsOrder/{pointsOrder_id}', [PointsOrderController::class, 'updatePointsOrder'])->middleware('auth:api')->middleware('OnOffApp:api');
    Route::post('/pointsOrdersOfUser', [PointsOrderController::class, 'pointsOrdersOfUser'])->middleware('auth:api');

    Route::post('/editStateOfPointsOrder/{PointsOrder_id}', [PointsOrderController::class, 'editStateOfPointsOrder']);

    ////////////////////////////////////////////////////////////// Complaints Or Suggestion ////////////////////

    Route::post('/createComplaintsOrSuggestion', [ComplaintsAndSuggestionController::class, 'createComplaintsOrSuggestion'])->middleware('auth:api')->middleware('OnOffApp:api');
    Route::get('/ComplaintsOrSuggestionDetails/{ComplaintsOrSuggestion_id}', [ComplaintsAndSuggestionController::class, 'ComplaintsOrSuggestionDetails']);
    Route::get('/ComplaintsOrSuggestionUser', [ComplaintsAndSuggestionController::class, 'ComplaintsOrSuggestionUser'])->middleware('auth:api');
    Route::get('/allComplaintsOrSuggestion', [ComplaintsAndSuggestionController::class, 'allComplaintsOrSuggestion']);

    Route::post('/createRateAndReview', [RateAndReviewController::class, 'createRateAndReview'])->middleware('auth:api')->middleware('OnOffApp:api');
    Route::get('/RateAndReviewDetails/{RateAndReview_id}', [RateAndReviewController::class, 'RateAndReviewDetails']);

    Route::get('/getReviewsUseer', [RateAndReviewController::class, 'getReviewsUseer'])->middleware('OnOffApp:api');
    Route::get('/getReviewsAdmin', [RateAndReviewController::class, 'getReviewsAdmin']);

    Route::post('/displayRateOrNot/{rateAndReview_id}', [RateAndReviewController::class, 'displayRateOrNot']);
    Route::post('/reportUserOrders', [OrdersController::class, 'reportUserOrders'])->middleware('auth:api')->middleware('OnOffApp:api');
    Route::post('/reportAdminOrdersBetweenDates', [OrdersController::class, 'reportAdminOrdersBetweenDates']);
    Route::post('/orderByState/{user_id}', [OrdersController::class, 'orderByState']);
    Route::post('/orderByStateForAdmin', [OrdersController::class, 'orderByStateForAdmin']);



    /////////////////////////////////////////////////////////////////////maintenance
    Route::post('/AppOnOff', [AttributeController::class, 'AppOnOff']);

    Route::post('/choseLanguage', [AuthenticationController::class, 'choseLanguage'])->middleware('auth:api');
});
