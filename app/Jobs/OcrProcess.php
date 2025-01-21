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



                    $pythonVenv = '/var/www/admin.xtiplyai.com/html/app/Console/Commands/Python/venv/bin/activate'; // Path to your venv
//        $script_path = "/Users/jony/projects/mupdf/file_to_image.py"; // Path to your venv
                    $script_path = '/var/www/admin.xtiplyai.com/html/app/Console/Commands/Python/azure_ocr.py'; // Path to your venv
                    $azure_end_point = env('AZURE_OCR_ENDPOINT');
                    $azure_secrect_key = env('AZURE_OCR_KEY');
                    //  $command = "source $pythonVenv && python3 $script_path " .escapeshellarg($apiKey)." ". escapeshellarg($inputPdfs) . " " . escapeshellarg($outputDir) . " " . escapeshellarg($openaiModel) ;
                    $command = "bash -c 'source $pythonVenv && python3 $script_path " . escapeshellarg(storage_path("app/".$file_path)) . " " . escapeshellarg($azure_end_point) . " " . escapeshellarg($azure_secrect_key) . " && deactivate' 2>&1";

                    $output = shell_exec($command);

                    $converted_file_path = str_replace("\n", "",$output);

                    if (file_exists($converted_file_path)) {
                        $send_file = base64_encode(file_get_contents($converted_file_path));
                        $this->request['attachments'][0] = $attachment;
                        $this->request['attachments'][0]['content'] = $send_file;
                        Log::info("try to sent converted");
                        $response = Http::timeout(900)->post('https://docs2ai.com/api/incoming', $this->request);
                      

                        // dd('success');
                    }else{
                        Log::info("try to not converted just sent");
//                        Log::info(json_encode("error ocr=".$result->errorOutput()));
//                        $data = $this->request;
                        $data['content'] =base64_encode(file_get_contents(storage_path("app/".$file_path)));

                        $this->request['attachments'][0] = $attachment;
                        $this->request['attachments'][0]['content'] = $data['content'];

                        Log::info("try to sent");
//            $response = Http::timeout(900)->post($this->request['callback'], $data);
                        $response = Http::timeout(900)->post('https://docs2ai.com/api/incoming',  $this->request);
                    }
                 //   Log::info(json_encode("error ocr=".$result->errorOutput()));

                }else{
                    Log::info("try to sent");
                    $this->request['attachments'][0] = $attachment;
                    $response = Http::timeout(900)->post('https://docs2ai.com/api/incoming',  $this->request);
                }



                Log::info("====================================");

                sleep(30);
            }

        }





      //$response = Http::post('https://docs2ai.com/api/incoming', $this->request);
    //  dd($response);


    }
}
