<?php
/*
 * Template Name: User Dashboard Demo
*/
echo st()->load_template('layouts/modern/common/header-userdashboard');
wp_enqueue_script('template-user-js');
wp_enqueue_script('user.js');
?>

<div class="page-wrapper chiller-theme">
    <!-- sidebar-wrapper  -->
    <main class="page-content">
        <div class="st_content">
            <?php include get_template_directory() . '/user_demo/view/edit-tours.php'; ?>
        </div>
        <?php echo st()->load_template('layouts/modern/common/footer-userdashboard'); ?>
    </main>
    <!-- page-content" -->
</div>
