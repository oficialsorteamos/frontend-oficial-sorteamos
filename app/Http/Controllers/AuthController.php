<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Management\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Management\User;
use Illuminate\Support\Facades\Log;
use Validator;

class AuthController extends Controller
{
    /**
    * Create user
    *
    * @param  [string] name
    * @param  [string] email
    * @param  [string] password
    * @param  [string] password_confirmation
    * @return [string] message
    */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'username' => 'required|string',
            'email'=>'required|string|unique:users',
            'password'=>'required|string',
            'c_password' => 'required|same:password'
        ]);

        $user = new User([
            'name'  => $request->name,
            'username'  => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        if($user->save()){
            $tokenResult = $user->createToken('Personal Access Token');
            $token = $tokenResult->plainTextToken;

            return response()->json([
            'message' => 'Successfully created user!',
            'accessToken'=> $token,
            ],201);
        }
        else{
            return response()->json(['error'=>'Provide proper details']);
        }
    }

    /**
    * Login user and create token
    *
    * @param  [string] email
    * @param  [string] password
    * @param  [boolean] remember_me
    */

    public function login(Request $request)
    {
        $userController = new UserController();
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            //'remember_me' => 'boolean'
        ]);

        //$credentials = request(['email','password']);
        $credentials = ['email' => $request->email, 'password' => $request->password, 'status' => 'A'];
        if(!Auth::attempt($credentials))
        {
            return response()->json([
                'error' => 'Unauthorized'
            ],500);
        }

        //Se o usuário estiver inativado
        if($request->user()->status == 'I') {
            return response()->json([
                'error' => 'Unauthorized'
            ],500);
        }


        $user = $request->user();
        $user = $userController->getUserRole($user->id);
        $tokenResult = $user->createToken('Personal Access Token');
        $user = $userController->show($user->id);
        
        $userData = json_encode($user);
        $userData = json_decode($userData, true);

        //Pega os dados do novo contato
        $user = $userData['original'];

        //$user['ability'] = array(['action' => 'manage', 'subject' => 'all']);
        $user['ability'] = $userController->getUserPermissions($user['id']);
        $token = $tokenResult->plainTextToken;


        return response()->json([
            'accessToken' =>$token,
            'token_type' => 'Bearer',
            'role' => $user['roles'][0]['id'],
            'userData' => $user,
        ]);
    }

    /**
     * Get the authenticated User
    *
    * @return [json] user object
    */
    public function user(Request $request)
    {
        return response()->json($request->user(), 200);
    }

    /**
     * Logout user (Revoke the token)
    *
    * @return [string] message
    */
    public function logout(Request $request)
    {
        $user = $request->user();
        //Deleta o token associado ao acesso atual do usuário
        $request->user()->currentAccessToken()->delete();
        //$request->user()->tokens()->delete(); //Remove todos os tokens associados ao usuário

        return response()->json([
        'message' => 'Successfully logged out'
        ]);
    }
}