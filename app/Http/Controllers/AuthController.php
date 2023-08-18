<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);
        $credentials = $request->only('email', 'password');
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials', 'token'=>'not'], 422);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token', 'token'=>'not'], 500);
        }
        $user = User::where('email', $request->email)->first();
        return response()->json(['token' => $this->createNewToken($token), 'error' => 'no'], 200);
    }

    public function register(Request $request)
    {
        $validate = $this->validator($user = $request->all());
        if($validate->fails()){
            return response()->json(['error'=>$validate->errors()->toJson(), 'token'=>'not'], 400);
        }
        if (!$token = JWTAuth::fromUser($this->create($user))) {
            return response()->json(['token'=>'not', 'error' => 'Invalid Field'], 401);
        }
        return response()->json(['token' => $this->createNewToken($token), 'error' => 'no'], 200);
    }

    protected function validator(array $data){
        return Validator::make($data, [
            'name' => ['required'],
            'email' => ['required'],
            'password' => ['required'],
        ]);
    }

    //re-> eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9yZWdpc3RlciIsImlhdCI6MTY5MjI3MzE1NiwiZXhwIjoxNjkyMjc2NzU2LCJuYmYiOjE2OTIyNzMxNTYsImp0aSI6IjFvOFFPbnVjREtDaFJEdzYiLCJzdWIiOjEsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.BwCJn5oGhoyyBXgW2lOroDXVuV85Xqyr13CpVWS9-hg
    //lo-> eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9sb2dpbiIsImlhdCI6MTY5MjI3MzM4MSwiZXhwIjoxNjkyMjc2OTgxLCJuYmYiOjE2OTIyNzMzODEsImp0aSI6Iml5SU0yMUJGTWltcHVDaEgiLCJzdWIiOjEsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.2j8Tj6KbLJOn2BSkzRlUETqM5-fbu6_1Rspg-Kg36TM

    protected function create(array $data)
    {
        $name = $data['name'];
        $email = $data['email'];
        $password = Hash::make($data['password']);
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);
    }

    public function logout()
    {
        $this->guard()->logout();
        return response()->json(['message' => 'Successfully logged out'], 200);
    }

    public function refresh() {
        $token = $this->createNewToken(JWTAuth::refresh());
        return response()->json(['token' => $token, 'error' => 'no'], 200);
    }

    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            // 'expires_in' => auth('api')->factory()->getTTL() * 60 * 12 * 30 * 365,
        ]);
    }

    public function guard()
    {
        return Auth::guard('api');
    }
}
