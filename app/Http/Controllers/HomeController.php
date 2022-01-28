<?php

namespace App\Http\Controllers;

use App\Helpers\Files;
use App\Helpers\Reply;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Filesystem\Filesystem;
use ZanySoft\Zip\Zip;
use ZipArchive;

class HomeController extends Controller
{
    public $typeIndexes;

    public function __construct()
    {
        $this->typeIndexes = [
            'mug' => 0,
            'sweatshirt' => 0,
            't-shirt' => 0,
            'hoodie' => 0,
        ];
    }

    public function index()
    {
        return view('index');
    }

    public function uploadFiles(Request $request)
    {
        // Check Mimetype
        $fileFormats = ['image/jpeg','image/png','image/gif', 'application/octet-stream'];
        foreach ($request->file as $index => $fFormat) {
            if (!in_array($fFormat->getClientMimeType(), $fileFormats)){
                return Reply::dataOnly([
                    'status' => 'failed',
                    'msg' => 'This file format not allowed'
                ]);
            }
        }

        $files = array();

        foreach($request->file as $file) {
            $files[] = [
                'name' => $file->getClientOriginalName(),
                'hashname' => Files::uploadLocalOrS3($file,'client-files')
            ];
        }

        return Reply::successWithData('file uploaded', ['data' => $files]);

    }

    public function store(Request $request)
    {
        $rules = array(
            'car_type' => 'required',
            'product_name' => 'required',
        );

        $validations = Validator::make($request->all(), $rules);

        if ($validations->fails()) {
            $messages = $validations->messages();
            return Reply::error('validation error', $messages);
        }

        // define parameters for the zip file
        $zip_file_name = $request->car_type . '-clothing-' . $request->product_name .'.zip';
        $dir_path = public_path('downloads/');

        // create zip file
        $zip = new ZipArchive();
        $zip->open($dir_path.$zip_file_name, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $files = json_decode($request->attach_files, true);
        foreach ($files as $file) {
            // generate file name
            $new_file_name = $this->generateFileName($request, $file['name']);

            if(!is_null($new_file_name)) {
                $zip->addFile(public_path('user-uploads/client-files/' . $file['hashname']), $new_file_name);
            }
        }
        $zip->close();

        return Reply::successWithData('saved data successfully', ['file_name' => str_replace('.zip', '', $zip_file_name)]);

    }

    public function download(Request $request, $file_name)
    {
        $zip_file_name = $file_name.'.zip';
        $dir_path = public_path('downloads/');

        $headers = array(
            'Content-Type: application/zip',
        );

        //download file
        return Response::download($dir_path.$zip_file_name, $zip_file_name, $headers);

    }

    public function reset()
    {
        // remove temp directories
        \File::deleteDirectory(public_path('user-uploads/client-files'));
        \File::deleteDirectory(public_path('user-uploads/temp'));
        \File::deleteDirectory(public_path('downloads'));

        // create new directories
        \File::makeDirectory(public_path('user-uploads/client-files'), 0777, true, true);
        \File::makeDirectory(public_path('downloads'), 0777, true, true);

        return Reply::success('reset form');
    }

    public function generateFileName($request, $file_name)
    {
        $car_type = $request->car_type;
        $product_name = $request->product_name;

        foreach($this->typeIndexes as $key => $val) {
            if(str_contains($file_name, $key)) {
                $ext = strtolower(\File::extension($file_name));
                $this->typeIndexes[$key] ++;
                return $car_type . '-clothing-' . $product_name . '-' . $key . 's' . '-car-clothing-company-'. $this->typeIndexes[$key] . '.' . $ext;
            }
        }

        return null;

    }


}
