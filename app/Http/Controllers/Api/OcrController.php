<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class OcrController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
       dispatch(new \App\Jobs\OcrProcess($request->request->all()));

//        $cmd= "ocrmypdf ".public_path('asset/Scan.pdf').' '.public_path('').' --force-ocr';
//    // dd($cmd);
//        $process = Process::timeout(900)->start($cmd);
//
//
//        while ($process->running()) {
//            echo $process->latestOutput();
//            echo $process->latestErrorOutput();
//
//            sleep(1);
//        }
//        $result = $process->wait();
//
//        if ($result->successful()) {
//            return "Done";
//        }
//      dd($result->errorOutput());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
