document.addEventListener('DOMContentLoaded', (e) => {
    document.querySelectorAll('[id ^= "drclubs-actions-customer-"]').forEach((el) => {


        el.addEventListener('submit', (event) => {

            doAction(event, el);
        });
    });

    // form.addEventListener('submit', connectToAPI);

    const doAction = (e, form) => {
        e.preventDefault();

        let loader = form.querySelector('.loader-container');
        let button = form.querySelector('[type="submit"]');

        loader.classList.add('show');


        let url = form.dataset.url;
        let formdata = new FormData(form);
        let command = button.value;
        formdata.append('command', command);
        let params = new URLSearchParams(formdata);

        button.innerHTML = "...";

        form.querySelector('[type="submit"]').disabled = true;
        axios.post(url, params, {
            // headers: {

            //
            // },
        }).then((response) => {
            console.log(response.data);
            if (response === 0 || response.data === false || !response.status) {

                loader.classList.remove('show');
                button.innerHTML = command;
                return;
            }

            // form.reset();
            loader.classList.remove('show');
            // resetMessages();
            window.location.reload();
        }).catch((error) => {

            console.log(error);
            loader.classList.remove('show');

            if (confirm('خطایی رخ داد')) {
                window.location.reload();
            } else {

            }

        });

    };


});