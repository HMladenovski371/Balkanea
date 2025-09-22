module.exports = function (grunt) {

    grunt.initConfig({
        paths: {
            src: {
                js: [
                    'js/modernizr.js',
                    'js/bootstrap.js',
                    'js/jquery.waypoints.min.js',
                    'js/jquery.slimmenu.min.js',
                    'js/magnific.js',
                    'js/bootstrap-datepicker.js',
                    'js/bootstrap-timepicker.js',
                    'js/fotorama.js',
                    'inc/plugins/ot-custom/fields/gmap/js/gmap3.min.js',
                    'js/markerclusterer.js',
                    'js/custom_google_map.js',
                    'js/jquery.form.js',
                    'js/icheck.js',
                    //'js/custom3.js',
                    'js/infobox.js',
                    'js/noty/packaged/jquery.noty.packaged.min.js',
                    'js/init/class.notice.js',
                    'js/init/booking_modal.js',
                    'js/jquery.mousewheel-3.0.6.pack.js',
                    'js/admin/custom-price.js',
                    'js/sticky.js',
                    'js/iconpicker/js/fontawesome-iconpicker.min.js',
                    'js/jquery.scrollTo.min.js',
                    'inc/modules/flights/js/custom_flight.js',
                    'js/select2-new/js/select2.full.js',
                    'js/handlebars-v2.0.0.js',
                    'js/typeahead.js',
                    'js/init/st-select.js',
                    'js/ionrangeslider.js',
                    'inc/modules/flights/js/st-flight-select.js',
                    'js/custom-travelpayouts.js',
                    'js/bootstrap-select.js',
                    'js/amadeus/custom-amadeus.js',
                    'inc/plugins/wpbooking-form-builder/assets/js/form-builder.js',
                    'js/owl-carousel.js',
                    'js/richmarker.js',
                    'js/init/review_form.js',
                    // 'js/jquery.qtip.js',
                    'js/fullcalendar-2.4.0/lib/moment.min.js',
                    'js/date.js',
                    // 'js/fullcalendar-2.4.0/fullcalendar.min.js',
                    // 'js/fullcalendar-2.4.0/lang-all.js',
                    'js/user.js',
                    'js/custom.js',
                    'js/init/hotel-ajax.js',
                    'js/init/single-hotel.js',
                    'js/custom5.js',
                    'js/init/rental-date-ajax.js',
                    // 'js/init/single-rental.js',
                    // 'js/init/single-hotel-room.js',
                    'js/custom4.js',
                    'js/custom-lazyload.js',
                ]
            },
            dest: {
                js: 'dist/traveler.js',
                jsMin: 'dist/traveler.min.js'
            }
        },
        //Mobile
        pathsMobile: {
            src: {
                js: [
                    'js/modernizr.js',
                    'js/bootstrap.js',
                    'js/jquery.waypoints.min.js',
                    'js/jquery.slimmenu.min.js',/**/
                    'js/magnific.js',
                    'js/bootstrap-datepicker.js',
                    'js/bootstrap-timepicker.js',
                    'js/fotorama.js',/**/
                    'inc/plugins/ot-custom/fields/gmap/js/gmap3.min.js',
                    'js/markerclusterer.js',
                    'js/custom_google_map.js',/**/
                    'js/jquery.form.js',
                    'js/icheck.js',
                    'js/custom3.js',
                    'js/infobox.js',
                    'js/noty/packaged/jquery.noty.packaged.min.js',
                    'js/init/class.notice.js',
                    'js/init/booking_modal.js',/**/
                    'js/jquery.mousewheel-3.0.6.pack.js',
                    'js/admin/custom-price.js',
                    'js/sticky.js',
                    'js/iconpicker/js/fontawesome-iconpicker.min.js',
                    'js/jquery.scrollTo.min.js',
                    'inc/modules/flights/js/custom_flight.js',
                    'js/select2/select2.min.js',
                    'js/handlebars-v2.0.0.js',/**/
                    'js/typeahead.js',/**/
                    'js/init/st-select.js',
                    'js/ionrangeslider.js',
                    'inc/modules/flights/js/st-flight-select.js',
                    'js/custom-travelpayouts.js',
                    'js/bootstrap-select.js',
                    'js/amadeus/custom-amadeus.js',
                    'inc/plugins/wpbooking-form-builder/assets/js/form-builder.js',/**/
                    'js/owl-carousel.js',
                    'js/richmarker.js',
                    'js/init/review_form.js',/**/
                    //'js/jquery.qtip.js',
                    'js/fullcalendar-2.4.0/lib/moment.min.js',/**/
                    'js/date.js',/**/
                    //'js/fullcalendar-2.4.0/fullcalendar.min.js',/**/
                    //'js/fullcalendar-2.4.0/lang-all.js',/**/
                    'js/user.js',
                    'js/custom.js',
                    'js/init/hotel-ajax.js',/**/
                    'js/init/single-hotel.js',/**/
                    'js/custom5.js',/**/
                    'js/init/rental-date-ajax.js',/**/
                    //'js/init/single-rental.js',/**/
                    //'js/init/single-hotel-room.js',/**/
                    'js/custom4.js',/**/
                    'js/mobile_menu.js',
                    'js/custom-lazyload.js',
                ]
            },
            dest: {
                js: 'dist/mobile/traveler.js',
                jsMin: 'dist/mobile/traveler.min.js'
            }
        },
        concat: {
            pc: {
                options: {
                    separator: ';'
                },
                src: '<%= paths.src.js %>',
                dest: '<%= paths.dest.js %>'
            },
            mobile: {
                options: {
                    separator: ';'
                },
                src: '<%= pathsMobile.src.js %>',
                dest: '<%= pathsMobile.dest.js %>'
            },
        },
        uglify: {
            pc: {
                options: {
                    compress: true,
                    mangle: true,
                    sourceMap: true
                },
                files: {
                    '<%= paths.dest.jsMin %>': '<%= paths.src.js %>'
                }
            },
            mobile: {
                options: {
                    compress: true,
                    mangle: true,
                    sourceMap: true
                },
                files: {
                    '<%= pathsMobile.dest.jsMin %>': '<%= pathsMobile.src.js %>'
                }
            }
        },
    });

    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');

    grunt.registerTask('default', ['concat:pc', 'uglify:pc'], function (concat=true) {
        // grunt default:true
        if (concat) {
            // Update the uglify dest to be the result of concat
            // var dest = grunt.config('concat.pc.dest');
            //grunt.config('uglify.target.pc.src', dest);
            grunt.task.run('concat:pc');
        }

        // grunt default
        grunt.task.run('uglify:pc');
    });

    grunt.registerTask('mobile', ['concat:mobile', 'uglify:mobile'], function (concat=true) {
        // grunt default:true
        if (concat) {
            // Update the uglify dest to be the result of concat
            //var dest = grunt.config('concat.mobile.dest');
            //grunt.config('uglify.target.mobile.src', dest);
            grunt.task.run('concat:mobile');
        }

        // grunt default
        grunt.task.run('uglify:mobile');
    });
};
