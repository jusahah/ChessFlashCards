<?php

namespace App\Http\Transformers;

use App\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    protected $availableIncludes = [

    ];

    public function transform(User $user)
    {
        return [
            'id'    => (int) $user->getId(),
            'name'  => $user->getName(),
            'email' => $user->getEmail()
        ];
    }

}