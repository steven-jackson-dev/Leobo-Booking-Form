import FancyBox from '@fancyapps/fancybox';
import domReady from '@roots/sage/client/dom-ready';
import gsap from 'gsap';
import { ScrollToPlugin, ScrollTrigger } from 'gsap/all.js';
import 'jquery';
import Swiper from 'swiper/bundle';

/**
 * Application entrypoint
 */
domReady(async () => {
  // ...
  gsap.registerPlugin(ScrollToPlugin, ScrollTrigger);
  ScrollTrigger.clearScrollMemory('body');

  // Banner text animation
  const bannerTxt_anim = gsap.utils.toArray('.bannerTxt_anim');
  bannerTxt_anim.forEach((box, i) => {
    gsap.fromTo(
      box.children,
      { autoAlpha: 0, y: 10 },
      {
        delay: 0.4,
        duration: 1,
        ease: 'power1.out',
        autoAlpha: 1,
        y: 0,
        stagger: 0.2,
        toggleActions: 'play none none none',
        once: false,
      }
    );
  });

  if (window.innerWidth > 767) {
    // Text Fade In Animation
    const anim_fadeinup = gsap.utils.toArray('.anim_fadeinup');
    anim_fadeinup.forEach((box, i) => {
      const anim = gsap.fromTo(
        box.children,
        { autoAlpha: 0, y: 10 },
        { delay: 0.4, duration: 0.6, autoAlpha: 1, y: 0, stagger: 0.2 }
      );
      ScrollTrigger.create({
        trigger: box,
        ease: 'power1.out',
        animation: anim,
        toggleActions: 'play none none none',
        once: false,
      });
    });
  } else {
    // Mobile Text Fade In Animation
    const anim_fadeinup = gsap.utils.toArray('.anim_fadeinup');
    anim_fadeinup.forEach((box, i) => {
      const anim = gsap.fromTo(
        box.children,
        { autoAlpha: 0, y: 10 },
        { delay: 0.4, duration: 0.6, autoAlpha: 1, y: 0, stagger: 0.2 }
      );
      ScrollTrigger.create({
        trigger: box,
        ease: 'power1.out',
        start: 'top 80%',
        end: 'bottom top',
        animation: anim,
        toggleActions: 'play none none none',
        once: true,
      });
    });
  }

  // Main Intro Animation
  const scroll_fadeani = gsap.utils.toArray('.scroll_fadeani');
  scroll_fadeani.forEach((box, i) => {
    const anim = gsap.fromTo(
      box.children,
      { autoAlpha: 0 },
      { delay: 0.2, duration: 0.6, autoAlpha: 1, stagger: 0.2 }
    );
    ScrollTrigger.create({
      trigger: box,
      ease: 'power1.out',
      start: 'top bottom',
      end: 'top top',
      scrub: true,
      animation: anim,
    });
  });

  // Related Cards animation (content)
  const scroll_fadeani_cards = gsap.utils.toArray('.scroll_fadeani_cards');
  scroll_fadeani_cards.forEach((box, i) => {
    const anim = gsap.fromTo(
      box.children,
      { autoAlpha: 0 },
      { delay: 0.2, duration: 0.6, autoAlpha: 1, stagger: 0.2 }
    );
    ScrollTrigger.create({
      trigger: box,
      ease: 'power1.out',
      start: 'top bottom',
      scrub: 0.9,
      end: 'top top',
      animation: anim,
    });
  });

  // Main Logo Animation
  let mainLogo = gsap.utils.toArray('.main-logo--home');
  let mainLogoAni = gsap
    .timeline({
      scrollTrigger: {
        trigger: '.main-logo--home',
        scrub: 0.9,
        start: 'top 96px',
        // end: '+=80%',
      },
    })
    .to(mainLogo, {
      scale: 0.5,
      ease: 'none',
    });

  // Main Logo Animation
  let subheading = gsap.utils.toArray('.subheading--home');
  let subheadingAni = gsap
    .timeline({
      scrollTrigger: {
        trigger: '.subheading--home',
        scrub: 0.9,
        start: 'top 170px',
        end: 'top 144px',
      },
    })
    .to(subheading, {
      opacity: 0,
      ease: 'none',
    });

  if (window.innerWidth > 767) {
    // Image Left Animation
    let sectionscolumnleft = gsap.utils.toArray('.image-left');
    let t1left = gsap
      .timeline({
        scrollTrigger: {
          trigger: '.image-left',
          scrub: 0.9,
          end: '+=80%',
        },
      })
      .to(sectionscolumnleft, {
        xPercent: -32,
        ease: 'none',
      });

    // Image Right Animation
    let sectionscolumnright = gsap.utils.toArray('.image-right');
    let t2right = gsap
      .timeline({
        scrollTrigger: {
          trigger: '.image-right',
          scrub: 0.9,
          end: '+=80%',
        },
      })
      .to(sectionscolumnright, {
        xPercent: 32,
        ease: 'none',
      });
  }

  const isMenuModal = document.querySelector('.leo-menu-modal');
  if (isMenuModal) {
    mobileMenu();
  }

  const isPopup = document.querySelector('.leo-popup');
  if (isPopup) {
    popup();
  }

  const isDomePopup = document.querySelector('.dome-popup');
  if (isDomePopup) {
    domePopup();
  }

  const isVideoLightbox = document.querySelector('.video-lightbox');
  if (isVideoLightbox) {
    videoLightbox();
  }

  const isAwardsSlider = document.querySelector('.leo-awards-slider');
  if (isAwardsSlider) {
    const awardsSlider = new Swiper('.leo-awards-slider', {
      loop: true,
      slidesPerView: 3,
      centeredSlides: true,
      spaceBetween: 20,
      freeMode: true,
      autoplay: {
        delay: 0,
        disableOnInteraction: false,
      },
      grabCursor: true,
      speed: 3000,
      breakpoints: {
        767: {
          slidesPerView: 5,
          spaceBetween: 20,
        },
        1023: {
          slidesPerView: 7,
          spaceBetween: 30,
        },
      },
    });
  }

  const isHighlightsSlider = document.querySelector('.leo-highlights-slider');
  if (isHighlightsSlider) {
    const highlightsSlider = new Swiper('.leo-highlights-slider', {
      loop: true,
      slidesPerView: 1.5,
      centeredSlides: true,
      spaceBetween: 20,
      grabCursor: true,
      speed: 600,
      navigation: {
        nextEl: '.leo-highlights-slider__next-btn',
        prevEl: '.leo-highlights-slider__prev-btn',
      },
      pagination: {
        el: '.leo-highlights-slider__pagination',
        type: 'progressbar',
      },
      breakpoints: {
        767: {
          slidesPerView: 3.5,
          spaceBetween: 20,
        },
        1023: {
          slidesPerView: 3.5,
          spaceBetween: 35,
        },
      },
    });
    highlightsSlider.on('transitionStart', function () {
      const slider = document.querySelector(
        '.leo-highlights-slider-wrapper .leo-slide-count span'
      );

      if (highlightsSlider.realIndex + 1 > 9) {
        document.querySelector(
          '.leo-highlights-slider-wrapper .leo-slide-count div'
        ).innerHTML = '';
        slider.innerHTML = highlightsSlider.realIndex + 1;
      } else {
        document.querySelector(
          '.leo-highlights-slider-wrapper .leo-slide-count div'
        ).innerHTML = 0;
        slider.innerHTML = highlightsSlider.realIndex + 1;
      }
    });
  }

  const isMediaContentSlider = document.querySelector(
    '.leo-media-content-slider'
  );
  if (isMediaContentSlider) {
    const mediaContentSlider = new Swiper('.leo-media-content-slider', {
      loop: true,
      slidesPerView: 1,
      grabCursor: true,
      speed: 600,
      navigation: {
        nextEl: '.leo-media-content__next-btn',
        prevEl: '.leo-media-content__prev-btn',
      },
    });
  }

  const isStayBanner = document.querySelector('.leo-stay-banner-slider');
  if (isStayBanner) {
    const StayBanner = new Swiper('.leo-stay-banner-slider', {
      loop: true,
      slidesPerView: 1,
      centeredSlides: true,
      grabCursor: true,
      speed: 600,
      navigation: {
        nextEl: '.leo-stay-banner-slider__next-btn',
        prevEl: '.leo-stay-banner-slider__prev-btn',
      },
      breakpoints: {
        767: {
          slidesPerView: 1,
        },
        1023: {
          slidesPerView: 1,
        },
      },
    });
  }

  const isExperiencesSlider = document.querySelector('.leo-experiences-slider');
  if (isExperiencesSlider) {
    const experiencesSlider = new Swiper('.leo-experiences-slider', {
      loop: true,
      slidesPerView: 1.3,
      centeredSlides: true,
      spaceBetween: 20,
      grabCursor: true,
      speed: 1000,
      navigation: {
        nextEl: '.leo-experiences-slider__next-btn',
        prevEl: '.leo-experiences-slider__prev-btn',
      },
      pagination: {
        el: '.leo-experiences-slider__pagination',
        type: 'progressbar',
      },
      breakpoints: {
        767: {
          slidesPerView: 1.7,
          spaceBetween: 20,
        },
        1023: {
          slidesPerView: 1.7,
          spaceBetween: 30,
        },
      },
    });
    experiencesSlider.on('transitionStart', function () {
      const slider = document.querySelector(
        '.leo-experiences-slider-wrapper .leo-slide-count span'
      );

      if (experiencesSlider.realIndex + 1 > 9) {
        document.querySelector(
          '.leo-experiences-slider-wrapper .leo-slide-count div'
        ).innerHTML = '';
        slider.innerHTML = experiencesSlider.realIndex + 1;
      } else {
        document.querySelector(
          '.leo-experiences-slider-wrapper .leo-slide-count div'
        ).innerHTML = 0;
        slider.innerHTML = experiencesSlider.realIndex + 1;
      }
    });
  }

  const isTabs = document.querySelector('.leo-tabs__tab-list__item');
  if (isTabs) {
    tabs();
  }
});

