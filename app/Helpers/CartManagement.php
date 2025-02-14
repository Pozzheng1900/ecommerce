<?php
namespace App\Helpers;

use App\Models\Product;
use Illuminate\Support\Facades\Cookie;

class CartManagement {
    // Add item to cart
    static public function addCartItem($product_id) {
        $cart_items = self::getCartItemsFromCookie();
        $existing_item = null;

        foreach ($cart_items as $key => $item) {
            if ($item['product_id'] == $product_id) {
                $existing_item = $key;
                break;
            }
        }

        if ($existing_item !== null) {
            $cart_items[$existing_item]['quantity'] += 1;
        } else {
            $product = Product::select(['id', 'name', 'price', 'images'])->find($product_id);

            if ($product) {
                $image = is_array($product->images) ? $product->images[0] : $product->images;

                $cart_items[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'image' => $image,
                    'quantity' => 1,
                    'unit_amount' => $product->price,
                    'total_amount' => $product->price
                ];
            }
        }

        self::addCartItemToCookie($cart_items);
        return count($cart_items);
    }

    // Add item to cart with quantity
    static public function addCartItemWithQty($product_id, $qty = 1) {
        $cart_items = self::getCartItemsFromCookie();
        $existing_item = null;

        foreach ($cart_items as $key => $item) {
            if ($item['product_id'] == $product_id) {
                $existing_item = $key;
                break;
            }
        }

        if ($existing_item !== null) {
            $cart_items[$existing_item]['quantity'] = max(1, $qty);
        } else {
            $product = Product::select(['id', 'name', 'price', 'images'])->find($product_id);

            if ($product) {
                $image = is_array($product->images) ? $product->images[0] : $product->images;

                $cart_items[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'image' => $image,
                    'quantity' => max(1, $qty),
                    'unit_amount' => $product->price,
                    'total_amount' => $product->price * $qty
                ];
            }
        }

        self::addCartItemToCookie($cart_items);
        return count($cart_items);
    }

    // Remove item from cart
    static public function removeCartItem($product_id) {
        $cart_items = self::getCartItemsFromCookie();
        $cart_items = array_filter($cart_items, fn($item) => $item['product_id'] !== $product_id);
        
        self::addCartItemToCookie($cart_items);
        return $cart_items;
    }

    // Add cart item to cookie
    static public function addCartItemToCookie($cart_items) {
        Cookie::queue('cart_items', json_encode($cart_items, JSON_UNESCAPED_UNICODE), 60 * 24 * 30);
    }

    // Clean cart item from cookie
    static public function cleanCartItemsFromCookie() {
        Cookie::queue(Cookie::forget('cart_items'));
    }

    // Get all cart items from cookie
    static public function getCartItemsFromCookie() {
        $cart_item = json_decode(Cookie::get('cart_items'), true);
        return is_array($cart_item) ? $cart_item : [];
    }

    // Increment item quantity
    static function incrementQuantityCartItem($product_id) {
        $cart_items = self::getCartItemsFromCookie();
        foreach ($cart_items as &$item) {
            if ($item['product_id'] == $product_id) {
                $item['quantity'] += 1;
                $item['total_amount'] = $item['quantity'] * $item['unit_amount'];
                break;
            }
        }
        self::addCartItemToCookie($cart_items);
        return $cart_items;
    }

    // Decrement item quantity
    static function decrementQuantityCartItem($product_id) {
        $cart_items = self::getCartItemsFromCookie();
        foreach ($cart_items as &$item) {
            if ($item['product_id'] == $product_id && $item['quantity'] > 1) {
                $item['quantity'] -= 1;
                $item['total_amount'] = $item['quantity'] * $item['unit_amount'];
                break;
            }
        }
        self::addCartItemToCookie($cart_items);
        return $cart_items;
    }

    // Calculate grand total
    static public function calculateGrandTotal($items) {
        return array_sum(array_column($items, 'total_amount'));
    }

    // clear cart items
    static public function clearCartItems() {
        self::cleanCartItemsFromCookie();
    }
}
?>
