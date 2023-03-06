/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!**************************!*\
  !*** ./src/js/slider.js ***!
  \**************************/
document.addEventListener('DOMContentLoaded', function (e) {
  var sliderView = document.querySelector('.dc-slider--view > ul'),
      sliderViewSlides = document.querySelectorAll('.dc-slider--view__slides'),
      arrowLeft = document.querySelector('.dc-slider--arrows__left'),
      arrowRight = document.querySelector('.dc-slider--arrows__right'),
      sliderLength = sliderViewSlides.length;

  var slideMe = function slideMe(sliderViewItems, isActiveItem) {
    isActiveItem.classList.remove('is-active');
    sliderViewItems.classList.add('is-active');
    sliderView.setAttribute('style', 'transform:translateX(-' + sliderViewItems.offsetLeft + 'px)');
  };

  var beforeSliding = function beforeSliding(i) {
    var isActiveItem = document.querySelector('.dc-slider--view__slides.is-active'),
        currentItem = Array.from(sliderViewSlides).indexOf(isActiveItem) + i,
        nextItem = currentItem + i,
        sliderViewItems = document.querySelector('.dc-slider--view__slides:nth-child(' + nextItem + ')');

    if (nextItem > sliderLength) {
      sliderViewItems = document.querySelector('.dc-slider--view__slides:nth-child(1)');
    }

    if (nextItem == 0) {
      sliderViewItems = document.querySelector(".dc-slider--view__slides:nth-child(' + ".concat(sliderLength, " + ')"));
    }

    slideMe(sliderViewItems, isActiveItem);
  };

  arrowRight.addEventListener('click', function () {
    return beforeSliding(1);
  });
  arrowLeft.addEventListener('click', function () {
    return beforeSliding(0);
  });
});
/******/ })()
;