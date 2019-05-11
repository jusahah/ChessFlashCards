<?php

namespace App;

use App\GameSet;
use App\Move;
use App\Position;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    //
	public $incrementing = false;
	public $guarded = [];

	public function gameSet()
	{
		return $this->belongsTo(GameSet::class);
	}

	public function moves()
	{
		return $this->hasMany(Move::class);
	}

	public function positions()
	{
		return $this->belongsToMany(Position::class)
			->using(Move::class)
			->withPivot([
                'verdict',
                'eval'
            ]);;
	}
}
