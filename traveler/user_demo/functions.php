<?php
/**
 * Created by PhpStorm.
 * User: HanhDo
 * Date: 5/14/2019
 * Time: 4:12 PM
 */
class ST_User_Demo{
    static $_inst;
    public static $validator;

    public function __construct(){
        self::$validator = new STValidate();
        add_action('wp_enqueue_scripts',array($this,'__addQuickViewScripts'));
        add_action('wp_ajax_st_partner_create_service_tour_demo', array($this, '__stPartnerCreateServiceTour'));
        add_action('wp_ajax_nopriv_st_partner_create_service_tour_demo', array($this, '__stPartnerCreateServiceTour'));
    }

    private function stSetErrorMessage($arr){
        $validator = self::$validator;
        $err = array();
        if(!empty($arr)){
            foreach ($arr as $v){
                if(!empty($validator->error($v))){
                    $err[$v] = $validator->error($v);
                }
            }
        }

        return $err;
    }

    private function getSuccessEditService($sc, $post_id){
        $linkEdit = get_the_permalink(30952);

        STTemplate::set_message(__('Successfully.', 'traveler'), 'success');
        echo json_encode(array(
            'status' => true,
            'next_step' => 'final',
            'post_id' => 9999,
            'linkEdit' => $linkEdit,
            'message' => __('Successfully.', 'traveler')
        ));
        die;
    }

