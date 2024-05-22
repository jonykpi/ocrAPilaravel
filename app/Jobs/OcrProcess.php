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
use Illuminate\Support\Facades\Log;
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
    public $timeout = 60*10;
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!isset($this->request['attachments'][0])){
            $data = $this->request;
            $response = Http::timeout(900)->post('https://docs2ai.com/api/incoming', $data);
        }


        if (isset($this->request['attachments'][0])){

            foreach ($this->request['attachments'] as $attachment){
                $file_name = $attachment['file_name'];
                if (Str::lower(substr(strrchr($file_name, "."), 1)) == "pdf"){
                    $file_path = Str::random('17').".pdf";
                    $converted_file_path = "asset/".Str::random('17').".pdf";

                    $stroage = Storage::put($file_path,base64_decode($attachment['content']));




                    // $cmd= "ocrmypdf ".storage_path($file_path).' '.public_path($converted_file_path).' --force-ocr';
                    $cmd= "ocrmypdf ".storage_path("app/".$file_path).' '.public_path($converted_file_path).' --skip-text --optimize 0';

//        shell_exec($cmd);

                    $process = Process::timeout(900)->start($cmd);

                    while ($process->running()) {
//            Log::info($process->latestOutput());
//            Log::info($process->latestErrorOutput());

                        sleep(1);
                    }
                    $result = $process->wait();

                    if ($result->successful()) {
                        $send_file = base64_encode(file_get_contents(public_path($converted_file_path)));
                        $this->request['attachments'][0] = $attachment;
                        $this->request['attachments'][0]['content'] = $send_file;
                        $response = Http::timeout(900)->post('https://docs2ai.com/api/incoming', $this->request);

                        Log::info(json_encode('success ocr'));
                        Log::info("incoming");
                        Log::info(json_encode($response->body()));
                        // dd('success');
                    }else{
//                        Log::info(json_encode("error ocr=".$result->errorOutput()));
//                        $data = $this->request;
                        $data['content'] =base64_encode(file_get_contents(storage_path("app/".$file_path)));

                        $this->request['attachments'][0] = $attachment;
                        $this->request['attachments'][0]['content'] = $data['content'];


//            $response = Http::timeout(900)->post($this->request['callback'], $data);
                        $response = Http::timeout(900)->post('https://docs2ai.com/api/incoming', $data);
                    }
                    Log::info(json_encode("error ocr=".$result->errorOutput()));

                }else{
                    $data = $this->request;
                    $data['attachments'][0] = $attachment;
                    $response = Http::timeout(900)->post('https://docs2ai.com/api/incoming', $data);
                }


                Log::info(json_encode($this->request));
                Log::info("====================================");

                sleep(30);
            }

        }





      //$response = Http::post('https://docs2ai.com/api/incoming', $this->request);
    //  dd($response);


    }
}
