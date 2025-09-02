<?php

namespace App\Livewire\Pos;

use App\Models\Product;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\StockMovement;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Illuminate\Validation\Rule;

class Checkout extends Component
{
    public string $productSearch = '';
    public string $serviceSearch = '';
    // cart items peuvent porter stylist_id pour les services
    public array $cart = []; // [['type'=>'product|service','id'=>int,'name'=>string,'price'=>float,'qty'=>int,'stylist_id'=>?int]]
    public string $payment_method = 'cash';
    public ?int $client_id = null;

    // Création rapide de client
    public bool $showNewClient = false;
    public string $newClient_name = '';
    public ?string $newClient_email = null;
    public ?string $newClient_phone = null;
    public ?string $newClient_birthdate = null;
    public ?string $newClient_gender = null;
    public ?string $newClient_notes = null;
    public ?int $newClient_loyalty_point = 0;

    public function render()
    {
        $products = Product::query()
            ->when($this->productSearch, fn ($q) =>
                $q->where('name', 'like', "%{$this->productSearch}%")->orWhere('sku', 'like', "%{$this->productSearch}%"))
            ->orderBy('name')->limit(10)->get();

        $services = Service::query()
            ->when($this->serviceSearch, fn ($q) => $q->where('name', 'like', "%{$this->serviceSearch}%"))
            ->where('active', true)->orderBy('name')->limit(10)->get();

        $clients = Client::query()
            ->orderByDesc('id')->limit(50)->get()
            ->map(fn($c) => ['id' => $c->id, 'label' => $this->clientLabel($c)]);

        // Liste du staff (tous users; adapte si tu veux filtrer par rôle)
        $staff = User::orderBy('name')->get(['id','name']);

        return view('livewire.pos.checkout', compact('products', 'services', 'clients', 'staff'))
            ->layout('layouts.main', ['title' => 'Caisse']);
    }

    private function clientLabel(object $c): string
    {
        // Essaie plusieurs champs fréquents pour construire un libellé
        $candidates = [
            'name',
            'full_name',
            fn() => (isset($c->first_name) || isset($c->last_name))
                ? trim(($c->first_name ?? '').' '.($c->last_name ?? ''))
                : null,
            'first_name',
            'last_name',
            'email',
            'phone',
            'phone_number',
        ];

        foreach ($candidates as $candidate) {
            $value = is_callable($candidate) ? $candidate() : ($c->{$candidate} ?? null);
            if (is_string($value) && trim($value) !== '') {
                return $value;
            }
        }
        return "Client #{$c->id}";
    }

    public function addProduct(int $id): void
    {
        $p = Product::findOrFail($id);
        $this->addToCart('product', $p->id, $p->name, (float)$p->price, 1);
    }

