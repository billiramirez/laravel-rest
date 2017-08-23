<?php

namespace App\Http\Controllers;

use App\Meeting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests;


class MeetingController extends Controller
{

    public function __construct()
    {
        // $this->middleware('name');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {



        $meetings = Meeting::all();
        foreach ($meetings as $meeting){
            $meeting->view_meeting = [
                'href' => 'api/v1/meeting/' . $meeting->id,
                'method' => 'GET'
            ];
        }
        $response = [
            'msg' => 'List of all Meetings',
            'meeting' => $meeting
            ];


        return $response()->json($response, 200);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $this->validate($request, [
            'title' => 'required',
            'description' => 'required',
            'time' => 'required|date_format:YmdHie',
            'user_id' => 'required'
        ]);

        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        $user_id = $request->input('user_id');

        $meeting = new Meeting([
            'time' => Carbon::createFromFormat('YmdHie', $time),
            'title' => $title,
            'description' => $description
        ]);

        if ($meeting->save())
        {
            $meeting->users()->attach($user_id);
            $meeting->view_meeting = [
                'href' => 'api/v1/meeting/1',
                'method' => 'GET'
            ];

            $message = [
                'msg' =>'Meeting Created',
                'meeting' => $meeting
            ];

            return response()->json($message, 201);
        }

        $response = [
            'msg' => 'Error occurred',
            'meeting' => $meeting
        ];

        return response()->json($response, 404);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $meeting = Meeting::with('users')->where('id', $id)->firstOrFail();
        $meeting->view_meeting = [
            'href' => 'api/v1/meeting',
            'method' => 'GET'
        ];

        $response = [
            'msg' => 'Meeting Information',
            'meeting' => $meeting
        ];

        return response()->json($response, 200);
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
        $this->validate($request, [
            'title' => 'required',
            'description' => 'required',
            'time' => 'required|date_format:YmdHie',
            'user_id' => 'required'
        ]);


        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        $user_id = $request->input('user_id');


        $meeting = Meeting::with('users')->findOrFail($id);

        if (!$meeting->users()->where('users.id', $user_id)->first())
        {
            return response()->json(['msg' => 'User not registered for meeting, update no successful'], 401);
        }
        $meeting->time =  Carbon::createFromFormat('YmdHie', $time);
        $meeting->title = $title;
        $meeting->description = $description;

        if (!$meeting->update())
        {
            return response()->json(['msg'=> 'Error during updating'], 404);
        }

        $meeting->view_meeting = [
          'href' => 'api/v1/meeting' . $meeting->id,
            'method' => 'GET'
        ];

        $response = [
            'msg' => 'Meeting updated',
            'meeting' => $meeting
        ];

        return response()->json($response, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $meeting = Meeting::findOrFail($id);
        $users = $meeting->users;
        $meeting->users->detach();
        if (!$meeting->delete())
        {
            foreach ($users as $user) {
                $meeting->users()->attach($user);
            }

            return response()->json(['msg' => 'Deletion Failed'], 404);
        }

        $response = [
            'msg' => 'Meeting Deleted',
            'create' =>[
                'href' => 'api/v1/meeting',
                'method' => 'POST',
                'params' => 'title, description, time'
            ]
        ];

        return response()->json($response, 200);
    }
}
