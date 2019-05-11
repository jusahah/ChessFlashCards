<?php

namespace App;

use App\Attempt;
use App\Game;
use App\Move;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    //
	public $guarded = [];

    public function attemps()
    {
    	return $this->hasMany(Attempt::class);
    }

    public function games()
    {
    	return $this->belongsToMany(Game::class)->using(Move::class);
    }

}
