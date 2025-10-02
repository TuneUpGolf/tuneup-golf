@component('mail::message')
# Lesson Reminder

Hello {{ $studentName }}, this is a reminder that you have a lesson scheduled with **{{ $instructorName }}** on **Date:** {{ $date }} at {{ $time }}

@component('mail::button', ['url' => route('lessons.show', $lessonId)])
View Lesson Details
@endcomponent

Thanks,  
{{ config('app.name') }}
@endcomponent



{{-- <x-mail::message>
# Introduction

The body of your message.

<x-mail::button :url="''">
Button Text
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message> --}}