    public function __stPartnerCreateServiceTour(){
        $step = STInput::post('step', 1);
        $step_name = STInput::post('step_name', 'basic_info');
        switch ($step_name){
            case 'basic_info':
                $valid = $this->stTourValidate(1);
                if($valid){
                    echo json_encode(array(
                        'status' => true,
                        'next_step' => 2,
                        'next_step_name' => $step_name,
                        'post_id' => 9999,
                    ));
                    die;
                }else{
                    $err = $this->stSetErrorMessage(array('st_title', 'st_content', 'st_desc', 'contact_email', 'website', 'video'));
                    echo json_encode(array(
                        'status' => false,
                        'err' => $err
                    ));
                    die;
                }
                break;
            case 'info':
                $post_id = STInput::post('post_id');
                if(!empty($post_id)){
                    $valid = $this->stTourValidate(2);
                    if($valid){
                        echo json_encode(array(
                            'status' => true,
                            'next_step' => 3,
                            'next_step_name' => $step_name,
                            'post_id' => 9999
                        ));
                        die;
                    }else{
                        $arr_err = array('duration_day', 'min_people');
                        if(isset($_REQUEST['st_tour_external_booking']) && $_REQUEST['st_tour_external_booking'] == 'on'){
                            array_push($arr_err, 'st_tour_external_booking_link');
                        }
                        $err = $this->stSetErrorMessage($arr_err);
                        echo json_encode(array(
                            'status' => false,
                            'err' => $err
                        ));
                        die;
                    }
                }
                break;
            case 'photos':
                $post_id = STInput::post('post_id');
                if(!empty($post_id)){
                    $valid = $this->stTourValidate(3);
                    if($valid) {
                        echo json_encode(array(
                            'status' => true,
                            'next_step' => 4,
                            'next_step_name' => $step_name,
                            'post_id' => 9999
                        ));
                        die;
                    }else{
                        $err = $this->stSetErrorMessage(array('id_featured_image', 'id_gallery'));
                        echo json_encode(array(
                            'status' => false,
                            'err' => $err
                        ));
                        die;
                    }
                }
                break;
            case 'prices':
                $post_id = STInput::post('post_id');
                if(!empty($post_id)) {
                    $valid = $this->stTourValidate(4);
                    if($valid) {
                        echo json_encode(array(
                            'status' => true,
                            'next_step' => 5,
                            'next_step_name' => $step_name,
                            'post_id' => 9999
                        ));
                        die;
                    }else{
                        if(isset($_REQUEST['tour_price_by'])){
                            $tour_price_type = $_REQUEST['tour_price_by'];
                            $arr = [];
                            if($tour_price_type == 'person' || $tour_price_type == 'fixed_depart'){
                                array_push($arr, 'adult_price');
                                array_push($arr, 'child_price');
                                array_push($arr, 'infant_price');
                            }
                            if($tour_price_type == 'fixed_depart'){
                                array_push($arr, 'start_date_fixed');
                                array_push($arr, 'end_date_fixed');
                            }
                            if($tour_price_type == 'fixed'){
                                array_push($arr, 'base_price');
                            }
                        }
                        if(isset($_REQUEST['is_sale_schedule'])) {
                            $is_sale_schedule = $_REQUEST['is_sale_schedule'];
                            if ($is_sale_schedule == 'on') {
                                array_push($arr, 'sale_price_from');
                                array_push($arr, 'sale_price_to');
                            }
                        }
                        if(isset($_REQUEST['deposit_payment_status'])){
                            $deposit_payment_status = $_REQUEST['deposit_payment_status'];
                            if($deposit_payment_status == 'percent'){
                                array_push($arr, 'deposit_payment_amount');
                            }
                        }
                        if(isset($_REQUEST['st_allow_cancel'])){
                            $st_allow_cancel = $_REQUEST['st_allow_cancel'];
                            if($st_allow_cancel == 'on'){
                                array_push($arr, 'st_cancel_number_days');
                                array_push($arr, 'st_cancel_percent');
                            }
                        }
                        $err = $this->stSetErrorMessage($arr);
                        echo json_encode(array(
                            'status' => false,
                            'err' => $err
                        ));
                        die;
                    }
                }
                break;
            case 'locations':
                $post_id = STInput::post('post_id');
                if(!empty($post_id)) {
                    $valid = $this->stTourValidate(5);
                    if($valid) {
                        echo json_encode(array(
                            'status' => true,
                            'next_step' => 6,
                            'next_step_name' => $step_name,
                            'post_id' => 9999
                        ));
                        die;
                    }else{
                        $err = $this->stSetErrorMessage(array('address', 'multi_location'));
                        echo json_encode(array(
                            'status' => false,
                            'err' => $err
                        ));
                        die;
                    }
                }
                break;
            case 'payments':
                $post_id = STInput::post('post_id');
                if(!empty($post_id)) {

                    if($step != 'final'){
                        echo json_encode(array(
                            'status' => true,
                            'next_step' => 7,
                            'next_step_name' => $step_name,
                            'post_id' => 9999
                        ));
                        die;
                    }else{
                        $this->getSuccessEditService('edit-tours', $post_id);
                    }
                }
                break;
            case 'package':
                $post_id = STInput::post('post_id');
                if(!empty($post_id)) {
                    echo json_encode(array(
                        'status' => true,
                        'next_step' => 8,
                        'sc' => 'edit-tours',
                        'next_step_name' => $step_name,
                        'post_id' => 9999
                    ));
                    die;
                }
                break;
            case 'availability':
                $post_id = STInput::post('post_id');
                if(!empty($post_id)) {
                    echo json_encode(array(
                        'status' => true,
                        'next_step' => 9,
                        'next_step_name' => $step_name,
                        'post_id' => 9999
                    ));
                    die;
                }
                break;
            case 'ical':
                $post_id = STInput::post('post_id');
                if(!empty($post_id)) {
                    if($step != 'final') {
                        echo json_encode(array(
                            'status' => true,
                            'next_step' => 10,
                            'next_step_name' => $step_name,
                            'post_id' => 9999
                        ));
                        die;
                    }else{
                        $this->getSuccessEditService('edit-tours', $post_id);
                    }
                }
                break;
            case 'custom_fields':

                $post_id = STInput::post('post_id');
                if(!empty($post_id)) {
                    if($step != 'final') {
                        echo json_encode(array(
                            'status' => true,
                            'next_step' => 11,
                            'post_id' => 9999,
                            'next_step_name' => $step_name
                        ));
                        die;
                    }else{
                        $this->getSuccessEditService('edit-tours', $post_id);
                    }
                }
                break;
        }



    }
    private function stTourValidate($step = 1){
        $validator = self::$validator;
        switch ($step){
            case 1:
                $validator->set_rules('st_title', __("Title", 'traveler'), 'required|min_length[6]|max_length[100]');
                $validator->set_rules('st_content', __("Content", 'traveler'), 'required');
                $validator->set_rules('st_desc', __("Short Intro", 'traveler'), 'required');
                $validator->set_rules('contact_email', __("Email", 'traveler'), 'required|valid_email');
                $validator->set_rules('website', __("Website", 'traveler'), 'valid_url');
                $validator->set_rules('video', __("Video", 'traveler'), 'valid_url');
                break;
            case 2:
                $validator->set_rules('duration_day', __("Duration", 'traveler'), 'required');
                $validator->set_rules('min_people', __("Min people", 'traveler'), 'required');
                if(isset($_REQUEST['st_tour_external_booking']) && $_REQUEST['st_tour_external_booking'] == 'on'){
                    $validator->set_rules('st_tour_external_booking_link', __("External booking URL", 'traveler'), 'required|valid_url');
                }
                break;
            case 3:
                $validator->set_rules('id_featured_image', __("Featured image", 'traveler'), 'required');
                $validator->set_rules('id_gallery', __("Gallery", 'traveler'), 'required');
                break;
            case 4:
                if(isset($_REQUEST['tour_price_by'])){
                    $tour_price_type = $_REQUEST['tour_price_by'];
                    if($tour_price_type == 'person' || $tour_price_type == 'fixed_depart'){
                        $validator->set_rules('adult_price', __("Adult price", 'traveler'), 'required');
                        $validator->set_rules('child_price', __("Child price", 'traveler'), 'required');
                        $validator->set_rules('infant_price', __("Infant price", 'traveler'), 'required');
                    }

                    if($tour_price_type == 'fixed_depart'){
                        $validator->set_rules('start_date_fixed', __("Start date", 'traveler'), 'required');
                        $validator->set_rules('end_date_fixed', __("End date", 'traveler'), 'required');
                    }

                    if($tour_price_type == 'fixed'){
                        $validator->set_rules('base_price', __("Base price", 'traveler'), 'required');
                    }
                }
                if(isset($_REQUEST['is_sale_schedule'])){
                    $is_sale_schedule = $_REQUEST['is_sale_schedule'];
                    if($is_sale_schedule == 'on'){
                        $validator->set_rules('sale_price_from', __("Sale start date", 'traveler'), 'required');
                        $validator->set_rules('sale_price_to', __("Sale end date", 'traveler'), 'required');
                    }
                }
                if(isset($_REQUEST['deposit_payment_status'])){
                    $deposit_payment_status = $_REQUEST['deposit_payment_status'];
                    if($deposit_payment_status == 'percent'){
                        $validator->set_rules('deposit_payment_amount', __("Deposit amount", 'traveler'), 'required');
                    }
                }
                if(isset($_REQUEST['st_allow_cancel'])){
                    $st_allow_cancel = $_REQUEST['st_allow_cancel'];
                    if($st_allow_cancel == 'on'){
                        $validator->set_rules('st_cancel_number_days', __("Number of days before the arrival", 'traveler'), 'required');
                        $validator->set_rules('st_cancel_percent', __("Percent of total price", 'traveler'), 'required');
                    }
                }
                break;
            case 5:
                $validator->set_rules('address', __("Address", 'traveler'), 'required');
                $location = $_REQUEST['multi_location'];
                if(isset($_REQUEST['multi_location']) && empty($location)){
                    $validator->set_error_message('multi_location', __("The Location field is required.", 'traveler'));
                }
                break;
        }

        $result = $validator->run();
        return $result;
    }

