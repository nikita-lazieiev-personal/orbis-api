<?php

namespace App\Services;

use App\Models\Share;
use Carbon\Carbon;
use DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class ShareService {

    public function fetchAll() {
        $user = JWTAuth::toUser(JWTAuth::parseToken());

        return Share::where('user_id', $user->id)->get(['id', 'amount', 'sign']);
    }

    public function getStats($date = null, $sign = null)
    {
        if(!$sign) {
            $sign = '$';
        }

        $user = JWTAuth::toUser(JWTAuth::parseToken());
        $query = $user->shares->where('sign', $sign);
        $comparingDate = null;

        if($date) {
            $carbonDate = Carbon::parse($date);
            $date = Carbon::parse($carbonDate)->format('Y-m-d H:i:s');

            $matchTimeline = $query
                ->filter(function($item) use ($carbonDate) {
                    $itemDate = Carbon::parse($item->created_at)->format('Y-m-d H:i:s');
                    return $carbonDate->gt($itemDate);
                })
                ->count()
            ;

            if($matchTimeline) {
                $comparingDate = $date;
            }
        }

        if(!$comparingDate) {
            $comparingDate = $query->max('created_at');
            $comparingDate = Carbon::parse($comparingDate)->format('Y-m-d H:i:s');
        }

        $shares = $query->where('created_at', '<=', $comparingDate);

        $absoleteDifference = 0;
        $relativeDifference = 0;
        $decimalPlaces = config('app.decimal_places');
        $totalAmount = $shares->pluck('amount')->sum();

        if($query->first()) {
            $firstAmount = $query->first()->amount;
            $absoleteDifference = $totalAmount - $firstAmount;
            $relativeDifference = number_format(($totalAmount / $firstAmount) * 100, $decimalPlaces);
        }

        return [
            'amount' => number_format($shares->sum('amount'), $decimalPlaces),
            'absoleteDiff' => $sign.$absoleteDifference,
            'relativeDiff' => "$relativeDifference%",
        ];
    }

    public function store($data) 
    {
        return Share::create($data);
    }

    public function update($data) 
    {
        return Share::find($data['id'])->update($data);
    }

    public function signsList() 
    {
        return DB::table('signs')->pluck('sign');
    }
}
