<?php

namespace App\Http\Controllers\Api;

use App\Game;
use App\GameSet;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GamesController extends Controller
{

    public function unprocessedGames(Request $request) 
    {

        $user = \Auth::guard('api')->user();

        $games = Game::where('user_id', $user->id)
            ->where('processed_by_user', false)
            ->orderBy('id', 'asc')
            ->get();

        // TODO: Fractal    
        return $games;    


    }

    public function reject(Request $request, int $id) 
    {

        $user = \Auth::guard('api')->user();
        $game = Game::findOrFail($id);

        if ($game->user_id !== $user->id) {
            throw new \Exception('Can not reject Game that is not owned by user');
        }

        $game->delete();

        return response('', 204);  


    }    
}
