<?php

namespace App\Livewire;

use App\Helpers\CartManagement;
use App\Livewire\Partials\Navbar;
use App\Models\Product;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Product Detail - Coding Doubt')]

class ProductDetailPage extends Component
{
    use LivewireAlert;
    public $slug;
    public $quantity = 1;

    

    public function mount($slug){
        $this->slug = $slug;
    }

    public function increaseQty(){
        $this->quantity++;
    }

    public function decreaseQty(){
        if($this->quantity > 1){
            $this->quantity--;
        }
    }

    public function addToCart($product_id) {
        $total_count = CartManagement::addCartItemWithQty($product_id, $this->quantity);
        $this->dispatch('update-cart-count', total_count : $total_count)->to(Navbar::class);

        $this->alert('success', '✅ Product Added Successfully!', [
            'position' => 'bottom-end', // Centered position
            'toast' => true,
            'timer' => 3000,
            'showConfirmButton' => false,
            'background' => '#333',
            'color' => '#fff',
            'iconColor' => '#0DCAF0',
        ]);
        
    }

    public function render()
    {
        return view('livewire.product-detail-page', [
            'product' => Product::where('slug', $this->slug)->firstOrFail()
        ]);
    }
}
