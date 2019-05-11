<?php

namespace App;

use App\GameGroup;
use App\GameSet;
use App\Move;
use App\Position;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Game extends Model
{
    //
	public $guarded = [];

	/*
	* This is used to generate hash id that works as primary key
	*/
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
        	// This also provides protection towards duplicate game submission
            $model->hash = Hash::make($model->pgn);
        });
    }

	public function gameGroup()
	{
		return $this->belongsTo(GameGroup::class);
	}

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
