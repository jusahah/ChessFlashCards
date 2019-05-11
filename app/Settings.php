<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    //
	public $guarded = [];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
