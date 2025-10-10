<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class VideoUploadController extends Controller
{
    //Upload to Storage
    public function uploadChunk(Request $request)
    {
        if ($request->isMethod('get')) {
            $identifier = $request->input('resumableIdentifier');
            $chunkNumber = $request->input('resumableChunkNumber');
            $tempPath = "temp/{$identifier}/chunk{$chunkNumber}";

            if (Storage::disk('local')->exists($tempPath)) {
                return response()->json(['success' => true], 200);
            }
            return response()->json(['success' => false], 404);
        }

        $file = $request->file('file');
        $identifier = $request->input('resumableIdentifier');
        $chunkNumber = $request->input('resumableChunkNumber');
        $filename = $request->input('resumableFilename');

        $tempPath = "temp/{$identifier}/";
        Storage::disk('local')->putFileAs($tempPath, $file, "chunk{$chunkNumber}");

        return response()->json(['success' => true]);
    }

    public function finalizeUpload(Request $request)
    {
        $fileId = $request->input('fileId');
        $fileName = time() . '_' . uniqid() . '_' . $request->input('fileName');

        $tempDir = "temp/{$fileId}/";
        $finalPath = "videos/{$fileName}";


        $chunks = Storage::disk('local')->files($tempDir);
        if (empty($chunks)) {
            return response()->json(['success' => false, 'message' => 'No chunks found'], 400);
        }


        usort($chunks, function ($a, $b) {
            preg_match('/chunk(\d+)/', $a, $aNum);
            preg_match('/chunk(\d+)/', $b, $bNum);
            return $aNum[1] <=> $bNum[1];
        });


        Storage::disk('local')->makeDirectory('videos');
        $fileResource = fopen(Storage::disk('local')->path($finalPath), 'wb');
        foreach ($chunks as $chunk) {
            $chunkContent = Storage::disk('local')->get($chunk);
            fwrite($fileResource, $chunkContent);
        }
        fclose($fileResource);

        Storage::disk('local')->deleteDirectory($tempDir);

        return response()->json([
            'success' => true,
            'message' => 'Video uploaded successfully',
            'filePath' => $finalPath,
            'fileName' => $fileName
        ]);
    }


    //Upload to Public Asset
    // public function uploadChunk(Request $request)
    // {
    //     if ($request->isMethod('get')) {
    //         $identifier = $request->input('resumableIdentifier');
    //         $chunkNumber = $request->input('resumableChunkNumber');
    //         $tempPath = public_path("assets/temp/{$identifier}/chunk{$chunkNumber}");

    //         if (File::exists($tempPath)) {
    //             return response()->json(['success' => true], 200);
    //         }
    //         return response()->json(['success' => false], 404);
    //     }

    //     $file = $request->file('file');
    //     $identifier = $request->input('resumableIdentifier');
    //     $chunkNumber = $request->input('resumableChunkNumber');
    //     $filename = $request->input('resumableFilename');

    //     $tempPath = public_path("assets/temp/{$identifier}");
    //     if (!File::exists($tempPath)) {
    //         File::makeDirectory($tempPath, 0755, true);
    //     }
    //     $file->move($tempPath, "chunk{$chunkNumber}");

    //     return response()->json(['success' => true]);
    // }

    // public function finalizeUpload(Request $request)
    // {
    //     $fileId = $request->input('fileId');
    //     $fileName = $request->input('fileName');

    //     $tempDir = public_path("assets/temp/{$fileId}");
    //     $finalPath = public_path("assets/videos/{$fileName}");

    //     $chunks = File::glob("{$tempDir}/chunk*");
    //     if (empty($chunks)) {
    //         return response()->json(['success' => false, 'message' => 'No chunks found'], 400);
    //     }

    //     usort($chunks, function ($a, $b) {
    //         preg_match('/chunk(\d+)/', $a, $aNum);
    //         preg_match('/chunk(\d+)/', $b, $bNum);
    //         return $aNum[1] <=> $bNum[1];
    //     });

    //     $videosDir = public_path('assets/videos');
    //     if (!File::exists($videosDir)) {
    //         File::makeDirectory($videosDir, 0755, true);
    //     }

    //     $fileResource = fopen($finalPath, 'wb');
    //     foreach ($chunks as $chunk) {
    //         $chunkContent = File::get($chunk);
    //         fwrite($fileResource, $chunkContent);
    //     }
    //     fclose($fileResource);


    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Video uploaded successfully',
    //         'filePath' => "assets/videos/{$fileName}" 
    //     ]);
    // }
}
