<?php

namespace App\Support;

use Exception;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use FFMpeg\Coordinate\Dimension;

class VideoConverter
{
    private $ffmpeg;
    private $inputPath;
    private $outputPath;

    public function __construct($ffmpegConfig = [])
    {
        $defaultConfig = [
            'ffmpeg.binaries'  => env('FFMPEG_PATH', '/usr/bin/ffmpeg'),
            'ffprobe.binaries' => env('FFPROBE_PATH', '/usr/bin/ffprobe'),
            'timeout'          => 3600,
            'threads'          => 12,
        ];

        $config = array_merge($defaultConfig, $ffmpegConfig);

        try {
            $this->ffmpeg = FFMpeg::create($config);
        } catch (Exception $e) {
            throw new Exception("Failed to initialize FFmpeg: " . $e->getMessage());
        }
    }

    private function getSourceBitrate($video)
    {
        try {
            $streams = $video->getStreams();
            $videoStream = $streams->videos()->first();

            // Get original bitrate in kb/s
            $bitrate = $videoStream->get('bit_rate') / 1000;

            // If bitrate is not available, calculate from filesize and duration
            if (!$bitrate) {
                $duration = $videoStream->get('duration');
                $filesize = filesize($this->inputPath);
                if ($duration > 0) {
                    $bitrate = ($filesize * 8) / (1000 * $duration);
                }
            }

            return $bitrate ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    private function determineOptimalBitrate($video)
    {
        $streams = $video->getStreams();
        $videoStream = $streams->videos()->first();

        $width = $videoStream->get('width');
        $height = $videoStream->get('height');
        $framerate = $videoStream->get('r_frame_rate');

        if (strpos($framerate, '/') !== false) {
            list($num, $den) = explode('/', $framerate);
            $framerate = $num / $den;
        }

        if ($width >= 3840 || $height >= 2160) {     // 4K
            return 35000;
        } elseif ($width >= 2560 || $height >= 1440) { // 2K/1440p
            return 16000;
        } elseif ($width >= 1920 || $height >= 1080) { // 1080p
            return min(8000, max(6000, round($framerate / 30 * 8000)));
        } elseif ($width >= 1280 || $height >= 720) {  // 720p
            return min(5000, max(3500, round($framerate / 30 * 5000)));
        } else {
            return min(2500, max(1500, round($framerate / 30 * 2500)));
        }
    }

    public function convertMovToMp4($inputPath, $outputPath, $options = [])
    {
        $this->inputPath = $inputPath;
        $this->outputPath = $outputPath;

        $defaultOptions = [
            'width' => null,
            'height' => null,
            'bitrate' => 'auto',
            'audioBitrate' => 192,
            'audioChannels' => 2,
            'preset' => 'ultrafast',
            'crf' => 40
        ];

        $options = array_merge($defaultOptions, $options);

        try {

            $video = $this->ffmpeg->open($this->inputPath);

            if ($options['bitrate'] === 'source') {
                $bitrate = $this->getSourceBitrate($video);
            } elseif ($options['bitrate'] === 'auto') {
                $bitrate = $this->determineOptimalBitrate($video);
            } else {
                $bitrate = $options['bitrate'];
            }

            // Create format instance
            $format = new X264('aac','libx264');

            if ($bitrate) {
                $format->setKiloBitrate($bitrate);
            }

            // Set audio parameters
            $format->setAudioKiloBitrate($options['audioBitrate'])
                ->setAudioChannels($options['audioChannels']);

            // Add additional parameters including CRF and preset
            $additionalParams = [
                '-preset',
                $options['preset'],
                '-crf',
                $options['crf']
            ];

            if ($options['audioChannels']) {
                $additionalParams[] = '-ac';
                $additionalParams[] = $options['audioChannels'];
            }

            $format->setAdditionalParameters($additionalParams);
            $format->setAdditionalParameters(['-pix_fmt', 'yuv420p']);
            // Handle resize if dimensions are specified
            if ($options['width'] && $options['height']) {
                $video->filters()->resize(new Dimension($options['width'], $options['height']));
            }

            // Start conversion with progress monitoring
            $format->on('progress', function ($video, $format, $percentage) {
                // echo "Progress: $percentage%\r";
            });

            $video->save($format, $this->outputPath);

            echo "\n"; // New line after progress

            return [
                'success' => true,
                'message' => 'Conversion completed successfully',
                'output_path' => $this->outputPath,
                'used_bitrate' => $bitrate,
                'crf' => $options['crf'],
                'preset' => $options['preset']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Conversion failed: ' . $e->getMessage(),
                'input_path' => $this->inputPath
            ];
        }
    }
}
