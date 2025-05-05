@if (Str::contains($video->video_url, 'digitaloceanspaces.com'))
    <video width='320' height='240' controls>
        <source src="{{ $video }}" type='video/mp4'>
    </video>
@else
    <video width='320' height='240' controls>
        <source src="{{ route('getVideo', $video->id) }}" type='video/mp4'>
    </video>
@endif