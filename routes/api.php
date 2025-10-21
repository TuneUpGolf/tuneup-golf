<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\TenantAPIResource;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\MessageNotifyController;
use App\Http\Controllers\Superadmin\UserController;
use App\Http\Controllers\Admin\StripeWebhookController;
use App\Http\Controllers\Admin\Payment\StripeController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;

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

// Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);

Route::post('/notify-message', [MessageNotifyController::class, 'handleNotification']);


Route::post('/sanctum/token', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device_name' => 'required',
    ]);
    try {
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
    } catch (\Exception $e) {
        return abort(300, $e->getMessage());
    }
    return $user->createToken($request->device_name)->plainTextToken;
});


// Route::get('/request', function () {
//     try {
//         return '12123';
//         return view('request-demo');
//     } catch (\Exception $e) {
//         return abort(300, $e->getMessage());
//     }
// });


Route::group(['middleware' => [
    InitializeTenancyByDomainOrSubdomain::class,
]], function () {
    Route::get('/stripe/success', [StripeController::class, 'redirectFromCreate'])->name('stripe-redirect-create');
    
});

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('user/get/all', [UserController::class, 'getAllUsers']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('user/edit/service_fee', function (Request $request) {
        $request->validate([
            'service_fee' => 'required:numeric',
        ]);
        $user = User::find($request->user()->id);

        if ($request->user()->type == Role::ROLE_ADMIN && !!$user) {
            try {
                $user->service_fee = (float)$request->service_fee;
                $user->update();
            } catch (\Exception $e) {
                return throw new Exception($e->getMessage());
            }
            return response()->json(new TenantAPIResource($user), 200);
        } else {
            return response()->json('Tenant not found', 419);
        }
    });
    Route::post('master/user/create', [UserController::class, 'store']);
});
