<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoUploadController extends Controller
{
    public function uploadChunk(Request $request)
    {
        // Handle Resumable.js test chunk request (GET)
        if ($request->isMethod('get')) {
            $identifier = $request->input('resumableIdentifier');
            $chunkNumber = $request->input('resumableChunkNumber');
            $tempPath = "temp/{$identifier}/chunk{$chunkNumber}";

            if (Storage::disk('public')->exists($tempPath)) {
                return response()->json(['success' => true], 200);
            }
            return response()->json(['success' => false], 404);
        }

        // Handle chunk upload (POST Auckland
        $file = $request->file('file');
        $identifier = $request->input('resumableIdentifier');
        $chunkNumber = $request->input('resumableChunkNumber');
        $filename = $request->input('resumableFilename');

        // Store chunk in public disk
        $tempPath = "temp/{$identifier}/";
        Storage::disk('public')->putFileAs($tempPath, $file, "chunk{$chunkNumber}");

        return response()->json(['success' => true]);
    }

    public function finalizeUpload(Request $request)
    {
        $fileId = $request->input('fileId');
        $fileName = $request->input('fileName');

        $tempDir = "temp/{$fileId}/";
        $finalPath = "videos/{$fileName}"; // Relative to storage/app/public/

        // Check if all chunks exist
        $chunks = Storage::disk('public')->files($tempDir);
        if (empty($chunks)) {
            return response()->json(['success' => false, 'message' => 'No chunks found'], 400);
        }

        // Sort chunks numerically
        usort($chunks, function ($a, $b) {
            preg_match('/chunk(\d+)/', $a, $aNum);
            preg_match('/chunk(\d+)/', $b, $bNum);
            return $aNum[1] <=> $bNum[1];
        });

        // Combine chunks
        Storage::disk('public')->makeDirectory('videos');
        $fileResource = fopen(Storage::disk('public')->path($finalPath), 'wb');
        foreach ($chunks as $chunk) {
            $chunkContent = Storage::disk('public')->get($chunk);
            fwrite($fileResource, $chunkContent);
        }
        fclose($fileResource);

        // Clean up temporary chunks
        Storage::disk('public')->deleteDirectory($tempDir);

        // Return the relative path for use in the database and Blade
        return response()->json([
            'success' => true,
            'message' => 'Video uploaded successfully',
            'filePath' => $finalPath // e.g., 'videos/video.mp4'
        ]);
    }
}
