let user_register = {
    'username': document.getElementById('username'),
    'password': document.getElementById('password'),
    'confirm_password': document.getElementById('confirm_password'),
    'register': () => {
        if (this.isAllowedToRegister()) {
            $.ajax({
                url: '/api/v1/user/register',
                type: 'POST',
                data: {
                    'username': this.username.value,
                    'password': this.password.value
                },
                success: (data) => {
                    if (data.status === 'success') {
                        this.notify('Success', 'You have successfully registered.', 2000);
                        this.clearFields();
                        setTimeout(() => {
                            window.location.href = '/';
                        }, 2000);
                    } else {
                        this.notify('Error', data.message, 2000);
                    }
                },
                error: (data) => {
                    this.notify('Error', 'Something went wrong.', 2000);
                }
            });
        }
    },
    'isValidUsername': () => {
        return RegExp(/^[a-zA-Z0-9]{3,}$/).test(this.username.value);
    },
    'isValidPassword': () => {
        return RegExp(/^[a-zA-Z0-9!@#$*()=]{6,}$/).test(this.password.value);
    },
    'isPasswordsMatch': () => {
        return this.password.value === this.confirm_password.value;
    },
    'isAllowedToRegister': () => {
        return this.isValidUsername() && this.isValidPassword() && this.isPasswordsMatch();
    },
    'clearFields': () => {
        this.username.value = '';
        this.password.value = '';
        this.confirm_password.value = '';
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