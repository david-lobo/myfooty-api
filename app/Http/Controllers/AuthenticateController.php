<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use JWTAuth;
use Tymon\JWTAuthExceptions\JWTException;
use App\User;
use Illuminate\Support\Facades\Hash;
use Validator;
use Illuminate\Foundation\Auth\ThrottlesLogins;

class AuthenticateController extends Controller
{
    use ThrottlesLogins;
    public $username = "";

    public function loginUsername() {
        return $this->username;
    }

    public function username() {
        return $this->username;
    }

    /**
     * Determine if the class is using the ThrottlesLogins trait.
     *
     * @return bool
     */
    protected function isUsingThrottlesLoginsTrait()
    {
        return in_array(
            ThrottlesLogins::class, class_uses_recursive(get_class($this))
        );
    }

     /**
    * Get a validator for an incoming registration request.
    *
    * @param  array  $data
    * @return \Illuminate\Contracts\Validation\Validator
    */
   protected function validator(array $data)
   {
       return Validator::make($data, [
           //'name' => 'required|max:255',
           'device_id' => 'required|max:255|min:20',
       ]);
   }

    public function __construct()
    {
       // Apply the jwt.auth middleware to all methods in this controller
       // except for the authenticate method. We don't want to prevent
       // the user from retrieving their token if they don't already have it
       $this->middleware('jwt.auth', ['except' => ['login', 'registerDevice']]);
    }

    public function index()
    {
        // Retrieve all the users in the database and return them
        $users = User::all();
        return $users;
    }

    public function registerDevice(Request $request)
    {
        //sleep(5);
        $deviceId = $request->input('device_id');
        $validator = $this->validator($request->all(), 'register-device');
        if ($validator->fails()) {
            $responseBody = [
                'message' => 'The request contains invalid or missing parameters',
                'errors' => $validator->messages()
            ];
            return response()->json($responseBody, 400);
        }

        // Check if device is already registered
        $user = User::where('device_id', '=', $deviceId)->first();

        // Create new user if device is not already registered
        if (!$user) {
            $input = $request->all();
            $user = User::create($input);
        }

        // Create an access token for the device
        try {
            if (!$user || !$token = JWTAuth::fromUser($user)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        // save access_token for the user
        $user->access_token = $token;
        $user->save();

        // if no errors are encountered we can return a JWT
        //return response()->json(['access_token' => $token]);
        return response()->json([
            'data' => [
                'access_token' => $token,
                'user' => $user,
            ]
        ]);
    }

    public function getAuthenticatedUser()
    {
        try {

            if (!$user = JWTAuth::parseToken()->authenticate()) {
                /*$error = [
                    'code' => 404,
                    'message' => 'user_not_found',
                    'description' => 'User does not exist'
                ];

                return response()->json($error, 404);*/
                return response()->json(['user_not_found'], 404);
            }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }

       //$user2 = JWTAuth::toUser();
       //var_dump($user2->id);

        // the token is valid and we have found the user via the sub claim
        return response()->json(compact('user'));
    }
}