// mobile menu
const mobileMenu = () => {
  document.querySelector('.leo-menu-btn').addEventListener('click', () => {
    document.querySelector('body').style.overflow = 'hidden';
    document.querySelector('.mobile-menu').style.display = 'block';
    setTimeout(function () {
      document.querySelector('body').classList.add('menu-open');
    }, 10);
  });

  document
    .querySelector('.leo-close-menu-modal')
    .addEventListener('click', () => {
      document.querySelector('body').classList.remove('menu-open');
      setTimeout(function () {
        document.querySelector('.mobile-menu').style.display = 'none';
        document.querySelector('body').style.overflow = 'visible';
      }, 400);
    });

  document
    .querySelectorAll('#menu-main-menu .menu-item a')
    .forEach(function (menuItem) {
      menuItem.addEventListener('mouseover', (e) => {
        const videos = document.querySelectorAll(
          '.leo-menu-modal-media-wrapper--desktop .leo-menu-modal-media-wrapper-inner'
        );

        document
          .querySelectorAll('#menu-main-menu .menu-item a')
          .forEach(function (item) {
            item.classList.remove('active');
          });

        e.target.classList.add('active');

        videos.forEach(function (video) {
          video.style.display = 'none';

          if (
            video.getAttribute('id').toLowerCase() ===
            e.target.innerText.toLowerCase()
          ) {
            video.style.display = 'block';
          }
        });
      });
    });
};

