<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>ChessFlashCards App</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Switch main style here (yeti, united, etc.) -->
        <link rel="stylesheet" type="text/css" href="{{asset('css/bootstrap-united.css')}}">
        <link rel="stylesheet" type="text/css" href="{{asset('css/cm-chessboard/chessboard.css')}}">

    </head>
    <body>
        <div class="container">
        <div class="page-header">
            <br>
            <div class="row">
                <div class="col-sm-12">
                    <div id="app">
                        <app></app>
                    </div>

                </div>
            </div>
        </div>    
        <script src="{{ mix('js/app.js') }}"></script>
    </body>
</html>
