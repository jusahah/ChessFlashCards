<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/*
* Call passed-in Usecase via queue (potentially async depending on queue driver).
*/
class DelayedUsecaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    // IMPORTANT! We want to guarantee this will not be retried right away
    public $tries = 1;

    protected $usecaseClass;
    protected $args;

    public function __construct(string $usecaseClass, array $args = [])
    {
        // Note. These variables need to be serializable. So only trivial data.
        $this->usecaseClass = $usecaseClass;
        $this->args = $args;
    }

    public function handle()
    {

        $usecase = $this->usecaseClass::create();

        \Log::info('Calling delayed usecase ' . get_class($usecase));
        
        // Call usecase with provided args.
        $usecase->apply(...$this->args);
    }
}