// Popup
const popup = () => {
  document.querySelector('.leo-open-popup').addEventListener('click', () => {
    document.querySelector('body').style.overflow = 'hidden';
    document.querySelector('.leo-popup').style.display = 'block';
    setTimeout(function () {
      document.querySelector('body').classList.add('popup-open');
    }, 10);
  });

  document.querySelector('.leo-close-popup').addEventListener('click', () => {
    document.querySelector('body').classList.remove('popup-open');
    setTimeout(function () {
      document.querySelector('.leo-popup').style.display = 'none';
      document.querySelector('body').style.overflow = 'visible';
    }, 400);
  });
};

const domePopup = () => {
  document.querySelector('#dome-open-popup').addEventListener('click', () => {
    document.querySelector('body').style.overflow = 'hidden';
    document.querySelector('.dome-popup').style.display = 'block';
    setTimeout(function () {
      document.querySelector('body').classList.add('popup-open');
    }, 10);
  });

  document.querySelector('.dome-close-popup').addEventListener('click', () => {
    document.querySelector('body').classList.remove('popup-open');
    setTimeout(function () {
      document.querySelector('.dome-popup').style.display = 'none';
      document.querySelector('body').style.overflow = 'visible';
    }, 400);
  });
};

const videoLightbox = () => {
  // open lightbox
  document.querySelectorAll('.play-in-lightbox').forEach((videoBtn) => {
    videoBtn.onclick = () => {
      const video = document.querySelector('.video-lightbox video');
      const videoLightbox = document.querySelector('.video-lightbox');
      const videoClose = document.querySelector('.video-lightbox__close');

      // set video src
      video.src = videoBtn.querySelector(`.video-src`).getAttribute('data-src');

      // show lightbox
      videoLightbox.style.display = 'block';
      setTimeout(function () {
        videoLightbox.style.opacity = '100%';
      }, 10);
      setTimeout(function () {
        video.style.opacity = '100%';
        video.style.transform = 'scale(1,1)';
      }, 140);
      setTimeout(function () {
        videoClose.style.opacity = '100%';
        videoClose.style.transform = 'translateX(0)';
      }, 350);

      video.play();
    };
  });

  // close lightbox
  document.querySelector('.video-lightbox .video-lightbox__inner').onclick =
    () => {
      const video = document.querySelector('.video-lightbox video');
      const videoLightbox = document.querySelector('.video-lightbox');
      const videoClose = document.querySelector('.video-lightbox__close');
      document.querySelector('.video-lightbox');

      // hide lightbox
      videoClose.style.transform = 'translateX(10%)';
      videoClose.style.opacity = '0';

      setTimeout(function () {
        video.style.opacity = '0';
        video.style.transform = 'scale(0.9,0.9)';
      }, 200);
      setTimeout(function () {
        videoLightbox.style.opacity = '0';
      }, 300);
      setTimeout(function () {
        videoLightbox.style.display = 'none';
      }, 600);

      video.pause();
    };
};

