<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan salah.'],
            ]);
        }

        if ($user->avatar) {
            $user->avatar_url = Storage::disk('public')->url($user->avatar);
        } else {
            $user->avatar_url = null;
        }

        return response()->json([
            'token' => $user->createToken($request->device_name)->plainTextToken,
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'avatar' => 'nullable|string', // Base64
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->filled('avatar') && !str_contains($request->avatar, '/')) {
            $imageName = 'avatar_' . $user->id . '_' . time() . '.jpg';
            $path = 'avatars/' . $imageName;
            
            $manager = new ImageManager(new Driver());
            $image = $manager->read(base64_decode($request->avatar));
            
            if ($image->width() > 400) {
                $image->scale(width: 400);
            }
            
            $encoded = $image->toJpeg(80);
            Storage::disk('public')->put($path, (string) $encoded);
            $user->avatar = $path;
        }

        $user->save();

        if ($user->avatar) {
            $user->avatar_url = Storage::disk('public')->url($user->avatar);
        } else {
            $user->avatar_url = null;
        }

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'user' => $user,
        ]);
    }
}
