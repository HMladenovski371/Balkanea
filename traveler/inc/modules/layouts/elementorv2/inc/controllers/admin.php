<?php
if (!class_exists('STT_Hotelv2_Admin')) {
    class STT_Hotelv2_Admin
    {
        private static $_inst;

        public function __construct()
        {
            add_filter('st_list_menu_style', [$this, '_addNewHeaderSelection']);
            add_filter('st_settings_page_options', [$this, '_changePageOptions']);
            add_filter('st_layout_search_tour', [$this, '_addNewLayoutSearchTour']);
            add_filter('st_layout_single_tour',[$this, '_addNewLayoutSingleTour']);
        }

        public function _changePageOptions($options) {
            if (check_using_elementor()) {
                array_splice( $options, 8, 0, [
                    [
                        'id' => 'page_checkout_style',
                        'label' => __('Select Checkout Page Style', 'traveler'),
                        'desc' => __('Select styles of checkout page (it is default as style 1)', 'traveler'),
                        'type' => 'radio-image',
                        'section' => 'option_page',
                        'std' => '1',
                        'choices' => apply_filters('st_checkout_page_style', [
                            [
                                'id' => '1',
                                'alt' => __('Style 1', 'traveler'),
                                'src' => get_template_directory_uri() . '/img/checkout/style-1.png',
                            ],
                            [
                                'id' => '2',
                                'alt' => __('Style 2', 'traveler'),
                                'src' => get_template_directory_uri() . '/img/checkout/style-2.png',
                            ]
                        ]),
                    ]
                ]);
            }
            return $options;
        }

        public function _addNewHeaderSelection($lists)
        {

            if (check_using_elementor()) {
                $lists[] = [
                    'id' => '9',
                    'alt' => esc_html__('White Header', 'traveler'),
                    'src' => get_template_directory_uri() . '/img/nav10.png'
                ];
                $lists[] = [
                    'id' => '10',
                    'alt' => esc_html__('Solo Header', 'traveler'),
                    'src' => get_template_directory_uri() . '/img/header-solo.png'
                ];
            }
            return $lists;
        }

        public function _addNewLayoutSearchTour($lists)
        {

            if (check_using_elementor()) {
                $lists[] = [
                    'value' => '8',
                    'label' => esc_html__('Solo Layout', 'traveler'),
                    'src' => get_template_directory_uri() . '/v2/images/layouts/tour_rs_layout_5.png'
                ];
            }
            return $lists;
        }
        public function _addNewLayoutSingleTour($lists) {
            if (check_using_elementor()) {
                $lists[] = [
                    'value' => '10',
					'label' => esc_html__( 'Layout modern 3', 'traveler' ),
					'src'   => get_template_directory_uri() . '/v2/images/layouts/tour_detail_layout_solo_ele.png',
                ];
            }
            return $lists;
        }
        public static function inst()
        {
            if (empty(self::$_inst)) {
                self::$_inst = new self();
            }
            return self::$_inst;
        }
    }

    STT_Hotelv2_Admin::inst();
}
