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
    'account_id': document.getElementById('account_id'),
    'upload': () => {
        if (file_upload.isAllowedToUpload()) {
            var file_data = file_upload.file.files[0];
            var form_data = new FormData();
            form_data.append('file', file_data);
            form_data.append('id', file_upload.account_id.value.toLowerCase());
            $.ajax({
                url: '/api/v1/files/upload',
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

let dashboard = {
    'settings_close_btn': document.getElementById('settings_close_btn'),
    'dashboard': document.getElementById('dashboard'),
    'settings': document.getElementById('settings'),
    'change_settings_btn': document.getElementById('change_settings_btn'),
    'username': document.getElementById('username'),
    'password': document.getElementById('password'),
    'account_id': document.getElementById('account_id'),
    'delete_all_files': document.getElementById('delete_all_files'),
    'delete_account': document.getElementById('delete_account'),
    'logout': document.getElementById('logout'),
    'change_settings': () => {
        if (dashboard.isAllowedToChangeSettings()) {
            $.ajax({
                url: '/api/v1/user/change_settings',
                type: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                },
                data: {
                    'id': dashboard.account_id.value.toLowerCase(),
                    'username': dashboard.username.value,
                    'password': dashboard.password.value
                },
                success: (data) => {
                    if (data.status === 'success') {
                        notify('Success', 'You have successfully changed your settings.', 2000);
                    } else if (data.status === 'error') {
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
    'tagChangeEvent': () => {
        let url_tag = window.location.hash.substr(1);
        if (url_tag === 'settings') {
            dashboard.setViewVisible(dashboard.settings);
            dashboard.setViewInvisible(dashboard.dashboard);
        } else {
            dashboard.setViewVisible(dashboard.dashboard);
            dashboard.setViewInvisible(dashboard.settings);
        }
    },
    'isViewHidden': (view) => {
        return view.style.display === 'none';
    },
    'setViewVisible': (view) => {
        view.style.display = 'block';
    },
    'setViewInvisible': (view) => {
        view.style.display = 'none';
    },
    'settings_close_click': () => {
        dashboard.setViewVisible(dashboard.dashboard);
        dashboard.setViewInvisible(dashboard.settings);
        window.location.hash = '';
    },
    'isValidUsername': () => {
        return RegExp(/^[a-zA-Z0-9_]{3,20}$/).test(dashboard.username.value);
    },
    'isValidPassword': () => {
        return RegExp(/^[a-zA-Z0-9!@#$*()=]{6,}$/).test(dashboard.password.value);
    },
    'isAllowedToChangeSettings': () => {
        return dashboard.isValidUsername() && dashboard.isValidPassword();
    },
    'onLogout': () => {
        localStorage.removeItem('token');
        window.location.href = '/logout';
    },
    'deleteAllFiles': () => {
        $.ajax({
            url: '/api/v1/files/delete_all',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            },
            data: {
                'id': dashboard.account_id.value.toLowerCase()
            },
            success: (data) => {
                if (data.status === 'success') {
                    notify('Success', 'You have successfully deleted all files.', 2000);
                } else if (data.status === 'error') {
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
    },
    'onDeleteAllFiles': () => {
        if (confirm('Are you sure you want to delete all files? you can\'t undo this operation!') === true) {
            dashboard.deleteAllFiles();
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

function change_settings() {
    if (dashboard.isValidUsername()) {
        if (dashboard.isValidPassword()) {
            dashboard.change_settings();
        } else {
            notify("Error", "Password is not valid", 3000);
        }
    } else {
        notify("Error", "Username is not valid.", 3000);
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
    if (dashboard.settings_close_btn) {
        dashboard.tagChangeEvent();
        window.addEventListener('popstate', function(event) {
            dashboard.tagChangeEvent();
        });
        dashboard.settings_close_btn.addEventListener('click', dashboard.settings_close_click);
        dashboard.change_settings_btn.addEventListener('click', change_settings);
        dashboard.logout.addEventListener('click', dashboard.onLogout);
        dashboard.delete_all_files.addEventListener('click', dashboard.onDeleteAllFiles);
    }
}

(function() {
    eventListenerSetup();
})();