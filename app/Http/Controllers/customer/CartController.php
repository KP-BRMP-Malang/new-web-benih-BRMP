<?php

namespace App\Http\Controllers\customer;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Address;

class CartController extends Controller
{
    public function show()
    {
        if (!Auth::check()) return redirect()->route('login');
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->user_id)->first();
        $items = $cart ? $cart->cartItems()->with('product')->get() : collect();
        $total = $items->sum(function($item) { return $item->quantity * $item->price_per_unit; });
        $hasAddress = Address::where('user_id', $user->user_id)->exists();
        return view('customer.cart', compact('items', 'total', 'hasAddress'));
    }

    public function addToCart(Request $request, $productId)
    {
        $user = Auth::user();
        $product = Product::findOrFail($productId);
        
        // Cek stok tersedia
        $availableStock = $product->stock - $product->minimum_stock;
        if ($availableStock <= 0) {
            return redirect()->back()->with('error', 'Maaf, stok produk ini telah habis.');
        }
        
        // Validasi input quantity
        $inputQty = $request->input('quantity', '');
        
        // Validasi quantity tidak kosong
        if (empty($inputQty) || $inputQty == 0) {
            return redirect()->back()->with('error', 'Silakan masukkan jumlah yang ingin dibeli');
        }
        
        // Validasi minimal pembelian
        if ($inputQty < $product->minimum_purchase) {
            return redirect()->back()->with('error', 'Minimal pembelian: ' . number_format($product->minimum_purchase, 0, ',', '') . ' ' . $product->unit);
        }
        
        if (in_array($product->unit, ['Mata', 'Tanaman', 'Rizome'])) {
            $qty = max($product->minimum_purchase, (int) $inputQty);
        } else {
            $qty = max($product->minimum_purchase, (float) $inputQty);
        }
        
        // Validasi quantity tidak melebihi stok tersedia
        if ($qty > $availableStock) {
            return redirect()->back()->with('error', 'Maaf, stok tidak mencukupi. Maksimal: ' . $availableStock . ' ' . $product->unit);
        }

        // Cari cart aktif user, jika belum ada buat baru
        $cart = Cart::firstOrCreate([
            'user_id' => $user->user_id
        ]);

        // Cek apakah produk sudah ada di cart_items
        $cartItem = CartItem::where('cart_id', $cart->cart_id)
            ->where('product_id', $product->product_id)
            ->first();

        if ($cartItem) {
            // Cek total quantity setelah ditambah tidak melebihi stok
            $totalQuantity = $cartItem->quantity + $qty;
            if ($totalQuantity > $availableStock) {
                return redirect()->back()->with('error', 'Maaf, stok tidak mencukupi untuk menambahkan ke keranjang.');
            }
            
            // Update quantity
            $cartItem->quantity = $totalQuantity;
            $cartItem->save();
            $newCartItemId = $cartItem->cart_item_id;

        } else {
            // Tambah item baru
            $newItem = CartItem::create([
                'cart_id' => $cart->cart_id,
                'product_id' => $product->product_id,
                'quantity' => $qty,
                'price_per_unit' => $product->price_per_unit,
            ]);
            $newCartItemId = $newItem->cart_item_id;
        }
        return redirect()->route('cart')->with([
            'success' => 'Produk berhasil ditambahkan ke keranjang!',
            'new_cart_item_id' => $newCartItemId
        ]);
    }

    public function deleteItem($cart_item)
    {
        try {
            // Pastikan user sudah login
            if (!Auth::check()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $user = Auth::user();
            $item = CartItem::findOrFail($cart_item);
            
            // Pastikan item milik user yang login
            $cart = Cart::where('cart_id', $item->cart_id)
                        ->where('user_id', $user->user_id)
                        ->first();
            
            if (!$cart) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            
            $item->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Error deleting cart item: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus item.'], 500);
        }
    }

    public function updateQuantity(Request $request, $cart_item)
    {
        $item = CartItem::findOrFail($cart_item);
        $product = $item->product;
        $minimalPembelian = $product->minimum_purchase ?? 1;
        $satuan = strtolower($product->unit ?? '');
        $inputQty = $request->input('quantity', $minimalPembelian);
        
        // Cek stok tersedia
        $availableStock = $product->stock - $product->minimum_stock;
        if ($availableStock <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Maaf, stok produk ini telah habis.'
            ]);
        }
        
        // Validasi quantity tidak kosong
        if (empty($inputQty) || $inputQty == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan masukkan jumlah yang ingin dibeli'
            ]);
        }
        
        // Validasi minimal pembelian
        if ($inputQty < $minimalPembelian) {
            return response()->json([
                'success' => false,
                'message' => 'Minimal pembelian: ' . number_format($minimalPembelian, 0, ',', '') . ' ' . $product->unit
            ]);
        }
        
        if (in_array($satuan, ['mata', 'tanaman', 'rizome'])) {
            $qty = max($minimalPembelian, (int) $inputQty);
        } else {
            $qty = max($minimalPembelian, (float) $inputQty);
        }
        
        // Validasi quantity tidak melebihi stok tersedia
        if ($qty > $availableStock) {
            return response()->json([
                'success' => false,
                'message' => 'Maaf, stok tidak mencukupi. Maksimal: ' . $availableStock . ' ' . $product->unit
            ]);
        }
        
        $item->quantity = $qty;
        $item->save();
        return response()->json([
            'success' => true,
            'quantity' => $item->quantity,
            'subtotal' => number_format($item->price_per_unit * $item->quantity, 0, ',', '.')
        ]);
    }
} 