// tabs
const tabs = () => {
  const tabs = document.querySelectorAll(`.leo-tabs__tab-list__item`);
  const tabContents = document.querySelectorAll(`.leo-tabs__tab`);

  // set initial active tab
  let activeTab = 'tab-1';

  document.querySelector(`#${activeTab}`).classList.add('active-tab');
  document.querySelector(`#${activeTab}-content`).classList.add('active-tab');

  tabs.forEach((tab) => {
    tab.addEventListener('click', () => {
      activeTab = tab.id;
      for (let i = 0; i < tabs.length; i++) {
        tabs[i].classList.remove('active-tab');
        tabContents[i].classList.remove('active-tab');
      }

      document.querySelector(`#${activeTab}`).classList.add('active-tab');
      document
        .querySelector(`#${activeTab}-content`)
        .classList.add('active-tab');
    });
  });
};

//Video Banner
document.addEventListener('DOMContentLoaded', function () {
  const video = document.getElementById('myVideo');
  const playIcon = document.querySelector('.video-icon-container');

  playIcon.addEventListener('click', function () {
    video.play();
    playIcon.style.display = 'none';
  });

  video.addEventListener('play', function () {
    playIcon.style.display = 'none';
  });

  video.addEventListener('pause', function () {
    playIcon.style.display = 'flex';
  });

  video.addEventListener('click', function () {
    if (video.paused) {
      video.play();
      playIcon.style.display = 'none';
    } else {
      video.pause();
      playIcon.style.display = 'flex';
    }
  });
});

document.addEventListener('DOMContentLoaded', function () {
  const accordionItems = document.querySelectorAll('.accordion-title');   


  accordionItems.forEach((item) => {
    item.addEventListener('click', function () {
      const description = this.nextElementSibling;
      description.classList.toggle('hidden');

      const icons = this.querySelector('.accordion-icon').children;
      icons[0].classList.toggle('hidden');
      icons[1].classList.toggle('hidden');
    });
  });
});

document.addEventListener('DOMContentLoaded', function () {
  var page = 1;  // Start at page 1
  var loading = false;  // Prevent multiple requests

  jQuery('#load-more-btn').on('click', function () {
      if (!loading) {
          loading = true;  // Prevent new request before current one finishes
          page++;  // Increment the page count

          jQuery.ajax({
              url: frontend_ajax_object.ajaxUrl,  // Get AJAX URL from localized script
              type: 'POST',
              data: {
                  action: 'load_more_experiences',  // Action defined in functions.php
                  page: page  // Send the next page number
              },
              success: function (response) {
                  if (response.trim().length === 0) {
                      // If no more posts, hide the button
                      jQuery('#load-more-btn').hide();
                  } else {
                      // Append new experiences directly to the grid
                      jQuery('.experience-grid').append(response);
                  }
                  loading = false;  // Allow new requests again
              }
          });
      }
  });
});

/**
 * @see {@link https://webpack.js.org/api/hot-module-replacement/}
 */
if (import.meta.webpackHot) import.meta.webpackHot.accept(console.error);
