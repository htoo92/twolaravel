<?php

use Illuminate\Support\Facades\Route;
use App\Events\MessageNotification;
// use Auth;

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

Route::get('/event',function(){
    
    event(new MessageNotification("This is our first brodcast message!"));
    return view("event.event");
    
});

Route::get('/', function () {
    return redirect('/login');
    
});

Auth::routes();


Route::get('/home', [App\Http\Controllers\ChangeLimitController::class, 'index']);
Route::get('/users', [App\Http\Controllers\UsersController::class, 'index']);

Route::get('/users/assignto', [App\Http\Controllers\UsersController::class, 'assignToGet']);
Route::post('/users/assignto', [App\Http\Controllers\UsersController::class, 'assignToPost']);

Route::post('/users/create', [App\Http\Controllers\UsersController::class, 'store']);
Route::get('/users/create', [App\Http\Controllers\UsersController::class, 'create']);
Route::get('/users/show/{id}', [App\Http\Controllers\UsersController::class, 'show']);
Route::get('/users/edit/{id}', [App\Http\Controllers\UsersController::class, 'edit']);
Route::post('/users/update/{id}', [App\Http\Controllers\UsersController::class, 'update']);
Route::post('/users/delete/{id}', [App\Http\Controllers\UsersController::class, 'destroy']);

Route::get('/groups', [App\Http\Controllers\GroupController::class, 'index']);
Route::post('/groups/create', [App\Http\Controllers\GroupController::class, 'store']);
Route::get('/groups/create', [App\Http\Controllers\GroupController::class, 'create']);
Route::get('/groups/show/{id}', [App\Http\Controllers\GroupController::class, 'show']);
Route::get('/groups/edit/{id}', [App\Http\Controllers\GroupController::class, 'edit']);
Route::post('/groups/update/{id}', [App\Http\Controllers\GroupController::class, 'update']);
Route::delete('/groups/delete/{id}', [App\Http\Controllers\GroupController::class, 'destroy']);

Route::get('/roles', [App\Http\Controllers\RoleController::class, 'index']);
Route::post('/roles/create', [App\Http\Controllers\RoleController::class, 'store']);
Route::get('/roles/create', [App\Http\Controllers\RoleController::class, 'create']);
Route::get('/roles/show/{id}', [App\Http\Controllers\RoleController::class, 'show']);
Route::get('/roles/edit/{id}', [App\Http\Controllers\RoleController::class, 'edit']);
Route::post('/roles/update/{id}', [App\Http\Controllers\RoleController::class, 'update']);
Route::delete('/roles/delete/{id}', [App\Http\Controllers\RoleController::class, 'destroy']);

Route::get('/changelimit', [App\Http\Controllers\ChangeLimitController::class, 'index'])->name('home');
Route::post('/changelimit/updateall/{id}', [App\Http\Controllers\ChangeLimitController::class, 'changeAll']);
Route::post('/changelimit/update/{id}', [App\Http\Controllers\ChangeLimitController::class, 'update']);


Route::get('/bets', [App\Http\Controllers\BetController::class, 'index']);
Route::post('/bets/create', [App\Http\Controllers\BetController::class, 'store']);
Route::get('/bets/create', [App\Http\Controllers\BetController::class, 'create']);
Route::get('/bets/show/{id}', [App\Http\Controllers\BetController::class, 'show']);
Route::get('/bets/edit/{id}', [App\Http\Controllers\BetController::class, 'edit']);
Route::post('/bets/update/{id}', [App\Http\Controllers\BetController::class, 'update']);
Route::post('/bets/delete', [App\Http\Controllers\BetController::class, 'destroy']);

// Route::get('/bets', [App\Http\Controllers\TestController::class, 'index']);
// Route::post('/bets/create', [App\Http\Controllers\TestController::class, 'store']);
// Route::get('/bets/create', [App\Http\Controllers\TestController::class, 'create']);
// Route::get('/bets/show/{id}', [App\Http\Controllers\TestController::class, 'show']);
// Route::get('/bets/edit/{id}', [App\Http\Controllers\TestController::class, 'edit']);
// Route::post('/bets/update/{id}', [App\Http\Controllers\TestController::class, 'update']);
// Route::delete('/bets/delete/{id}', [App\Http\Controllers\TestController::class, 'destroy']);

Route::post('/customers/create', [App\Http\Controllers\MemberController::class, 'store']);
Route::get('/customers/create',[App\Http\Controllers\MemberController::class, 'create']);
Route::get('/customers', [App\Http\Controllers\MemberController::class, 'index']);

Route::get('/ownerdetails/{id}', [App\Http\Controllers\OwnerDetailsController::class, 'edit']);
Route::post('/ownerdetails/update', [App\Http\Controllers\OwnerDetailsController::class, 'update']);
Route::post('/ownerdetails/sendReport/{id}', [App\Http\Controllers\OwnerDetailsController::class, 'sendReport']);

Route::post('/changenumberlimit/update/{id}', [App\Http\Controllers\HighlevelnumberController::class, 'update']);

Route::get('/clearall', [App\Http\Controllers\ClearAllController::class, 'index']);
Route::post('/clearall/destroy', [App\Http\Controllers\ClearAllController::class, 'destroy']);


Route::get('/luckynumber', [App\Http\Controllers\LuckNumberController::class, 'index']);
// Route::post('/luckynumber', [App\Http\Controllers\LuckNumberController::class, 'show']);
Route::get('/luckynumber/detail', [App\Http\Controllers\LuckNumberController::class, 'show']);
Route::get('/revenue', [App\Http\Controllers\RevenueController::class, 'index']);
Route::get('/errors',[App\Http\Controllers\ErrorsController::class, 'index']);
// Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
