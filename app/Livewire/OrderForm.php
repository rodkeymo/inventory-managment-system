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
    public $invoice_id;
    #[Validate('required')]
    public int $taxes = 0;
    public array $invoiceProducts = [];
    protected $listeners = ['cartUpdated' => 'render'];
    #[Validate('required', message: 'Please select products')]
    public Collection $allProducts;

    public function mount($cartInstance, $invoice_id = null): void
    {
        $this->cart_instance = $cartInstance;
        $this->invoice_id = $invoice_id;
        $this->allProducts = Product::where('account_id', auth()->user()->account_id)
            ->orderBy('name')
            ->get();
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
            if (!$invoiceProduct['is_saved']) {
                $this->addError('invoiceProducts.'.$key, 'This line must be saved before editing another.');
                return;
            }
        }

        $this->invoiceProducts[$index]['is_saved'] = false;
    }

    public function saveProduct($index): void
    {
        $this->resetErrorBag();
        $product = $this->allProducts->find($this->invoiceProducts[$index]['product_id']);
        if (!$product || $product->account_id !== auth()->user()->account_id) {
            session()->flash('error', 'Unauthorized product access.');
            return;
        }

        $this->invoiceProducts[$index]['product_name'] = $product->name;
        $this->invoiceProducts[$index]['product_price'] = $product->selling_price;
        $this->invoiceProducts[$index]['is_saved'] = true;

        // Update cart
        $cart = Cart::instance($this->cart_instance);
        $cartItem = $cart->search(fn($cartItem) => $cartItem->id === $product->id)->first();

        if ($cartItem) {
            $cart->update($cartItem->rowId, [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->selling_price,
                'qty' => $this->invoiceProducts[$index]['quantity'],
                'weight' => 1,
                'options' => [
                    'code' => $product->code,
                ],
            ]);
        } else {
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
        }

        $this->dispatch('cartUpdated');
    }

    public function removeProduct($index): void
    {
        // Find the product in the allProducts collection using the product_id from invoiceProducts
        $product = $this->allProducts->find($this->invoiceProducts[$index]['product_id']);
    
        // If the product is not found, flash an error message and return
        if (!$product) {
            session()->flash('error', 'Product not found in the invoice.');
            return;
        }
    
        // Use the 'order' cart instance explicitly
        $cart = Cart::instance('order');
    
        // Get all items in the cart before deletion
        $cartItemsBeforeDeletion = $cart->content();
        if ($cartItemsBeforeDeletion) {
            session()->flash('cart_items_before', $cartItemsBeforeDeletion);
        }
    
        // Perform the deletion logic
        $cartItem = $cart->search(fn($cartItem) => $cartItem->id === $product->id)->first();
        if ($cartItem) {
            $cart->remove($cartItem->rowId);
        }
    
        // Remove the product from the invoiceProducts array
        array_splice($this->invoiceProducts, $index, 1);
    
        // Get all items in the cart after deletion
        $cartItemsAfterDeletion = $cart->content();
        session()->flash('cart_items_after', $cartItemsAfterDeletion);
    
        // Dispatch the cartUpdated event
        $this->dispatch('cartUpdated');
    
        // Flash a success message
        session()->flash('message', 'Product removed successfully.');
    }
    
}