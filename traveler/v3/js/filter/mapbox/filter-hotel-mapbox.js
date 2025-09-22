(function ($) {
    var requestRunning = false;
    var xhr;
    var hasFilter = false;
    available_hotels = [];
    available_hotels_filter = new Set();
    const current_domain_url = `${window.location.protocol}//${window.location.hostname}`;
    let page = 1;

    // Nikola JS functions START
    function formatDate(date) {

        if (date === null || date === '' || date === undefined) return -1;
        
        let date_years = date.split('/')[2];
        let date_months = date.split('/')[1];
        let date_days = date.split('/')[0];

        let _date = date_years + '-' + date_months + '-' + date_days;

        return _date;
    }
    
    async function getNonce(action) {
    try {
     //   let nonceResponse = await fetch("https://staging.balkanea.com/wp-plugin/APIs/generate-nonce.php", {
      let nonceResponse = await fetch(balkaneaApi.generate_nonce_url, { 
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ action: action })
        });

        if (!nonceResponse.ok) {
            throw new Error(`HTTP error! Status: ${nonceResponse.status}`);
        }

        let nonceData = await nonceResponse.json();

        if (!nonceData || !nonceData.nonce) {
            throw new Error("Failed to retrieve nonce.");
        }

        let nonce = nonceData.nonce;
        return nonce;

        } catch (error) {
            console.error("Error fetching nonce:", error.message);
            return null;
        }
    }

    function showLoadingPrices() {
        
        let loading_text_class = document.querySelectorAll('.loading-text');
        
        if (loading_text_class.length > 0)
            return;
        
        document.querySelectorAll('[itemprop="priceRange"]').forEach(function(element) {
            var priceElement = element.querySelector('.price');
            var unitElement = element.querySelector('.unit');
            if (priceElement) priceElement.style.display = 'none';
            if (unitElement) unitElement.style.display = 'none';
    
            var loadingText = document.createElement('span');
            loadingText.textContent = 'Loading prices...';
            loadingText.className = 'loading-text';
            element.appendChild(loadingText);
    
            loadingText.style.animation = 'fade-in-out 1.5s infinite';
    
            var styleSheet = document.createElement('style');
            styleSheet.type = 'text/css';
            styleSheet.innerText = `
                @keyframes fade-in-out {
                    0%, 100% { opacity: 1; }
                    50% { opacity: 0.5; }
                }
                .loading-text {
                    font-style: italic;
                    color: grey;
                }
            `;
            document.head.appendChild(styleSheet);
        });
    }

    function hideLoadingPrices(){
        document.querySelectorAll('[itemprop="priceRange"]').forEach(element => {
            element.querySelector('.unit').style.display = '';
            element.querySelector('.price').style.display = '';
            element.querySelector('.loading-text').style.display = 'none';
        })
    }
    // Nikola JS functions END

    var data = URLToArrayNew();
    jQuery(function ($) {
        if($('.search-result-page.st-style-elementor').length) {
            ajaxFilterHandler();
            $('.show-filter-mobile .button-filter').on('click', function() {
                $('.sidebar-filter').fadeIn();
            });
            $('.sidebar-filter .close-sidebar').on('click', function() {
                $('.sidebar-filter').fadeOut();
            });
        }
    });

    /*Layout*/
    $('.toolbar .layout span.layout-item').on('click', function () {
        if(!$(this).hasClass('active')){
            $(this).parent().find('span').removeClass('active');
            $(this).addClass('active');
            data['layout'] = $(this).data('value');
            ajaxFilterHandler(false);
        }
    });

    $('#st-map-coordinate').on('change', function () {
        let coordinate = $('#st-map-coordinate').val();
        if (coordinate) {
            let objCoordinate = coordinate.split('_');
            if (objCoordinate.length === 3) {
                data['location_lat'] = objCoordinate[0];
                data['location_lng'] = objCoordinate[1];
                data['location_distance'] = objCoordinate[2];
                data['move_map'] = true;
                ajaxFilterHandler(true);
            }
        }
    });

    /*
        Nikola - Sort menu
    */
    $('.sort-menu input.service_order').on('change', function () {
        window.scrollTo({
            top: $('#modern-result-string').offset().top - 50,
            behavior: 'smooth'
        });
    
        let available_hotels_filter_sorted;
    
        var divResult = $('.modern-search-result');
        divResult.addClass('loading');
    
        let sort_by = $(this).data('value');
        
        if (sort_by == 'price_desc' || sort_by == 'price_asc') {
            available_hotels_filter_sorted = Array.from(document.querySelectorAll("#available")).sort((a, b) => {
                const priceA = parseFloat(a.querySelector('.price').textContent.replace(/[^\d.-]/g, ''));
                const priceB = parseFloat(b.querySelector('.price').textContent.replace(/[^\d.-]/g, ''));
                
                if (sort_by == 'price_asc') return priceA - priceB;
                else return priceB - priceA;
            });
        } else {
            available_hotels_filter_sorted = Array.from(document.querySelectorAll("#available")).sort((a, b) => {
                const nameA = a.querySelector('.c-main').textContent.trim().toLowerCase();
                const nameB = b.querySelector('.c-main').textContent.trim().toLowerCase();
                
                if (sort_by == 'name_asc') {
                    if (nameA < nameB) return -1;
                    if (nameA > nameB) return 1;
                } else {
                    if (nameA < nameB) return 1;
                    if (nameA > nameB) return -1;
                }
                return 0;
            });
        }
    
        const container = document.querySelector('.service-list-wrapper.row') ?? document.querySelector('#modern-search-result');
    
        available_hotels_filter_sorted.forEach(card => {
            container.appendChild(card);
        });
    
        document.getElementById('No-hotels').innerText = document.querySelectorAll('#modern-search-result > div.service-list-wrapper.row > div:not([style="display: none;"])').length;
        
        $(this).closest('.dropdown-menu').slideUp(50);
        
        divResult.removeClass('loading');
    });
    
    /* Nikola - Price Filter */
    $('.btn-apply-price-range').on('click', function (e) {
        
        console.log(available_hotels);
        
        window.scrollTo({
            top: $('#modern-result-string').offset().top - 50,
            behavior: 'smooth'
        });
        
        var divResult = $('.modern-search-result');
        divResult.addClass('loading');
        e.preventDefault();
        
        let price_range = $(this).closest('.range-slider').find('.price_range').val();
        
        let price_starting_from = price_range.split(';')[0];
        let price_ending_to = price_range.split(';')[1];
        
        document.querySelectorAll("#available").forEach( hotel_card => {
            hotel_card_price = parseFloat(hotel_card.querySelector('.price').textContent.replace(/[^\d.-]/g, ''));

            if (hotel_card_price < price_starting_from || hotel_card_price > price_ending_to){
                if (Array.from(available_hotels_filter).length > 0)
                    available_hotels_filter.delete(hotel_card);
                    
                if (hotel_card.hasAttribute('data-id'))
                    hotel_card.parentElement.style.display = 'none';
                else
                    hotel_card.style.display = 'none';
            }else{
                available_hotels_filter.add(hotel_card);
                hotel_card.style.display = '';
            }
            
        })
        
        // document.querySelector('#No-hotels').innerHTML = Array.from(available_hotels_filter).length;
        if (document.querySelectorAll('#available').length > 1){
            document.getElementById('No-hotels').innerText = document.querySelectorAll('#available:not([style="display: none;"])').length;
        }else{
            document.getElementById('No-hotels').innerText = document.querySelectorAll('#modern-search-result > div.service-list-wrapper.row > div:not([style="display: none;"])').length;
        }
        
        data['page'] = 1;
        divResult.removeClass('loading');
        // ajaxFilterHandler();
    });

    /*Nikola - Checkbox click*/
    var filter_checkbox = {};
    $('.filter-item').each(function () {
        if(!Object.keys(filter_checkbox).includes($(this).data('type'))){
            filter_checkbox[$(this).data('type')] = [];
        }
    });

    $('.filter-item').on('change',function () {
        
        window.scrollTo({
            top: $('#modern-result-string').offset().top - 50,
            behavior: 'smooth'
        });
        
        var divResult = $('.modern-search-result');
        divResult.addClass('loading');
        
        var t = $(this);
        var filter_type = t.data('type');
        
        let review_score = t.val();
        let filter_selector = '.st-stars';
        
        if (filter_type == 'star_rate'){
            filter_selector = '.rate-text';
            switch (t.val()){
                case '4':
                    review_score = 'Excellent';
                    break;
                case '3':
                    review_score = 'Very Good';
                    break;
                case '2':
                    review_score = 'Average';
                    break;
                case '1':
                    review_score = 'Poor'
                    break;
                case 'zero':
                    review_score = 'Terrible'
                    break;
            }
        }
        
        if(t.is(':checked')){
              filter_checkbox[filter_type].push(review_score);
          }else{
              var index = filter_checkbox[filter_type].indexOf(review_score);
              if (index > -1) {
                  filter_checkbox[filter_type].splice(index, 1);
              }
          }
          if(filter_checkbox[filter_type].length){
              data[filter_type] = filter_checkbox[filter_type].toString();
          }else{
              if(typeof data[filter_type] != 'undefined'){
                  delete data[filter_type];
              }
          }
                
        if (filter_checkbox['star_rate'].length == 0 && filter_checkbox['hotel_rate'].length == 0){
            document.querySelectorAll("#available").forEach(hotel_card => { 
                hotel_card.style.display = '';
            })
        }
        else{
            document.querySelectorAll("#available").forEach(hotel_card => {
                let rate_text = hotel_card.querySelector('.rating-text').innerText.trim();
                let rate_star = hotel_card.querySelector('.st-stars').childElementCount.toString();
                
                let matchesStarRate = filter_checkbox['star_rate'].includes(rate_text);
                let matchesHotelRate = filter_checkbox['hotel_rate'].includes(rate_star);
            
                if (matchesStarRate || matchesHotelRate) {
                    hotel_card.style.display = '';
                } else {
                    hotel_card.style.display = 'none';
                }
            });
        }

        if (document.querySelectorAll('#available').length > 1){
            document.getElementById('No-hotels').innerText = document.querySelectorAll('#available:not([style="display: none;"])').length;
        }else{
            document.getElementById('No-hotels').innerText = document.querySelectorAll('#modern-search-result > div.service-list-wrapper.row > div:not([style="display: none;"])').length;
        }
        // document.getElementById('No-hotels').innerText = Array.from(available_hotels_filter).length;
    
        divResult.removeClass('loading');
        data['page'] = 1;
        // ajaxFilterHandler();
    });

    /* Nikola - Taxnonomy Filter*/
    var arrTax = [];
    $('.filter-tax').each(function () {
        if(!Object.keys(arrTax).includes($(this).data('type'))){
            arrTax[$(this).data('type')] = [];
        }

        if($(this).is(':checked')){
            arrTax[$(this).data('type')].push($(this).val());
        }
        
    });

    /* Pagination - Nikola */
    $(document).on('click', '#load-more-btn', function (e) {
        data['page']++;
        ajaxFilterHandler();
    });
    
    
    $(document).on('click', '.pagination a.page-numbers:not(.current, .dots)', function (e) {
        e.preventDefault();
        var t = $(this);
        var pagUrl = t.attr('href');

        pageNum = 1;

        if (typeof pagUrl !== typeof undefined && pagUrl !== false) {
            console.log("Pagination set");
            var arr = pagUrl.split('/');
            var pageNum = arr[arr.indexOf('page') + 1];
            if (isNaN(pageNum)) {
                pageNum = 1;
            }
            
            data['page'] = pageNum;
            ajaxFilterHandler();
            if($('.modern-search-result-popup').length){
                $('.col-left-map').animate({scrollTop: 0}, 'slow');
            }

            if($('#modern-result-string').length) {
                    window.scrollTo({
                        top: $('#modern-result-string').offset().top - 20,
                        behavior: 'smooth'
                    });
            }
            
            return false;
        } else {
            return false;
        }
    });

    // Nikola - Tax filter
    $('.filter-tax').on('change',function () {
        var divResult = $('.modern-search-result');
        divResult.addClass('loading');
        
        window.scrollTo({
            top: $('#modern-result-string').offset().top - 50,
            behavior: 'smooth'
        });
        
        var t = $(this);
        var filter_type = t.data('type');

        if(t.is(':checked')){
            arrTax[filter_type].push(t.val());
        }else{
            var index = arrTax[filter_type].indexOf(t.val());
            if (index > -1) {
                arrTax[filter_type].splice(index, 1);
            }
        }
        if(arrTax[filter_type].length){
            if(typeof data['taxonomy'] == 'undefined')
                data['taxonomy'] = {};
            data['taxonomy['+filter_type+']'] = arrTax[filter_type].toString();
        }else{
            if(typeof data['taxonomy'] == 'undefined')
                data['taxonomy'] = {};
            if(typeof data['taxonomy['+filter_type+']'] != 'undefined'){
                delete data['taxonomy['+filter_type+']'];
            }
        }

        if(Object.keys(data['taxonomy']).length <= 0){
            delete data['taxonomy'];
        }
        
        data['page'] = 1;
        ajaxFilterHandler();
        
        let data_ids = [];
        document.querySelectorAll('#available').forEach( hotel => {
            data_ids.push(hotel.querySelector('[data-id]').getAttribute('data-id'));
        })
   
    if (data["taxonomy[hotel-facilities]"]){
        getNonce("filter_hotel").then(nonce => {
                if (!nonce) {
                    console.error("Failed to retrieve nonce.");
                    return;
                }
            $.ajax({
                url: `${current_domain_url}/wp-plugin/APIs/filter_hotel.php`,
                type: 'GET',
                data: {
                    'data_ids': data_ids,
                    'taxonomy': data["taxonomy[hotel-facilities]"],
                },
                success: (response) => {
                    
                    if (response == '[]'){
                        console.log("Empty");
                        document.querySelectorAll('#available').forEach(hotel => {
                            hotel.style.display = 'none';
                        })
                        document.getElementById('No-hotels').innerText = 0;
                        
                        return;
                    }
                    
                    let jsonString = response.replace(/\[\]/g, '');
                    jsonString = jsonString.replace(/\]\[/g, ',');
            
                    let filtered_hotels;
                    try {
                        filtered_hotels = JSON.parse(jsonString);
                    } catch (error) {
                        console.error('Error parsing JSON:', error);
                        return;
                    }
    
                    let hotelIds = filtered_hotels.map(item => parseInt(item.object_id));
            
                    document.querySelectorAll('#available').forEach(hotel => {
                        let hotelId = parseInt(hotel.querySelector('[data-id]').getAttribute('data-id'));
                        if (!hotelIds.includes(hotelId)) {
                            hotel.style.display = 'none';
                        } else {
                            hotel.style.display = '';
                        }
                    });
                    
                    divResult.removeClass('loading');
                    
                },
                error: (err) => {
                    console.log(err);
                },
                complete: () =>{
                    if (document.querySelectorAll('#available').length > 1){
                        document.getElementById('No-hotels').innerText = document.querySelectorAll('#available:not([style="display: none;"])').length;
                    }else{
                        document.getElementById('No-hotels').innerText = document.querySelectorAll('#modern-search-result > div.service-list-wrapper.row > div:not([style="display: none;"])').length;
                    }
                }
            });
        });
    }else{
        console.log('TUKA');
        divResult.removeClass('loading');
        document.getElementById('No-hotels').innerText = data_ids.length
        document.querySelectorAll('#available').forEach(hotel => {
          hotel.style.display = '';
        });
    }
    });

    function duplicateData(parent, parentGet){
        if(typeof data['price_range'] != 'undefined'){
            $('input[name="price_range"]', parent).each(function () {
                var instance = $(this).data("ionRangeSlider");
                var price_range_arr = data['price_range'].split(';');
                if(price_range_arr.length){
                    instance.update({
                        from: price_range_arr[0],
                        to: price_range_arr[1]
                    });
                }
            });
        }

        //Filter
        var dataFilterItem = [];
        parent.find('.filter-item').prop('checked', false);
        parentGet.find('.filter-item').each(function () {
            var t = $(this);
            if(t.is(':checked')) {
                if (Object.keys(dataFilterItem).includes(t.data('type'))) {
                    dataFilterItem[t.data('type')].push(t.val());
                } else {
                    dataFilterItem[t.data('type')] = [];
                    dataFilterItem[t.data('type')].push(t.val());
                }
            }
        });
        if(Object.keys(dataFilterItem).length){
            for(var i = 0; i < Object.keys(dataFilterItem).length; i++){
                var iD = dataFilterItem[Object.keys(dataFilterItem)[i]];
                if(iD.length){
                    for(var j = 0; j < iD.length; j++){
                        $('.filter-item[data-type="'+ Object.keys(dataFilterItem)[i] +'"][value="'+ iD[j] +'"]', parent).prop('checked', true);
                    }
                }
            }
        }

        //Tax
        var dataFilterTax = [];
        parent.find('.filter-tax').prop('checked', false);
        parentGet.find('.filter-tax').each(function () {
            var t = $(this);
            if(t.is(':checked')){
                if(Object.keys(dataFilterTax).includes(t.data('type'))){
                    dataFilterTax[t.data('type')].push(t.val());
                }else{
                    dataFilterTax[t.data('type')] = [];
                    dataFilterTax[t.data('type')].push(t.val());
                }
            }
        });
        if(Object.keys(dataFilterTax).length){
            for(var i = 0; i < Object.keys(dataFilterTax).length; i++){
                var iD = dataFilterTax[Object.keys(dataFilterTax)[i]];
                if(iD.length){
                    for(var j = 0; j < iD.length; j++){
                        $('.filter-tax[data-type="'+ Object.keys(dataFilterTax)[i] +'"][value="'+ iD[j] +'"]', parent).prop('checked', true);
                    }
                }
            }
        }
    }

    $('.map-view').on('click', function () {
        var parent = $('.map-view-popup .top-filter');
        var parentGet = $('.sidebar-item');

        duplicateData(parent, parentGet);

        $('.map-view-popup').fadeIn();
        $('html, body').css({'overflow' : 'hidden'});
        ajaxFilterHandler();
    });

    $('.close-map-view-popup').on('click', function () {
        var parentGet = $('.map-view-popup .top-filter');
        var parent = $('.sidebar-item');
        duplicateData(parent, parentGet);
        $('html, body').css({'overflow' : 'auto'});
        $('.map-view-popup').fadeOut();
    });

    $('.toolbar-action-mobile .btn-date').on('click',function (e) {
        e.preventDefault();
        var me = $(this);
        window.scrollTo({
            top     : 0,
            behavior: 'auto'
        });
        $('.popup-date').each(function () {
            var t = $(this);

            var checkinOut = t.find('.check-in-out');
            var options = {
                singleDatePicker: false,
                autoApply: true,
                disabledPast: true,
                dateFormat: t.data('format'),
                customClass: 'popup-date-custom',
                widthSingle: 500,
                onlyShowCurrentMonth: true,
                alwaysShowCalendars: true,
            };
            if (typeof locale_daterangepicker == 'object') {
                options.locale = locale_daterangepicker;
            }
            checkinOut.daterangepicker(options,
                function (start, end, label) {
                    me.text(start.format(t.data('format')) + ' - ' + end.format(t.data('format')));
                    data['start'] = start.format(t.data('format'));
                    data['end'] = end.format(t.data('format'));
                    if($('#modern-result-string').length) {
                        window.scrollTo({
                            top: $('#modern-result-string').offset().top - 20,
                            behavior: 'smooth'
                        });
                    }
                    ajaxFilterHandler();
                    t.hide();
                });
            checkinOut.trigger('click');
            t.fadeIn();
        });
    });

    $('.popup-close').on('click',function () {
        $(this).closest('.st-popup').hide();
    });

    $('.btn-guest-apply', '.popup-guest').on('click', function (e) {
        e.preventDefault();
        var textGuestAdult = '1 Adult';
        var textGuestChild = '0 Children';

        var me = $('.toolbar-action-mobile .btn-guest');

        $('.popup-guest').each(function () {
            var t = $(this);
            var adult_number = $('input[name="adult_number"]', t).val();
            if(parseInt(adult_number) == 1){
                textGuestAdult = adult_number + ' ' + st_params.text_adult;
            }else{
                textGuestAdult = adult_number + ' ' + st_params.text_adults;
            }
            data['adult_number'] = adult_number;
            me.text(textGuestAdult + ' - ' + textGuestChild);

            var child_number = $('input[name="child_number"]', t).val();
            if(parseInt(child_number) <= 1){
                textGuestChild = child_number + ' ' + st_params.text_child;
            }else{
                textGuestChild = child_number + ' ' + st_params.text_childs;
            }
            data['child_number'] = child_number;
            me.text(textGuestAdult + ' - ' + textGuestChild);

            data['room_num_search'] = $('input[name="room_num_search"]', t).val();

            $(this).closest('.st-popup').hide();

            ajaxFilterHandler();
        });
    });

    $('.toolbar-action-mobile .btn-guest').on('click',function (e) {
        e.preventDefault();
        $('.popup-guest').each(function () {
            var t = $(this);
            t.fadeIn();
        });
    });

    $('.toolbar-action-mobile .btn-map').on('click', function (e) {
        e.preventDefault();
        $('.page-half-map .col-right').show();
        $('.full-map').show();
        ajaxFilterMapHandler();
        //$('html, body').css({overflow: 'hidden'});
    });
    $('.show-map-mobile').on('click', function() {
        var t = $(this);
        $('.page-half-map').find('.maparea').show();
        $('body').css({'overflow': 'hidden'});
        ajaxFilterMapHandler();
    });
    $('.close-map-new').on('click', function() {
        var t = $(this);
        t.closest('.maparea').fadeOut();
        $('body').css({'overflow': 'auto'});
    });
    $('#btn-show-map-mobile').on('change', function () {
        var t           = $(this);
        var pageHalfMap = $('.page-half-map');
        if (t.is(':checked')) {
            pageHalfMap.find('.col-right').show();
            ajaxFilterMapHandler();
        }
    });

    $('#btn-show-map').on('change', function () {
        var t = $(this);
        var pageHalfMap = $('.page-half-map');
        if (t.is(':checked')) {
            pageHalfMap.find('.modern-search-result').css(
                {
                    "height": "calc(100vh - 80px)",
                    "overflow-y": "scroll"
                }
            );
            pageHalfMap.removeClass('snormal');
            pageHalfMap.find('.col-right').show();
            pageHalfMap.find('.col-left').attr('class', '').addClass('col-lg-6 col-left static');
            if (pageHalfMap.find('.col-left .list-style').length) {
                pageHalfMap.find('.col-left .item-service').attr('class', '').addClass('col-lg-12 item-service');
            } else {
                pageHalfMap.find('.col-left .item-service').attr('class', '').addClass('col-lg-6 col-md-6 col-sm-4 col-xs-6 item-service');
            }
            $('.as').slideUp();
            var topEl = $('.st-hotel-result').offset().top;
            var scroll = $(window).scrollTop();

            if (topEl == scroll) {
                setTimeout(function () {
                    $('.page-half-map').find('.col-left').getNiceScroll().remove();
                    $('.page-half-map').find('.col-left').niceScroll();
                    $('.page-half-map').find('.col-left').getNiceScroll().resize();
                }, 500);
            }
            pageHalfMap.find('.col-left').css({'width': '50%'});
        } else {
            pageHalfMap.find('.modern-search-result').css(
                {
                    "height": "auto",
                    "overflow-y": "hidden"
                }
            );
            pageHalfMap.addClass('snormal');
            pageHalfMap.find('.col-right').hide();
            pageHalfMap.find('.col-left').attr('class', '').addClass('col-lg-12 col-left');
            if (pageHalfMap.find('.col-left .list-style').length) {
                pageHalfMap.find('.col-left .item-service').attr('class', '').addClass('col-lg-6 col-md-6 item-service');
            } else {
                pageHalfMap.find('.col-left .item-service').attr('class', '').addClass('col-lg-3 col-md-3 col-sm-4 col-xs-6 item-service');
            }

            setTimeout(function () {
                $('.has-matchHeight').matchHeight({remove: true});
                $('.has-matchHeight').matchHeight();
            }, 400);

            $('.as').slideDown();
            pageHalfMap.find('.col-left').css({'width': '100%'});
        }
    });

    $('#btn-show-map').on('change', function () {
        var t = $(this);
        if (t.is(':checked')) {
            data['half_map_show'] = 'yes';
            ajaxFilterMapHandler();
        }else{
            data['half_map_show'] = 'no';
            setTimeout(function () {
                if($('.has-matchHeight').length){
                    $('.has-matchHeight').matchHeight({ remove: true });
                    $('.has-matchHeight').matchHeight();
                }
            }, 100);
        }
        $('.st-hotel-result').find('.col-left').getNiceScroll().remove();
    });

    function ajaxFilterHandler(loadMap = true){
        
        if (requestRunning) {
            xhr.abort();
        }

        hasFilter = true;

        $('html, body').css({'overflow': 'auto'});

        if (window.matchMedia('(max-width: 991px)').matches) {
            $('.sidebar-filter').fadeOut();

            if($('#modern-result-string').length) {
                window.scrollTo({
                    top: $('#modern-result-string').offset().top - 20,
                    behavior: 'smooth'
                });
            }
        }

        $('.filter-loading').show();
        var layout = $('#modern-search-result').data('layout');
        data['format'] = $('#modern-search-result').data('format');
        if($('#st-layout-fullwidth').length)
            data['fullwidth'] = 1;
        if($('.modern-search-result-popup').length){
            data['is_popup_map'] = '1';
        }

        data['action'] = 'st_filter_hotel_ajax';
        data['is_search_page'] = 1;
        data['_s'] = st_params._s;
        if(typeof  data['page'] == 'undefined'){
            data['page'] = 1;
        }

        if ($('.search-result-page.layout5, .search-result-page.layout6').length) {
            let wrapper = $('.search-result-page');
            data['version'] = 'elementorv2';
            data['version_layout'] = wrapper.data('layout');
            data['version_format'] = wrapper.data('format');
        }

        var divResult = $('.modern-search-result');
        var divResultString = $('.modern-result-string');
        // var divPagination = $('.moderm-pagination');

        $(document).trigger('st_before_search_ajax', [data]);

        divResult.addClass('loading');
        $('.map-content-loading').each(function() {
            $(this).fadeIn();
        });

        // xhr = $.ajax({
        //     url: st_params.ajax_url,
        //     dataType: 'json',
        //     type: 'get',
        //     data: data,
        //     success: function (doc) {

        //         let content = doc.content;
        //         if ($('.search-result-page.layout5').length) {
        //             content += '<div class="pagination moderm-pagination" id="moderm-pagination">'+ doc.pag +'</div>';

        //         } else {
        //             divPagination.each(function () {
        //                 $(this).html(doc.pag);
        //             });
        //         }
        //         if ($('.search-result-page.layout5').length) {
        //             divResult.each(function () {
        //                 $(this).html(content);
        //             });
        //         } else {
        //             divResult.each(function () {
        //                 $(this).html(doc.content);
        //             });
        //         }

        //         if ($('.modern-search-result-popup').length) {
        //             $('.modern-search-result-popup').html(doc.content_popup);
        //             if($('.col-left-map').length){
        //                 $('.col-left-map').each(function () {
        //                     $(this).getNiceScroll().resize();
        //                 })
        //             }
        //         }

        //         $('.map-full-height, .full-map-form').each(function () {
        //             var t = $(this);
        //             var data_map = doc.data_map;
        //             if(loadMap && !t.is(':hidden')){
        //                 initHalfMapBox(t, data_map.data_map, data_map.map_lat_center, data_map.map_lng_center, '', data_map.map_icon, data.version, data.move_map);
        //             }

        //         });
                
                // showLoadingPrices();
                
        // if ( regions.includes(data.location_id) ) {

            // console.log(data);
            
            // if (data.location_name == '' && data.location_id == ''){
            //     console.log('No location selected');
            //     return;
            // }
            
            // date_start = data.start.split('/'); // [0] - dd [1] - MM [2]YYYY
            // date_now = new Date();
            
            // if (date_start[0] < date_now.getDate() && date_start[1] <= (date_now.getMonth() + 1) && date_start <= date_start.getFullYear() ){
            //     console.log('Invalid date selected');
            //     return;
            // }
            
            // Data prep for reservation request START
            let current_curency = document.querySelector('#dropdown-currency').innerText.trim();
            let start = formatDate(data.start);
            let end = formatDate(data.end);
            let guests_adult_number = data['adult_number'];
            let guests_child_number = data['child_number'];
            let location_id = data['location_id'];

            showLoadingPrices();
        
            var hotel_ids = '';
                    
            document.querySelectorAll("#modern-search-result > div.service-list-wrapper.list-style > div > div").forEach( item => {
                hotel_ids += item.getAttribute('data-id') + ',';
            });

            hotel_ids = hotel_ids.slice(0, -1);
                
            getNonce("filter_hotel").then(nonce => {
                if (!nonce) {
                    console.error("Failed to retrieve nonce.");
                    return;
                }
                
                /* Requesting hotels with pagination - Nikola */
                $.ajax({
                    url: `${current_domain_url}/wp-plugin/APIs/filter_hotel.php`,
                    type: 'GET',
                    data: {
                        'ids': hotel_ids,
                        'location_id': location_id,
                        'start': start,
                        'end': end,
                        'adults': guests_adult_number,
                        'children': guests_child_number,
                        'currency': current_curency,
                        'security': nonce,
                        'page': data['page']
                    },
                    success: (response) => {
                        if (response)
                            response = JSON.parse(response);
                            
                        if (!response.load_more){
                            $("#moderm-pagination").hide();
                        }
                            
                        if (response.status === 'ok' && response.html != null){
                            
                            const hotelContainer = document.querySelector('#modern-search-result > div.service-list-wrapper.row');
                                if (hotelContainer) {
                                    hotelContainer.innerHTML += response.html;
                                }
                            
                            if ($('#No-hotels').length){
                                let current_hotel_no = parseInt($('#No-hotels').text().replace(/\D/g, ''));
                                let total_hotel_no = current_hotel_no + response.hotel_count;
                            
                                $('#No-hotels').text(total_hotel_no);
                            }else{
                                let hotel_count = `<h2 class="search-string modern-result-string" id="modern-result-string">${data.location_name}: <span style="font-weight: normal; color: #5E6D77;" id='No-hotels' > ${response.hotel_count} </span> hotels found </h2>`;
                                
                                divResultString.each(function () {
                                    $(this).html(hotel_count);
                                });
                            }
                            
                        } else if (Object.keys(response)[0] == 'warning' || Object.keys(response)[0] == 'error'){
                            const h2Element = document.getElementById('modern-result-string');

                            const match = h2Element.textContent.match(/(\d+)/);
                            
                            if (match) {
                                h2Element.innerHTML = h2Element.textContent.replace(match[0], `<span style="font-weight: normal; color: #5E6D77; font-size: 1rem" id='No-hotels'>${match[0]}</span>`);
                            }
                            available_hotels = Array.from(document.querySelectorAll("#available"));
                            available_hotels_filter = new Set(document.querySelectorAll("#available"));
                            divResult.removeClass('loading');
                            $('.map-content-loading').fadeOut();
                            hideLoadingPrices();
                            
                            if (Object.keys(response)[0] == 'error'){
                                document.querySelector("#modern-search-result").innerHTML = `<div class="alert alert-warning mt15"> ${response.error} <div>`
                                document.querySelector("#No-hotels").innerHTML = 0;
                            }
                            
                            return;
                        }else{
                            // document.querySelectorAll("#modern-search-result > div.service-list-wrapper.list-style > div").forEach( item => {
                            //     item.style.display = 'none'
                            // })
                            let hotel_count = `<h2 class="search-string modern-result-string" id="modern-result-string">No hotels found in ${data.location_name}</h2>`;
                                
                            divResultString.each(function () {
                                $(this).html(hotel_count);
                            });
                            
                            document.querySelector("#modern-search-result").innerHTML = `<div class="alert alert-warning mt15">No hotels found.</div>`;
                        }

                        divResult.removeClass('loading');
                        $('.map-content-loading').fadeOut();

                        return;
                        
                        // if (document.querySelectorAll('#available').length > 1){
                        //     available_hotels = Array.from(document.querySelectorAll('#available'));
                        //     available_hotels_filter = new Set(document.querySelectorAll('#available'));
                        // }
                        // else{
                        //     available_hotels = Array.from(document.querySelectorAll("#modern-search-result > div.service-list-wrapper.list-style > div > div").length > 1 ? document.querySelectorAll("#modern-search-result > div.service-list-wrapper.list-style > div > div") : document.querySelectorAll("#modern-search-result > div.service-list-wrapper.row > div"));
                        //     available_hotels_filter = new Set(document.querySelectorAll("#modern-search-result > div.service-list-wrapper.list-style > div > div").length > 1 ? document.querySelectorAll("#modern-search-result > div.service-list-wrapper.list-style > div > div") : document.querySelectorAll("#modern-search-result > div.service-list-wrapper.row > div"));
                        // }
                        
                        // all_ids = response.all_ids;
                        // response = response.found_ids;
                        // window.scrollTo({
                        //     top: $('#modern-result-string').offset().top - 50,
                        //     behavior: 'smooth'
                        // });
                        
                        // let array_ids;
                        // try {
                        //     array_ids = JSON.parse(response);
                        // } catch (error) {
                        //     console.error('Error parsing JSON response:', error);
                        //     divResult.removeClass('loading');
                        //     $('.map-content-loading').fadeOut();
                        //     hideLoadingPrices();
                        //     return;
                        // }
                
                        // const idArray = Object.keys(array_ids);
                        
                        // // let hotel_count = `<span id='No-hotels' > ${idArray.length} </span> hotels found in ${data.location_name}<div id="btn-clear-filter" class="btn-clear-filter" style="display: none;">Clear filter</div>`;

                        // all_ids.forEach( (hotelId) => {
                        //     item = document.querySelector('[data-id="'+hotelId+'"]');
                        //     if (idArray.includes(hotelId)) {
                        //         item.style.display = '';
                        //         item.parentNode.setAttribute('id', 'available');
                        //         // available_hotels.push(item);
                        //         const price = array_ids[hotelId];

                        //         const priceElement = item.querySelector('.price');
                        //         if (priceElement) {
                        //             item.querySelector('.loading-text').style.display = 'none';
                        //             var unitElement = item.querySelector('.unit');
                        //             priceElement.textContent = `${price}`;
                        //             priceElement.style.display = '';
                                    
                        //             if (unitElement) {
                        //                 unitElement.style.display = '';
                        //             }
                        //         }
                        //     }else{
                        //         item.parentNode.setAttribute('id', 'not-available');
                        //         if (item.hasAttribute('data-id'))
                        //             item.parentElement.style.display = 'none';
                        //         else
                        //             item.style.display = 'none';
                        //     }

                        //     divResult.removeClass('loading');
                        //     $('.map-content-loading').fadeOut();

                        //     item.querySelector('.loading-text').style.display = 'none';

                        //     item.querySelectorAll('a').forEach(anchor => {
                        //         const currentHref = anchor.getAttribute('href');
                        //         const newHref = `${currentHref}&search=yes`;
                        //         anchor.setAttribute('href', newHref);
                        //     });
                        // })

                        // hideLoadingPrices();

                        // // available_hotels_filter

                        // let available_items = document.querySelectorAll('#modern-search-result > div.service-list-wrapper.row > div:not(#not-available)');
                        // available_items.forEach( item => {
                        //     item.setAttribute('id', 'available');
                        // })

                        // document.querySelector('#No-hotels').innerHTML = available_items.length;
                        
                        // if (available_items.length == 0){
                        //     // document.querySelectorAll("#modern-search-result > div.service-list-wrapper.list-style > div").forEach( item => {
                        //     //     item.style.display = 'none'
                        //     // })
                        //     document.querySelector("#modern-search-result").innerHTML = `<div class="alert alert-warning mt15">No hotels found.</div>`;
                        // }
                        
                    },
                    error: (err) => {
                        divResult.removeClass('loading');
                        console.error(err);
                    }
                });
            });
            // Data prep for reservation request END
    };
    var resizeMap = 0;
    jQuery(function ($) {
        if (window.matchMedia('(min-width: 992px)').matches) {
            ajaxFilterMapHandler();
        }
    });

    function ajaxFilterMapHandler(){
        console.log("After pagination arrow click");
        var layout = $('#modern-search-result').data('layout');
        if($('.search-result-page').length){
            let wrapper = $('.search-result-page');
            if(wrapper.hasClass('layout5') || wrapper.hasClass('layout6')) {
                data['version'] = 'elementorv2';
                data['version_layout'] = wrapper.data('layout');
                data['version_format'] = wrapper.data('format');
            }
        }
        data['action'] = 'st_filter_hotel_map';
        data['is_search_page'] = 1;
        data['_s'] = st_params._s;
        if(typeof  data['page'] == 'undefined'){
            data['page'] = 1;
        }
        $('.map-loading').fadeIn();
        console.log("Page: " + data['page']);
        $.ajax({
            url: st_params.ajax_url,
            dataType: 'json',
            type: 'get',
            data: data,
            success: function (doc) {
                // var els = document.getElementsByClassName("map-full-height");
                // console.log(els);
                // Array.prototype.forEach.call(els, function(el) {
                //     var t = $(el);
                //     initHalfMapBox(t, doc.data_map, doc.map_lat_center, doc.map_lng_center, '', doc.map_icon, data.version, data.move_map);
                // });
                // var els = document.getElementsByClassName("full-map-form");
                // Array.prototype.forEach.call(els, function(el) {
                //     var t = $(el);
                //     initHalfMapBox(t, doc.data_map, doc.map_lat_center, doc.map_lng_center, '', doc.map_icon, data.version, data.move_map);
                // });


                $('.full-map-form').each(function () {
                    var t = $(this);
                    initHalfMapBox(t, doc.data_map, doc.map_lat_center, doc.map_lng_center, '', doc.map_icon, data.version, data.move_map);
                });
                if ($('.search-result-page.layout5, .search-result-page.layout6').length) {
                    if (window.matchMedia('(max-width: 767px)').matches) {
                        $('.map-full-height').each(function () {
                            var t = $(this);
                            initHalfMapBox(t, doc.data_map, doc.map_lat_center, doc.map_lng_center, '', doc.map_icon, data.version, data.move_map);
                        });
                    }
                } else {
                    $('.map-full-height').each(function () {
                        var t = $(this);
                        initHalfMapBox(t, doc.data_map, doc.map_lat_center, doc.map_lng_center, '', doc.map_icon, data.version, data.move_map);
                    });
                }

            },
            complete: function () {
                $('.map-loading').fadeOut();
                $('.filter-loading').hide();
                resizeMap = 0;
            },
        });
    }

    // Nikola Load more Button functionallity START
    $('#load-more-hotels').on('click', () => {
        let current_currency = document.querySelector('#dropdown-currency').innerText.trim();
        let start = formatDate(data.start);
        let end = formatDate(data.end);
        let guests_adult_number = data['adult_number'];
        let guests_child_number = data['child_number'];
        let location_id = data['location_id'];
    
        showLoadingPrices();
    
        let hotel_ids = Array.from(document.querySelectorAll("#modern-search-result > div.service-list-wrapper.row > div"))
            .map(item => item.getAttribute('data-id'))
            .filter(id => id !== null)
            .join(',');
    
        paginationOffset += 100; // Increment pagination for the next batch
    
        getNonce("filter_hotel").then(nonce => {
            if (!nonce) {
                console.error("Failed to retrieve nonce.");
                return;
            }
    
            $.ajax({
                url: `${current_domain_url}/wp-plugin/APIs/filter_hotel.php`,
                type: 'GET',
                data: {
                    'ids': hotel_ids,
                    'location_id': location_id,
                    'start': start,
                    'end': end,
                    'adults': guests_adult_number,
                    'children': guests_child_number,
                    'currency': current_currency,
                    'pagination_offset': paginationOffset,  // Use updated offset
                    'security': nonce
                },
                success: (response) => {
                    if (response) {
                        response = JSON.parse(response);
                    }

                    console.log(response);
    
                    if (response.error || response.warning) {
                        console.warn(response.error || response.warning);
                        hideLoadingPrices();
                        return;
                    }
    
                    let foundHotels = JSON.parse(response.found_ids || '{}');
                    let allHotels = response.all_ids || [];
    
                    let resultsContainer = document.querySelector("#modern-search-result > div.service-list-wrapper.row");
    
                    Object.keys(foundHotels).forEach(hotelId => {
                        if (!document.querySelector(`[data-id="${hotelId}"]`)) {
                            let hotelDiv = document.createElement("div");
                            hotelDiv.classList.add("hotel-item");
                            hotelDiv.setAttribute("data-id", hotelId);
                            hotelDiv.innerHTML = `
                                <div class="hotel-card">
                                    <h3>Hotel ID: ${hotelId}</h3>
                                    <p>Price: ${foundHotels[hotelId]}</p>
                                </div>
                            `;
                            resultsContainer.appendChild(hotelDiv);
                        }
                    });
    
                    hideLoadingPrices();
    
                    let availableHotelsCount = document.querySelectorAll('#modern-search-result > div.service-list-wrapper.row > div').length;
                    document.querySelector('#No-hotels').innerHTML = availableHotelsCount;
    
                    if (Object.keys(foundHotels).length === 0) {
                        document.querySelector("#load-more-hotels").style.display = "none"; // Hide button if no more hotels
                    }
                },
                error: (xhr, status, error) => {
                    console.error("AJAX Error:", error);
                    hideLoadingPrices();
                }
            });
        });
    });
    // Nikola Load more Button functionallity END

    jQuery(function($) {
        if(checkClearFilter()){
            $('.btn-clear-filter').fadeIn();
        }else{
            $('.btn-clear-filter').fadeOut();
        }
        $(document).on('click', '.btn-clear-filter', function () {
            var arrResetTax = [];
            $('.filter-tax').each(function () {
                if(!Object.keys(arrResetTax).includes($(this).data('type'))){
                    arrResetTax[$(this).data('type')] = [];
                }

                if($(this).is(':checked')){
                    arrResetTax[$(this).data('type')].push($(this).val());
                }
            });

            if(Object.keys(arrResetTax).length){
                for(var i = 0; i < Object.keys(arrResetTax).length; i++){
                    if(typeof data['taxonomy['+ Object.keys(arrResetTax)[i] +']'] != 'undefined'){
                        delete data['taxonomy['+ Object.keys(arrResetTax)[i] +']'];
                    }
                }
            }

            if(typeof data['price_range'] != 'undefined'){
                delete data['price_range'];
                $('input[name="price_range"]').each(function () {
                    var sliderPrice = $(this).data("ionRangeSlider");
                    sliderPrice.reset();
                });
            }

            if(typeof data['star_rate'] != 'undefined'){
                delete data['star_rate'];
            }

            if(typeof data['hotel_rate'] != 'undefined'){
                delete data['hotel_rate'];
            }

            if($('.filter-item').length) {
                $('.filter-item').prop('checked', false);
            }
            if($('.filter-tax').length) {
                $('.filter-tax').prop('checked', false);
            }

            if($('.sort-item').length){
                data['orderby'] = '';
                $('.sort-item').find('input').prop('checked', false);
            }

            $(document).trigger('st_clear_filter_action');
            $(this).fadeOut();
            ajaxFilterHandler();

        });
    });

    function checkClearFilter(){
        if (((typeof data['price_range'] != 'undefined' && data['price_range'].length) || (typeof data['star_rate'] != 'undefined' && data['star_rate'].length) || (typeof data['hotel_rate'] != 'undefined' && data['hotel_rate'].length) || (typeof data['taxonomy[hotel_facilities]'] != 'undefined' && data['taxonomy[hotel_facilities]'].length) || (typeof data['taxonomy[hotel_theme]'] != 'undefined' && data['taxonomy[hotel_theme]'].length) || (typeof data['orderby'] != 'undefined' && data['orderby'] != 'new' && data['orderby'] != '')) && hasFilter) {
            return true;
        } else {
            return false;
        }
    }

    function decodeQueryParam(p) {
        return decodeURIComponent(p.replace(/\+/g, ' '));
    }
    function URLToArrayNew() {
        var res = {};

        $('.toolbar .layout span').each(function () {
            if ($(this).hasClass('active')) {
                res['layout'] = $(this).data('value');
            }
        });

        res['orderby'] = '';

        var sPageURL = window.location.search.substring(1);
        if (sPageURL != '') {
            var sURLVariables = sPageURL.split('&');
            if (sURLVariables.length) {
                for (var i = 0; i < sURLVariables.length; i++) {
                    var sParameterName = sURLVariables[i].split('=');
                    if (sParameterName.length) {
                        let val = decodeQueryParam(sParameterName[1]);
                        res[decodeURIComponent(sParameterName[0])] = val == 'undefined' ? '' : val;
                    }
                }
            }
        }
        return res;
    }


})(jQuery);