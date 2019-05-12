<?php

use App\Game;
use App\GameGroup;
use App\GameSet;
use App\Helpers\FenAnalyzer;
use App\Move;
use App\Position;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        if (\App::environment('local')) {
          // Dev-login from UI is hard-coded to use this token
          // Speeds up things a bit in dev
          $jussiToken = '123456';
        } else {
          $jussiToken = null;
        }
        
        $user = User::create([
          'name' => 'Jussi',
          'email' => 'jussiahamalainen@gmail.com',
          'api_token' => $jussiToken,
          'role' => 'admin',
        ]);

        return;


        // Create gameset

        $set = GameSet::create([
        	'user_id' => $user->id,
        	'hash' => Hash::make('1. e4 e5 2. Nf3 Nc6 3. Bb5 a6 4. Ba4 Nf6 5. O-O')
        ]);

        // Create example game group
        $exampleGameGroup = GameGroup::create([
        	'user_id' => $user->id,
        	'name' => 'Ruy Lopez Opening',
        	'first_n_moves' => '1. e4 e5 2. Nf3 Nc6 3. Bb5',
        	'am_i_white' => true
        ]);

        // Create example game
        $exampleGame = Game::create([
        	'white' => 'Jaakko Testaaja',
        	'black' => 'Mikko Testaaja',
        	'result' => rand(0, 10) > 5 ? '1-0' : '1/2',
        	'game_set_id' => $set->id,
        	'game_group_id' => $exampleGameGroup->id,
        	'user_id' => $user->id,
        	'pgn' => '1. e4 e5 2. Nf3 Nc6 3. Bb5 a6 4. Ba4 Nf6 5. O-O'
        ]);

        $exampleGameFens = collect([
        	'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1',
        	'rnbqkbnr/pppppppp/8/8/4P3/8/PPPP1PPP/RNBQKBNR b KQkq e3 0 1',
        	'rnbqkbnr/pppp1ppp/8/4p3/4P3/8/PPPP1PPP/RNBQKBNR w KQkq e6 0 2',
        	'rnbqkbnr/pppp1ppp/8/4p3/4P3/5N2/PPPP1PPP/RNBQKB1R b KQkq - 1 2',
        	'r1bqkbnr/pppp1ppp/2n5/4p3/4P3/5N2/PPPP1PPP/RNBQKB1R w KQkq - 2 3',
        	'r1bqkbnr/pppp1ppp/2n5/1B2p3/4P3/5N2/PPPP1PPP/RNBQK2R b KQkq - 3 3',
        	'r1bqkbnr/1ppp1ppp/p1n5/1B2p3/4P3/5N2/PPPP1PPP/RNBQK2R w KQkq - 0 4',
        	'r1bqkbnr/1ppp1ppp/p1n5/4p3/B3P3/5N2/PPPP1PPP/RNBQK2R b KQkq - 1 4',
        	'r1bqkb1r/1ppp1ppp/p1n2n2/4p3/B3P3/5N2/PPPP1PPP/RNBQK2R w KQkq - 2 5',
        	//'r1bqkb1r/1ppp1ppp/p1n2n2/4p3/B3P3/5N2/PPPP1PPP/RNBQ1RK1 b kq - 3 5'
        ]);

        $exampleMoves = collect([
        	'e4',
        	'e5',
        	'Nf3',
        	'Nc6',
        	'Bb5',
        	'a6',
        	'Ba4',
        	'Nf6',
        	'O-O'
        ]);

        if ($exampleGameFens->count() !== $exampleMoves->count()) {
        	throw new \Exception('Seed prevented: exampleGameFens vs. exampleMoves lenghts differ');
        }

        $halfMoveNum = 1;

        $exampleGameFens->each(function($fen) use ($exampleGame, &$exampleMoves, &$halfMoveNum) {
	        $position = Position::create([
	        	'fen' => $fen,
	        	'is_white_to_move' => FenAnalyzer::whoIsToMove($fen) === 'w',
	        ]);

	        // Pivot model
	        $move = Move::create([
	        	'game_id' => $exampleGame->id,
	        	'position_id' => $position->id,
	        	'move' => $exampleMoves->shift(),
	        	'half_move_num' => $halfMoveNum
	        ]);

	        $halfMoveNum++;
        });

    }
}
