<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;


class LoginController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
        ->scopes(['openid', 'profile', 'email'])
        ->with(['access_type' => 'offline', 'prompt' => 'consent'])
        ->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // Create a new user if not exists
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => bcrypt(uniqid()), // Generate a random password
                    'google_id' => $googleUser->getId(),
                    'google_access_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken, // Store the refresh token if available
                    'google_token_expires_at' => now()->addSeconds($googleUser->expiresIn)
                ]);
            } else {
                // Update the user with the latest Google refresh token if not null
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'google_access_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken ?? $user->google_refresh_token,
                    'google_token_expires_at' => now()->addSeconds($googleUser->expiresIn)
                ]);
            }

            // Log the user in
            Auth::login($user);

            return redirect()->route('user.details', ['id' => $user->id]);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    private function checkAndRefreshGoogleToken($user)
    {
        try{
            if ($user->google_token_expires_at->isPast()) {
                $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                    'client_id' => config('services.google.client_id'),
                    'client_secret' => config('services.google.client_secret'),
                    'refresh_token' => $user->google_refresh_token,
                    'grant_type' => 'refresh_token',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $user->update([
                        'google_access_token' => $data['access_token'],
                        'goole_refresh_token' => $data['refresh_token'],
                        'google_token_expires_at' => now()->addSeconds($data['expires_in']),
                    ]);
                }
            }
            return redirect()->route('user.details', ['id' => $user->id]);
        }catch(Exception $e){
                return response()->json([
                    'error' => $e
                ]);
        }

    }

    public function userDetails($id)
    {
        try{
        $user = User::find($id);
        if($user){
        $this->checkAndRefreshGoogleToken($user);
        // return response()->json([
        //     'id' => $user->id,
        //     'name' => $user->name,
        //     'email' => $user->email,
        //     'google_id' => $user->google_id,
        //     'google_access_token' => $user->google_access_token,
        //     'google_refresh_token' => $user->google_refresh_token,
        //     'expires_at' =>  $user->google_token_expires_at,
        // ]);

        return view('user_details', compact('user'));
        }
        }catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function logout()
    {
        try {
            // Get the currently authenticated user
            $user = Auth::user();

            if ($user) {
                // Clear the Google tokens
                $user->update([
                    'google_access_token' => null,
                    'google_refresh_token' => null,
                    'google_token_expires_at' => null,
                ]);

                // Log the user out of the application
                Auth::logout();

                // Redirect to the homepage or login page
                return "logged out successfully";
            }

            return "doesn't have user logged in";
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

}




// ya29.a0AcM612wDEjV5SosuBEg3U3mAZDW1qAhwmwZOq12KFnGLrhbU4IONAJwffjOKZ5EHKd_mp49zyCtflIz_5qExhF2YEvYploXU9-WRT2jRFE7H4eWNi7ejrECYb2zRlhLNBzJmufUOnn-V4vgW38Mh5IyWUTwFpgrRJlewaCgYKARESARISFQHGX2MiPCdzMxEcdJjXzXvCjnEZxw0171