<?php

namespace App\Http\Controllers\Api;

use App\Game;
use App\GameSet;
use App\Http\Controllers\Controller;
use App\Http\Resources\PositionResource;
use App\Position;
use App\User;
use App\Verdict;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PositionController extends Controller
{

    /*
    public function addBetterMove(Request $request)
    {
        $user = \Auth::guard('api')->user();
        $move = $request->input('move'); // a1a2
        $fromto = $request->input('fromto');
        $fen = $request->input('fen');

        $p = Position::where('user_id', $user->id)->where('fen', $fen)->first();

        if (!$p) {
            $p = Position::create([
                'user_id' => $user->id,
                'is_white_to_move' => explode(' ', $fen)[1] === 'w',
                'fen' => $fen
            ]);
        }

        if (!$p->best_move) {
            $p->best_move = $move . '|' . $fromto;
        } else {
            throw new \Exception('Maximum number of better moves is 1 and has been reached');
        }

        $p->save();

        return [
            'fen' => $p->fen,
            'verdicts' => $p->verdicts->map(function($v) use ($p) {
                return [
                    'fen' => $p->fen,
                    'move' => $v->move,
                    'verdict' => static::$verdictInverseMapping[$v->verdict]
                ];

            }),
            'bettermoves' => collect([$p->best_move])
                ->filter()->map(function($bm) use ($fen) {
                    return [
                        'fromto' => explode('|', $bm)[1],
                        'move' => explode('|', $bm)[0],
                        'fen' => $fen
                    ];
                })

        ];

    }
    */

    public function positionsFromFens(Request $request)
    {
        $user = \Auth::guard('api')->user();
        $fens = $request->input('fens');

        \Log::info('Get verdicts for fens');
        \Log::info($fens);

        $positions = Position::with(['verdicts'])->where('user_id', $user->id)->whereIn('fen', $fens)->get();

        return PositionResource::collection($positions);
    }

    public function trainable(Request $request)
    {
        $user = \Auth::guard('api')->user();

        $positions = Position::with(['verdicts', 'attemps'])
            ->where('user_id', $user->id)
            ->where('training_enabled', true)
            ->orderBy('id', 'desc')
            ->limit(1000)
            ->get();

         return PositionResource::collection($positions);
    }

    public function enableTraining(Request $request)
    {
        $user = \Auth::guard('api')->user();
        $fen = $request->input('fen');

        $position = Position::where('user_id', $user->id)->where('fen', $fen)->firstOrFail();

        $position->training_enabled = true;
        $position->save();

        return new PositionResource($position);       
    }

    public function disableTraining(Request $request)
    {
        $user = \Auth::guard('api')->user();
        $fen = $request->input('fen');

        $position = Position::where('user_id', $user->id)->where('fen', $fen)->firstOrFail();

        $position->training_enabled = false;
        $position->save();

        return new PositionResource($position);       
    }

    /*
    public function getVerdicts(Request $request)
    {
        $user = \Auth::guard('api')->user();
        $fens = $request->input('fens');

        \Log::info('Get verdicts for fens');
        \Log::info($fens);

        return Position::with(['verdicts'])->where('user_id', $user->id)->whereIn('fen', $fens)->get()->map(function($p) {

            return [
                'fen' => $p->fen,
                'verdicts' => $p->verdicts->map(function($v) use ($p) {
                    return [
                        'fen' => $p->fen,
                        'move' => $v->move,
                        'verdict' => static::$verdictInverseMapping[$v->verdict]
                    ];

                })

            ];

        })->filter();
    }
    */

    public function storeVerdict(Request $request) 
    {

        $user = \Auth::guard('api')->user();

        $verdict = $request->input('verdict');
        $move = $request->input('move'); // a1a2
        $san = $request->input('san');
        $fen = $request->input('fen');
        $trainable = $request->input('trainable');

        \Log::info('Storing verdict for fen ' . $fen);
        
        $p = Position::where('user_id', $user->id)->where('fen', $fen)->first();

        if (!$p) {
            $p = Position::create([
                'user_id' => $user->id,
                'is_white_to_move' => explode(' ', $fen)[1] === 'w',
                'fen' => $fen
            ]);
        }


        $verdicts = $p->verdicts;

        $matchingVerdict = $verdicts->first(function($v) use ($move) {
            return $v->move === $move;
        });

        if ($matchingVerdict) {
            $matchingVerdict->verdict = $verdict;
            $matchingVerdict->trainable = !!$trainable;
            $matchingVerdict->save();
        } else {
            $matchingVerdict = Verdict::create([
                'user_id' => $user->id,
                'position_id' => $p->id,
                'verdict' => $verdict,
                'move' => $move,
                'san' => $san,
                'trainable' => !!$trainable
            ]);
        }

        $p = $p->fresh();
        $p->load('verdicts');

        return new PositionResource($p);
        /*
        return [
            'fen' => $p->fen,
            'verdicts' => $p->verdicts->map(function($v) use ($p) {
                return [
                    'fen' => $p->fen,
                    'move' => $v->move,
                    'san' => $v->san,
                    'verdict' => $v->verdict
                ];

            })
        ];
        */

    }    
}
