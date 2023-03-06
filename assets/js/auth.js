/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!************************!*\
  !*** ./src/js/auth.js ***!
  \************************/
document.addEventListener('DOMContentLoaded', function (e) {
  var showAuthBtn = document.getElementById('drclubs-show-auth-form'),
      authContainer = document.getElementById('drclubs-auth-container'),
      close = document.getElementById('drclubs-auth-close'),
      authForm = document.getElementById('drclubs-auth-form'),
      status = authForm.querySelector('[data-message="status"]');
  showAuthBtn.addEventListener('click', function () {
    authContainer.classList.add('show');
    showAuthBtn.parentElement.classList.add('hide');
  });
  close.addEventListener('click', function () {
    authContainer.classList.remove('show');
    showAuthBtn.parentElement.classList.remove('hide');
  });
  authForm.addEventListener('submit', function (e) {
    e.preventDefault();
    resetMessages();
    var data = {
      name: authForm.querySelector('[name="username"]').value,
      password: authForm.querySelector('[name="password"]').value,
      nonce: authForm.querySelector('[name="drclubs_auth"]').value
    };

    if (!data.name || !data.password) {
      status.innerHTML = "نام کاربری یا گذرواژه نامعتبر است";
      status.classList.add('error');
      return;
    }

    var url = authForm.dataset.url;
    var params = new URLSearchParams(new FormData(authForm));
    authForm.querySelector('[name="submit"]').value = "در حال ورود...";
    authForm.querySelector('[name="submit"]').disabled = true;
    fetch(url, {
      method: "POST",
      body: params
    }).then(function (res) {
      return res.json();
    })["catch"](function (error) {
      resetMessages();
    }).then(function (response) {
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

  var resetMessages = function resetMessages() {
    status.innerHTML = "";
    status.classList.remove("success", "error");
    authForm.querySelector('[name="submit"]').value = "ورود";
    authForm.querySelector('[name="submit"]').disabled = false;
  };
});
/******/ })()
;