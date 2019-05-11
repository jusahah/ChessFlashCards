<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Fractal\FractalController;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UserCreationRequest;
use App\Http\Transformers\UserTransformer;
use App\Repositories\UserRepository;
use App\Usecases\User\CreateUser;
use App\Usecases\User\UpdateUser;
use App\User;
use Illuminate\Http\Request;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class UserController extends FractalController
{
    protected $users;

    public function __construct(UserRepository $users) {

        parent::__construct();

        $this->users = $users;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $users = $this->users->all();

        $resource = new Collection($users, new UserTransformer, 'user');
        return $this->fractal->createData($resource)->toArray(); 
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\UserCreationRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function store(UserCreationRequest $request)
    {
        $user = CreateUser::create()->apply($request->only(['email', 'name', 'role']));

        $resource = new Item($user, new UserTransformer, 'user');

        return response(
            $this->fractal->createData($resource)->toArray(), 
            201 // HTTP 201 Created
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateUserRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function partialUpdate(UpdateUserRequest $request, $id)
    {

        UpdateUser::create()->apply($id, array_only($request->all(), [
            // For only now role can be changed. Add column names here
            // to allow more attributes to be edited.
            'role'
        ])); 

        return response(
            ['updated' => $id], 
            200
        );
    }
}
