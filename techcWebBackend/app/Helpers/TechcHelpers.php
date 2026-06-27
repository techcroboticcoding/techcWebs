<?php

use App\Models\User;

if (!function_exists('techc_user_from_request')) {
    function techc_user_from_request(\Illuminate\Http\Request $request)
    {
        $authorization = $request->header('Authorization');

        if ($authorization && str_contains($authorization, 'dummy-token-')) {
            $userId = trim(str_replace('Bearer dummy-token-', '', $authorization));

            if (is_numeric($userId)) {
                $user = User::find((int) $userId);
                if ($user) return $user;
            }
        }

        $userId = $request->header('X-User-Id')
            ?? $request->query('user_id')
            ?? $request->input('user_id');

        if ($userId && is_numeric($userId)) {
            $user = User::find((int) $userId);
            if ($user) return $user;
        }

        $email = $request->header('X-User-Email')
            ?? $request->query('email')
            ?? $request->input('email');

        if ($email) {
            $user = User::where('email', $email)->first();
            if ($user) return $user;
        }

        return null;
    }
}

if (!function_exists('techc_storage_url')) {
    function techc_storage_url($path)
    {
        if (!$path) return null;

        $path = trim($path);

        if (str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'http://')) {
            return str_replace('http://', 'https://', $path);
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'public/')) {
            $path = str_replace('public/', '', $path);
        }

        if (str_starts_with($path, 'storage/')) {
            $url = url($path);
        } else {
            $url = url('storage/' . $path);
        }

        return str_replace('http://', 'https://', $url);
    }
}

if (!function_exists('techc_photo_url')) {
    function techc_photo_url($path)
    {
        return techc_storage_url($path);
    }
}