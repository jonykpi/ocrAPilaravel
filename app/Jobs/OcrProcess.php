<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OcrProcess implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $request;
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $file_path = Str::random('17').".pdf";
        $converted_file_path = "asset/".Str::random('17').".pdf";

        $stroage = Storage::put($file_path,base64_decode($this->request['attachments'][0]['content']));


       // $cmd= "ocrmypdf ".storage_path($file_path).' '.public_path($converted_file_path).' --force-ocr';
        $cmd= "ocrmypdf ".storage_path("app/".$file_path).' '.public_path($converted_file_path).' --force-ocr';

//        shell_exec($cmd);

        $process = Process::timeout(900)->start($cmd);

        while ($process->running()) {
            echo $process->latestOutput();
            echo $process->latestErrorOutput();

            sleep(1);
        }
        $result = $process->wait();

        if ($result->successful()) {
            dd('success');
        }
       dd($result->errorOutput());

      $response = Http::post('https://docs2ai.com/api/incoming', $this->request);
      dd($response);


    }
}
