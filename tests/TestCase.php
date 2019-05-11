<?php

namespace Tests;

use App\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function fakeUserHeaders(string $role = 'admin'): array 
    {
        // Create Admin User that we use when faking.
        $user = User::create([
            'role' => $role,
            'api_token' => 'testtoken'
        ]);


        return $this->fakeAlreadyCreatedUserHeaders($user);        
    }

    protected function fakeAlreadyCreatedUserHeaders(User $user, string $customToken = null): array 
    {
        $token = $customToken ? $customToken : $user->getApiKey();

        return [
            // Structure copied from frontend app
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ];    
    }

    protected function getDefaultFormData(): array 
    {
        
        // This should represent/match structure of real form data coming from frontend

        return [
            'smth' => 1,
            'smth2' => 1           
        ];

    }

     


}
