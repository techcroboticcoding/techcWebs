<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

use App\Models\Student;
use App\Models\Lesson;
use App\Models\Order;
use App\Models\Todo;

class ProfileController extends Controller
{
    public function show()
{
    return auth()->user();
}

    public function getProfile(Request $request)
    {
$user = $request->user(); // ✅ ini yang benar

        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'bio' => $user->bio,
            'country' => $user->country,
            'city' => $user->city,
            'postal_code' => $user->postal_code,
            'tax_id' => $user->tax_id,
            'photo_url' => $user->photo 
                ? asset('storage/profile/' . $user->photo)
                : null
        ]);
    }


public function dashboard(Request $request)
{
    $user = $request->user();

    return response()->json([
        'anak' => (int) ($user->jumlah_anak ?? 0),
        'progress' => (int) ($user->progress_belajar ?? 0),
        'tagihan' => (int) ($user->tagihan ?? 0),
        'pengumuman' => (int) ($user->pengumuman ?? 0),
        'catatan' => $user->catatan ?? 'Belum ada catatan',

        // 🔥 FIX ANTI ERROR
        'jadwal' => $user->jadwal
            ? json_decode($user->jadwal, true)
            : [],

        'chart' => [
            'labels' => ['M1','M2','M3','M4'],
            'data' => [20,40,60,75],
        ]
    ]);
}



  public function uploadPhoto(Request $request)
{
    $request->validate([
        'photo' => 'required|image|mimes:jpg,jpeg,png'
    ]);

    $user = auth()->user();

    if ($request->hasFile('photo')) {
        $file = $request->file('photo');
        $path = $file->store('profile', 'public');

        $user->photo = $path;
        $user->save();
    }

    return response()->json([
        'photo_url' => asset('storage/' . $user->photo)
    ]);
}
}