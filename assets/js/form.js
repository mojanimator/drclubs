/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!************************!*\
  !*** ./src/js/form.js ***!
  \************************/
document.addEventListener('DOMContentLoaded', function (e) {
  var testimonialForm = document.getElementById('drclubs-testimonial-form');
  testimonialForm.addEventListener('submit', function (e) {
    e.preventDefault(); //reset form  messages

    resetMessages(); //collect all data from form

    var data = {
      name: testimonialForm.querySelector('[name="name"]').value,
      email: testimonialForm.querySelector('[name="email"]').value,
      message: testimonialForm.querySelector('[name="message"]').value,
      nonce: testimonialForm.querySelector('[name="nonce"]').value
    }; //validate data

    if (!data.name) {
      testimonialForm.querySelector('[data-error="invalidName"]').classList.add('show');
      return;
    }

    if (!validateEmail(data.email)) {
      testimonialForm.querySelector('[data-error="invalidEmail"]').classList.add('show');
      return;
    }

    if (!data.message) {
      testimonialForm.querySelector('[data-error="invalidMessage"]').classList.add('show');
      return;
    } //ajax request


    var url = testimonialForm.dataset.url;
    var params = new URLSearchParams(new FormData(testimonialForm));
    testimonialForm.querySelector('.js-form-submission').classList.add('show');
    fetch(url, {
      method: 'POST',
      body: params
    }).then(function (res) {
      return res.json();
    })["catch"](function (error) {
      resetMessages();
      testimonialForm.querySelector('.js-form-error').classList.add('show');
    }).then(function (response) {
      resetMessages(); //response is here

      if (response === 0 || response.status === 'error') {
        testimonialForm.querySelector('.js-form-error').classList.add('show');
        return;
      }

      testimonialForm.querySelector('.js-form-success').classList.add('show');
      testimonialForm.reset();
    });
  });
});

function resetMessages() {
  document.querySelectorAll('.field-msg').forEach(function (field) {
    field.classList.remove('show');
  });
}

function validateEmail(email) {
  var re = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
  return re.test(String(email).toLowerCase());
}
/******/ })()
;