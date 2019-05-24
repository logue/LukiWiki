<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ app()->getLocale() }}" xml:lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title')</title>
    @if(isset($page))
    <link rel="canonical" href="{{ $page ? url($page) : url('/') }}" />
    @endif
    <link rel="stylesheet" href="{{ mix('css/default.css') }}" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/paper-css/0.4.1/paper.css" integrity="sha256-hktQKhVc7KEsNf1bx+RYdzCMHyDyUjsA4N10rS1h8WA=" crossorigin="anonymous" />
    <style>
        @page {
            size: A4
        }
    </style>
</head>

<body class="A4">
    <main id="app" class="sheet padding-10mm mx-auto">
        <article>
            <h1>{{ $page }}</h1>
            <hr />
            {!! $body !!}
            <hr />
            @if($notes)
            <hr />
            <aside>
                <ul class="fa-ul">
                    @foreach ($notes as $no=>$note)
                    <li id="note-{{ $no }}">
                        <a href="#note-anchor-{{ $no }}" id="note-{{ $no }}" class="fa-li">
                            <font-awesome-icon fas icon="thumbtack">*</font-awesome-icon>
                            <sup>{{ $no }}</sup>
                        </a>
                        {!! $note !!}
                    </li>
                    @endforeach
                </ul>
            </aside>
            @endif
            @if($entry->attachments)
            <hr />
            <aside>
                <ul class="fa-ul">
                    @foreach ($entry->attachments as $attach)
                    <li>
                        <font-awesome-icon fas icon="clip">*</font-awesome-icon>
                        {!! $attach->name !!}
                    </li>
                    @endforeach
                </ul>
            </aside>
            @endif
        </article>
    </main>
    <script src="{{ mix('js/app.js') }}"></script>
    @yield('scripts')
</body>

</html>