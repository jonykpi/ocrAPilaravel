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

        $file_path = Str::random('17').".pdf";
        $converted_file_path = "asset/".Str::random('17').".pdf";
        //$converted_file_path = public_path('asset/ocr22.pdf');

        $stroage = Storage::put($file_path,base64_decode($this->request['content']));

        if (env('APP_ENV') != 'pc'){
//            $cmd= "/usr/local/bin/ocrmypdf ".storage_path("app/".$file_path).' '.public_path($converted_file_path).' --skip-text --optimize 0';
//
//            $cmd = "python azure_ocr.py /Users/jony/projects/chatpdfbackend/app/Console/Commands/Python/pdf/deposito.pdf https://ocr-intelligence-azure.cognitiveservices.azure.com/ 9FH3vVnU1Gzh2A3p0gsJaVIYRqUeiqkC7rj7dPUoI3UmoEky4O6rJQQJ99BAACYeBjFXJ3w3AAALACOG8b30";
//            $process = Process::timeout(900)->start($cmd);
//
//



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
                $data = $this->request;
                $data['content'] =base64_encode(file_get_contents($converted_file_path));
                $response = Http::timeout(900)->post($this->request['callback'], $data);

                dd($response);

            }else{
                $data = $this->request;
                $data['content'] =base64_encode(file_get_contents(storage_path("app/".$file_path)));
                $response = Http::timeout(900)->post($this->request['callback'], $data);

            }



        }






    }

    public function failed(\Exception $exception)
    {
        // handle the failed job
        Log::info('Job failed: '.$exception->getMessage());

    }
}
