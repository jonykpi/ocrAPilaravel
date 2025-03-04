<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OcrController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Log::info("Job dispatched");
       dispatch(new \App\Jobs\OcrProcess($request->request->all()))->onQueue('ocr_api');

    }

    public function ocrConvert(Request $request){


        dispatch(new \App\Jobs\OcrConvert($request->request->all()))->onQueue('ocr_api');
       return ['success'=>true,"message"=>__('Dispatched')];


       // $cmd= "ocrmypdf ".storage_path($file_path).' '.public_path($converted_file_path).' --force-ocr';
      //  $cmd= "ocrmypdf ".storage_path("app/".$file_path).' '.public_path($converted_file_path).' --force-ocr';

    }

    public function staticIncoming(Request $request){


        dispatch(new \App\Jobs\OcrStaticConvert($request->request->all()))->onQueue('ocr_api');
       return ['success'=>true,"message"=>__('Dispatched')];


       // $cmd= "ocrmypdf ".storage_path($file_path).' '.public_path($converted_file_path).' --force-ocr';
      //  $cmd= "ocrmypdf ".storage_path("app/".$file_path).' '.public_path($converted_file_path).' --force-ocr';

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
