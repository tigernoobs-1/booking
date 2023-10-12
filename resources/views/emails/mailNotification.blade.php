<x-mail::message>
    # {{ $mailData['title'] }}


    {{ $mailData['body'] }}


    {{ $mailData['end'] }}

    Thanks,
    {{ config('app.name') }}
</x-mail::message>
