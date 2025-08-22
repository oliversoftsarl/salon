<?php

use Illuminate\Support\Facades\Route;

use Livewire\Livewire;

use App\Livewire\Services\Index as ServicesIndex;
use App\Livewire\Products\Index as ProductsIndex;
use App\Livewire\Clients\Index as ClientsIndex;
use App\Livewire\Appointments\Calendar as AppointmentsCalendar;
use App\Livewire\Staff\Schedule as StaffSchedule;
use App\Livewire\Pos\Checkout as PosCheckout;
use App\Livewire\Users\Index as UsersIndex;

Route::get('/', function () {
    return view('pages.dashboard');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('pages.dashboard');
    })->name('dashboard');
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/appointments', AppointmentsCalendar::class)
        ->middleware('role:admin,staff')
        ->name('appointments.calendar');

    Route::get('/services', ServicesIndex::class)
        ->middleware('role:admin,staff')
        ->name('services.index');

    Route::get('/products', ProductsIndex::class)
        ->middleware('role:admin,staff')
        ->name('products.index');

    Route::get('/clients', ClientsIndex::class)
        ->middleware('role:admin,staff')
        ->name('clients.index');

    Route::get('/staff/schedule', StaffSchedule::class)
        ->middleware('role:admin')
        ->name('staff.schedule');

    Route::get('/pos', PosCheckout::class)
        ->middleware('role:admin,cashier')
        ->name('pos.checkout');

    // Gestion utilisateurs (admin only)
    Route::get('/users', UsersIndex::class)
        ->middleware('role:admin')
        ->name('users.index');
});
