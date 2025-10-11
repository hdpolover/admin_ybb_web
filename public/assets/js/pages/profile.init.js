/*
Template Name: Velzon - Admin & Dashboard Template
Author: Themesbrand
Website: https://Themesbrand.com/
Contact: Themesbrand@gmail.com
File: Profile init js
*/

// project-swiper
var swiper = new Swiper(".project-swiper", {
    slidesPerView: 1,
    spaceBetween: 16,
    direction: 'horizontal',
    navigation: {
        nextEl: ".slider-button-next",
        prevEl: ".slider-button-prev",
    },
    // Enable touch/swipe on mobile
    touchEventsTarget: 'container',
    simulateTouch: true,
    grabCursor: true,
    // Optimize for mobile performance
    speed: 400,
    threshold: 10,
    breakpoints: {
        // Mobile devices (portrait)
        320: {
            slidesPerView: 1,
            spaceBetween: 12,
            direction: 'horizontal',
        },
        // Mobile devices (landscape) and small tablets
        640: {
            slidesPerView: 1,
            spaceBetween: 15,
            direction: 'horizontal',
        },
        // Tablets
        768: {
            slidesPerView: 2,
            spaceBetween: 20,
        },
        // Desktop
        1200: {
            slidesPerView: 3,
            spaceBetween: 25,
        },
    },
});