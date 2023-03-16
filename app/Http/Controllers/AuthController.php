<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\RequestTrait;
use App\Services\ShareService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    use RequestTrait;

    public function register(Request $request, ShareService $shareService)
    {
        $input = $request->only('name', 'email', 'password', 'c_password', 'shares_amount', 'shares_sign');

        $validator = Validator::make($input, [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'c_password' => 'required|same:password',
            'shares_amount' => 'sometimes|numeric',
            'shares_sign' => 'required|exists:signs,sign',
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors(), 'Validation Error', 422);
        }

        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['user'] = $user;

        if(isset($input['shares_amount']) && isset($input['shares_sign'])) {
            $shareService->store([
                'amount' => $input['shares_amount'],
                'sign' => $input['shares_sign'],
                'user_id' => $user->id,
            ]);
        }

        return $this->sendResponse($success, 'user registered successfully', 201);

    }

    public function login(Request $request)
    {
        $input = $request->only('email', 'password');

        $validator = Validator::make($input, [
            'email' => 'required',
            'password' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors(), 'Validation Error', 422);
        }

        try {
            if (! $token = JWTAuth::attempt($input)) {
                return $this->sendError([], "invalid login credentials", 400);
            }
        } catch (JWTException $e) {
            return $this->sendError([], $e->getMessage(), 500);
        }

        $success = [
            'token' => $token,
        ];
        return $this->sendResponse($success, 'successful login', 200);
    }

    public function logout(Request $request)
    {
        JWTAuth::parseToken()->invalidate(true);
        return $this->sendResponse('success', 'successful logout', 200);
    }
}