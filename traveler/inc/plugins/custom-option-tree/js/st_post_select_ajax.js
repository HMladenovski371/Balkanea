/**
 * Created by me664 on 11/24/14.
 */
jQuery(function($){
    $('.st_post_select_ajax').each(function(){
        var me=$(this);
        $(this).select2({
            placeholder: me.data('placeholder'),
            minimumInputLength:2,
            allowClear: true,
            width: '100%',
            ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
                url: ajaxurl,
                dataType: 'json',
                quietMillis: 250,
                data: function (term, page) {
                    return {
                        q: term, // search term,
                        action:'st_post_select_ajax',
                        post_type:me.data('post-type')
                    };
                },
                processResults: function (data, params) {
                    return {
                      results: $.map(data.items, function(obj) {
                        return {
                          id: obj.id,
                          text: obj.name
                        };
                      })
                    };
                },
                cache: true
            },
            formatSelection: function(state){
                if (!state.id) return state.name; // optgroup
                return state.name+'<p><em>'+state.description+'</em></p>';
            },
            escapeMarkup: function(m) { return m; }
        });
    });


    const select_selector = document.querySelector("#st_upsell");
    if(select_selector){
        var me=$('#st_upsell');
        let type_service = document.querySelector(".stt_select_type");
        let type_service_value = type_service.value;
        type_service.addEventListener('change', function(event){
            type_service_value = event.target.value;
        });
        if(type_service){
            me.select2({
                placeholder: me.data('placeholder'),
                minimumInputLength:2,
                multiple: true,
                ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
                    url: ajaxurl,
                    dataType: 'json',
                    quietMillis: 250,
                    
                    data: function (term, page) {
                        return {
                            q: term, // search term,
                            action:'st_post_select_ajax',
                            post_type:type_service_value
                        };
                    },
                    processResults: function (data, params) {
                        return {
                          results: $.map(data.items, function(obj) {
                            return {
                              id: obj.id,
                              text: obj.name
                            };
                          })
                        };
                    },
                    cache: true
                },
                escapeMarkup: function(m) { return m; }
            });
        }
    }


    
});
