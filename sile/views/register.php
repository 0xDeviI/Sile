<?php

$applicationName = "{{application_name}}";
$favIcon = "{{favicon}}";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="/{{favicon}}" type="image/png">
    <meta name="theme-color" content="#663399">
    <link rel="stylesheet" href="/{{application_name}}/resources/css/style.css">
    <title>{{page_title}}</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>

<body>
    <div class="login-form">
        <div class="login-form-header">
            <h1 class="form-title">{{application_name}} register</h1>
        </div>
        <div class="login-form-body">
            <div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-control" placeholder="Username">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Password">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm password">
                </div>
                <div class="form-group">
                    <button type="button" class="btn btn-primary form-control mt20" onclick="register()">Register new account</button>
                </div>
                <div class="form-group">
                    <p class="text-center mt40">Already registerd? <a href="/">Login now</a></p>
                </div>
            </div>
        </div>
    </div>
    <script src="/{{application_name}}/resources/js/UUID.js"></script>
    <script src="/{{application_name}}/resources/js/Notification.js"></script>
    <script src="/{{application_name}}/resources/js/Application.js"></script>
</body>

</html>