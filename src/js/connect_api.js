document.addEventListener('DOMContentLoaded', (e) => {
    let form = document.getElementById('drclubs-connect-api-form');
    if (!form) return;

    let loader = form.querySelector('.loader-container');
    let status = form.querySelector('.status');
    let submitButton = form.querySelector('[type="submit"]');
    let checkBox = form.querySelector('#drclubs_status');


    checkBox.addEventListener('change', (event) => {

        connectToAPI(event);
    });

    // form.addEventListener('submit', connectToAPI);

    const connectToAPI = (e) => {
        e.preventDefault();


        resetMessages();
        loader.classList.add('show');

        let data = {
            name: form.querySelector('#drclubs_username').value,
            password: form.querySelector('#drclubs_password').value,
            nonce: form.querySelector('#_wpnonce').value,
            status: checkBox.checked,
        };

        if (!data.name || !data.password) {
            status.innerHTML = "نام کاربری و گذرواژه نمیتواند خالی باشد";
            status.classList.add('error');
            loader.classList.remove('show');
            return;
        }

        let url = form.dataset.url;
        let params = new URLSearchParams(new FormData(form));

        form.querySelector('[type="submit"]').value = "در حال اتصال...";

        form.querySelector('[type="submit"]').disabled = true;
        axios.post(url, params, {
            // headers: {

            //
            // },
        }).then((response) => {
            // console.log(response.data);
            if (response === 0 || response.data === false || !response.status) {
                status.innerHTML = 'نام کاربری یارمز عبور نامعتبر است';
                status.classList.add('error');
                loader.classList.remove('show');
                checkBox.checked = false;
                return;
            }

            status.innerHTML = checkBox.checked ? 'با موفقیت متصل شد!' : 'با موفقیت قطع شد!';

            status.classList.add('success');
            // form.reset();
            loader.classList.remove('show');
            // resetMessages();
            window.location.reload();
        }).catch((error) => {
            resetMessages();
            console.log(error);
            loader.classList.remove('show');

        });

    };


    const resetMessages = () => {
        let status = form.querySelector('.status');
        status.innerHTML = "";
        status.classList.remove("success", "error");

        form.querySelector('[type="submit"]').value = "اتصال";
        form.querySelector('[type="submit"]').disabled = false;
    };
});