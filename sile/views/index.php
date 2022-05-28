<?php

$applicationName = "{{application_name}}";
$favIcon = "{{favicon}}";
if (!isset($_SESSION["user"])) {
    header("Location: /login");
}
$user = $_SESSION["user"];
$jwt = $_SESSION["JWT"];

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
    <div id="dashboard" class="dashboard">
        <header>
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="header-content">
                            <div class="header-content-inner">
                                <h1 class="text-center">{{page_title}}</h1>
                                <hr>
                                <ul class="header-menu">
                                    <li class="header-element"><a href="/">Home</a></li>
                                    <li class="header-element"><a href="/#settings">Settings</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <h2>Upload</h2>
        <p>Here you can upload your files.</p>
        <div class="upload">
            <div class="upload-box">
                <div class="upload-form">
                    <div class="upload-stuff">
                        <p id="file_upload_text">Click in this area to select a file.</p>
                        <button id="upload_btn" type="button" class="btn btn-primary upload-btn" name="submit">Upload</button>
                    </div>
                    <input class="upload-input" title="" type="file" name="file" id="file">
                </div>
            </div>
        </div>
        <br>
        <h2>Files</h2>
        <p>Here you can find all files uploaded by you.</p>
        <table class="file-table">
            <thead>
                <tr>
                    <th class="table-id">#</th>
                    <th>File Name</th>
                    <th>Password Protection</th>
                    <th>Operations</th>
                </tr>
            </thead>
            <tbody id="tbody">
            </tbody>
        </table>
    </div>
    <div id="settings" class="settings" hidden>
        <div class="settings-header">
            <h2>Settings</h2>
            <span id="settings_close_btn" class="close top-right"></span>
        </div>
        <p>Here you can change your settings.</p>
        <div class="center-settings">
            <div class="form-group">
                <label for="account_id">Account ID</label>
                <input disabled value="<?php echo strtoupper($user["id"]); ?>" type="text" class="form-control" id="account_id" name="account_id" placeholder="Account ID">
            </div>
            <div class="form-group">
                <label for="token">Access token</label>
                <input disabled value="<?php echo $jwt; ?>" type="text" class="form-control" id="token" name="token" placeholder="Access token">
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" value="<?php echo $user["username"]; ?>" class="form-control" id="username" name="username" placeholder="Username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="text" value="<?php echo $user["password"]; ?>" class="form-control" id="password" name="password" placeholder="Password">
            </div>
            <br>
            <button id="change_settings_btn" type="button" class="btn btn-primary form-control bold-text">Change settings</button>
        </div>
        <br>
        <h2>Danger Zone</h2>
        <p>Be careful here, you can't undo these settings.</p>
        <div class="center-dzone">
            <button id="delete_all_files" type="button" class="btn btn-danger form-control bold-text">Delete All Files</button>
            <button id="delete_account" type="button" class="btn btn-danger-outbox form-control bold-text">Delete Account</button>
        </div>
        <br>
        <h2>Account</h2>
        <p>Here you can find account operations.</p>
        <div class="center-dzone">
            <button id="logout" type="button" class="btn btn-primary bold-text w120">Logout</button>
        </div>
    </div>
    <script src="/{{application_name}}/resources/js/UUID.js"></script>
    <script src="/{{application_name}}/resources/js/Notification.js"></script>
    <script src="/{{application_name}}/resources/js/Application.js"></script>
</body>

</html>