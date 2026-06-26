/**
 * Pato - Swiper Carousel Configuration
 * Version: 2.0.0 | January 2026
 * Vanilla JS (replacement for Slick)
 */

(function() {
    'use strict';

    // In Next.js we load scripts with strategy=afterInteractive, which can run
    // after DOMContentLoaded. Run init immediately if the DOM is already ready.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        initHeroSlider();
        initEventSlider();
        initReviewSlider();
    }

    /**
     * Hero Slider (slick1 replacement)
     */
    function initHeroSlider() {
        const container = document.querySelector('.wrap-slick1');
        if (!container || typeof Swiper === 'undefined') return;

        const swiperEl = container.querySelector('.swiper-hero');
        if (!swiperEl || container.dataset.swiperHeroInit === '1') return;
        container.dataset.swiperHeroInit = '1';

        const paginationEl = container.querySelector('.wrap-slick1-dots');
        const nextEl = container.querySelector('.next-slick1');
        const prevEl = container.querySelector('.prev-slick1');

        const swiper = new Swiper(swiperEl, {
            slidesPerView: 1,
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },
            loop: true,
            autoplay: {
                delay: 6000,
                disableOnInteraction: false,
            },
            pagination: {
                el: paginationEl,
                clickable: true,
                bulletClass: 'swiper-pagination-bullet',
                bulletActiveClass: 'swiper-pagination-bullet-active',
            },
            navigation: {
                nextEl,
                prevEl,
            },
            on: {
                init: function() {
                    animateSlideContent(this.slides[this.activeIndex]);
                },
                slideChangeTransitionStart: function() {
                    // Reset all animations
                    this.slides.forEach(slide => {
                        resetSlideAnimation(slide);
                    });
                },
                slideChangeTransitionEnd: function() {
                    animateSlideContent(this.slides[this.activeIndex]);
                }
            }
        });
    }

    /**
     * Event Slider (slick2 replacement)
     */
    function initEventSlider() {
        const container = document.querySelector('.wrap-slick2');
        if (!container || typeof Swiper === 'undefined') return;

        const swiperEl = container.querySelector('.swiper-event');
        if (!swiperEl || container.dataset.swiperEventInit === '1') return;
        container.dataset.swiperEventInit = '1';

        const paginationEl = container.querySelector('.wrap-slick2-dots');
        const nextEl = container.querySelector('.next-slick2');
        const prevEl = container.querySelector('.prev-slick2');

        const swiper = new Swiper(swiperEl, {
            slidesPerView: 1,
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },
            loop: true,
            autoplay: {
                delay: 6000,
                disableOnInteraction: false,
            },
            pagination: {
                el: paginationEl,
                clickable: true,
            },
            navigation: {
                nextEl,
                prevEl,
            },
            on: {
                init: function() {
                    animateEventContent(this.slides[this.activeIndex]);
                },
                slideChangeTransitionStart: function() {
                    this.slides.forEach(slide => {
                        resetEventAnimation(slide);
                    });
                },
                slideChangeTransitionEnd: function() {
                    animateEventContent(this.slides[this.activeIndex]);
                }
            }
        });
    }

    /**
     * Review Slider (slick3 replacement)
     */
    function initReviewSlider() {
        const container = document.querySelector('.wrap-slick3');
        if (!container || typeof Swiper === 'undefined') return;

        const swiperEl = container.querySelector('.swiper-review');
        if (!swiperEl || container.dataset.swiperReviewInit === '1') return;
        container.dataset.swiperReviewInit = '1';

        const paginationEl = container.querySelector('.wrap-slick3-dots');
        const nextEl = container.querySelector('.next-slick3');
        const prevEl = container.querySelector('.prev-slick3');

        const swiper = new Swiper(swiperEl, {
            slidesPerView: 1,
            loop: true,
            autoplay: {
                delay: 6000,
                disableOnInteraction: false,
            },
            pagination: {
                el: paginationEl,
                clickable: true,
            },
            navigation: {
                nextEl,
                prevEl,
            },
            on: {
                init: function() {
                    animateReviewContent(this.slides[this.activeIndex]);
                },
                slideChangeTransitionStart: function() {
                    this.slides.forEach(slide => {
                        resetReviewAnimation(slide);
                    });
                },
                slideChangeTransitionEnd: function() {
                    animateReviewContent(this.slides[this.activeIndex]);
                }
            }
        });
    }

    /**
     * Animation helpers for Hero Slider
     */
    function animateSlideContent(slide) {
        const caption1 = slide.querySelector('.caption1-slide1');
        const caption2 = slide.querySelector('.caption2-slide1');
        const btn = slide.querySelector('.wrap-btn-slide1');

        if (caption1) {
            setTimeout(() => {
                caption1.classList.add(caption1.dataset.appear, 'visible-true');
            }, 200);
        }

        if (caption2) {
            setTimeout(() => {
                caption2.classList.add(caption2.dataset.appear, 'visible-true');
            }, 1000);
        }

        if (btn) {
            setTimeout(() => {
                btn.classList.add(btn.dataset.appear, 'visible-true');
            }, 1800);
        }
    }

    function resetSlideAnimation(slide) {
        const caption1 = slide.querySelector('.caption1-slide1');
        const caption2 = slide.querySelector('.caption2-slide1');
        const btn = slide.querySelector('.wrap-btn-slide1');

        [caption1, caption2, btn].forEach(el => {
            if (el && el.dataset.appear) {
                el.classList.remove(el.dataset.appear, 'visible-true');
            }
        });
    }

    /**
     * Animation helpers for Event Slider
     */
    function animateEventContent(slide) {
        const blo2 = slide.querySelector('.blo2');
        if (blo2) {
            setTimeout(() => {
                blo2.classList.add(blo2.dataset.appear, 'visible-true');
            }, 200);
        }
    }

    function resetEventAnimation(slide) {
        const blo2 = slide.querySelector('.blo2');
        if (blo2 && blo2.dataset.appear) {
            blo2.classList.remove(blo2.dataset.appear, 'visible-true');
        }
    }

    /**
     * Animation helpers for Review Slider
     */
    function animateReviewContent(slide) {
        const pic = slide.querySelector('.pic-review');
        const content = slide.querySelector('.content-review');
        const more = slide.querySelector('.more-review');

        if (pic) {
            setTimeout(() => {
                pic.classList.add(pic.dataset.appear, 'visible-true');
            }, 200);
        }

        if (content) {
            setTimeout(() => {
                content.classList.add(content.dataset.appear, 'visible-true');
            }, 1000);
        }

        if (more) {
            setTimeout(() => {
                more.classList.add(more.dataset.appear, 'visible-true');
            }, 1000);
        }
    }

    function resetReviewAnimation(slide) {
        const pic = slide.querySelector('.pic-review');
        const content = slide.querySelector('.content-review');
        const more = slide.querySelector('.more-review');

        [pic, content, more].forEach(el => {
            if (el && el.dataset.appear) {
                el.classList.remove(el.dataset.appear, 'visible-true');
            }
        });
    }

})();
