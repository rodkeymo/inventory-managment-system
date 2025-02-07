<?php

namespace App\Livewire;

use App\Models\Product;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Validate;
use Livewire\Component;

class OrderForm extends Component
{
    public $cart_instance;
    private $product;
    #[Validate('Required')]
    public int $taxes = 0;
    public array $invoiceProducts = [];
    protected $listeners = ['cartUpdated' => 'render'];
    #[Validate('required', message: 'Please select products')]
    public Collection $allProducts;

    public function mount($cartInstance): void
    {
        $this->cart_instance = $cartInstance;
        $this->allProducts = Product::all();
        $this->invoiceProducts = [];
    }

    public function render(): View
    {
        $total = 0;

        foreach ($this->invoiceProducts as $invoiceProduct) {
            if ($invoiceProduct['is_saved'] && $invoiceProduct['product_price'] && $invoiceProduct['quantity']) {
                $total += $invoiceProduct['product_price'] * $invoiceProduct['quantity'];
            }
        }

        $cart_items = Cart::instance($this->cart_instance)->content();

        return view('livewire.order-form', [
            'subtotal' => $total,
            'total' => $total * (1 + (is_numeric($this->taxes) ? $this->taxes : 0) / 100),
            'cart_items' => $cart_items,
        ]);
    }

    public function addProduct(): void
    {
        foreach ($this->invoiceProducts as $key => $invoiceProduct) {
            if (! $invoiceProduct['is_saved']) {
                $this->addError('invoiceProducts.'.$key, 'This line must be saved before creating a new one.');
                return;
            }
        }

        $this->invoiceProducts[] = [
            'product_id' => '',
            'quantity' => 1,
            'is_saved' => false,
            'product_name' => '',
            'product_price' => 0,
        ];
    }

    public function editProduct($index): void
    {
        foreach ($this->invoiceProducts as $key => $invoiceProduct) {
            if (! $invoiceProduct['is_saved']) {
                $this->addError('invoiceProducts.'.$key, 'This line must be saved before editing another.');
                return;
            }
        }

        $productId = $this->invoiceProducts[$index]['product_id'];
        $cart = Cart::instance($this->cart_instance);
        
        // Remove the product from the cart before editing
        $cartItem = $cart->search(fn($cartItem) => $cartItem->id === $productId);
        if ($cartItem->isNotEmpty()) {
            Cart::instance($this->cart_instance)->remove($cartItem->first()->rowId);
        }

        // Mark product as unsaved for editing
        $this->invoiceProducts[$index]['is_saved'] = false;
        $this->dispatch('cartUpdated');
    }

    public function saveProduct($index): void
    {
        $this->resetErrorBag();

        $product = $this->allProducts->find($this->invoiceProducts[$index]['product_id']);
        
        // Remove existing product from cart if it exists
        $cart = Cart::instance($this->cart_instance);
        $cartItem = $cart->search(fn($cartItem) => $cartItem->id === $product->id);
        if ($cartItem->isNotEmpty()) {
            Cart::instance($this->cart_instance)->remove($cartItem->first()->rowId);
        }

        // Update invoice product details
        $this->invoiceProducts[$index]['product_name'] = $product->name;
        $this->invoiceProducts[$index]['product_price'] = $product->selling_price;
        $this->invoiceProducts[$index]['is_saved'] = true;

        // Add to cart with new details
        $cart = Cart::instance($this->cart_instance);
        $exists = $cart->search(function ($cartItem) use ($product) {
            return $cartItem->id === $product->id;
        });

        if ($exists->isNotEmpty()) {
            session()->flash('message', 'Product exists in the cart!');
            return;
        }

        $cart->add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->selling_price,
            'qty' => $this->invoiceProducts[$index]['quantity'],
            'weight' => 1,
            'options' => [
                'code' => $product->code,
            ],
        ]);

        $this->dispatch('cartUpdated');
    }

    public function removeProduct($index): void
    {
        $product = $this->invoiceProducts[$index] ?? null;
        
        if ($product && isset($product['product_id'])) {
            // Remove from cart
            $cart = Cart::instance($this->cart_instance);
            $cartItem = $cart->search(fn($cartItem) => $cartItem->id === $product['product_id']);
            if ($cartItem->isNotEmpty()) {
                $cart->remove($cartItem->first()->rowId);
            }

            // Handle database deletion if needed
            if (isset($product['id'])) {
                InvoiceProduct::where('id', $product['id'])->delete();
            }
        }

        // Remove from invoiceProducts array
        unset($this->invoiceProducts[$index]);
        $this->invoiceProducts = array_values($this->invoiceProducts);

        session()->flash('message', 'Product removed successfully.');
        $this->dispatch('cartUpdated');
    }
}