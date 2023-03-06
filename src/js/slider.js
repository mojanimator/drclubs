document.addEventListener('DOMContentLoaded', function (e) {

    const sliderView = document.querySelector('.dc-slider--view > ul'),
        sliderViewSlides = document.querySelectorAll('.dc-slider--view__slides'),
        arrowLeft = document.querySelector('.dc-slider--arrows__left'),
        arrowRight = document.querySelector('.dc-slider--arrows__right'),
        sliderLength = sliderViewSlides.length;

    const slideMe = (sliderViewItems, isActiveItem) => {

        isActiveItem.classList.remove('is-active');
        sliderViewItems.classList.add('is-active');
        sliderView.setAttribute('style', 'transform:translateX(-' + sliderViewItems.offsetLeft + 'px)')
    };
    const beforeSliding = (i) => {
        let isActiveItem = document.querySelector('.dc-slider--view__slides.is-active'),
            currentItem = Array.from(sliderViewSlides).indexOf(isActiveItem) + i,
            nextItem = currentItem + i,
            sliderViewItems = document.querySelector('.dc-slider--view__slides:nth-child(' + nextItem + ')');

        if (nextItem > sliderLength) {
            sliderViewItems = document.querySelector('.dc-slider--view__slides:nth-child(1)');
        }
        if (nextItem == 0) {
            sliderViewItems = document.querySelector(`.dc-slider--view__slides:nth-child(' + ${sliderLength} + ')`);
        }

        slideMe(sliderViewItems, isActiveItem);
    };

    arrowRight.addEventListener('click', () => beforeSliding(1));
    arrowLeft.addEventListener('click', () => beforeSliding(0));
});