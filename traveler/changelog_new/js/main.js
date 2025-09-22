jQuery(function($){
    $(function() {
        // grab the initial top offset of the navigation
        var stickyNavTop = 20;

        // our function that decides weather the navigation bar should have "fixed" css position or not.
        var stickyNav = function(){
            var scrollTop = $(window).scrollTop(); // our current vertical position from the top
            if (scrollTop >= stickyNavTop || scrollTop <= 26) {
                $('#main-menu').removeClass('sticky');
                $('.header').css({'padding-top' : '20px'});
            } else {
                $('#main-menu').addClass('sticky');
                $('.header').css({'padding-top' : '80px'});
            }
            stickyNavTop = scrollTop;
        };

        stickyNav();
        // and run it again every time you scroll
        $(window).on('scroll', function() {
            stickyNav();
        });
    });
});
