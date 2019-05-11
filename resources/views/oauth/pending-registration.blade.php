<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Oauth register process completed</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <link rel="stylesheet" type="text/css" href="css/bootstrap.css">

    </head>
    <body>
        <div class="container">
        <div class="page-header">
            <br>
            <div class="row">
                <div class="col-sm-12">

                    <h2>Your account has been created.</h2>
                    <p>Your account has been created with following details:</p>
                    <ul>
                    <li>Name: {{$user->getName()}}</li>
                    <li>Email: {{$user->getEmail()}}</li>
                    <hr>
                    <p style="color: red;">Note! Account is currently disabled. Wait for admin to enable your account first.</p>

                </div>
            </div>
        </div>    
    </body>
</html>