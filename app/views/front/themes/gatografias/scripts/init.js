
    //hamburger menu
    $('.nav-button-holder').on('click', function(e) {

        e.preventDefault();

        if ($(this).is('.inactive')) {

            $(this).toggleClass('inactive active');
            $('body').addClass('has-active-menu');
            $('.nav-button').addClass('active');
            $('.mask-nav-3, #header-3').addClass('is-active');

        } else if ($(this).is('.active')) {

            $(this).toggleClass('inactive active');
            $('body').removeClass('has-active-menu');
            $('.nav-button').removeClass('active');
            $('.mask-nav-3, #header-3').removeClass('is-active');

        }

    });

    $('.menu-nav-3 > li.menu-item-has-children > a').on('click', function(e) {

        e.preventDefault();
        e.stopPropagation();

        if ($(this).parent().hasClass('menu-open'))

            $(this).parent().removeClass('menu-open');

        else {

            $('.menu-nav-3').find('.menu-item-has-children').removeClass('menu-open');

            $(this).parent().addClass('menu-open');

        }

    });

    // end hamburger menu

    // faq

    $('h4.faq-title').on('click', function() {

        if ($(this).next().is(':hidden')) {
            $('h4.faq-title').removeClass('active').next().slideUp();
            $(this).toggleClass('active').next().slideDown();
        } else {
            $('h4.faq-title').removeClass('active').next().slideUp();
        }
        return false;
    });


    //gallery


    $(window).on('resize', function() {

        imageGallery();

    });

    //scroll up button

    $(window).on('scroll', function() {
        if ($(this).scrollTop() > 400) {
            $('.scrollup').fadeIn();
        } else {
            $('.scrollup').fadeOut();
        }
    });

    $("a.scrolltop[href^='#']").on('click', function(e) {
        e.preventDefault();
        var hash = this.hash;
        $('html, body').stop().animate({
            scrollTop: 0
        }, 1000, 'easeOutExpo');

    });

    function imageZoom() {

        $('.gallery-holder').magnificPopup({
            delegate: 'a', // child items selector, by clicking on it popup will open
            type: 'image',
            gallery: {
                enabled: true
            },
            mainClass: 'mfp-with-zoom',
            zoom: {
                enabled: true, // By default it's false, so don't forget to enable it
                duration: 300,
                easing: 'ease-in-out',

            }

            // other options
        });

    }

    function imageGallery() {
        var mobile = $(window).width() < 1024;
        var row_height = mobile ? 120 : 300;
        var imgMargin = mobile ? 10 : 20;

        $('.gallery-holder').justifiedGallery({
            rowHeight: row_height,
            margins: imgMargin
        }).on('jg.complete', imageZoom);

    }
