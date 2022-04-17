<?php

namespace App\Http\Controllers;

use App\Models\TemporaryFile;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $folder = uniqid() . '-' . now()->timestamp;
        mkdir(storage_path('app/public/avatars/tmp/' . $folder));
        file_put_contents('app/public/avatars/tmp/' . $folder . '/file.part','');
        return $folder;
        //old
        if($request->hasFile('avatar')){
            $file = $request->file('avatar');
            $folder = uniqid() . '-' .now()->timestamp;
            $filename = $file->getClientOriginalName();
            Storage::disk('avatars')->putFileAs('tmp/'.$folder,$file, $filename);

            TemporaryFile::create([
                'folder' => $folder,
                'filename' => $filename
            ]);

            return $folder;
        }

        return '';
    }

    public function update(Request $request)
    {
        $path = storage_path('app/public/avatars/tmp/' . $request->query('patch') . '/file.part');
        File::append($path, $request->getContent());

        if(filesize($path) == $request->header('Upload-Length')){
            $name = $request->header('Upload-Name');
            File::move($path, storage_path('app/public/avatars/tmp/' . $request->query('patch') . '/' . $name));

            TemporaryFile::create([
                'folder' => $request->query('patch'),
                'filename' => $name
            ]);
        }
        return response()->json(['uploaded' => true]);
    }
}
