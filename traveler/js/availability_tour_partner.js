
jQuery(function($) {
    if ($(".st_partner_avaiablity.edit-tours").length < 1) return;
    $('.date-picker').datepicker({
        language: st_params.locale || '',
        format: dashboard_params.dateformat_convert,
        weekStart: 1
    });
    class TourCalendar {
        constructor(container) {
            var self = this;
            this.container = jQuery(container);
            this.calendar = null;
            this.fullCalendar;
            this.timeOut;
            this.form_container = null;
            this.fullCalendarOptions = {
                initialView: 'dayGridMonth',
                firstDay: 1,
                locale: st_params.locale_fullcalendar,
                timeZone: st_timezone.timezone_string,
                customButtons: {
                    reloadButton: {
                        text: st_params.text_refresh,
                        click: function () {
                            self.fullCalendar.refetchEvents();
                        }
                    }
                },
                headerToolbar: {
                    start: 'today,reloadButton',
                    center: 'title',
                    end: 'prev,next'
                },
                displayEventTime: true,
                selectable: true,
                select: function ({ start, end, startStr, endStr, allDay, jsEvent, view, resource }) {
                    if (moment(start).isBefore(moment(), 'day')
                        || moment(end).isBefore(moment(), 'day')) {
                        self.fullCalendar.unselect();
                        setCheckInOut("", "", self.form_container);
                    } else {
                        var zone = moment(start).format("Z");
                        zone = zone.split(":");
                        zone = "" + parseInt(zone[0]) + ":00";
                        var check_in = moment(start).utcOffset(zone).format(String(dashboard_params.dateformat || "MM/DD/YYYY").toUpperCase());
                        var check_out = moment(end).utcOffset(zone).subtract(1, 'day').format(String(dashboard_params.dateformat || "MM/DD/YYYY").toUpperCase());
                        setCheckInOut(check_in, check_out, self.form_container);
                    }
                },
                events: function (info, successCallback, failureCallback) {
                    $.ajax({
                        url: ajaxurl,
                        dataType: "json",
                        type: "post",
                        data: {
                            action: 'st_get_availability_tour',
                            tour_id: $(self.container).data("post-id"),
                            start: moment(info.start.valueOf()).unix(),
                            end: moment(info.end.valueOf()).unix(),
                        },
                        success: function (doc) {
                            if (typeof doc == "object") {
                                successCallback(doc);
                            }
                        },
                        error: function (e) {
                            alert(
                                "Error get availability"
                            );
                        }
                    });
                },
                eventContent: function (arg) {
                    var hide_adult = jQuery('select[name=hide_adult_in_booking_form]').val();
                    var hide_children = jQuery('select[name=hide_children_in_booking_form]').val();
                    var hide_infant = jQuery('select[name=hide_infant_in_booking_form]').val();
                    let contentEl = document.createElement('div');
                    let priceEl = document.createElement('div');
                    let startTimeEl = document.createElement('div');
                    contentEl.classList.add('fc-content');
                    priceEl.classList.add('price');
                    startTimeEl.classList.add('starttime');
                    if (arg.event.extendedProps.status) {
                        // available, unavailable
                        let status = arg.event.extendedProps.status;
                        if (status === 'unavailable') {
                            contentEl.classList.remove('available');
                            contentEl.classList.add('unavailable');
                            contentEl.innerHTML = '<div class="not_available">' + st_params.text_unavailable + '</div>';
                        } else {
                            contentEl.classList.remove('unavailable');
                            contentEl.classList.add('available');
                            var price_type = $('select[name=tour_price_by]').val();
                            if (price_type == 'person') {
                                if (hide_adult != 'on') {
                                    if (typeof arg.event.extendedProps.adult_price != 'undefined') {
                                        let adultPriceEl = document.createElement('div');
                                        adultPriceEl.classList.add('price');
                                        adultPriceEl.innerHTML = st_params.text_adult + arg.event.extendedProps.adult_price;

                                        contentEl.appendChild(adultPriceEl);
                                    }
                                }
                                if (hide_children != 'on') {
                                    if (typeof arg.event.extendedProps.child_price != 'undefined') {
                                        let childPriceEl = document.createElement('div');
                                        childPriceEl.classList.add('price');
                                        childPriceEl.innerHTML = st_params.text_child + arg.event.extendedProps.child_price;

                                        contentEl.appendChild(childPriceEl);
                                    }
                                }
                                if (hide_infant != 'on') {
                                    if (typeof arg.event.extendedProps.infant_price != 'undefined') {
                                        let infantPriceEl = document.createElement('div');
                                        infantPriceEl.classList.add('price');
                                        infantPriceEl.innerHTML = st_params.text_infant_price + arg.event.extendedProps.infant_price;

                                        contentEl.appendChild(infantPriceEl);
                                    }
                                }
                            } else {
                                if (typeof arg.event.extendedProps.base_price != 'undefined') {
                                    let basePriceEl = document.createElement('div');
                                    basePriceEl.classList.add('price');
                                    basePriceEl.innerHTML = st_params.text_price + arg.event.extendedProps.base_price;

                                    contentEl.appendChild(basePriceEl);
                                }
                            }

                            if (arg.event.extendedProps.starttime) {
                                startTimeEl.innerHTML = '<i class="fa-solid fa-timer dashicons dashicons-clock"></i>' + arg.event.extendedProps.starttime;

                                contentEl.appendChild(startTimeEl);
                            }
                        }
                    }

                    let arrayOfDomNodes = [contentEl];
                    return {
                        domNodes: arrayOfDomNodes
                    };
                },
                viewDidMount: function (arg) {
                    if (arg.el) {
                        let el = arg.el;
                        if (self.timeOut) { clearTimeout(self.timeOut); }
                        self.timeOut = setTimeout(function () {
                            let viewHardnessEl = $(el).closest('.fc-view-harness.fc-view-harness-active');
                            if (viewHardnessEl && viewHardnessEl.outerHeight() == 0) {
                                viewHardnessEl.css({ minHeight: '250px' });
                            }
                        }, 400);
                    }
                },
                eventClick: function ({ event, el, jsEvent, view }) {
                    let starts = moment(event.start)
					.subtract(
						moment(event.start).utcOffset(),
						'minutes'
					).utc().toDate();

					let ends = moment(event.end)
					.subtract(
						moment(event.end).utcOffset(),
						'minutes'
					).utc().toDate();


					let startTime = moment(starts).format(String(st_params.dateformat || 'DD/MM/YYYY').toUpperCase());
                    let endTime;
                    if (event.end) {
                        endTime = moment(ends).format(String(st_params.dateformat || 'DD/MM/YYYY').toUpperCase());
                    } else {
                        endTime = startTime;
                    }
                    setCheckInOut(
                        startTime,
                        endTime,
                        self.form_container
                    );
                    var hasTimeFormat = false;
                    if ($('.calendar_starttime_format').length) {
                        hasTimeFormat = true;
                    }

                    if (event.extendedProps.starttime) {
                        var starttime_arr = event.extendedProps.starttime.split(', ');
                        starttime_arr = cleanArray(starttime_arr);
                        $('.calendar-form .calendar-starttime-wraper')
                            .not('.starttime-origin').remove();
                        $('.calendar-form .calendar-starttime-wraper.starttime-origin')
                            .find('select.calendar_starttime_hour')
                            .attr('name', 'calendar_starttime_hour[]');
                        $('.calendar-form .calendar-starttime-wraper.starttime-origin')
                            .find('select.calendar_starttime_minute')
                            .attr('name', 'calendar_starttime_minute[]');
                        if (hasTimeFormat) {
                            $('.calendar-form .calendar-starttime-wraper.starttime-origin')
                                .find('select.calendar_starttime_format')
                                .attr('name', 'calendar_starttime_format[]');
                        }
                        if (starttime_arr.length > 0) {
                            for (var i = 0; i < (starttime_arr.length - 1); i++) {
                                $('.calendar-form .calendar-starttime-wraper.starttime-origin').clone(true).show()
                                    .removeClass('starttime-origin')
                                    .insertBefore('.calendar-form #calendar-add-starttime');
                            }
                        }
                        $('.calendar-form .calendar-starttime-wraper').show();
                        $('.calendar-form .calendar-starttime-wraper').each(function (index, value) {
                            if (starttime_arr.length > 0) {
                                var starttime_string = starttime_arr[index];
                                var starttime_sub_arr = starttime_string.split(':');
                                if (hasTimeFormat) {
                                    $('.calendar-form .calendar-starttime-wraper .calendar_starttime_hour')
                                        .eq(index).val(starttime_sub_arr[0]);
                                    var starttime_sub_with_format_arr = starttime_sub_arr[1].split(' ');
                                    $('.calendar-form .calendar-starttime-wraper .calendar_starttime_minute')
                                        .eq(index).val(starttime_sub_with_format_arr[0]);
                                    $('.calendar-form .calendar-starttime-wraper .calendar_starttime_format')
                                        .eq(index).val(starttime_sub_with_format_arr[1]);
                                } else {
                                    $('.calendar-form .calendar-starttime-wraper .calendar_starttime_hour')
                                        .eq(index).val(starttime_sub_arr[0]);
                                    $('.calendar-form .calendar-starttime-wraper .calendar_starttime_minute')
                                        .eq(index).val(starttime_sub_arr[1]);
                                }
                            } else {
                                if (hasTimeFormat) {
                                    $('.calendar-form .calendar-starttime-wraper .calendar_starttime_hour')
                                        .eq(index).val('01');
                                    $('.calendar-form .calendar-starttime-wraper .calendar_starttime_minute')
                                        .eq(index).val('00');
                                    $('.calendar-form .calendar-starttime-wraper .calendar_starttime_format')
                                        .eq(index).val('AM');
                                } else {
                                    $('.calendar-form .calendar-starttime-wraper .calendar_starttime_hour')
                                        .eq(index).val('00');
                                    $('.calendar-form .calendar-starttime-wraper .calendar_starttime_minute')
                                        .eq(index).val('00');
                                }
                            }
                        });
                    } else {
                        $('.calendar-form .calendar-starttime-wraper')
                            .not('.starttime-origin').remove();
                        $('.calendar-form .calendar-starttime-wraper.starttime-origin').hide();
                        $('.calendar-form .calendar-starttime-wraper.starttime-origin')
                            .find('select.calendar_starttime_hour').attr('name', '');
                        $('.calendar-form .calendar-starttime-wraper.starttime-origin')
                            .find('select.calendar_starttime_minute').attr('name', '');
                        if (hasTimeFormat) {
                            $('.calendar-form .calendar-starttime-wraper.starttime-origin')
                                .find('select.calendar_starttime_format').attr('name', '');
                        }
                    }

                    var price_type = $('select#st-field-tour_price_by').val();
                    if (price_type == 'person') {
                        if (typeof event.extendedProps.adult_price != 'undefined') {
                            $('#calendar_adult_price', self.form_container).val(event.extendedProps.adult_price);
                        }
                        if (typeof event.extendedProps.child_price != 'undefined') {
                            $('#calendar_child_price', self.form_container).val(event.extendedProps.child_price);
                        }
                        if (typeof event.extendedProps.infant_price != 'undefined') {
                            $('#calendar_infant_price', self.form_container).val(event.extendedProps.infant_price);
                        }
                    } else {
                        if (typeof event.extendedProps.base_price != 'undefined') {
                            $('#calendar_base_price', self.form_container).val(event.extendedProps.base_price);
                        }
                    }
                    if (event.extendedProps.status) {
                        $('#calendar_status option[value=' + event.extendedProps.status + ']', self.form_container).prop('selected');
                    }
                    var zone = moment(event.start).format('Z');
                    zone = zone.split(':');
                    zone = "" + parseInt(zone[0]) + ":00";
                    jQuery(self.calendar).trigger('st.click.eventcalendar', [moment(event.start).utcOffset(zone), moment(event.start).utcOffset(zone), el, view]);
                },
                loading: function (isLoading) {
                    if (isLoading) {
                        $(".overlay-form", self.container).show();
                    } else {
                        $(".overlay-form", self.container).hide();
                    }
                },
            };
            this.init = function () {
                self.container = container;
                self.calendar = container.querySelector('#calendar-content');
                self.form_container = $(".calendar-form", self.container);
                setCheckInOut("", "", self.form_container);
                self.initCalendar();
            };
            this.initCalendar = function () {
                if (typeof FullCalendar) {
                    self.fullCalendar = new FullCalendar.Calendar(self.calendar, self.fullCalendarOptions);
                    self.fullCalendar.render();
                }
            };
        }
    }
    function setCheckInOut(check_in, check_out, form_container) {
        $('#calendar_check_in', form_container).val(check_in);
        $('#calendar_check_out', form_container).val(check_out)
    }
    function resetForm(form_container) {
        $('#calendar_check_in', form_container).val('');
        $('#calendar_check_out', form_container).val('');
        $('#calendar_adult_price', form_container).val('');
        $('#calendar_child_price', form_container).val('');
        $('#calendar_infant_price', form_container).val('');
        $('#calendar_number', form_container).val('')
    }
    function cleanArray(actual) {
        var newArray = new Array();
        for (var i = 0; i < actual.length; i++) {
            if (actual[i]) {
                newArray.push(actual[i]);
            }
        }
        return newArray;
    }
    if ($('#form-bulk-edit').length) {
        $('#calendar-bulk-close').on('click', function (event) {
            $(this).closest('#form-bulk-edit').fadeOut();
            $(this).closest('.calendar-wrapper').find('#calendar-content').trigger('refetchEvents')
        })
    }

    $('#calendar-bulk-edit').on('click', function (event) {
        if ($('#form-bulk-edit').length) {
            if ( $('select[name=tour_price_by]', '.st-partner-create-form').val() == 'person' ) {
                $('input[name=adult-price-bulk]', '#form-bulk-edit').parent().parent().show();
                $('input[name=children-price-bulk]', '#form-bulk-edit').parent().parent().show();
                $('input[name=price-bulk]', '#form-bulk-edit').parent().parent().hide();
            } else {
                $('input[name=adult-price-bulk]', '#form-bulk-edit').parent().parent().hide();
                $('input[name=children-price-bulk]', '#form-bulk-edit').parent().parent().hide();
                $('input[name=price-bulk]', '#form-bulk-edit').parent().parent().show();
            }
            $('#form-bulk-edit').fadeIn()
        }
    });
    jQuery(function($) {
        if ($('a[href="#availablility_tab"], ul li[data-step="#step_availablility"]').length) {
            $('a[href="#availablility_tab"], ul li[data-step="#step_availablility"]').on('click', function(event) {
                setTimeout(function() {
                    $('#calendar-content', '.calendar-wrapper').fullCalendar('today')
                }, 1000)
            })
        }
        $('.calendar-wrapper').each(function(index, el) {
            var t = $(this);
            var tour = new TourCalendar(el);

            var flag_submit = false;
            $('#calendar_submit', t).on('click', function(event) {
                var data = $('input, select', '.calendar-form').serializeArray();
                data.push({
                    name: 'action',
                    value: 'st_add_custom_price_tour'
                });
                $('.form-message', t).attr('class', 'form-message').find('p').html('');
                $('.overlay', self.container).addClass('open');
                if(flag_submit) return false; flag_submit = true;
                $.post(ajaxurl, data, function(respon, textStatus, xhr) {
                    if (typeof respon == 'object') {
                        if (respon.status == 1) {
                            resetForm(t);
                            tour.init();
                            if (tour.fullCalendar) {
                                tour.fullCalendar.refetchEvents();
                            }
                        } else {
                            $('.form-message', t).addClass(respon.type).find('p').html(respon.message);
                            $('.overlay', self.container).removeClass('open')
                        }
                    } else {
                        $('.overlay', self.container).removeClass('open')
                    }
                    flag_submit = false;
                }, 'json');
                return false;
            });
            $('#calendar-content', t).on('refetchEvents', function() {
                tour.init();
                if (tour.fullCalendar) {
                    tour.fullCalendar.refetchEvents();
                }
              });
            $('body').on('calendar.change_month', function(event, value){
                tour.init();
            	var date = tour.calendar.fullCalendar('getDate');
            	var month = date.format('M');
            	date = date.add(value-month, 'M');
            	tour.calendar.fullCalendar( 'gotoDate', date.format('YYYY-MM-DD') );
            });
            $('a[href="#availability"]').on('click',function(){
                tour.init();
                if (tour.fullCalendar) {
                    tour.fullCalendar.refetchEvents();
                }
                $(this).trigger('show_price_base_calendar');
            });

            $('select[name=tour_price_by]').on('change',function(){
                $(this).trigger('show_price_base_calendar');
            });

            $('.page-template-template-user').on('show_price_base_calendar',function(){
                var currentPriceType = $('.st-partner-create-form #st-field-tour_price_by').val();
                var tourType = $('.st-partner-create-form #st-field-type_tour').val();
                if(tourType === 'specific_date'){
                    $('#calendar_groupday').closest('.col-xs-6').show();
                    $('.form-bulk-edit-activity-tour #calendar_groupday').closest('div').show();
                }else{
                    $('#calendar_groupday').closest('.col-xs-6').hide();
                    $('.form-bulk-edit-activity-tour #calendar_groupday').closest('div').hide();
                }
                if(currentPriceType === 'fixed'){
                    $('.tour-calendar-price-person').hide();
                    $('.tour-calendar-price-fixed').show();
                }else{
                    $('.tour-calendar-price-person').show();
                    $('.tour-calendar-price-fixed').hide();
                }
                if(currentPriceType === 'person' || currentPriceType === 'fixed_depart'){
                    $('#calendar_price_type', '.calendar-form').val('person');
                }else{
                    $('#calendar_price_type', '.calendar-form').val('fixed');
                }
            })

        });
        if ($('select#type_tour').length && $('select#type_tour').val() == 'daily_tour') {
            $('input#calendar_groupday').prop('checked', !1).parents('.form-group').hide()
        } else {
            $('input#calendar_groupday').parents('.form-group').show()
        }
        $('select#type_tour').on('change', function(event) {
            tour_type = $(this).val();
            if (tour_type == 'daily_tour') {
                $('input#calendar_groupday').prop('checked', !1).parents('.form-group').hide()
            } else {
                $('input#calendar_groupday').parents('.form-group').show()
            }
        })
        $(document).on('click', '.partner-starttime .calendar-add-starttime', function () {
            var sparent = $(this).closest('.partner-starttime');
            if(!$('.calendar-starttime-wraper.starttime-origin', sparent).is(":visible")) {
                $('.calendar-starttime-wraper.starttime-origin', sparent).find('select.calendar_starttime_hour').attr('name', 'calendar_starttime_hour[]');
                $('.calendar-starttime-wraper.starttime-origin', sparent).find('select.calendar_starttime_minute').attr('name', 'calendar_starttime_minute[]');
                if($(this).data('time-format') === '12h'){
                    $('.calendar-starttime-wraper.starttime-origin', sparent).find('select.calendar_starttime_format').attr('name', 'calendar_starttime_format[]');
                }
            }
            $('.calendar-starttime-wraper.starttime-origin', sparent).clone(true).show().removeClass('starttime-origin').insertBefore($(this));
            if(!$('.calendar-starttime-wraper.starttime-origin', sparent).is(":visible")) {
                $('.calendar-starttime-wraper.starttime-origin', sparent).find('select.calendar_starttime_hour').attr('name', '');
                $('.calendar-starttime-wraper.starttime-origin', sparent).find('select.calendar_starttime_minute').attr('name', '');
                if($(this).data('time-format') === '12h'){
                    $('.calendar-starttime-wraper.starttime-origin', sparent).find('select.calendar_starttime_format').attr('name', '');
                }
            }
        });
        $(document).on('click', '.bulk-starttime .calendar-add-starttime', function () {
            var sparent = $(this).closest('.bulk-starttime');
            if(!$('.calendar-starttime-wraper.starttime-origin', sparent).is(":visible")) {
                $('.calendar-starttime-wraper.starttime-origin', sparent).find('select.calendar_starttime_hour').attr('name', 'calendar_starttime_hour[]');
                $('.calendar-starttime-wraper.starttime-origin', sparent).find('select.calendar_starttime_minute').attr('name', 'calendar_starttime_minute[]');
                if($(this).data('time-format') === '12h'){
                    $('.calendar-starttime-wraper.starttime-origin', sparent).find('select.calendar_starttime_format').attr('name', 'calendar_starttime_format[]');
                }
            }
            $('.calendar-starttime-wraper.starttime-origin', sparent).clone(true).show().removeClass('starttime-origin').insertBefore($(this));
            if(!$('.calendar-starttime-wraper.starttime-origin', sparent).is(":visible")) {
                $('.calendar-starttime-wraper.starttime-origin', sparent).find('select.calendar_starttime_hour').attr('name', '');
                $('.calendar-starttime-wraper.starttime-origin', sparent).find('select.calendar_starttime_minute').attr('name', '');
                if($(this).data('time-format') === '12h'){
                    $('.calendar-starttime-wraper.starttime-origin', sparent).find('select.calendar_starttime_format').attr('name', '');
                }
            }
        });
        $(document).on('click', '.calendar-remove-starttime', function () {
            if($(this).parent().hasClass('starttime-origin')){
                $(this).parent().hide();
                $(this).parent().find('select.calendar_starttime_hour').attr('name', '');
                $(this).parent().find('select.calendar_starttime_minute').attr('name', '');
                if($(this).data('time-format') === '12h'){
                    $(this).parent().find('select.calendar_starttime_format').attr('name', '');
                }
            }else{
                $(this).parent().remove();
            }
        });
    })
})
