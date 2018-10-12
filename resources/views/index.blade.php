<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Domain Checker</title>
        <meta name="description" content="Tool for domain checking.">
        <meta name="keywords" content="domain,whois,availability,check,register">
        <link rel="icon" type="image/png" href="/favicon.png?v=2">
        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet" type="text/css">
        <link href="{{ mix('css/app.css') }}" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div id="root"><search></search></div>        
        <script src="{{mix('js/app.js')}}"></script>
    </body>
</html>
