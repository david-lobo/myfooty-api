<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use JWTAuth;
use Tymon\JWTAuthExceptions\JWTException;
use App\User;
use Illuminate\Support\Facades\Hash;
use Validator;

class UserController extends Controller
{

     public function __construct()
    {
       // Apply the jwt.auth middleware to all methods in this controller
       // except for the authenticate method. We don't want to prevent
       // the user from retrieving their token if they don't already have it
       $this->middleware('jwt.auth', ['except' => ['login', 'registerDevice']]);
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
           'apns_token' => 'max:1000|min:10',
       ]);
   }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // TODO - don't expose device id to all users
        $user = User::find($id);

        if ($user) {
            return response()->json([
                'status' => 'success',
                'data' => ['user' => $user]
            ]);
        }

        return response()->json(
            [
                'code' => 404,
                'message' => 'Record not found',
                'description' => 'User not found with that id'
            ],
            404
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $authUser = JWTAuth::toUser();
        $user = User::find($id);
        $error = array();

        if (!$authUser) {
            $code = 404;
            $error['code'] = $code;
            $error['message'] = 'Not authenticated';
            return response()->json($error, $code);
        }

        if (!$user) {
            $code = 404;
            $error['code'] = $code;
            $error['message'] = 'User not found';
            return response()->json($error, $code);
        }

        if ($user->id !== $authUser->id) {
            $code = 404;
            $error['code'] = $code;
            $error['message'] = 'Not authorized';
            return response()->json($error, $code);
        }

        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            $code = 400;
            $error['code'] = $code;
            $error['message'] = 'The request contains invalid or missing parameters';
            $error['errors]'] = $validator->messages();
            return response()->json($error, 400);
        }

        // Only using PATCH for partial updates
        $method = strtoupper($request->method());
        if ($method !== 'PATCH') {
            $code = 405;
            $error['code'] = $code;
            $error['message'] = "Method '{$method}' not allowed";
            return response()->json($error, $code);
        }

        $isUserUpdated = false;

        // Update the APNS token attribute
        if ($apnsToken = $request->input('apns_token')) {
            // save access_token for the user
            $user->apns_token = $apnsToken;
            $isUserUpdated = true;
        }

        // Update the team alias attribute
        if ($teamAlias = $request->input('team_alias')) {
            $team = \App\Models\TeamConfig::where('title_normalised', $teamAlias)->first();
            if (!$team) {
                $code = 400;
                $error['code'] = $code;
                $error['message'] = 'The request contains invalid or missing parameters';
                $error['errors]'] = $validator->messages();
                return response()->json($error, 400);
            } else {
                $user->team_alias = $teamAlias;
                $isUserUpdated = true;
            }
        }

        if ($isNotificationsEnabled = $request->input('notifications_enabled')) {
            if (strtoupper($isNotificationsEnabled) == 'TRUE') {
                $user->is_notifications_enabled = 1;
                $isUserUpdated = true;
            }
            if (strtoupper($isNotificationsEnabled) == 'FALSE') {
                $user->is_notifications_enabled = 0;
                $isUserUpdated = true;
            }
        }

        if ($isUserUpdated) {
            $user->save();
        }

        return response()->json([
                'status' => 'success',
                'data' => ['user' => $user]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
