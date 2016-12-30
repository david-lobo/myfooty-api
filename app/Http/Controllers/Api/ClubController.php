<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\Competition;
use App\Models\Match;

class ClubController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $premierLeague = false;
        $perPage = 1000;
        if ($request->has('premier_league')) {
            $premierLeague = $request->input('premier_league');
            $premierLeague = ($premierLeague == 'true') ? true : false;
        }

        if ($premierLeague) {
            $teams = Team::where('premier_league', 1)
                ->orderBy('title', 'asc')
                ->simplePaginate($perPage);
        } else {
            $teams = Team::orderBy('title', 'asc')
                ->simplePaginate($perPage);
        }
        return response()->json([
            'status' => 'success',
            'data' => $teams
        ]);
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
        $team = Team::find($id);

        if ($team) {
            return response()->json([
                'status' => 'success',
                'data' => ['club' => $team]
            ]);
        }

        return $this->errorResponse(
            404,
            'Record not found',
            'Team not found with that id'
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
        //
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
