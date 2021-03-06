<?php

namespace App;

use App\Game;
use App\User;
use Illuminate\Database\Eloquent\Model;

class GameSet extends Model
{
    //
	public $guarded = [];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

    public function games()
    {
    	return $this->hasMany(Game::class);
    }
}
