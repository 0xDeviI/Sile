let user_register = {
    'username': document.getElementById('username'),
    'password': document.getElementById('password'),
    'confirm_password': document.getElementById('confirm_password'),
    'register_btn': document.getElementById('register_btn'),
    'register': () => {
        if (user_register.isAllowedToRegister()) {
            $.ajax({
                url: '/api/v1/user/register',
                type: 'POST',
                data: {
                    'username': user_register.username.value,
                    'password': user_register.password.value
                },
                success: (data) => {
                    if (data.status === 'success') {
                        notify('Success', 'You have successfully registered.', 2000);
                        user_register.clearFields();
                        setTimeout(() => {
                            window.location.href = '/';
                        }, 2000);
                    } else {
                        notify('Error', data.message, 2000);
                    }
                },
                error: (data) => {
                    notify('Error', 'Something went wrong.', 2000);
                }
            });
        }
    },
    'isValidUsername': () => {
        return RegExp(/^[a-zA-Z0-9_]{3,20}$/).test(user_register.username.value);
    },
    'isValidPassword': () => {
        return RegExp(/^[a-zA-Z0-9!@#$*()=]{6,}$/).test(user_register.password.value);
    },
    'isPasswordsMatch': () => {
        return user_register.password.value === user_register.confirm_password.value;
    },
    'isAllowedToRegister': () => {
        return user_register.isValidUsername() && user_register.isValidPassword() && user_register.isPasswordsMatch();
    },
    'clearFields': () => {
        user_register.username.value = '';
        user_register.password.value = '';
        user_register.confirm_password.value = '';
    }
}

let user_login = {
    'username': document.getElementById('username'),
    'password': document.getElementById('password'),
    'login_btn': document.getElementById('login_btn'),
    'login': () => {
        if (user_login.isAllowedToLogin()) {
            $.ajax({
                url: '/api/v1/user/login',
                type: 'POST',
                data: {
                    'username': user_login.username.value,
                    'password': user_login.password.value
                },
                success: (data) => {
                    if (data.status === 'success') {
                        localStorage.setItem('token', data.token);
                        notify('Success', 'You have successfully logged in.', 2000);
                        user_login.clearFields();
                        setTimeout(() => {
                            window.location.href = '/';
                        }, 2000);
                    } else {
                        notify('Error', data.message, 2000);
                    }
                },
                error: (data) => {
                    if (data.responseText)
                        notify('Error', data.responseText, 2000);
                    else
                        notify('Error', 'Something went wrong.', 2000);
                }
            });
        }
    },
    'isValidUsername': () => {
        return RegExp(/^[a-zA-Z0-9_]{3,20}$/).test(user_login.username.value);
    },
    'isValidPassword': () => {
        return RegExp(/^[a-zA-Z0-9!@#$*()=]{6,}$/).test(user_login.password.value);
    },
    'isAllowedToLogin': () => {
        return user_login.isValidUsername() && user_login.isValidPassword();
    },
    'clearFields': () => {
        user_login.username.value = '';
        user_login.password.value = '';
    }
}

let file_upload = {
    'file': document.getElementById('file'),
    'upload_btn': document.getElementById('upload_btn'),
    'file_upload_text': document.getElementById('file_upload_text'),
    'upload': () => {
        if (file_upload.isAllowedToUpload()) {
            var file_data = file_upload.file.files[0];
            var form_data = new FormData();
            form_data.append('file', file_data);
            $.ajax({
                url: '/api/v1/file/upload',
                type: 'POST',
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                success: (data) => {
                    if (data.status === 'success') {
                        notify('Success', 'You have successfully uploaded a file.', 2000);
                        file_upload.clearFields();
                        setTimeout(() => {
                            window.location.href = '/';
                        }, 2000);
                    } else {
                        notify('Error', data.message, 2000);
                    }
                },
                error: (data) => {
                    if (data.responseText)
                        notify('Error', data.responseText, 2000);
                    else
                        notify('Error', 'Something went wrong.', 2000);
                }
            });
        }
    },
    'isAllowedToUpload': () => {
        return file_upload.file.files[0] !== undefined;
    },
    'clearFields': () => {
        file_upload.file.value = '';
    },
    'fileUploadChange': () => {
        console.log(file_upload.file.files[0]);
        if (file_upload.file.files[0]) {
            file_upload.file_upload_text.innerHTML = file_upload.file.files[0].name;
            file_upload.upload_btn.disabled = false;
        } else {
            file_upload.upload_btn.disabled = true;
        }
    }
}

function login() {
    if (user_login.isValidUsername()) {
        if (user_login.isValidPassword()) {
            user_login.login();
        } else {
            notify("Error", "Password is not valid", 3000);
        }
    } else {
        notify("Error", "Username is not valid", 3000);
    }
}

function register() {
    if (user_register.isValidUsername()) {
        if (user_register.isValidPassword()) {
            if (user_register.isPasswordsMatch()) {
                user_register.register();
            } else {
                notify("Error", "Passwords don't match", 3000);
            }
        } else {
            notify("Error", "Password is not valid", 3000);
        }
    } else {
        notify("Error", "Username is not valid", 3000);
    }
}

function upload() {
    if (file_upload.isAllowedToUpload()) {
        file_upload.upload();
    } else {
        notify("Error", "File is not selected or not valid.", 3000);
    }
}

function eventListenerSetup() {
    if (user_login.login_btn) {
        user_login.login_btn.addEventListener('click', login);
    }
    if (user_register.register_btn) {
        user_register.register_btn.addEventListener('click', register);
    }
    if (file_upload.upload_btn) {
        file_upload.upload_btn.disabled = true;
        file_upload.file.addEventListener('change', file_upload.fileUploadChange);
        file_upload.upload_btn.addEventListener('click', upload);
    }
}

(function() {
    eventListenerSetup();
})();