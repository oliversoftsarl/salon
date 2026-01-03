<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Inventory\Supplies as InventorySupplies;

use App\Livewire\Inventory\Consumptions as InventoryConsumptions;

use App\Livewire\Services\Index as ServicesIndex;
use App\Livewire\Products\Index as ProductsIndex;
use App\Livewire\Clients\Index as ClientsIndex;
use App\Livewire\Appointments\Calendar as AppointmentsCalendar;
use App\Livewire\Staff\Schedule as StaffSchedule;
use App\Livewire\Pos\Checkout as PosCheckout;
use App\Livewire\Users\Index as UsersIndex;
use App\Livewire\Pos\TransactionsList as PosTransactionsList;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Redirection intelligente selon le rôle
    Route::get('/', function () {
        $user = auth()->user();
        if ($user && $user->role === 'cashier') {
            return redirect()->route('pos.checkout');
        }
        return view('pages.dashboard');
    });

    Route::get('/dashboard', function () {
        $user = auth()->user();
        if ($user && $user->role === 'cashier') {
            return redirect()->route('pos.checkout');
        }
        return view('pages.dashboard');
    })->name('dashboard');

    Route::get('/user/profile', function () {
        return view('profile.show');
    })->name('profile.show');
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
    Route::get('/pos/transactions', PosTransactionsList::class)
        ->middleware('role:admin,cashier')
        ->name('pos.transactions');

    Route::get('/users', UsersIndex::class)
        ->middleware('role:admin')
        ->name('users.index');

    Route::get('/inventory/consumptions', InventoryConsumptions::class)
        ->middleware('role:admin,staff')
        ->name('inventory.consumptions');

    Route::get('/inventory/supplies', InventorySupplies::class)
        ->middleware('role:admin,staff')
        ->name('inventory.supplies');


    Route::get('/download-receipt/{transaction}', function ($transactionId) {
    $transaction = Transaction::with(['items.product', 'items.service', 'items.stylist', 'client'])
        ->findOrFail($transactionId);
    $pdf = Pdf::loadView('pdf.receipt', [
        'transaction' => $transaction,
        'company' => [
            // 'name' => config('app.name', 'Salon de Coiffure Gobel'),
            'name' => 'Salon de Coiffure Gobel',
            'address' => 'Q. Office 1 Kanisa la mungu',
            'city' => 'NK Goma',
            'phone' => '243 990 378 202',
        ]
    ]);
    // Format personnalisé pour ticket (70mm de large)
    $pdf->setPaper([0, 0, 210, 500], 'portrait'); // 70mm ≈ 210 points
    return $pdf->download('receipt-'.$transaction->id.'.pdf');
})->name('download.receipt');
});


