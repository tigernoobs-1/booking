<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ComplainController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\ComponentController;
use App\Http\Controllers\FacilityServiceController;
use App\Http\Controllers\AttachmentFileController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\CompanyLookupController;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
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

Route::controller(LoginController::class)->group(function () {
    Route::post('login', 'login');
});

Route::controller(UserController::class)->group(function () {
    Route::post('register', 'store');
});

Route::controller(UserController::class)->group(function () {
    Route::get('get-email', 'userEmail');
});

Route::controller(PasswordResetController::class)->group(function () {
    Route::post('reset-email', 'sendEmail');
});

Route::controller(PasswordResetController::class)->group(function () {
    Route::post('reset-password', 'reset');
});


Route::get('user-group', function () {
    $config = config('userGroup');
    $apiList = array_values($config);
    return response()->json($apiList);
});

Route::group([
    'middleware' => ['auth:sanctum']
], function () {
    Route::controller(LoginController::class)->group(function () {
        Route::post('logout', 'logout');
    });
});


Route::controller(CompanyLookupController::class)->group(function () {
    Route::get('company-list', 'index');
});



//item
Route::group([
    'middleware' => ['auth:sanctum']
], function () {
    Route::controller(ItemController::class)->group(function () {
        Route::post('item', 'store');
    });
    Route::controller(ItemController::class)->group(function () {
        Route::put('item/{id}', 'update');
    });
    Route::controller(ItemController::class)->group(function () {
        Route::delete('item/{id}', 'destroy');
    });
});

Route::controller(ItemController::class)->group(function () {
    Route::get('item', 'index');
});

Route::controller(ComponentController::class)->group(function () {
    Route::get('component', 'index');
});


Route::group([
    'middleware' => ['auth:sanctum']
], function () {
    Route::controller(FacilityServiceController::class)->group(function () {
        Route::post('service', 'store');
    });
    Route::controller(FacilityServiceController::class)->group(function () {
        Route::put('service/{id}', 'update');
    });
    Route::controller(FacilityServiceController::class)->group(function () {
        Route::get('service-user', 'indexServiceRequest');
    });
    Route::controller(FacilityServiceController::class)->group(function () {
        Route::get('work-order', 'indexWorkOrder');
    });
    Route::controller(FacilityServiceController::class)->group(function () {
        Route::get('service-admin', 'indexAdmin');
    });
    Route::controller(FacilityServiceController::class)->group(function () {
        Route::get('work-admin', 'indexWorkAdmin');
    });
    Route::controller(FacilityServiceController::class)->group(function () {
        Route::delete('service/{id}', 'destroy');
    });
});


Route::group(
    [
        'middleware' => ['auth:sanctum']
    ],
    function () {
        Route::controller(AttachmentFileController::class)->group(function () {
            Route::post('store-file', 'store');
        });
        Route::controller(AttachmentFileController::class)->group(function () {
            Route::post('download-file', 'download');
        });

        Route::controller(AttachmentFileController::class)->group(function () {
            Route::delete('delete-file/{id}', 'destroy');
        });
    }
);


//notice
Route::group([
    'middleware' => ['auth:sanctum']
], function () {
    Route::controller(NoticeController::class)->group(function () {
        Route::post('notice', 'store');
    });
    Route::controller(NoticeController::class)->group(function () {
        Route::put('notice/{id}', 'update');
    });
    Route::controller(NoticeController::class)->group(function () {
        Route::delete('notice/{id}', 'destroy');
    });
});

Route::controller(NoticeController::class)->group(function () {
    Route::get('notice', 'index');
});

//user
Route::apiResource('user', UserController::class)->middleware('auth:sanctum');


//config
Route::group([
    'middleware' => ['auth:sanctum']
], function () {
    Route::controller(ConfigController::class)->group(function () {
        Route::get('config-day', 'configDay');
    });
    Route::controller(ConfigController::class)->group(function () {
        Route::get('config-time', 'configTime');
    });
    Route::controller(ConfigController::class)->group(function () {
        Route::put('config-time/{id}', 'updateTimeConfig');
    });
    Route::controller(ConfigController::class)->group(function () {
        Route::post('config', 'store');
    });
    Route::controller(ConfigController::class)->group(function () {
        Route::delete('config/{id}', 'destroy');
    });
});



Route::controller(ComplainController::class)->group(function () {
    Route::post('complain', 'store');
});


//complain
Route::group([
    'middleware' => ['auth:sanctum']
], function () {
    Route::controller(ComplainController::class)->group(function () {
        Route::get('complain', 'index');
    });
    Route::controller(ComplainController::class)->group(function () {
        Route::delete('complain/{id}', 'destroy');
    });
});

Route::controller(ComplainController::class)->group(function () {
    Route::post('complain', 'store');
});


//bookings
Route::group([
    'middleware' => ['auth:sanctum']
], function () {
    Route::controller(BookingController::class)->group(function () {
        Route::get('booking-admin', 'indexAdmin');
    });
    Route::controller(BookingController::class)->group(function () {
        Route::get('booking-user', 'indexUser');
    });

    Route::controller(BookingController::class)->group(function () {
        Route::get('find-booking', 'findBooking');
    });

    Route::controller(BookingController::class)->group(function () {
        Route::post('booking', 'store');
    });

    Route::controller(BookingController::class)->group(function () {
        Route::put('booking/{id}', 'update');
    });

    Route::controller(BookingController::class)->group(function () {
        Route::put('delete-booking/{id}', 'destroy');
    });
});



//Route::apiResource('item', ItemController::class)->middleware('auth:sanctum');;
//Route::apiResource('complain', ComplainController::class);

/* Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
}); */