    public function addService(int $id): void
    {
        $s = Service::findOrFail($id);
        // services peuvent porter stylist_id (par défaut null)
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

    public function createClient(): void
    {
        $this->validate([
            'newClient_name'  => ['required', 'string', 'max:255'],
            'newClient_email' => ['nullable', 'email', 'max:255', Rule::unique('clients', 'email')],
            'newClient_phone' => ['nullable', 'string', 'max:50'],
            'newClient_birthdate' => ['nullable', 'date'],
            'newClient_gender' => ['nullable', 'string', 'max:16'],
            'newClient_loyalty_point' => ['nullable', 'integer', 'min:0'],
            'newClient_notes' => ['nullable', 'string'],
        ]);

        $cols = Schema::getColumnListing((new Client)->getTable());
        $data = [];

        // name / first_name / last_name
        if (in_array('first_name', $cols, true)) {
            $parts = preg_split('/\s+/', trim($this->newClient_name));
            $first = $parts[0] ?? $this->newClient_name;
            $last  = isset($parts[1]) ? implode(' ', array_slice($parts, 1)) : null;
            $data['first_name'] = $first;
            if (in_array('last_name', $cols, true)) {
                $data['last_name'] = $last;
            }
        } elseif (in_array('name', $cols, true)) {
            $data['name'] = $this->newClient_name;
        } elseif (in_array('names', $cols, true)) {
            $data['names'] = $this->newClient_name;
        }

        if (in_array('email', $cols, true)) {
            $data['email'] = $this->newClient_email;
        }
        if (in_array('phone', $cols, true)) {
            $data['phone'] = $this->newClient_phone;
        } elseif (in_array('phone_number', $cols, true)) {
            $data['phone_number'] = $this->newClient_phone;
        }

        if (in_array('birthdate', $cols, true)) {
            $data['birthdate'] = $this->newClient_birthdate ?: null;
        }
        if (in_array('gender', $cols, true)) {
            $data['gender'] = $this->newClient_gender ?: null;
        }
        if (in_array('loyalty_point', $cols, true)) {
            $data['loyalty_point'] = (int)($this->newClient_loyalty_point ?? 0);
        }
        if (in_array('notes', $cols, true)) {
            $data['notes'] = $this->newClient_notes ?: null;
        }

        $client = Client::create($data);

        $this->client_id = $client->id;
        $this->showNewClient = false;

        // reset form
        $this->newClient_name = '';
        $this->newClient_email = null;
        $this->newClient_phone = null;
        $this->newClient_birthdate = null;
        $this->newClient_gender = null;
        $this->newClient_notes = null;
        $this->newClient_loyalty_point = 0;

        session()->flash('success', 'Client créé et sélectionné.');
    }

    private function buildClientDataFromInputs(): array
    {
        // Découpe "Nom complet" en prénom/nom si nécessaire
        $first = $this->newClient_name;
        $last = null;

        if (Schema::hasColumn('clients', 'first_name')) {
            // Split sur le premier espace significatif
            $parts = preg_split('/\s+/', trim($this->newClient_name));
            if ($parts && count($parts) > 1) {
                $first = array_shift($parts);
                $last = implode(' ', $parts);
            } else {
                $first = $this->newClient_name;
                $last = null;
            }
        }

        $data = [];

        // Nom selon schéma
        if (Schema::hasColumn('clients', 'first_name')) {
            $data['first_name'] = $first;
            if (Schema::hasColumn('clients', 'last_name')) {
                $data['last_name'] = $last;
            }
        } elseif (Schema::hasColumn('clients', 'name')) {
            $data['name'] = $this->newClient_name;
        } elseif (Schema::hasColumn('clients', 'names')) {
            $data['names'] = $this->newClient_name;
        }

        // Email si colonne existe
        if (Schema::hasColumn('clients', 'email')) {
            $data['email'] = $this->newClient_email;
        }

        // Téléphone selon schéma
        if (Schema::hasColumn('clients', 'phone')) {
            $data['phone'] = $this->newClient_phone;
        } elseif (Schema::hasColumn('clients', 'phone_number')) {
            $data['phone_number'] = $this->newClient_phone;
        }

        // Notes si existant (on laisse vide)
        if (Schema::hasColumn('clients', 'notes')) {
            $data['notes'] = null;
        }

        return $data;
    }

    public function checkout(): void
    {
        if (empty($this->cart)) {
            $this->addError('cart', 'Le panier est vide.');
            return;
        }

        $this->validate([
            'client_id' => ['nullable', 'exists:clients,id'],
        ]);

        // Pré-contrôle: tous les produits du panier doivent avoir un stock suffisant
        $productNeeds = [];
        foreach ($this->cart as $item) {
            if (($item['type'] ?? null) === 'product') {
                $productNeeds[$item['id']] = ($productNeeds[$item['id']] ?? 0) + (int)$item['qty'];
            }
        }
        if (!empty($productNeeds)) {
            $products = Product::whereIn('id', array_keys($productNeeds))->get()->keyBy('id');
            foreach ($productNeeds as $pid => $needed) {
                $available = (int)($products[$pid]->stock_quantity ?? 0);
                if ($needed > $available) {
                    $this->addError('cart', 'Stock insuffisant pour le produit "'.($products[$pid]->name ?? ('#'.$pid)).'". Disponible: '.$available.', demandé: '.$needed);
                    return;
                }
            }
        }

        DB::transaction(function () {
            $tx = Transaction::create([
                'reference'      => null,
                'type'           => 'sale',
                'total'          => $this->total,
                'payment_method' => $this->payment_method,
                'client_id'      => $this->client_id,
                // 'cashier_id'   => auth()->id(),
            ]);

            foreach ($this->cart as $item) {
                $unit = (float)$item['price'];
                $qty  = (int)$item['qty'];
                $line = $unit * $qty;

                TransactionItem::create([
                    'transaction_id' => $tx->id,
                    'product_id'     => $item['type'] === 'product' ? $item['id'] : null,
                    'service_id'     => $item['type'] === 'service' ? $item['id'] : null,
                    'stylist_id'     => $item['type'] === 'service'
                        ? ($item['stylist_id'] ?? null)
                        : null,
                    'quantity'       => $qty,
                    'unit_price'     => $unit,
                    'line_total'     => $line,
                ]);

                if ($item['type'] === 'product') {
                    $p = Product::lockForUpdate()->findOrFail($item['id']);
                    $available = (int)$p->stock_quantity;

                    // Re-vérifie au niveau DB pour éviter les races
                    if ($qty > $available) {
                        throw new \RuntimeException('Stock insuffisant pour '.$p->name.'.');
                    }

                    $p->decrement('stock_quantity', $qty);

                    StockMovement::create([
                        'product_id'   => $p->id,
                        'qty_change'   => -$qty,
                        'reason'       => 'Vente',
                        'reference_id' => $tx->id,
                    ]);
                }
            }
        });

        $this->cart = [];
        $this->payment_method = 'cash';
        $this->client_id = null;
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
        $row = compact('type', 'id', 'name', 'price', 'qty');
        if ($type === 'service') {
            $row['stylist_id'] = null; // coiffeur à sélectionner
        }
        $this->cart[] = $row;
    }
}
