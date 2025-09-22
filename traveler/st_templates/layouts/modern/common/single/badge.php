<div class="widget-box st-widgets-detail st-badge-widget st-border-radius">
    <h4 class="heading"><?php echo __('Why book with us?', 'traveler') ?></h4>
    <ul class="st-list-badges">
        <?php 
            foreach ($list_badges as $key => $badge) {?>
                <li class="flex justify-left d-flex align-items-start align-self-start">
                    <div class="st-list-badge">
                        <img src="<?php echo esc_url($badge['icon']);?>" alt="<?php echo esc_attr($badge['title']);?>">
                    </div>
                    <div class="content-badge">
                        <p class="title">
                            <?php echo esc_html($badge['title']);?>
                        </p>
                        <p class="content">
                            <?php echo esc_html($badge['content']);?>
                        </p>
                    </div>
                </li>
            <?php }
        ?>
    </ul>
</div>