    public function __addQuickViewScripts()
    {
        if (is_page_template('user_demo/template-user-demo.php')) {
            wp_enqueue_media();
            //wp_dequeue_script('traveler');
            wp_dequeue_style('fixudashboard-css');
            wp_dequeue_style('traveler-ext');
            wp_dequeue_style('js_composer_front');
            wp_dequeue_style('woocommerce-layout');
            wp_dequeue_style('woocommerce-smallscreen');
            wp_dequeue_style('woocommerce-general');
            wp_dequeue_style('contact-form-7');
            //wp_dequeue_style('st-select2');
            wp_dequeue_style('traveler');
            //wp_dequeue_script('nicescroll.js');
            wp_localize_script('jquery', 'dashboard_params', array(
                'complete_registration_text' => __('COMPLETE YOUR REGISTRATION', 'traveler'),
                'complete_text' => __('COMPLETE', 'traveler'),
                'continue_text' => __('CONTINUE', 'traveler'),
            ));
            wp_enqueue_style('google-font-Poppins', 'https://fonts.googleapis.com/css?family=Poppins:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i');
            wp_enqueue_style('fontawesome', get_template_directory_uri() . '/css/font-awesome.css');
            wp_enqueue_style('daterangepicker-css', get_template_directory_uri() . '/v2/js/daterangepicker/daterangepicker.css');
            wp_enqueue_style('fontawesome', get_template_directory_uri() . '/css/font-awesome.css');
            wp_enqueue_style('bootstrap-css', get_template_directory_uri() . '/v2/css/bootstrap.min.css');
            wp_enqueue_style('st-partner-v2', get_template_directory_uri() . '/v2/css/partner.css');
            wp_enqueue_style('st-user-demo', get_template_directory_uri() . '/user_demo/css/main.css');

            wp_enqueue_style('st-user-css', get_template_directory_uri() . '/css/user.css');

            wp_enqueue_style('st-partner-h-v2', get_template_directory_uri() . '/v2/css/partner-h.css');

            wp_enqueue_script('daterangepicker-lang-js', get_template_directory_uri() . '/v2/js/daterangepicker/languages/' . get_locale() . '.js', ['jquery'], null, true);
            wp_enqueue_script('daterangepicker-js', get_template_directory_uri() . '/v2/js/daterangepicker/daterangepicker.js', ['jquery'], null, true);
            wp_enqueue_script('st-partner-v2', get_template_directory_uri() . '/v2/js/partner.js', ['jquery'], null, true);
            wp_enqueue_script('st-partner-h-v2', get_template_directory_uri() . '/user_demo/js/partner-h.js', ['jquery'], null, true);
            //wp_enqueue_script('bootstrap-js', get_template_directory_uri() . '/v2/js/bootstrap.min.js', ['jquery'], null, true);
            wp_enqueue_script('bootstrap-timepicker-js', get_template_directory_uri() . '/v2/js/bootstrap-timepicker.js', ['jquery'], null, true);
            wp_enqueue_script('jquery.matchHeight-min');
            $gg_api_key = st()->get_option('google_api_key', "");

            if (is_ssl()) {
                $url = add_query_arg([
                    'v' => '3', //v=3.exp
                    'libraries' => 'places',
                    'language' => 'en',
                    'key' => $gg_api_key
                ], 'https://maps.googleapis.com/maps/api/js');
            } else {
                $url = add_query_arg([
                    'v' => '3',
                    'libraries' => 'places',
                    'language' => 'en',
                    'key' => $gg_api_key
                ], 'http://maps.googleapis.com/maps/api/js');
            }

            wp_register_script('gmap-apiv3', $url, ['jquery'], null, true);

            wp_register_script('st-partner-address_autocomplete', get_template_directory_uri() . '/v2/js/address_autocomplete.js', array('jquery', 'gmap-apiv3'), false, true);
            wp_register_style('st-partner-address_autocomplete', get_template_directory_uri() . '/v2/css/address_autocomplete.css');

            wp_register_script('st-partner-gmapv3', get_template_directory_uri() . '/v2/js/gmap3.min.js', array('jquery', 'gmap-apiv3'), false, true);
            wp_register_script('st-partner-gmapv3-init', get_template_directory_uri() . '/v2/js/partner-map.js', array('st-partner-gmapv3'), false, true);
        }
    }

    static function inst(){
        if(!self::$_inst) self::$_inst = new self();

        return self::$_inst;
    }
}

ST_User_Demo::inst();
