<?php

namespace App;

use App\Game;
use Illuminate\Database\Eloquent\Model;

class GameSet extends Model
{
    //
	public $guarded = [];
	
    public function games()
    {
    	return $this->hasMany(Game::class);
    }
}
