let user_register = {
    'username': document.getElementById('username'),
    'password': document.getElementById('password'),
    'confirm_password': document.getElementById('confirm_password'),
    'register_btn': document.getElementById('register_btn'),
    'register': () => {
        if (user_register.isAllowedToRegister()) {
            $.ajax({
                url: '/api/v1/users/register',
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
                url: '/api/v1/users/login',
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
    'tbody': document.getElementById('tbody'),
    'run_action': document.getElementById('run_action'),
    'group_action_select': document.getElementById('group_action_select'),
    'changeTabContent': (evt, tabId) => {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
        document.getElementById(tabId).style.display = "block";
        evt.currentTarget.className += " active";
    },
    'change_settings': () => {
        if (dashboard.isAllowedToChangeSettings()) {
            $.ajax({
                url: '/api/v1/users/change_settings',
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
    },
    'deleteAccount': () => {
        $.ajax({
            url: '/api/v1/users/delete_account',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            },
            data: {
                'id': dashboard.account_id.value.toLowerCase()
            },
            success: (data) => {
                if (data.status === 'success') {
                    notify('Success', 'You have successfully deleted your account.', 2000);
                    setTimeout(() => {
                        window.location.href = '/logout';
                    }, 2000);
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
    'onDeleteAccount': () => {
        if (confirm('Are you sure you want to delete your account? you can\'t undo this operation!') === true) {
            dashboard.deleteAccount();
        }
    },
    'selectedFiles': [],
    'forceSelectActions': ["DOWNLOAD_SELECTEDS_ONLY", "SET_PASSWORD", "REMOVE_PASSWORD", "DELETE"],
    'getAllFiles': () => {
        $.ajax({
            url: '/api/v1/files/',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            },
            data: {
                'id': dashboard.account_id.value.toLowerCase()
            },
            success: (data) => {
                if (data.status === 'success') {
                    var files = data.data;
                    files.forEach(element => {
                        dashboard.tbody.innerHTML += `
                        <tr class="file" id="${element.id}">
                            <td>
                                <input class="checkbox" type="checkbox" name="file_select_${element.id}" id="file_select_${element.id}">
                            </td>
                            <td>${element.fileName}</td>
                            <td>
                                <label class="switch">
                                    <input id="check_${element.id}" type="checkbox" ${element.password !== "" ? "checked" : ""}>
                                    <span class="slider round"></span>
                                </label>
                            </td>
                            <td>
                                <a href="api/v1/files/download/${element.id}">Download</a> / <a id="copy_link_${element.id}" href="javascript:void(0)">Copy link</a> / <a id="delete_file_${element.id}" class="danger" href="javascript:void(0)">Delete</a>
                            </td>
                        </tr>
                        `;
                    });
                    files.forEach(element => {
                        document.getElementById(`copy_link_${element.id}`).addEventListener('click', () => {
                            dashboard.copyToClipboard(`${window.location.origin}/api/v1/files/download/${element.id}`);
                            notify('Success', 'Link copied to clipboard.', 2000);
                        });
                        document.getElementById(`delete_file_${element.id}`).addEventListener('click', (event) => {
                            event.preventDefault();
                            if (confirm('Are you sure you want to delete this file? you can\'t recover it again') === true) {
                                dashboard.deleteFile(element.id);
                            }
                        });
                        document.getElementById(`check_${element.id}`).addEventListener('change', (e) => {
                            if (e.target.checked) {
                                var password = prompt('Enter password to protect file.');
                                if (password !== null) {
                                    var confirmPassword = prompt('Confirm password to protect file.');
                                    if (password.length != 0 && password === confirmPassword) {
                                        dashboard.protectFile(element.id, password);
                                    } else {
                                        e.target.checked = false;
                                        notify('Error', 'Passwords do not match.', 2000);
                                    }
                                } else {
                                    e.target.checked = false;
                                }
                            } else {
                                if (confirm('Are you sure you want to remove password protection for this file?') === true) {
                                    dashboard.unprotectFile(element.id);
                                } else {
                                    e.target.checked = true;
                                }
                            }
                        });
                        document.getElementById(`file_select_${element.id}`).addEventListener('change', (e) => {
                            if (e.target.checked) {
                                dashboard.selectedFiles.push(element.id);
                            } else {
                                dashboard.selectedFiles.splice(dashboard.selectedFiles.indexOf(element.id), 1);
                            }
                        });
                    });
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
    'copyToClipboard': (text) => {
        var dummy = document.createElement("textarea");
        document.body.appendChild(dummy);
        dummy.value = text;
        dummy.select();
        document.execCommand("copy");
        document.body.removeChild(dummy);
    },
    'deleteFile': (id) => {
        $.ajax({
            url: '/api/v1/files/delete',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            },
            data: {
                'id': id
            },
            success: (data) => {
                if (data.status === 'success') {
                    document.getElementById(id).remove();
                    notify('Success', 'You have successfully deleted the file.', 2000);
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
    'protectFile': (id, password) => {
        $.ajax({
            url: '/api/v1/files/protect',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            },
            data: {
                'id': id,
                'password': password
            },
            success: (data) => {
                if (data.status === 'success') {
                    notify('Success', 'You have successfully protected the file.', 2000);
                } else if (data.status === 'error') {
                    document.getElementById(`check_${id}`).checked = false;
                    notify('Error', data.message, 2000);
                }
            },
            error: (data) => {
                document.getElementById(`check_${id}`).checked = false;
                if (data.responseText) {
                    notify('Error', data.responseText, 2000);
                } else {
                    notify('Error', 'Something went wrong.', 2000);
                }
            }
        });
    },
    'unprotectFile': (id) => {
        $.ajax({
            url: '/api/v1/files/unprotect',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            },
            data: {
                'id': id
            },
            success: (data) => {
                if (data.status === 'success') {
                    notify('Success', 'You have successfully unprotected the file.', 2000);
                } else if (data.status === 'error') {
                    document.getElementById(`check_${id}`).checked = true;
                    notify('Error', data.message, 2000);
                }
            },
            error: (data) => {
                document.getElementById(`check_${id}`).checked = true;
                if (data.responseText)
                    notify('Error', data.responseText, 2000);
                else
                    notify('Error', 'Something went wrong.', 2000);
            }
        });
    },
    'getCurrentAction': () => {
        return dashboard.group_action_select.options[dashboard.group_action_select.selectedIndex].value;
    },
    'groupActionRun': (action) => {
        if (dashboard.isAllowedToRunGroupAction()) {
            var forceAction = dashboard.forceSelectActions.indexOf(action) !== -1;
            if (forceAction !== false) {
                if (dashboard.isMultipleFilesSelected()) {
                    if (action === "SET_PASSWORD") {
                        var password = prompt('Enter password to protect files.');
                        if (password !== null) {
                            var confirmPassword = prompt('Confirm password to protect files.');
                            if (password.length != 0 && password === confirmPassword) {
                                $.ajax({
                                    url: '/api/v1/files/group/protect',
                                    type: 'POST',
                                    headers: {
                                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                                    },
                                    data: {
                                        'password': password,
                                        'files': JSON.stringify(dashboard.selectedFiles)
                                    },
                                    success: (data) => {
                                        if (data.status === 'success') {
                                            dashboard.selectedFiles.forEach(element => {
                                                var check = document.getElementById(`check_${element}`);
                                                check.checked = true;
                                            });
                                            dashboard.deselectFiles();
                                            notify('Success', 'You have successfully protected the files.', 2000);
                                        }
                                    },
                                    error: (data) => {
                                        if (data.responseText)
                                            notify('Error', data.responseText, 2000);
                                        else
                                            notify('Error', 'Something went wrong.', 2000);
                                    }
                                });
                            } else {
                                notify('Error', 'Passwords do not match.', 2000);
                            }
                        }
                    } else if (action === "REMOVE_PASSWORD") {
                        if (confirm('Are you sure you want to remove password protection for selected files?') === true) {
                            $.ajax({
                                url: '/api/v1/files/group/unprotect',
                                type: 'POST',
                                headers: {
                                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                                },
                                data: {
                                    'files': JSON.stringify(dashboard.selectedFiles)
                                },
                                success: (data) => {
                                    if (data.status === 'success') {
                                        dashboard.selectedFiles.forEach(element => {
                                            var check = document.getElementById(`check_${element}`);
                                            check.checked = false;
                                        });
                                        dashboard.deselectFiles();
                                        notify('Success', 'You have successfully unprotected the files.', 2000);
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
                    } else if (action === "DELETE") {
                        if (confirm('Are you sure you want to delete selected files?') === true) {
                            $.ajax({
                                url: '/api/v1/files/group/delete',
                                type: 'POST',
                                headers: {
                                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                                },
                                data: {
                                    'files': JSON.stringify(dashboard.selectedFiles)
                                },
                                success: (data) => {
                                    if (data.status === 'success') {
                                        dashboard.selectedFiles.forEach(element => {
                                            var file = document.getElementById(element);
                                            file.remove();
                                        });
                                        dashboard.deselectFiles();
                                        notify('Success', 'You have successfully deleted the files.', 2000);
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
                    } else if (action === "DOWNLOAD_SELECTEDS_ONLY") {
                        dashboard.selectedFiles.forEach(element => {
                            window.open(`/api/v1/files/download/${element}`, '_blank');
                        });
                    }
                }
            } else {
                if (action === "SELECT_ALL") {
                    dashboard.selectAllFiles();
                } else if (action === "DESELECT_ALL") {
                    dashboard.deselectFiles();
                } else if (action === "SELECT_PASS_ONLY") {
                    dashboard.selectPasswordProtectedsOnly();
                } else if (action === "DESELECT_PASS_ONLY") {
                    dashboard.deselectPasswordProtectedsOnly();
                } else if (action === "SELECT_NOTPASS_ONLY") {
                    dashboard.selectNotPasswordProtectedsOnly();
                } else if (action === "DESELECT_NOTPASS_ONLY") {
                    dashboard.deselectNotPasswordProtectedsOnly();
                } else if (action === "DOWNLOAD_ALL") {
                    dashboard.downloadAllFiles();
                } else if (action === "DOWNLOAD_PASS_ONLY") {
                    dashboard.downloadPasswordProtectedsFile();
                } else if (action === "DOWNLOAD_NOT_PASS_ONLY") {
                    dashboard.downloadNotPasswordProtectedsFile();
                }
            }
        }
    },
    'downloadAllFiles': () => {
        var files = document.getElementsByClassName('file');
        for (var i = 0; i < files.length; i++) {
            var fileId = files[i].id;
            window.open(`/api/v1/files/download/${fileId}`, '_blank');
        }
    },
    'downloadPasswordProtectedsFile': () => {
        var files = document.getElementsByClassName('file');
        for (var i = 0; i < files.length; i++) {
            var fileId = files[i].id;
            var check = document.getElementById(`check_${fileId}`);
            if (check.checked === true) {
                window.open(`/api/v1/files/download/${fileId}`, '_blank');
            }
        }
    },
    'downloadNotPasswordProtectedsFile': () => {
        var files = document.getElementsByClassName('file');
        for (var i = 0; i < files.length; i++) {
            var fileId = files[i].id;
            var check = document.getElementById(`check_${fileId}`);
            if (check.checked === false) {
                window.open(`/api/v1/files/download/${fileId}`, '_blank');
            }
        }
    },
    'selectPasswordProtectedsOnly': () => {
        var files = document.getElementsByClassName('file');
        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            var id = file.id;
            var check = document.getElementById(`check_${id}`);
            if (check.checked === true) {
                var fileSelect = document.getElementById(`file_select_${id}`);
                fileSelect.checked = true;
            }
        }
    },
    'deselectPasswordProtectedsOnly': () => {
        var files = document.getElementsByClassName('file');
        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            var id = file.id;
            var check = document.getElementById(`check_${id}`);
            if (check.checked === true) {
                var fileSelect = document.getElementById(`file_select_${id}`);
                fileSelect.checked = false;
            }
        }
    },
    'selectNotPasswordProtectedsOnly': () => {
        var files = document.getElementsByClassName('file');
        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            var id = file.id;
            var check = document.getElementById(`check_${id}`);
            if (check.checked === false) {
                var fileSelect = document.getElementById(`file_select_${id}`);
                fileSelect.checked = true;
            }
        }
    },
    'deselectNotPasswordProtectedsOnly': () => {
        var files = document.getElementsByClassName('file');
        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            var id = file.id;
            var check = document.getElementById(`check_${id}`);
            if (check.checked === false) {
                var fileSelect = document.getElementById(`file_select_${id}`);
                fileSelect.checked = false;
            }
        }
    },
    'selectAllFiles': () => {
        var files = document.getElementsByClassName('file');
        for (var i = 0; i < files.length; i++) {
            var check = document.getElementById(`file_select_${files[i].id}`);
            if (check.checked === false) {
                check.checked = true;
                dashboard.selectedFiles.push(files[i].id);
            }
        }
    },
    'deselectFiles': () => {
        dashboard.selectedFiles.forEach(element => {
            var fileSelect = document.getElementById(`file_select_${element}`);
            if (fileSelect !== null)
                fileSelect.checked = false;
        });
        dashboard.selectedFiles = [];
    },
    'isAllowedToRunGroupAction': () => {
        return dashboard.getCurrentAction() !== "#";
    },
    'isMultipleFilesSelected': () => {
        return dashboard.selectedFiles.length > 0;
    }
}

let lock_screen = {
    'password': document.getElementById('password'),
    'unlock_file_btn': document.getElementById('unlock_file_btn'),
    'isAllowedToUnlock': () => {
        return lock_screen.password.value.length > 0;
    },
    'unlockFile': () => {
        if (lock_screen.isAllowedToUnlock()) {
            var _url = window.location.href.split('/');
            var id = _url[_url.length - 1];
            $.ajax({
                url: '/api/v1/files/unlock',
                type: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                },
                data: {
                    'id': id,
                    'password': lock_screen.password.value
                },
                success: (data) => {
                    if (data.status === 'success') {
                        lock_screen.password.value = '';
                        lock_screen.unlock_file_btn.classList.remove('btn-primary');
                        lock_screen.unlock_file_btn.classList.add('btn-success');
                        lock_screen.unlock_file_btn.innerText = 'File unlocked!';
                        lock_screen.unlock_file_btn.disabled = true;
                        notify('Success', 'You have successfully unlocked the file.', 2000);
                        setTimeout(() => {
                            window.location.href = `/api/v1/files/unlock/${data.download_token}`;
                        }, 2000);
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
        } else {
            notify('Error', 'Please enter password.', 2000);
        }
    },
    'stages': "x*X",
    'currentStage': 0,
    'playPasswordAnimation': () => {
        lock_screen.password.value += lock_screen.stages[lock_screen.currentStage];
        lock_screen.currentStage++;
        if (lock_screen.currentStage === lock_screen.stages.length) {
            lock_screen.currentStage = 0;
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

function onRunActionClicked() {
    if (dashboard.isAllowedToRunGroupAction()) {
        var action = dashboard.getCurrentAction();
        var forceAction = dashboard.forceSelectActions.indexOf(action) !== -1;
        if (forceAction !== false) {
            if (dashboard.isMultipleFilesSelected()) {
                dashboard.groupActionRun(action);
            } else {
                notify("Error", "Please select at least one file.", 3000);
            }
        } else {
            dashboard.groupActionRun(action);
        }
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
        dashboard.delete_account.addEventListener('click', dashboard.onDeleteAccount);
        dashboard.run_action.addEventListener('click', onRunActionClicked);
        dashboard.getAllFiles();
    }
    if (lock_screen.unlock_file_btn) {
        lock_screen.unlock_file_btn.addEventListener('click', lock_screen.unlockFile);
    }
}

(function() {
    eventListenerSetup();
})();