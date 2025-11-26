<?php

namespace App\Http\Controllers;

use App\Models\MemberSubscriptionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberSubscriptionDetailController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if(!$user) {
            return errorResponse('No data found', 200);
        }

        $details = MemberSubscriptionDetail::where('user_id', $user->id)->first();

        if(!$details) {
            MemberSubscriptionDetail::create([
                'user_id' => $user->id,
            ]);

            $details = MemberSubscriptionDetail::where('user_id', $user->id)->first();
        }

        return successResponse('success', 200, $details);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if(!$user) {
            return errorResponse('No data found', 200);
        }

        $details = MemberSubscriptionDetail::where('user_id', $user->id)->first();

        if(!$details) {
            MemberSubscriptionDetail::create([
                'user_id' => $user->id,
            ]);

            $details = MemberSubscriptionDetail::where('user_id', $user->id)->first();
        }

        $data = $request->all();
        $details->fill($data)->save();

        return successResponse('Update successful', 200, $details);
    }
}
