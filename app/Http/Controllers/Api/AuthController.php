<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class AuthController extends Controller
{

    protected static $usedProviders = ['google'];

    /**
     * Generate URL that client will be redirected into.
     *
     * @return \Illuminate\Http\Response
     */
    /*
    public function redirectToProvider(Request $request, $provider)
    {
        // For now I want only Github. Change is $usedProviders.
        if ( ! in_array($provider, static::$usedProviders)) {
            throw new \Exception('Login provider not supported');
        }

        // Return URL to front-end. As we are using Vue SPA, we do not want to
        // redirect, instead just return url and frontend can do whatever it wants.
        return [
            'url' => Socialite::driver($provider)->stateless()->redirect()->getTargetUrl()
        ];
    }
    */

    /**
     * Obtain the user information from provider.
     *
     * @return \Illuminate\Http\Response
     */
    /*
    public function handleProviderCallback(Request $request, $provider)
    {   
        // Request contains code-attribute. Socialite takes care of handling it.

        // For now I want only Google. Change in $usedProviders.
        if ( ! in_array($provider, static::$usedProviders)) {
            throw new \Exception('Login provider not supported (callback)');
        }        

        // Using 'stateless()' to prevent session setup.
        $user = Socialite::driver($provider)->stateless()->user();

        // We'll match user using his Google (etc) email.
        $email = $user->getEmail();

        try {
            $user = User::where('email', $email)->firstOrFail();
        } catch (ModelNotFoundException $e) {

            $name = $user->getName();
            // Create new User
            $user = CreateUser::create()->apply([
                'email' => $email,
                'name' => $name,
                'role' => 'denied' // Admin must enable this user manually
            ]);

            // Return special view to inform client his account has been created but
            // is pending enablation.
            return view('oauth/pending-registration', [
                'user' => $user
            ]);
        }

        // Generate api key
        $token = str_random(48);
        // Update User with api key
        $this->users->update($user, [
            'api_token' => $token
        ]);
        
        // Return special view that simply posts api key (token) to parent window
        // that is Vue app, and then this view closes itself
        return view('oauth/callback', [
            'token' => $token
        ]);
    }
    */

    public function logout(Request $request) 
    {

        $user = \Auth::guard('api')->user();

        // Update User with api key set to null (user has logged out)
        $user->update([
            'api_token' => null
        ]);

        return response([
            'deleted' => true
        ], 200);
               
    }

    public function validateToken(Request $request) 
    {
        // PRECONDITION: Auth middleware is set in routes-file for this route.

        // If we got this far the api key is valid (middleware did not throw)

        $user = \Auth::guard('api')->user();

        // Return user id to UI
        return response([
            'user_id' => $user->getId(),
            'username' => $user->getName(),
            // UI can use this to decide what menu items to show/hide etc.
            'role' => $user->getRole()
        ], 200);
    }
}
