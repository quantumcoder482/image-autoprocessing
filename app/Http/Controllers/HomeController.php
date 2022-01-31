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
        // Initialize product types for file name indexing
        $this->typeIndexes = [
            'mug' => 0,
            'sweatshirt' => 0,
            't-shirt' => 0,
            'hoodie' => 0,
        ];
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     * return view
     * langing page
     */
    public function index()
    {
        return view('index');
    }

    /**
     * @param Request $request
     * @return array|mixed
     * @throws \Exception
     * Upload multiple files
     */
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

    /**
     * @param Request $request
     * @return array
     * Store all uploaded files to a zip file(target file for download)
     */
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
        $file_count = 0;
        foreach ($files as $file) {
            // generate file name
            $new_file_name = $this->generateFileName($request, $file['name']);

            if(!is_null($new_file_name)) {
                $zip->addFile(public_path('user-uploads/client-files/' . $file['hashname']), $new_file_name);
                $file_count++;
            }
        }
        $zip->close();

        if($file_count == 0){
            return Reply::error('There are have not files to matching with product types');
        }

        return Reply::successWithData('saved data successfully', ['file_name' => str_replace('.zip', '', $zip_file_name)]);

    }

    /**
     * @param Request $request
     * @param $file_name
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * Download target zip file
     */
    public function download(Request $request, $file_name)
    {
        $zip_file_name = $file_name.'.zip';
        $dir_path = public_path('downloads/');

        $headers = array(
            'Content-Type: application/zip',
        );

        if(!\File::exists($dir_path.$zip_file_name)) {
            return Reply::error('Target file does not exist!');
        }

        //download file
        return Response::download($dir_path.$zip_file_name, $zip_file_name, $headers);

    }

    /**
     * @return array
     * Clean temporary folders for upload and download
     */
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

    /**
     * @param $request
     * @param $file_name
     * @return string|null
     * Generate new file name by the matching case and indexing
     */
    public function generateFileName($request, $file_name)
    {
        $car_type = $request->car_type;
        $product_name = $request->product_name;

        // check the string matching case and return generated new file name
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
