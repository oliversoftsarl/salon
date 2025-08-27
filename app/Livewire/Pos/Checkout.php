<?php

namespace App\Livewire\Pos;

use App\Models\Product;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Checkout extends Component
{
    public string $productSearch = '';
    public string $serviceSearch = '';
    public array $cart = []; // [ ['type'=>'product|service','id'=>int,'name'=>string,'price'=>float,'qty'=>int] ]
    public string $payment_method = 'cash';

    public function render()
    {
        $products = Product::query()
            ->when($this->productSearch, fn ($q) => $q->where('name', 'like', "%{$this->productSearch}%")->orWhere('sku', 'like', "%{$this->productSearch}%"))
            ->orderBy('name')->limit(10)->get();

        $services = Service::query()
            ->when($this->serviceSearch, fn ($q) => $q->where('name', 'like', "%{$this->serviceSearch}%"))
            ->where('active', true)->orderBy('name')->limit(10)->get();

        return view('livewire.pos.checkout', compact('products', 'services'))->layout('layouts.main', ['title' => 'Caisse']);
    }

    public function addProduct(int $id): void
    {
        $p = Product::findOrFail($id);
        $this->addToCart('product', $p->id, $p->name, (float)$p->price, 1);
    }

    public function addService(int $id): void
    {
        $s = Service::findOrFail($id);
        $this->addToCart('service', $s->id, $s->name, (float)$s->price, 1);
    }

    public function incrementItem(int $index): void
    {
        if (!isset($this->cart[$index])) return;
        $this->cart[$index]['qty']++;
    }

    public function decrementItem(int $index): void
    {
        if (!isset($this->cart[$index])) return;
        $this->cart[$index]['qty'] = max(1, (int)$this->cart[$index]['qty'] - 1);
    }

    public function removeItem(int $index): void
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    public function getTotalProperty(): float
    {
        return array_reduce($this->cart, fn($carry, $item) => $carry + ($item['price'] * $item['qty']), 0.0);
    }

    public function checkout(): void
    {
        if (empty($this->cart)) {
            $this->addError('cart', 'Le panier est vide.');
            return;
        }

        DB::transaction(function () {
            $tx = Transaction::create([
                'reference' => null,
                'type' => 'sale',
                'total' => $this->total,
                'payment_method' => $this->payment_method,
                // 'cashier_id' => auth()->id(), // si tu veux lier le caissier
            ]);

            foreach ($this->cart as $item) {
                $unit = (float)$item['price'];
                $qty = (int)$item['qty'];
                $line = $unit * $qty;

                // Respecte ton schéma: product_id/service_id + unit_price + line_total
                TransactionItem::create([
                    'transaction_id' => $tx->id,
                    'product_id'     => $item['type'] === 'product' ? $item['id'] : null,
                    'service_id'     => $item['type'] === 'service' ? $item['id'] : null,
                    'quantity'       => $qty,
                    'unit_price'     => $unit,
                    'line_total'     => $line,
                ]);

                // Mise à jour du stock uniquement pour les produits
                if ($item['type'] === 'product') {
                    $p = Product::lockForUpdate()->find($item['id']);
                    if ($p) {
                        $toDecrement = min($qty, max(0, (int)$p->stock_quantity));
                        if ($toDecrement > 0) {
                            $p->decrement('stock_quantity', $toDecrement);
                            StockMovement::create([
                                'product_id' => $p->id,
                                'quantity' => $toDecrement,
                                'direction' => 'out',
                                'reason' => 'Vente',
                            ]);
                        }
                    }
                }
            }
        });

        $this->cart = [];
        $this->payment_method = 'cash';
        session()->flash('success', 'Vente enregistrée.');
    }

    private function addToCart(string $type, int $id, string $name, float $price, int $qty): void
    {
        foreach ($this->cart as $i => $it) {
            if ($it['type'] === $type && $it['id'] === $id) {
                $this->cart[$i]['qty'] += $qty;
                return;
            }
        }
        $this->cart[] = compact('type', 'id', 'name', 'price', 'qty');
    }
}
