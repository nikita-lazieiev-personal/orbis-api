<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\RequestTrait;
use App\Services\ShareService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;

class ShareController extends Controller
{
    use RequestTrait;
    
    public function index(Request $request, ShareService $shareService) {
        $success['shares'] = $shareService->fetchAll();
        
        return $this->sendResponse($success, 'list of shares', 200);
    }

    public function store(Request $request, ShareService $shareService)
    {
        $input = $request->only('amount', 'sign');

        $validator = Validator::make($input, [
            'amount' => 'required|numeric',
            'sign' => 'required|exists:signs,sign',
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors(), 'Validation Error', 422);
        }

        $user = JWTAuth::toUser(JWTAuth::parseToken());
        $input['user_id'] = $user->id;

        $share = $shareService->store($input);
        $success['share'] = $share;

        return $this->sendResponse($success, 'share added successfully', 201);
    }

    public function update(Request $request, ShareService $shareService)
    {
        $input = $request->only('id', 'amount', 'sign');

        $validator = Validator::make($input, [
            'id' => 'required|numeric|exists:shares,id',
            'sign' => 'required|exists:signs,sign',
            'amount' => 'required|numeric',
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors(), 'Validation Error', 422);
        }

        $share = $shareService->update($input);
        $success['success'] = $share;

        return $this->sendResponse($success, 'share updated successfully', 200);
    }

    public function getStats(Request $request, ShareService $shareService)
    {
        $input = $request->only('date', 'sign');

        $validator = Validator::make($input, [
            'date' => 'sometimes|date',
            'sign' => 'sometimes|exists:signs,sign',
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors(), 'Validation Error', 422);
        }

        $date = $input['date'] ?? null;
        $sign = $input['sign'] ?? null;

        $shares = $shareService->getStats($date, $sign);
        $success['shares'] = $shares;

        return $this->sendResponse($success, 'shares stats', 200);
    }

    public function signsList(Request $request, ShareService $shareService) {
        $success['signs'] = $shareService->signsList();

        return $this->sendResponse($success, 'list of available signs', 200);
    }
}