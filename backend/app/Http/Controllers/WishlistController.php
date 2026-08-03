<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\Goat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::check()) {
            $wishlistItems = Wishlist::where('user_id', Auth::id())->with('goat')->get();
        } else {
            $sessionWishlist = session()->get('wishlist', []);
            $wishlistItems = Goat::whereIn('id', $sessionWishlist)->get()->map(function($goat) {
                return (object)['goat' => $goat];
            });
        }

        return view('marketplace.wishlist', compact('wishlistItems'));
    }

    public function add(Request $request)
    {
        $goatId = $request->goat_id;

        if (Auth::check()) {
            Wishlist::firstOrCreate([
                'user_id' => Auth::id(),
                'goat_id' => $goatId
            ]);
        } else {
            $wishlist = session()->get('wishlist', []);
            if (!in_array($goatId, $wishlist)) {
                $wishlist[] = $goatId;
                session()->put('wishlist', $wishlist);
            }
        }

        return redirect()->back()->with('success', 'Kambing berhasil ditambahkan ke wishlist.');
    }

    public function remove($goatId)
    {
        if (Auth::check()) {
            Wishlist::where('user_id', Auth::id())->where('goat_id', $goatId)->delete();
        } else {
            $wishlist = session()->get('wishlist', []);
            if (($key = array_search($goatId, $wishlist)) !== false) {
                unset($wishlist[$key]);
                session()->put('wishlist', $wishlist);
            }
        }

        return redirect()->back()->with('success', 'Item berhasil dihapus dari wishlist.');
    }
}
