<?php

namespace App;

use App\Position;
use Illuminate\Database\Eloquent\Model;

class Attempt extends Model
{
    //
	public $guarded = [];
	
    public function position()
    {
    	return $this->belongsTo(Position::class);
    }
}
