<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReading;
use App\Reading;
use Carbon\Carbon;

class ReadingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return array
     */
    public function index()
    {
        return Reading::orderBy('timestamp', 'desc')->limit(100)->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreReading $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(StoreReading $request)
    {
        $reading = new Reading([
            'remote_ip' => $request->ip(),
            'temperature' => $request->temperature,
            'timestamp' => Carbon::now()
        ]);

        $reading->save();

        return response(sprintf('Temperature of %s recorded successfully.', $reading->temperature), '201');
    }
}
