<?php

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/test', function () {
    /**
     * @var \App\Models\User $user
     */
    $merchant = \App\Models\Merchant::first();
    $user = Filament::auth()->user();
    dd(
//       $merchant->cinema()->first(),
//       $merchant->cinema
        $user->workstations()->pluck('id')
    );
});
