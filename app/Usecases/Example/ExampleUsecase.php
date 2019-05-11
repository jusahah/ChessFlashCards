<?php

namespace App\Usecases\Example;

use App\Usecases\Usecase;

class ExampleUsecase extends Usecase {


    // Type-hint dependencies you need
    /*
    public function __construct(UserRepository $users) {
        parent::__construct();
    }
    */  

    public function apply(string $msg): void 
    {
        
        \Log::info('ExampleUsecase runs: ' . $msg);

    }

}