<?php

$applicationName = "{{application_name}}";
$favIcon = "{{favicon}}";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="/{{favicon}}" type="image/png">
    <meta name="theme-color" content="#663399">
    <link rel="stylesheet" href="/{{application_name}}/resources/css/style.css">
    <title>{{page_title}}</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>

<body>
    <h2>Dashboard</h2>
    <p>Welcome to dashboard. Here you can upload your files.</p>
    <div class="upload">
        <div class="upload-box">
            <div class="upload-form">
                <div class="upload-stuff">
                    <p id="file_upload_text">Click in this area to select a file.</p>
                    <button id="upload_btn" type="button" class="btn btn-primary w120 upload-btn" name="submit">Upload</button>
                </div>
                <input class="upload-input" type="file" name="file" id="file">
            </div>
        </div>
    </div>
    <h2>Files</h2>
    <p>Here you can find all files uploaded by you.</p>
    <script src="/{{application_name}}/resources/js/UUID.js"></script>
    <script src="/{{application_name}}/resources/js/Notification.js"></script>
    <script src="/{{application_name}}/resources/js/Application.js"></script>
</body>

</html>