<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Goat;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = Cart::where('user_id', Auth::id())->with('goat')->get();
        $total = $cartItems->sum(function ($item) {
            return $item->goat->price * $item->quantity;
        });

        return view('marketplace.cart', compact('cartItems', 'total'));
    }

    public function add(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $goat = Goat::findOrFail($request->goat_id);

        $cartItem = Cart::where('user_id', Auth::id())
            ->where('goat_id', $goat->id)
            ->first();

        if ($cartItem) {
            $cartItem->increment('quantity');
        } else {
            Cart::create([
                'user_id' => Auth::id(),
                'goat_id' => $goat->id,
                'quantity' => 1,
            ]);
        }

        return redirect()->back()->with('success', 'Kambing berhasil dimasukkan ke keranjang.');
    }

    public function remove($id)
    {
        Cart::where('user_id', Auth::id())->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Item berhasil dihapus dari keranjang.');
    }

    public function checkout()
    {
        $cartItems = Cart::where('user_id', Auth::id())->with('goat')->get();
        
        if ($cartItems->isEmpty()) {
            return redirect()->route('catalog')->with('error', 'Keranjang Anda kosong.');
        }

        DB::transaction(function () use ($cartItems) {
            $total = $cartItems->sum(function ($item) {
                return $item->goat->price * $item->quantity;
            });

            $order = Order::create([
                'user_id' => Auth::id(),
                'total_amount' => $total,
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'goat_id' => $item->goat_id,
                    'price' => $item->goat->price,
                    'quantity' => $item->quantity,
                ]);
                
                // Optional: mark goat as sold? or keep it until paid.
            }

            Cart::where('user_id', Auth::id())->delete();
        });

        return redirect()->route('catalog')->with('success', 'Pesanan berhasil dibuat! Admin akan menghubungi Anda untuk proses pembayaran.');
    }
}
