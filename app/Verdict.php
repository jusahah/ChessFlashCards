<?php

namespace App;

use App\Position;
use Illuminate\Database\Eloquent\Model;

class Verdict extends Model
{
    //
    public $incrementing = true;
    public $guarded = [];
    protected $table = "verdicts";

    public function position()
    {
    	return $this->belongsTo(Position::class);
    }


}
