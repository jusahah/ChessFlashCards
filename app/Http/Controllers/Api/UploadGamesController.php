<?php

namespace App\Http\Controllers\Api;

use App\Game;
use App\GameSet;
use App\Http\Controllers\Controller;
use App\User;
use ChessZebra\Chess\Pgn\Splitter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UploadGamesController extends Controller
{

    public function upload(Request $request) 
    {

        \Log::info('UploadGamesController reached');

        $user = \Auth::guard('api')->user();
        $pgnsFile = $request->file('pgns');

        $originalFileName = $pgnsFile->getClientOriginalName();

        $stream = fopen($pgnsFile->getRealPath(), 'r');

        $pgns = collect([]);

        $splitter = new Splitter($stream, Splitter::SPLIT_GAMES);
        $splitter->split(function(string $buffer) use (&$pgns) {
            $pgns->push(trim((string)$buffer));
        });

        \Log::info('PGN games count is '.  $pgns->count());

        if ($pgns->count() < 1) {
            throw new \Exception('Did not find any PGN games in provided pgns input');
        }

        $gameset = GameSet::create([
            'user_id' => $user->id,
            'hash' => $originalFileName
        ]);

        $games = $pgns->map(function($pgn) use (&$gameset, &$user) {
            $game = Game::create([
                'user_id' => $user->id,
                'game_set_id' => $gameset->id,
                // This is not known yet, we must wait Lambda to process the game.
                'game_group_id' => null,
                'pgn' => $pgn,
                'processed_by_lambda' => false,
                'processed_by_user' => false
            ]);

            // TODO: Launch Lambda job

            return $game;
        });

        return response()->json([
            'game_set_id' => $gameset->id,
            'games_found' => $games->count(),
        ]);
    }
}
