<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Inventory\Supplies as InventorySupplies;
use App\Livewire\Inventory\Consumptions as InventoryConsumptions;
use App\Livewire\Inventory\StockSheet as InventoryStockSheet;
use App\Livewire\Cash\Register as CashRegister;

use App\Livewire\Services\Index as ServicesIndex;
use App\Livewire\Products\Index as ProductsIndex;
use App\Livewire\Clients\Index as ClientsIndex;
use App\Livewire\Appointments\Calendar as AppointmentsCalendar;
use App\Livewire\Staff\Schedule as StaffSchedule;
use App\Livewire\Staff\Performance as StaffPerformance;
use App\Livewire\Pos\Checkout as PosCheckout;
use App\Livewire\Users\Index as UsersIndex;
use App\Livewire\Pos\TransactionsList as PosTransactionsList;

use App\Models\Transaction;
use App\Models\Product;
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

    Route::get('/staff/performance', StaffPerformance::class)
        ->middleware('role:admin')
        ->name('staff.performance');

    Route::get('/pos', PosCheckout::class)
        ->middleware('role:admin,cashier')
        ->name('pos.checkout');
    Route::get('/pos/transactions', PosTransactionsList::class)
        ->middleware('role:admin,cashier')
        ->name('pos.transactions');

    Route::get('/cash', CashRegister::class)
        ->middleware('role:admin')
        ->name('cash.register');

    Route::get('/users', UsersIndex::class)
        ->middleware('role:admin')
        ->name('users.index');

    Route::get('/inventory/consumptions', InventoryConsumptions::class)
        ->middleware('role:admin,staff')
        ->name('inventory.consumptions');

    Route::get('/inventory/supplies', InventorySupplies::class)
        ->middleware('role:admin,staff')
        ->name('inventory.supplies');

    Route::get('/inventory/stock-sheet', InventoryStockSheet::class)
        ->middleware('role:admin')
        ->name('inventory.stock-sheet');

    Route::get('/inventory/stock-sheet/pdf', function () {
        $productId = request('product');
        $dateFrom = request('from');
        $dateTo = request('to');

        if (!$productId) {
            return redirect()->route('inventory.stock-sheet');
        }

        $product = Product::findOrFail($productId);

        // Calculer les données pour le PDF
        $stockSheet = new \App\Livewire\Inventory\StockSheet();
        $stockSheet->product_id = $productId;
        $stockSheet->date_from = $dateFrom;
        $stockSheet->date_to = $dateTo;
        $stockSheet->loadStockSheet();

        $pdf = Pdf::loadView('pdf.stock-sheet', [
            'product' => $product,
            'movements' => $stockSheet->movements,
            'summary' => $stockSheet->summary,
            'initialStock' => $stockSheet->initialStock,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'company' => [
                'name' => 'Salon de Coiffure Gobel',
                'address' => 'Q. Office 1 Kanisa la mungu',
                'city' => 'NK Goma',
                'phone' => '243 990 378 202',
            ]
        ]);

        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('fiche-stock-' . $product->sku . '-' . $dateFrom . '-' . $dateTo . '.pdf');
    })->middleware('role:admin')->name('inventory.stock-sheet.pdf');


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


