<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OcrConvert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60*10;

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
        //$converted_file_path = public_path('asset/ocr22.pdf');

        $stroage = Storage::put($file_path,base64_decode($this->request['content']));

        if (env('APP_ENV') != 'pc'){
            $cmd= "ocrmypdf ".storage_path("app/".$file_path).' '.public_path($converted_file_path).' --skip-text';
            $process = Process::timeout(900)->start($cmd);

            while ($process->running()) {
                 Log::info($process->latestOutput());
                  Log::info($process->latestErrorOutput());

                sleep(1);
            }
            $result = $process->wait();

            if ($result->successful()) {
                $data = $this->request;
                $data['content'] =base64_encode(file_get_contents(public_path($converted_file_path)));
                $response = Http::post($this->request['callback'], $data);
                Log::info(json_encode('success ocr'));

            }
            Log::info(json_encode("error ocr=".$result->errorOutput()));


        }






    }

    public function failed(\Exception $exception)
    {
        // handle the failed job
        Log::info('Job failed: '.$exception->getMessage());

    }
}
