<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MutualFund;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\DocumentController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/test', [InvestmentController::class, 'updateNavToScheme']);


Route::get('/handleactivation', [UserController::class, 'handleActivation']);


Route::get('/deleteuser', [UserController::class, 'deleteUser']);


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/getdashboard', function (Request $request) {
	$response = Http::get('http://bricksconsultancy.com/wp-admin/admin-ajax.php?action=stock_data');
	
    return $response;
});


Route::post('/login', [AuthController::class, 'authenticate']);


Route::middleware('auth:sanctum')->get('/dashboard', function (Request $request) {
    return "I am dashboard";
});


Route::middleware('auth:sanctum')->get('/getschemes', [MutualFund::class, 'getSchemes'] );
Route::middleware('auth:sanctum')->get('/getSchemeDropdown', [MutualFund::class, 'getSchemeDropdown'] );




Route::get('/logout', [AuthController::class, 'Logout']);
Route::post('/signup', [UserController::class, 'SignUp']);


//User management routes.
Route::middleware('auth:sanctum')->post('/createuser', [UserController::class, 'createUser']);
Route::middleware('auth:sanctum')->post('/updateprofile', [UserController::class, 'updateProfile']);
Route::middleware('auth:sanctum')->post('/changepassword', [UserController::class, 'changePassword']);

Route::middleware('auth:sanctum')->get('/getuserdetail', [UserController::class, 'getUserDetail']);
Route::middleware('auth:sanctum')->get('/getmanagers', [UserController::class, 'getManagers']);
Route::middleware('auth:sanctum')->post('/assignmanager', [UserController::class, 'assignManager']);
Route::middleware('auth:sanctum')->get('/getusers', [UserController::class, 'getUsers']);
Route::middleware('auth:sanctum')->get('/getprofile', [UserController::class, 'getProfile']);
Route::middleware('auth:sanctum')->get('/getclients', [UserController::class, 'getClients']);
Route::get('/verifyemail/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');



// investment routes 
Route::middleware('auth:sanctum')->post('/createinvestment', [InvestmentController::class, 'createInvestment']);
Route::middleware('auth:sanctum')->get('/viewinvestment', [InvestmentController::class, 'viewInvestmentByClientId']);
Route::middleware('auth:sanctum')->get('/myinvestment', [InvestmentController::class, 'getMyInvestmentList']);


//Document routes...
Route::middleware('auth:sanctum')->get('/getdocumentlist', [DocumentController::class, 'getDocumentList']);
Route::middleware('auth:sanctum')->get('/mydocumentlist', [DocumentController::class, 'getMyDocumentList']);
Route::middleware('auth:sanctum')->post('/createdocument',  [DocumentController::class, 'createDocument']);

//