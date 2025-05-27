// plugins/template-scripts.js
export default defineNuxtPlugin(nuxtApp => {
    if (process.client) {
        const scripts = [
            '/scripts/jquery-3.6.0.min.js',
            '/scripts/jquery-migrate-3.3.2.min.js',
            '/scripts/mmenu.min.js',
            '/scripts/chosen.min.js',
            '/scripts/slick.min.js',
            '/scripts/rangeslider.min.js',
            '/scripts/magnific-popup.min.js',
            '/scripts/waypoints.min.js',
            '/scripts/counterup.min.js',
            '/scripts/jquery-ui.min.js',
            '/scripts/tooltips.min.js',
            '/scripts/custom.js',
            '/scripts/switcher.js'
        ];

        const loadScript = src => {
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = src;
                script.async = false;
                script.onload = () => {
                    console.log(`Loaded: ${src}`);
                    resolve();
                };
                script.onerror = err => {
                    console.error(`Failed to load: ${src}`, err);
                    reject(err);
                };
                document.head.appendChild(script);
            });
        };

        const initPlugins = () => {
            if (!window.jQuery) {
                console.error('jQuery not loaded');
                return;
            }

            const $ = window.jQuery;

            const initCustom = () => {
                console.log('Initializing custom.js logic');

                // Mmenu
                function mmenuInit() {
                    var wi = $(window).width();
                    if (wi <= '1024') {
                        $('.mmenu-init').remove();
                        $('#navigation')
                            .clone()
                            .addClass('mmenu-init')
                            .insertBefore('#navigation')
                            .removeAttr('id')
                            .removeClass('style-1 style-2')
                            .find('ul, div')
                            .removeClass('style-1 style-2 mega-menu mega-menu-content mega-menu-section')
                            .removeAttr('id');
                        $('.mmenu-init').find('ul').addClass('mm-listview');
                        $('.mmenu-init').find('.mobile-styles .mm-listview').unwrap();

                        $('.mmenu-init').mmenu(
                            { counters: true },
                            {
                                offCanvas: { pageNodetype: '#wrapper' }
                            }
                        );

                        var mmenuAPI = $('.mmenu-init').data('mmenu');
                        var $icon = $('.hamburger');

                        $('.mmenu-trigger')
                            .off('click')
                            .click(function () {
                                mmenuAPI.open();
                            });

                        mmenuAPI.bind('open:finish', function () {
                            setTimeout(function () {
                                $icon.addClass('is-active');
                            });
                        });
                        mmenuAPI.bind('close:finish', function () {
                            setTimeout(function () {
                                $icon.removeClass('is-active');
                            });
                        });
                    }
                    $('.mm-next').addClass('mm-fullsubopen');
                }
                mmenuInit();
                $(window).off('resize.mmenu').on('resize.mmenu', mmenuInit);

                // User Menu
                $('.user-menu')
                    .off('click')
                    .on('click', function () {
                        $(this).toggleClass('active');
                    });

                var mouse_is_inside = false;
                $('.user-menu')
                    .off('mouseenter mouseleave')
                    .on('mouseenter', function () {
                        mouse_is_inside = true;
                    })
                    .on('mouseleave', function () {
                        mouse_is_inside = false;
                    });

                $('body')
                    .off('mouseup.userMenu')
                    .mouseup(function () {
                        if (!mouse_is_inside) $('.user-menu').removeClass('active');
                    });

                // Sticky Header
                // $('#header').not('#header.not-sticky').clone(true).addClass('cloned unsticky').insertAfter('#header');
                // $('#header.cloned #sign-in-dialog').remove();
                // $('#navigation.style-2').clone(true).addClass('cloned unsticky').insertAfter('#navigation.style-2');

                // $('#logo .sticky-logo').clone(true).prependTo('#navigation.style-2.cloned ul#responsive');

                // var headerOffset = 140;
                // $(window)
                //     .off('scroll.sticky')
                //     .scroll(function () {
                //         if ($(window).scrollTop() >= headerOffset) {
                //             $('#header.cloned').addClass('sticky').removeClass('unsticky');
                //             $('#navigation.style-2.cloned').addClass('sticky').removeClass('unsticky');
                //         } else {
                //             $('#header.cloned').addClass('unsticky').removeClass('sticky');
                //             $('#navigation.style-2.cloned').addClass('unsticky').removeClass('sticky');
                //         }
                //     });

                // $(window)
                //     .off('scroll.stickyLogo load.stickyLogo')
                //     .on('scroll load', function () {
                //         $('#header.cloned #logo img').attr('src', $('#header #logo img').attr('data-sticky-logo'));
                //     });

                // Back to Top
                var pxShow = 600;
                var scrollSpeed = 500;
                $(window)
                    .off('scroll.backtotop')
                    .scroll(function () {
                        if ($(window).scrollTop() >= pxShow) {
                            $('#backtotop').addClass('visible');
                        } else {
                            $('#backtotop').removeClass('visible');
                        }
                    });

                $('#backtotop a')
                    .off('click')
                    .on('click', function () {
                        $('html, body').animate({ scrollTop: 0 }, scrollSpeed);
                        return false;
                    });

                // Inline CSS
                function inlineCSS() {
                    $(
                        '.main-search-container, section.fullwidth, .listing-slider .item, .listing-slider-small .item, .address-container, .img-box-background, .image-edge, .edge-bg'
                    ).each(function () {
                        var attrImageBG = $(this).attr('data-background-image');
                        var attrColorBG = $(this).attr('data-background-color');
                        if (attrImageBG !== undefined) {
                            $(this).css('background-image', 'url(' + attrImageBG + ')');
                        }
                        if (attrColorBG !== undefined) {
                            $(this).css('background', '' + attrColorBG + '');
                        }
                    });
                }
                inlineCSS();

                function parallaxBG() {
                    $('.parallax').prepend('<div class="parallax-overlay"></div>');
                    $('.parallax').each(function () {
                        var attrImage = $(this).attr('data-background');
                        var attrColor = $(this).attr('data-color');
                        var attrOpacity = $(this).attr('data-color-opacity');
                        if (attrImage !== undefined) {
                            $(this).css('background-image', 'url(' + attrImage + ')');
                        }
                        if (attrColor !== undefined) {
                            $(this)
                                .find('.parallax-overlay')
                                .css('background-color', '' + attrColor + '');
                        }
                        if (attrOpacity !== undefined) {
                            $(this)
                                .find('.parallax-overlay')
                                .css('opacity', '' + attrOpacity + '');
                        }
                    });
                }
                parallaxBG();

                // Image Box
                $('.category-box').each(function () {
                    $(this).append('<div class="category-box-background"></div>');
                    $(this)
                        .children('.category-box-background')
                        .css({ 'background-image': 'url(' + $(this).attr('data-background-image') + ')' });
                });

                $('.img-box').each(function () {
                    $(this).append('<div class="img-box-background"></div>');
                    $(this)
                        .children('.img-box-background')
                        .css({ 'background-image': 'url(' + $(this).attr('data-background-image') + ')' });
                });

                // Parallax
                if ('ontouchstart' in window) {
                    document.documentElement.className = document.documentElement.className + ' touch';
                }
                if (!$('html').hasClass('touch')) {
                    $('.parallax').css('background-attachment', 'fixed');
                }

                function fullscreenFix() {
                    var h = $('body').height();
                    $('.content-b').each(function () {
                        if ($(this).innerHeight() > h) {
                            $(this).closest('.fullscreen').addClass('overflow');
                        }
                    });
                }
                $(window).off('resize.fullscreen').resize(fullscreenFix);
                fullscreenFix();

                function backgroundResize() {
                    var windowH = $(window).height();
                    $('.parallax').each(function () {
                        var path = $(this);
                        var contW = path.width();
                        var contH = path.height();
                        var imgW = path.attr('data-img-width');
                        var imgH = path.attr('data-img-height');
                        var ratio = imgW / imgH;
                        var diff = 100;
                        diff = diff ? diff : 0;
                        var remainingH = 0;
                        if (path.hasClass('parallax') && !$('html').hasClass('touch')) {
                            remainingH = windowH - contH;
                        }
                        imgH = contH + remainingH + diff;
                        imgW = imgH * ratio;
                        if (contW > imgW) {
                            imgW = contW;
                            imgH = imgW / ratio;
                        }
                        path.data('resized-imgW', imgW);
                        path.data('resized-imgH', imgH);
                        path.css('background-size', imgW + 'px ' + imgH + 'px');
                    });
                }
                $(window).off('resize.bg focus.bg').resize(backgroundResize).focus(backgroundResize);
                backgroundResize();

                function parallaxPosition() {
                    var heightWindow = $(window).height();
                    var topWindow = $(window).scrollTop();
                    var bottomWindow = topWindow + heightWindow;
                    var currentWindow = (topWindow + bottomWindow) / 2;
                    $('.parallax').each(function () {
                        var path = $(this);
                        var height = path.height();
                        var top = path.offset().top;
                        var bottom = top + height;
                        if (bottomWindow > top && topWindow < bottom) {
                            var imgH = path.data('resized-imgH');
                            var min = 0;
                            var max = -imgH + heightWindow;
                            var overflowH = height < heightWindow ? imgH - height : imgH - heightWindow;
                            top = top - overflowH;
                            bottom = bottom + overflowH;
                            var value = 0;
                            if ($('.parallax').is('.titlebar')) {
                                value = min + (((max - min) * (currentWindow - top)) / (bottom - top)) * 2;
                            } else {
                                value = min + ((max - min) * (currentWindow - top)) / (bottom - top);
                            }
                            var orizontalPosition = path.attr('data-oriz-pos') || '50%';
                            $(this).css('background-position', orizontalPosition + ' ' + value + 'px');
                        }
                    });
                }
                if (!$('html').hasClass('touch')) {
                    $(window).off('resize.parallax scroll.parallax').resize(parallaxPosition).scroll(parallaxPosition);
                    parallaxPosition();
                }

                if (navigator.userAgent.match(/Trident\/7\./)) {
                    $('body')
                        .off('mousewheel')
                        .on('mousewheel', function () {
                            event.preventDefault();
                            var wheelDelta = event.wheelDelta;
                            var currentScrollPosition = window.pageYOffset;
                            window.scrollTo(0, currentScrollPosition - wheelDelta);
                        });
                }

                // Chosen
                var config = {
                    '.chosen-select': { disable_search_threshold: 10, width: '100%' },
                    '.chosen-select-deselect': { allow_single_deselect: true, width: '100%' },
                    '.chosen-select-no-single': { disable_search_threshold: 100, width: '100%' },
                    '.chosen-select-no-single.no-search': { disable_search_threshold: 10, width: '100%' },
                    '.chosen-select-no-results': { no_results_text: 'Oops, nothing found!' },
                    '.chosen-select-width': { width: '95%' }
                };
                for (var selector in config) {
                    if (config.hasOwnProperty(selector)) {
                        $(selector).chosen('destroy').chosen(config[selector]);
                    }
                }

                // Magnific Popup
                $('.mfp-gallery-container').each(function () {
                    $(this).magnificPopup({
                        type: 'image',
                        delegate: 'a.mfp-gallery',
                        fixedContentPos: true,
                        fixedBgPos: true,
                        overflowY: 'auto',
                        closeBtnInside: false,
                        preloader: true,
                        removalDelay: 0,
                        mainClass: 'mfp-fade',
                        gallery: { enabled: true, tCounter: '' }
                    });
                });

                $('.popup-with-zoom-anim').magnificPopup({
                    type: 'inline',
                    fixedContentPos: false,
                    fixedBgPos: true,
                    overflowY: 'auto',
                    closeBtnInside: true,
                    preloader: false,
                    midClick: true,
                    removalDelay: 300,
                    mainClass: 'my-mfp-zoom-in'
                });

                $('.mfp-image').magnificPopup({
                    type: 'image',
                    closeOnContentClick: true,
                    mainClass: 'mfp-fade',
                    image: { verticalFit: true }
                });

                $('.popup-youtube, .popup-vimeo, .popup-gmaps').magnificPopup({
                    disableOn: 700,
                    type: 'iframe',
                    mainClass: 'mfp-fade',
                    removalDelay: 160,
                    preloader: false,
                    fixedContentPos: false
                });

                // Slick Carousel - Đảm bảo kiểm tra sự tồn tại và bọc trong try...catch
                if ($('.fullwidth-slick-carousel').length) {
                    try {
                        $('.fullwidth-slick-carousel').slick('unslick');
                    } catch (e) {
                        console.log('fullwidth-slick-carousel not initialized yet, skipping unslick');
                    }
                    $('.fullwidth-slick-carousel').slick({
                        centerMode: true,
                        centerPadding: '20%',
                        slidesToShow: 3,
                        dots: true,
                        arrows: false,
                        responsive: [
                            { breakpoint: 1920, settings: { centerPadding: '15%', slidesToShow: 3 } },
                            { breakpoint: 1441, settings: { centerPadding: '10%', slidesToShow: 3 } },
                            { breakpoint: 1025, settings: { centerPadding: '10px', slidesToShow: 2 } },
                            { breakpoint: 767, settings: { centerPadding: '10px', slidesToShow: 1 } }
                        ]
                    });
                    $(window)
                        .off('load.slickFix resize.slickFix')
                        .on('load resize', function () {
                            var carouselListItems = $('.fullwidth-slick-carousel .fw-carousel-item').length;
                            if (carouselListItems < 4) {
                                $('.fullwidth-slick-carousel .slick-slide').css({
                                    'pointer-events': 'all',
                                    opacity: '1'
                                });
                            }
                        });
                }

                if ($('.testimonial-carousel').length) {
                    try {
                        $('.testimonial-carousel').slick('unslick');
                    } catch (e) {
                        console.log('testimonial-carousel not initialized yet, skipping unslick');
                    }
                    $('.testimonial-carousel').slick({
                        centerMode: true,
                        centerPadding: '34%',
                        slidesToShow: 1,
                        dots: true,
                        arrows: false,
                        responsive: [
                            { breakpoint: 1025, settings: { centerPadding: '10px', slidesToShow: 2 } },
                            { breakpoint: 767, settings: { centerPadding: '10px', slidesToShow: 1 } }
                        ]
                    });
                }

                if ($('.listing-slider').length) {
                    try {
                        $('.listing-slider').slick('unslick');
                    } catch (e) {
                        console.log('listing-slider not initialized yet, skipping unslick');
                    }
                    $('.listing-slider').slick({
                        centerMode: true,
                        centerPadding: '20%',
                        slidesToShow: 2,
                        responsive: [
                            { breakpoint: 1367, settings: { centerPadding: '15%' } },
                            { breakpoint: 1025, settings: { centerPadding: '0' } },
                            { breakpoint: 767, settings: { centerPadding: '0', slidesToShow: 1 } }
                        ]
                    });
                }

                if ($('.listing-slider-small').length) {
                    try {
                        $('.listing-slider-small').slick('unslick');
                    } catch (e) {
                        console.log('listing-slider-small not initialized yet, skipping unslick');
                    }
                    $('.listing-slider-small').slick({
                        centerMode: true,
                        centerPadding: '0',
                        slidesToShow: 3,
                        responsive: [{ breakpoint: 767, settings: { slidesToShow: 1 } }]
                    });
                    $(window)
                        .off('load.slickFixSmall resize.slickFixSmall')
                        .on('load resize', function () {
                            var carouselListItems = $('.listing-slider-small .slick-track').children().length;
                            if (carouselListItems < 2) {
                                $('.listing-slider-small .slick-track').css({ transform: 'none' });
                            }
                        });
                }

                $('.home-search-carousel').append(
                    "<div class='slider-controls-container'>" +
                        "<div class='slider-controls'>" +
                        "<button type='button' class='slide-m-prev'></button>" +
                        "<div class='slide-m-dots'></div>" +
                        "<button type='button' class='slide-m-next'></button>" +
                        '</div>' +
                        '</div>'
                );

                if ($('.home-search-carousel').length) {
                    try {
                        $('.home-search-carousel').slick('unslick');
                    } catch (e) {
                        console.log('home-search-carousel not initialized yet, skipping unslick');
                    }
                    $('.home-search-carousel').slick({
                        slide: '.home-search-slide',
                        centerMode: true,
                        centerPadding: '15%',
                        slidesToShow: 1,
                        dots: true,
                        arrows: true,
                        appendDots: $('.home-search-carousel .slide-m-dots'),
                        prevArrow: $('.home-search-carousel .slide-m-prev'),
                        nextArrow: $('.home-search-carousel .slide-m-next'),
                        responsive: [
                            { breakpoint: 1940, settings: { centerPadding: '13%', slidesToShow: 1 } },
                            { breakpoint: 1640, settings: { centerPadding: '8%', slidesToShow: 1 } },
                            { breakpoint: 1430, settings: { centerPadding: '50px', slidesToShow: 1 } },
                            { breakpoint: 1370, settings: { centerPadding: '20px', slidesToShow: 1 } },
                            { breakpoint: 767, settings: { centerPadding: '20px', slidesToShow: 1 } }
                        ]
                    });
                    $(window)
                        .off('load.carouselPos')
                        .on('load', function () {
                            $('.home-search-slider-headlines').each(function () {
                                var carouselHeadlineHeight = $(this).height();
                                $(this).css('padding-bottom', carouselHeadlineHeight + 30);
                            });
                            $('.home-search-carousel').removeClass('carousel-not-ready');
                        });
                    $(window)
                        .off('load.carouselPosResize resize.carouselPosResize')
                        .on('load resize', function () {
                            if ($(window).width() < 992) {
                                $('.home-search-slider-headlines').each(function () {
                                    $(this).css('bottom', $('.main-search-input').height() + 65);
                                });
                            }
                        });
                }

                // if ($('.simple-slick-carousel').length) {
                //     try {
                //         $('.simple-slick-carousel').slick('unslick');
                //     } catch (e) {
                //         console.log('simple-slick-carousel not initialized yet, skipping unslick');
                //     }
                //     $('.simple-slick-carousel').slick({
                //         infinite: true,
                //         slidesToShow: 3,
                //         slidesToScroll: 3,
                //         dots: true,
                //         arrows: true,
                //         responsive: [
                //             { breakpoint: 992, settings: { slidesToShow: 2, slidesToScroll: 2 } },
                //             { breakpoint: 769, settings: { slidesToShow: 1, slidesToScroll: 1 } }
                //         ]
                //     });
                // }

                if ($('.simple-fw-slick-carousel').length) {
                    try {
                        $('.simple-fw-slick-carousel').slick('unslick');
                    } catch (e) {
                        console.log('simple-fw-slick-carousel not initialized yet, skipping unslick');
                    }
                    $('.simple-fw-slick-carousel').slick({
                        infinite: true,
                        slidesToShow: 5,
                        slidesToScroll: 1,
                        dots: true,
                        arrows: false,
                        responsive: [
                            { breakpoint: 1610, settings: { slidesToShow: 4 } },
                            { breakpoint: 1365, settings: { slidesToShow: 3 } },
                            { breakpoint: 1024, settings: { slidesToShow: 2 } },
                            { breakpoint: 767, settings: { slidesToShow: 1 } }
                        ]
                    });
                }

                if ($('.logo-slick-carousel').length) {
                    try {
                        $('.logo-slick-carousel').slick('unslick');
                    } catch (e) {
                        console.log('logo-slick-carousel not initialized yet, skipping unslick');
                    }
                    $('.logo-slick-carousel').slick({
                        infinite: true,
                        slidesToShow: 5,
                        slidesToScroll: 4,
                        dots: true,
                        arrows: true,
                        responsive: [
                            { breakpoint: 992, settings: { slidesToShow: 3, slidesToScroll: 3 } },
                            { breakpoint: 769, settings: { slidesToShow: 1, slidesToScroll: 1 } }
                        ]
                    });
                }

                // Tabs
                var $tabsNav = $('.tabs-nav'),
                    $tabsNavLis = $tabsNav.children('li');
                $tabsNav.each(function () {
                    var $this = $(this);
                    $this.next().children('.tab-content').stop(true, true).hide().first().show();
                    $this.children('li').first().addClass('active').stop(true, true).show();
                });
                $tabsNavLis.off('click').on('click', function (e) {
                    var $this = $(this);
                    $this.siblings().removeClass('active').end().addClass('active');
                    $this.parent().next().children('.tab-content').stop(true, true).hide().siblings($this.find('a').attr('href')).fadeIn();
                    e.preventDefault();
                });
                var hash = window.location.hash;
                var anchor = $('.tabs-nav a[href="' + hash + '"]');
                if (anchor.length === 0) {
                    $('.tabs-nav li:first').addClass('active').show();
                    $('.tab-content:first').show();
                } else {
                    anchor.parent('li').click();
                }

                // Accordions
                var $accor = $('.accordion');
                $accor.each(function () {
                    $(this).toggleClass('ui-accordion ui-widget ui-helper-reset');
                    $(this).find('h3').addClass('ui-accordion-header ui-helper-reset ui-state-default ui-accordion-icons ui-corner-all');
                    $(this).find('div').addClass('ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom');
                    $(this).find('div').hide();
                });
                var $trigger = $accor.find('h3');
                $trigger.off('click').on('click', function (e) {
                    var location = $(this).parent();
                    if ($(this).next().is(':hidden')) {
                        var $triggerloc = $('h3', location);
                        $triggerloc.removeClass('ui-accordion-header-active ui-state-active ui-corner-top').next().slideUp(300);
                        $triggerloc.find('span').removeClass('ui-accordion-icon-active');
                        $(this).find('span').addClass('ui-accordion-icon-active');
                        $(this).addClass('ui-accordion-header-active ui-state-active ui-corner-top').next().slideDown(300);
                    }
                    e.preventDefault();
                });

                // Toggle
                $('.toggle-container').hide();
                $('.trigger, .trigger.opened')
                    .off('click')
                    .on('click', function (a) {
                        $(this).toggleClass('active');
                        a.preventDefault();
                    });
                $('.trigger')
                    .off('click')
                    .on('click', function () {
                        $(this).next('.toggle-container').slideToggle(300);
                    });
                $('.trigger.opened').addClass('active').next('.toggle-container').show();

                // Tooltips (tipTip)
                $('.tooltip.top').tipTip({ defaultPosition: 'top' });
                $('.tooltip.bottom').tipTip({ defaultPosition: 'bottom' });
                $('.tooltip.left').tipTip({ defaultPosition: 'left' });
                $('.tooltip.right').tipTip({ defaultPosition: 'right' });

                // Rating Overview
                function ratingOverview(ratingElem) {
                    $(ratingElem).each(function () {
                        var dataRating = $(this).attr('data-rating');
                        if (dataRating >= 4.0) {
                            $(this).addClass('high');
                            $(this)
                                .find('.rating-bars-rating-inner')
                                .css({ width: (dataRating / 5) * 100 + '%' });
                        } else if (dataRating >= 3.0) {
                            $(this).addClass('mid');
                            $(this)
                                .find('.rating-bars-rating-inner')
                                .css({ width: (dataRating / 5) * 80 + '%' });
                        } else if (dataRating < 3.0) {
                            $(this).addClass('low');
                            $(this)
                                .find('.rating-bars-rating-inner')
                                .css({ width: (dataRating / 5) * 60 + '%' });
                        }
                    });
                }
                ratingOverview('.rating-bars-rating');
                $(window)
                    .off('resize.rating')
                    .on('resize', function () {
                        ratingOverview('.rating-bars-rating');
                    });

                // Upload Button
                var uploadButton = {
                    $button: $('.uploadButton-input'),
                    $nameField: $('.uploadButton-file-name')
                };
                uploadButton.$button.off('change').on('change', function () {
                    var selectedFile = [];
                    for (var i = 0; i < $(this).get(0).files.length; ++i) {
                        selectedFile.push($(this).get(0).files[i].name + '<br>');
                    }
                    uploadButton.$nameField.html(selectedFile);
                });

                // Recaptcha
                $('.message-vendor')
                    .off('click')
                    .on('click', function () {
                        $('.captcha-holder').addClass('visible');
                    });

                // Like Icon
                $('.like-icon, .widget-button, .like-button')
                    .off('click')
                    .on('click', function (e) {
                        e.preventDefault();
                        $(this).toggleClass('liked');
                        $(this).children('.like-icon').toggleClass('liked');
                    });

                // Search Form More Options
                $('.more-search-options-trigger')
                    .off('click')
                    .on('click', function (e) {
                        e.preventDefault();
                        $('.more-search-options, .more-search-options-trigger').toggleClass('active');
                        $('.more-search-options.relative').animate({ height: 'toggle', opacity: 'toggle' }, 300);
                    });

                // Half Screen Map
                $(window)
                    .off('load.map resize.map')
                    .on('load resize', function () {
                        var winWidth = $(window).width();
                        var headerHeight = $('#header-container').height();
                        $('.fs-inner-container, .fs-inner-container.map-fixed, #dashboard').css('padding-top', headerHeight);
                        if (winWidth < 992) {
                            $('.fs-inner-container.map-fixed').insertBefore('.fs-inner-container.content');
                        } else {
                            $('.fs-inner-container.content').insertBefore('.fs-inner-container.map-fixed');
                        }
                    });

                // CounterUp
                $(window)
                    .off('load.counter')
                    .on('load', function () {
                        $('.dashboard-stat-content h4').counterUp({
                            delay: 100,
                            time: 800
                        });
                    });

                // Rating Script
                $('.leave-rating input')
                    .off('change')
                    .change(function () {
                        var $radio = $(this);
                        $('.leave-rating .selected').removeClass('selected');
                        $radio.closest('label').addClass('selected');
                    });

                // Dashboard Scripts
                $('.dashboard-nav ul li a')
                    .off('click')
                    .on('click', function () {
                        if ($(this).closest('li').has('ul').length) {
                            $(this).parent('li').toggleClass('active');
                        }
                    });

                $(window)
                    .off('load.dashNav resize.dashNav')
                    .on('load resize', function () {
                        var wrapperHeight = window.innerHeight;
                        var headerHeight = $('#header-container').height();
                        var winWidth = $(window).width();
                        if (winWidth > 992) {
                            $('.dashboard-nav-inner').css('max-height', wrapperHeight - headerHeight);
                        } else {
                            $('.dashboard-nav-inner').css('max-height', '');
                        }
                    });

                $('.tip').each(function () {
                    var tipContent = $(this).attr('data-tip-content');
                    $(this).append('<div class="tip-content">' + tipContent + '</div>');
                });

                $('.verified-badge.with-tip').each(function () {
                    var tipContent = $(this).attr('data-tip-content');
                    $(this).append('<div class="tip-content">' + tipContent + '</div>');
                });

                $(window)
                    .off('load.badge resize.badge')
                    .on('load resize', function () {
                        var verifiedBadge = $('.verified-badge.with-tip');
                        verifiedBadge.find('.tip-content').css({
                            width: verifiedBadge.outerWidth(),
                            'max-width': verifiedBadge.outerWidth()
                        });
                    });

                $('.add-listing-section').each(function () {
                    var switcherSection = $(this);
                    var switcherInput = $(this).find('.switch input');
                    if (switcherInput.is(':checked')) {
                        $(switcherSection).addClass('switcher-on');
                    }
                    switcherInput.off('change').change(function () {
                        if (this.checked === true) {
                            $(switcherSection).addClass('switcher-on');
                        } else {
                            $(switcherSection).removeClass('switcher-on');
                        }
                    });
                });

                $('.dashboard-responsive-nav-trigger')
                    .off('click')
                    .on('click', function (e) {
                        e.preventDefault();
                        $(this).toggleClass('active');
                        var dashboardNavContainer = $('body').find('.dashboard-nav');
                        if ($(this).hasClass('active')) {
                            $(dashboardNavContainer).addClass('active');
                        } else {
                            $(dashboardNavContainer).removeClass('active');
                        }
                    });

                $(window)
                    .off('load.msg resize.msg')
                    .on('load resize', function () {
                        var msgContentHeight = $('.message-content').outerHeight();
                        var msgInboxHeight = $('.messages-inbox ul').height();
                        if (msgContentHeight > msgInboxHeight) {
                            $('.messages-container-inner .messages-inbox ul').css('max-height', msgContentHeight);
                        }
                    });

                // Time Slots
                $('.day-slots').each(function () {
                    var daySlots = $(this);
                    daySlots
                        .find('.add-slot-btn')
                        .off('click')
                        .on('click', function () {
                            var slotTime_Start = daySlots.find('.add-slot-inputs input.time-slot-start').val();
                            var slotTimePM_AM_Start = daySlots.find('.add-slot-inputs select.time-slot-start').val();
                            var slotTime_End = daySlots.find('.add-slot-inputs input.time-slot-end').val();
                            var slotTimePM_AM_End = daySlots.find('.add-slot-inputs select.time-slot-end').val();
                            if (slotTime_Start.length > 0 && slotTime_End.length > 0) {
                                var newTimeSlot = daySlots
                                    .find('.single-slot.cloned')
                                    .clone(true)
                                    .addClass('slot-animation')
                                    .removeClass('cloned');
                                setTimeout(function () {
                                    newTimeSlot.removeClass('slot-animation');
                                }, 300);
                                newTimeSlot.find('.plusminus input').val('1');
                                newTimeSlot.find('.plusminus').numberPicker();
                                var $twelve_hr = $('.add-slot-inputs select.twelve-hr');
                                if ($twelve_hr.length) {
                                    newTimeSlot
                                        .find('.single-slot-time')
                                        .html(
                                            slotTime_Start +
                                                ' ' +
                                                '<i class="am-pm">' +
                                                slotTimePM_AM_Start +
                                                '</i>' +
                                                ' - ' +
                                                slotTime_End +
                                                ' ' +
                                                '<i class="am-pm">' +
                                                slotTimePM_AM_End +
                                                '</i>'
                                        );
                                } else {
                                    newTimeSlot.find('.single-slot-time').html('' + slotTime_Start + ' - ' + slotTime_End);
                                }
                                newTimeSlot.appendTo(daySlots.find('.slots-container'));
                                $('.slots-container').sortable('refresh');
                            } else {
                                daySlots.find('.add-slot').addClass('add-slot-shake-error');
                                setTimeout(function () {
                                    daySlots.find('.add-slot').removeClass('add-slot-shake-error');
                                }, 600);
                            }
                        });

                    function hideSlotInfo() {
                        var slotCount = daySlots.find('.slots-container').children().length;
                        if (slotCount < 1) {
                            daySlots.find('.no-slots').addClass('no-slots-fadein').removeClass('no-slots-fadeout');
                        }
                    }
                    hideSlotInfo();

                    daySlots
                        .find('.remove-slot')
                        .off('click')
                        .on('click', function () {
                            $(this)
                                .closest('.single-slot')
                                .animate({ height: 0, opacity: 0 }, 'fast', function () {
                                    $(this).remove();
                                });
                            setTimeout(function () {
                                hideSlotInfo();
                            }, 400);
                        });

                    daySlots
                        .find('.add-slot-btn')
                        .off('click.slotInfo')
                        .on('click', function () {
                            var slotCount = daySlots.find('.slots-container').children().length;
                            if (slotCount >= 1) {
                                daySlots.find('.no-slots').removeClass('no-slots-fadein').addClass('no-slots-fadeout');
                            }
                        });
                });

                $('.slots-container').sortable();

                if ($('.availability-slots').attr('data-clock-type') == '24hr') {
                    $('.availability-slots').addClass('twenty-four-clock');
                    $('.availability-slots').find('input[type="time"]').attr({ max: '24:00' });
                }

                $('.plusminus').numberPicker();

                // Pricing List
                function newMenuItem() {
                    var newElem = $('tr.pricing-list-item.pattern').first().clone();
                    newElem.find('input').val('');
                    newElem.appendTo('table#pricing-list-container');
                }

                if ($('table#pricing-list-container').is('*')) {
                    $('.add-pricing-list-item')
                        .off('click')
                        .on('click', function (e) {
                            e.preventDefault();
                            newMenuItem();
                        });

                    $(document)
                        .off('click.pricing')
                        .on('click', '#pricing-list-container .delete', function (e) {
                            e.preventDefault();
                            $(this).parent().parent().remove();
                        });

                    $('.add-pricing-submenu')
                        .off('click')
                        .on('click', function (e) {
                            e.preventDefault();
                            var newElem = $(
                                '<tr class="pricing-list-item pricing-submenu">' +
                                    '<td>' +
                                    '<div class="fm-move"><i class="sl sl-icon-cursor-move"></i></div>' +
                                    '<div class="fm-input"><input type="text" placeholder="Category Title" /></div>' +
                                    '<div class="fm-close"><a class="delete" href="#"><i class="fa fa-remove"></i></a></div>' +
                                    '</td>' +
                                    '</tr>'
                            );
                            newElem.appendTo('table#pricing-list-container');
                        });

                    $('table#pricing-list-container tbody').sortable({
                        forcePlaceholderSize: true,
                        forceHelperSize: false,
                        placeholder: 'sortableHelper',
                        zIndex: 999990,
                        opacity: 0.6,
                        tolerance: 'pointer',
                        start: function (e, ui) {
                            ui.placeholder.height(ui.helper.outerHeight());
                        }
                    });
                }

                var fieldUnit = $('.pricing-price').children('input').attr('data-unit');
                $('.pricing-price')
                    .children('input')
                    .before('<i class="data-unit">' + fieldUnit + '</i>');

                // Notifications
                $('a.close')
                    .removeAttr('href')
                    .off('click')
                    .on('click', function () {
                        var fadeOut = { opacity: 0, transition: 'opacity 0.5s' };
                        $(this).parent().css(fadeOut).slideUp();
                    });

                // Panel Dropdown
                function close_panel_dropdown() {
                    $('.panel-dropdown').removeClass('active');
                    $('.fs-inner-container.content').removeClass('faded-out');
                }

                $('.panel-dropdown a')
                    .off('click')
                    .on('click', function (e) {
                        if ($(this).parent().is('.active')) {
                            close_panel_dropdown();
                        } else {
                            close_panel_dropdown();
                            $(this).parent().addClass('active');
                            $('.fs-inner-container.content').addClass('faded-out');
                        }
                        e.preventDefault();
                    });

                $('.panel-buttons button')
                    .off('click')
                    .on('click', function () {
                        $('.panel-dropdown').removeClass('active');
                        $('.fs-inner-container.content').removeClass('faded-out');
                    });

                var mouse_is_inside = false;
                $('.panel-dropdown')
                    .off('hover')
                    .hover(
                        function () {
                            mouse_is_inside = true;
                        },
                        function () {
                            mouse_is_inside = false;
                        }
                    );
                $('body')
                    .off('mouseup.panel')
                    .mouseup(function () {
                        if (!mouse_is_inside) close_panel_dropdown();
                    });

                $('.checkboxes.categories input')
                    .off('change')
                    .on('change', function () {
                        if ($(this).hasClass('all')) {
                            $(this).parents('.checkboxes').find('input').prop('checked', false);
                            $(this).prop('checked', true);
                        } else {
                            $('.checkboxes input.all').prop('checked', false);
                        }
                    });

                $('input[type="range"].distance-radius')
                    .rangeslider('destroy')
                    .rangeslider({
                        polyfill: false,
                        onInit: function () {
                            this.output = $('<div class="range-output" />').insertBefore(this.$range).html(this.$element.val());
                            var radiustext = $('.distance-radius').attr('data-title');
                            $('.range-output').after('<i class="data-radius-title">' + radiustext + '</i>');
                        },
                        onSlide: function (position, value) {
                            this.output.html(value);
                        }
                    });

                // Show More Button
                $('.show-more-button')
                    .off('click')
                    .on('click', function (e) {
                        e.preventDefault();
                        $(this).toggleClass('active');
                        $('.show-more').toggleClass('visible');
                        if ($('.show-more').is('.visible')) {
                            var el = $('.show-more'),
                                curHeight = el.height(),
                                autoHeight = el.css('height', 'auto').height();
                            el.height(curHeight).animate({ height: autoHeight }, 400);
                        } else {
                            $('.show-more').animate({ height: '450px' }, 400);
                        }
                    });

                // Listing Page Nav
                $(window)
                    .off('load.listNav resize.listNav scroll.listNav')
                    .on('load resize scroll', function () {
                        var containerWidth = $('.container').width();
                        $('.listing-nav-container.cloned .listing-nav').css('width', containerWidth);
                    });

                if (document.getElementById('listing-nav') !== null) {
                    $(window)
                        .off('scroll.listNav')
                        .scroll(function () {
                            var window_top = $(window).scrollTop();
                            var div_top = $('.listing-nav').not('.listing-nav-container.cloned .listing-nav').offset().top + 90;
                            if (window_top > div_top) {
                                $('.listing-nav-container.cloned').addClass('stick');
                            } else {
                                $('.listing-nav-container.cloned').removeClass('stick');
                            }
                        });
                }

                $('.listing-nav-container').clone(true).addClass('cloned').prependTo('body');

                $('.listing-nav a, a.listing-address, .star-rating a')
                    .off('click')
                    .on('click', function (e) {
                        e.preventDefault();
                        $('html,body').scrollTo(this.hash, this.hash, { gap: { y: -20 } });
                    });

                $('.listing-nav li:first-child a, a.add-review-btn, a[href="#add-review"]')
                    .off('click')
                    .on('click', function (e) {
                        e.preventDefault();
                        $('html,body').scrollTo(this.hash, this.hash, { gap: { y: -100 } });
                    });

                $(window)
                    .off('load.listNavHighlight resize.listNavHighlight')
                    .on('load resize', function () {
                        var aChildren = $('.listing-nav li').children();
                        var aArray = [];
                        for (var i = 0; i < aChildren.length; i++) {
                            var aChild = aChildren[i];
                            var ahref = $(aChild).attr('href');
                            aArray.push(ahref);
                        }

                        $(window)
                            .off('scroll.listNavHighlight')
                            .scroll(function () {
                                var windowPos = $(window).scrollTop();
                                for (var i = 0; i < aArray.length; i++) {
                                    var theID = aArray[i];
                                    var divPos = $(theID).offset().top - 150;
                                    var divHeight = $(theID).height();
                                    if (windowPos >= divPos && windowPos < divPos + divHeight) {
                                        $("a[href='" + theID + "']").addClass('active');
                                    } else {
                                        $("a[href='" + theID + "']").removeClass('active');
                                    }
                                }
                            });
                    });

                // Payment Accordion
                var radios = document.querySelectorAll('.payment-tab-trigger > input');
                for (var i = 0; i < radios.length; i++) {
                    radios[i].removeEventListener('change', expandAccordion);
                    radios[i].addEventListener('change', expandAccordion);
                }

                function expandAccordion(event) {
                    var allTabs = document.querySelectorAll('.payment-tab');
                    for (var i = 0; i < allTabs.length; i++) {
                        allTabs[i].classList.remove('payment-tab-active');
                    }
                    event.target.parentNode.parentNode.classList.add('payment-tab-active');
                }

                // Booking Sticky Footer
                $('.booking-sticky-footer a.button')
                    .off('click')
                    .on('click', function (e) {
                        var $anchor = $(this);
                        $('html, body').animate({ scrollTop: $($anchor.attr('href')).offset().top - 100 }, 1000);
                    });

                // Contact Form
                var shake = 'No';
                $('#message').hide();
                $('#name, #comments, #subject')
                    .off('focusout')
                    .focusout(function () {
                        if (!$(this).val()) {
                            $(this).addClass('error').parent().find('mark').removeClass('valid').addClass('error');
                        } else {
                            $(this).removeClass('error').parent().find('mark').removeClass('error').addClass('valid');
                        }
                        $('#submit').prop('disabled', false).removeClass('disabled');
                    });
                $('#email')
                    .off('focusout')
                    .focusout(function () {
                        if (!$(this).val() || !isEmail($(this).val())) {
                            $(this).addClass('error').parent().find('mark').removeClass('valid').addClass('error');
                        } else {
                            $(this).removeClass('error').parent().find('mark').removeClass('error').addClass('valid');
                        }
                    });

                $('#email')
                    .off('focusin')
                    .focusin(function () {
                        $('#submit').prop('disabled', false).removeClass('disabled');
                    });

                $('#submit')
                    .off('click')
                    .on('click', function () {
                        $('#contact-message').slideUp(200, function () {
                            $('#contact-message').hide();
                            $('#name, #subject, #phone, #comments, #website, #email').triggerHandler('focusout');
                            if ($('#contact mark.error').size() > 0) {
                                if (shake == 'Yes') {
                                    $('#contact').effect('shake', { times: 2 }, 75, function () {
                                        $('#contact input.error:first, #contact textarea.error:first').focus();
                                    });
                                } else $('#contact input.error:first, #contact textarea.error:first').focus();
                                return false;
                            }
                        });
                    });

                $('#contactform')
                    .off('submit')
                    .submit(function () {
                        if ($('#contact mark.error').size() > 0) {
                            if (shake == 'Yes') {
                                $('#contact').effect('shake', { times: 2 }, 75);
                            }
                            return false;
                        }
                        var action = $(this).attr('action');
                        $('#contact #submit').after('<img src="images/loader.gif" class="loader" />');
                        $('#submit').prop('disabled', true).addClass('disabled');
                        $.post(action, $('#contactform').serialize(), function (data) {
                            $('#contact-message').html(data);
                            $('#contact-message').slideDown();
                            $('#contactform img.loader').fadeOut('slow', function () {
                                $(this).remove();
                            });
                            if (data.match('success') !== null) $('#contactform').slideUp('slow');
                        });
                        return false;
                    });

                function isEmail(emailAddress) {
                    var pattern = new RegExp(
                        /^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i
                    );
                    return pattern.test(emailAddress);
                }

                // Ratings (Numerical and Star)
                function numericalRating(ratingElem) {
                    $(ratingElem).each(function () {
                        var dataRating = $(this).attr('data-rating');
                        if (dataRating >= 4.0) {
                            $(this).addClass('high');
                        } else if (dataRating >= 3.0) {
                            $(this).addClass('mid');
                        } else if (dataRating < 3.0) {
                            $(this).addClass('low');
                        }
                    });
                }
                numericalRating('.numerical-rating');

                function starRating(ratingElem) {
                    $(ratingElem).each(function () {
                        var dataRating = $(this).attr('data-rating');
                        function starsOutput(firstStar, secondStar, thirdStar, fourthStar, fifthStar) {
                            return (
                                '<span class="' +
                                firstStar +
                                '"></span>' +
                                '<span class="' +
                                secondStar +
                                '"></span>' +
                                '<span class="' +
                                thirdStar +
                                '"></span>' +
                                '<span class="' +
                                fourthStar +
                                '"></span>' +
                                '<span class="' +
                                fifthStar +
                                '"></span>'
                            );
                        }
                        var fiveStars = starsOutput('star', 'star', 'star', 'star', 'star');
                        var fourHalfStars = starsOutput('star', 'star', 'star', 'star', 'star half');
                        var fourStars = starsOutput('star', 'star', 'star', 'star', 'star empty');
                        var threeHalfStars = starsOutput('star', 'star', 'star', 'star half', 'star empty');
                        var threeStars = starsOutput('star', 'star', 'star', 'star empty', 'star empty');
                        var twoHalfStars = starsOutput('star', 'star', 'star half', 'star empty', 'star empty');
                        var twoStars = starsOutput('star', 'star', 'star empty', 'star empty', 'star empty');
                        var oneHalfStar = starsOutput('star', 'star half', 'star empty', 'star empty', 'star empty');
                        var oneStar = starsOutput('star', 'star empty', 'star empty', 'star empty', 'star empty');
                        if (dataRating >= 4.75) {
                            $(this).append(fiveStars);
                        } else if (dataRating >= 4.25) {
                            $(this).append(fourHalfStars);
                        } else if (dataRating >= 3.75) {
                            $(this).append(fourStars);
                        } else if (dataRating >= 3.25) {
                            $(this).append(threeHalfStars);
                        } else if (dataRating >= 2.75) {
                            $(this).append(threeStars);
                        } else if (dataRating >= 2.25) {
                            $(this).append(twoHalfStars);
                        } else if (dataRating >= 1.75) {
                            $(this).append(twoStars);
                        } else if (dataRating >= 1.25) {
                            $(this).append(oneHalfStar);
                        } else if (dataRating < 1.25) {
                            $(this).append(oneStar);
                        }
                    });
                }
                starRating('.star-rating');
            };

            // Gọi initCustom
            initCustom();

            // Waypoints (dùng trong parallax)
            if (typeof Waypoint !== 'undefined') {
                console.log('Initializing waypoints');
                $('.waypoint').each(function () {
                    new Waypoint({
                        element: this,
                        handler: () => $(this.element).addClass('animated'),
                        offset: '80%'
                    });
                });
            } else {
                console.log('waypoints not available');
            }

            // CounterUp
            if ($.fn.counterUp) {
                console.log('Initializing counterUp');
                $('.counter').counterUp('destroy');
                $('.counter').counterUp({
                    delay: 10,
                    time: 1000
                });
            }

            // RangeSlider
            if ($.fn.rangeslider) {
                console.log('Initializing rangeslider');
                $('input[type="range"]').rangeslider('destroy').rangeslider({
                    polyfill: false
                });
            }
        };

        Promise.all(scripts.map(loadScript))
            .then(() => {
                console.log('All scripts loaded successfully');
                initPlugins();
            })
            .catch(err => {
                console.error('Error loading scripts:', err);
            });

        nuxtApp.hook('page:finish', () => {
            console.log('Page changed, reinitializing plugins');
            setTimeout(() => {
                initPlugins();
            }, 200);
        });
    }
});
