<?php

namespace App\Usecases;

abstract class Usecase {

    public function __construct() {} 

    public static function create(): Usecase 
    {
        $class = get_called_class();
        // Call Container to auto-inject whatever is needed.
        return app($class);
    }  
    /*
    public function ensureOrderNotBusy(Order $order): void 
    {
        // PRECONDITION #1: Order is not 'busy'; it has no operations currently running/waiting.
        // If this Order already has Task that is being applied/queued, then
        // either that Task must run or admin must manually clear it. Can not
        // run new Task until old one is taken care of.

        if ($order->isProcessingAlready()) {
            throw new OrderIsBusy(
                "Operation on Order rejected - Order is in state (status) that does not allow new operations"
            );            
        }

        if ($order->hasTaskScheduled()) {
            throw new OrderAlreadyWaitingOnTaskException(
                "Operation on Order rejected - resolve/remove pending or scheduled operation first"
            );
        }    
    }
    */


}
