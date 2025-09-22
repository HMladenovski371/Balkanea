<?php
/**
 * Created by PhpStorm.
 * User: HanhDo
 * Date: 5/6/2019
 * Time: 11:12 AM
 */
class ST_QuickView_Demo{
    static $_inst;

    public function __construct(){
        add_action('wp_footer',array($this,'__addIconQuickView'));
        add_action('wp_enqueue_scripts',array($this,'__addQuickViewScripts'));

        add_action('st_qv_header',array($this,'__addQuickViewScriptsInLanding'));
        add_action('st_qv_footer_content',array($this,'__addIconQuickView'));
        add_action('st_qv_footer_script',array($this,'__addIconQuickViewScript'));
    }

    public function __addQuickViewScriptsInLanding(){
        ?>
        <link rel="stylesheet" href="<?php echo get_template_directory_uri() . '/quickview_demo/css/main.css'; ?>" />
        <?php
    }

    public function __addIconQuickView(){
        get_template_part('quickview_demo/toolbar');
    }

    public function __addIconQuickViewScript(){
        ?>
        <script src="<?php echo get_template_directory_uri() . '/quickview_demo/js/main.js' ?>"></script>
        <?php
    }

    public function getListDemo(){
        return array(
            array(
                'thumb' => 'mixservice.png',
                'url' => 'https://mixmap.travelerwp.com',
                'heading' => 'Mixmap - Mix Services',
                'status' => 'hot'
            ),
            array(
                'thumb' => 'remap.png',
                'url' => 'https://remap.travelerwp.com',
                'heading' => 'Remap - Rental Marketplace',
                'status' => 'hot'
            ),
            array(
                'thumb' => 'acmap.png',
                'url' => 'https://acmap.travelerwp.com',
                'heading' => 'Acmap - Activity Marketplace',
                'status' => 'hot'
            ),
            array(
                'thumb' => 'tomap.png',
                'url' => 'https://tomap.travelerwp.com',
                'heading' => 'Tomap - Tour Marketplace',
                'status' => 'hot'
            ),
            array(
                'thumb' => 'carmap.png',
                'url' => 'https://carmap.travelerwp.com',
                'heading' => 'Carmap - Car Marketplace',
                'status' => 'hot'
            ),
            array(
                'thumb' => 'citytour.png',
                'url' => 'https://shinetheme.com/travelerdata/citytour/',
                'heading' => 'CityTour',
                'status' => 'new'
            ),
            array(
                'thumb' => 'hostel.png',
                'url' => 'https://shinetheme.com/travelerdata/hostel/',
                'heading' => 'Hostel',
                'status' => 'new'
            ),
            array(
                'thumb' => 'yatour.png',
                'url' => 'https://shinetheme.com/travelerdata/yatour/',
                'heading' => 'Yatour - Tour Agency',
                'status' => 'new'
            ),
            array(
                'thumb' => 'sintour.png',
                'url' => 'https://touragency.travelerwp.com/',
                'heading' => 'Sintour - Tour Agency',
            ),
            array(
                'thumb' => 'solo_tour.png',
                'url' => 'https://shinetheme.com/travelerdata/solotour/',
                'heading' => 'SoloTour - Tour Agency',
            ),
            array(
                'thumb' => 'homap.png',
                'url' => 'https://homap.travelerwp.com',
                'heading' => 'Homap - Hotel Marketplace',
            ),
            array(
                'thumb' => 'hikingtour.png',
                'url' => 'https://shinetheme.com/travelerdata/hikingtourdemo/',
                'heading' => 'Hiking - Tour Agency',
                'status' => 'new'
            ),
            array(
                'thumb' => 'singlehotel.png',
                'url' => 'https://singlehotel.travelerwp.com',
                'heading' => 'Single Hotel',
            ),
            array(
                'thumb' => 'apartment_demo.png',
                'url' => 'https://newhotel.travelerwp.com/',
                'heading' => 'Lustay - Your Apartment',
            ),
            array(
                'thumb' => 'rtl.png',
                'url' => 'https://rtl.travelerwp.com',
                'heading' => 'RTL Demo',
            ),
            array(
                'thumb' => 'affiliate_demo.png',
                'url' => 'https://affiliate.travelerwp.com/',
                'heading' => 'Affiliate',
            ),
        );
    }

    public function getListElementorDemo(){
        return array(
            array(
                'thumb' => 'modmix.png',
                'url' => 'https://modmixmap.travelerwp.com',
                'heading' => 'Modmix',
                'status' => 'new'
            ),
            array(
                'thumb' => 'modtel.png',
                'url' => 'https://modtel.travelerwp.com',
                'heading' => 'Modtel',
                'status' => 'new'
            ),
            array(
                'thumb' => 'modtour.png',
                'url' => 'https://modtour.travelerwp.com',
                'heading' => 'Modtour',
                'status' => 'new'
            ),
            array(
                'thumb' => 'modactivity.png',
                'url' => 'https://modactivity.travelerwp.com',
                'heading' => 'Modactivity',
                'status' => 'new'
            ),
            array(
                'thumb' => 'modrent.png',
                'url' => 'https://modrent.travelerwp.com',
                'heading' => 'Modrent',
                'status' => 'new'
            ),
            array(
                'thumb' => 'modcar.png',
                'url' => 'https://modcar.travelerwp.com',
                'heading' => 'Modcar',
                'status' => 'new'
            ),
            array(
                'thumb' => 'mixservice.png',
                'url' => 'https://mixmap-elementor.travelerwp.com',
                'heading' => 'Mixmap - Mix Services',
                
            ),
            array(
                'thumb' => 'homap.png',
                'url' => 'https://homap-elementor.travelerwp.com',
                'heading' => 'Homap - Hotel Marketplace',
                
            ),
            array(
                'thumb' => 'remap.png',
                'url' => 'https://remap-elementor.travelerwp.com',
                'heading' => 'Remap - Rental Marketplace',
                
                
            ),
            array(
                'thumb' => 'acmap.png',
                'url' => 'https://acmap-elementor.travelerwp.com',
                'heading' => 'Acmap - Activity Marketplace',
                
            ),
            array(
                'thumb' => 'tomap.png',
                'url' => 'https://tomap-elementor.travelerwp.com',
                'heading' => 'Tomap - Tour Marketplace',
                
            ),
            array(
                'thumb' => 'carmap.png',
                'url' => 'https://carmap-elementor.travelerwp.com',
                'heading' => 'Carmap - Car Marketplace',
                
            ),
        );
    }

    public function __addQuickViewScripts()
    {
        wp_enqueue_style('quickview-demo-css', get_template_directory_uri() . '/quickview_demo/css/main.css');
        wp_enqueue_script('quickview-matchHeight-js', get_template_directory_uri() . '/v2/js/jquery.matchHeight.js');
        wp_enqueue_script('quickview-demo-js', get_template_directory_uri() . '/quickview_demo/js/main.js');
    }

    static function inst(){
        if(!self::$_inst) self::$_inst = new self();

        return self::$_inst;
    }
}

ST_QuickView_Demo::inst();
