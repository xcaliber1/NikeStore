<?php

namespace Modules\Upload\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Modules\Upload\Entities\Upload;

class UploadController extends Controller
{

    public function filepondUpload(Request $request) {
        if (!$request->ajax()) {
            return back();
        }

        $request->validate([
            'image' => 'required|image|mimes:png,jpeg,jpg'
        ]);

        if ($request->hasFile('image')) {
            $uploaded_file = $request->file('image');
            $filename = now()->timestamp . '.' . $uploaded_file->getClientOriginalExtension();
            $folder = uniqid() . '-' . now()->timestamp;

            $file = Image::make($uploaded_file)->encode($uploaded_file->getClientOriginalExtension());

            Storage::put('public/temp/' . $folder . '/' . $filename, $file);

            Upload::create([
                'folder'   => $folder,
                'filename' => $filename
            ]);

            return $folder;
        }

        return false;
    }

    public function filepondDelete(Request $request) {
        if (!$request->ajax()) {
            return back();
        }

        $upload = Upload::where('folder', $request->getContent())->first();

        Storage::deleteDirectory('public/temp/' . $upload->folder);
        $upload->delete();

        return response(null);
    }
}
