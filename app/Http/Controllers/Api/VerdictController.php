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

class VerdictController extends Controller
{

    public function remove(Request $request, int $id) 
    {

        $user = \Auth::guard('api')->user();

        $verdict = Verdict::findOrFail($id);

        // Check user owns the verdict

        if ($verdict->user_id !== $user->id) {
            return response('Can not remove foreign verdict', 403);
        }

        $verdict->delete();

        return response('', 204);

    }    
}
