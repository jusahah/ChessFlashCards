<?php

namespace App\Http\Controllers\Api;

use App\Game;
use App\GameSet;
use App\Http\Controllers\Controller;
use App\Position;
use App\User;
use App\Verdict;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PositionController extends Controller
{

    public static $verdictMapping = [
        '-1' => 'bad',
        '0' => 'neutral',
        '1' => 'good'
    ];

    public static $verdictInverseMapping = [
        'bad' => '-1',
        'neutral' => '0',
        'good' => '1'
    ];

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

    public function getVerdicts(Request $request)
    {
        $user = \Auth::guard('api')->user();
        $fens = $request->input('fens');

        \Log::info('Get verdicts for fens');
        \Log::info($fens);

        return Position::where('user_id', $user->id)->whereIn('fen', $fens)->get()->map(function($p) {

            $verdicts = $p->verdicts;
            $betterMoves = $p->bettermoves;
            $fen = $p->fen;

            return [
                'fen' => $p->fen,
                'verdicts' => $verdicts->map(function($v) use ($p) {
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

            /*

            if ($verdicts->count()) {

                return $verdicts->map(function($v) use ($p) {
                    return [
                        'fen' => $p->fen,
                        'move' => $v->move,
                        'verdict' => static::$verdictInverseMapping[$v->verdict],
                        'bettermoves' => $betterMoves
                    ];

                });

                
            }

            return null;
            */

        })->filter();
    }

    public function storeVerdict(Request $request) 
    {

        $user = \Auth::guard('api')->user();

        $verdict = $request->input('verdict');
        $move = $request->input('move'); // a1a2
        $fen = $request->input('fen');

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
            $matchingVerdict->verdict = static::$verdictMapping[(string)$verdict];
            $matchingVerdict->save();
        } else {
            $matchingVerdict = Verdict::create([
                'user_id' => $user->id,
                'position_id' => $p->id,
                'verdict' => static::$verdictMapping[(string)$verdict],
                'move' => $move
            ]);
        }

        $p = $p->fresh();

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
}
