<?php
/**
 * Template Name: ChangeLog New
 * Created by PhpStorm.
 * User: HanhDo
 * Date: 5/7/2019
 * Time: 2:19 PM
 */
$url = get_template_directory_uri() . '/changelog_new/';
?>
<!DOCTYPE html>
<!--[if !(IE 6) | !(IE 7) | !(IE 8)  ]><!-->
<html lang="en-US" class="no-js">
<!--<![endif]-->
<head>
    <meta charset="UTF-8"/>
    <title>Traveler Changelog - #1 BOOKING WORDPRESS THEME</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,500,600" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo esc_url($url) ?>bs/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/v2/js/owlcarousel/assets/owl.carousel.min.css" />
    <link rel="stylesheet" href="<?php echo esc_url($url) ?>css/main.css" />
    <?php do_action('st_qv_header'); ?>
</head>
<body>

<div class="header parallax">
    <div id="main-menu" class="sticky">
        <div class="container">
            <div class="row">
                <div class="col-xs-3">
                    <h1><a href="<?php echo home_url(); ?>"><img src="<?php echo esc_url($url) . 'img/logo.svg' ?>"
                                                                 alt="Traveler Logo"/></a></h1>
                </div>
                <div class="col-xs-9">
                    <div class="dropdown dropdown-main-menu">
                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="glyphicon glyphicon-menu-hamburger"></span>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item" href="https://shinehelp.shinetheme.com/">Support</a>
                            <a class="dropdown-item" href="https://travelerwp.com/traveler-changelog/">Changelog</a>
                            <a class="dropdown-item" href="http://guide.travelerwp.com/">Documentation</a>
                            <a href="https://themeforest.net/item/traveler-traveltourbooking-wordpress-theme/10822683"
                               class="dropdown-item btn-buynow">BUY NOW</a></li>
                        </div>
                    </div>
                    <ul class="menu">
                        <li>
                            <a href="https://themeforest.net/item/traveler-traveltourbooking-wordpress-theme/10822683"
                               class="btn-buynow">BUY NOW</a></li>
                        <li><a href="https://shinehelp.shinetheme.com/">Support</a></li>
                        <li><a href="https://travelerwp.com/traveler-changelog/">Changelog</a></li>
                        <li><a href="http://guide.travelerwp.com/">Documentation</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <h1 class="main-text"><?php echo get_the_title(); ?></h1>

</div>

<div class="cl-content">
    <div class="container">
        <?php
            if(have_posts()){
                while(have_posts()){
                    the_post();
                    the_content();
                }
            }
        ?>
    </div>
</div>

<div class="footer">
    <?php
        $year = date('Y');
        echo 'Copyright © '. $year .' by <a href="https://travelerwp.com/">Traveler</a>';
    ?>
</div>


<?php do_action('st_qv_footer_content'); ?>

<script src="<?php echo esc_url($url) ?>js/jquery.min.js"></script>
<script src="<?php echo esc_url($url); ?>/bs/js/bootstrap.min.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/v2/js/jquery.matchHeight.js"></script>
<script src="<?php echo esc_url($url) ?>js/main.js"></script>
<?php do_action('st_qv_footer_script'); ?>
</body>
</html>
