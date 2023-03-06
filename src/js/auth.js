document.addEventListener('DOMContentLoaded', (e) => {
    const showAuthBtn = document.getElementById('drclubs-show-auth-form'),
        authContainer = document.getElementById('drclubs-auth-container'),
        close = document.getElementById('drclubs-auth-close'),
        authForm = document.getElementById('drclubs-auth-form'),
        status = authForm.querySelector('[data-message="status"]')

    ;


    showAuthBtn.addEventListener('click', () => {
        authContainer.classList.add('show');
        showAuthBtn.parentElement.classList.add('hide');

    });
    close.addEventListener('click', () => {
        authContainer.classList.remove('show');
        showAuthBtn.parentElement.classList.remove('hide');

    });

    authForm.addEventListener('submit', e => {
        e.preventDefault();

        resetMessages();

        let data = {
            name: authForm.querySelector('[name="username"]').value,
            password: authForm.querySelector('[name="password"]').value,
            nonce: authForm.querySelector('[name="drclubs_auth"]').value,
        };

        if (!data.name || !data.password) {
            status.innerHTML = "نام کاربری یا گذرواژه نامعتبر است";
            status.classList.add('error');
            return;
        }

        let url = authForm.dataset.url;
        let params = new URLSearchParams(new FormData(authForm));

        authForm.querySelector('[name="submit"]').value = "در حال ورود...";
        authForm.querySelector('[name="submit"]').disabled = true;

        fetch(url, {
            method: "POST",
            body: params
        }).then(res => res.json()).catch(error => {
            resetMessages()
        }).then(response => {
            resetMessages();
            if (response === 0 || !response.status) {
                status.innerHTML = response.message;
                status.classList.add('error');
                return;
            }

            status.innerHTML = response.message;
            status.classList.add('success');
            authForm.reset();

            window.location.reload();
        });
    });

    const resetMessages = () => {
        status.innerHTML = "";
        status.classList.remove("success", "error");

        authForm.querySelector('[name="submit"]').value = "ورود";
        authForm.querySelector('[name="submit"]').disabled = false;
    };
});