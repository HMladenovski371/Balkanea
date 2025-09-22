<?php 
    
    function log_action( $message ) {
        $log_file = plugin_dir_path( __FILE__ ) . 'debug.log';
        file_put_contents( $log_file, date('Y-m-d H:i:s') . ': ' . $message . PHP_EOL, FILE_APPEND);
    }
    
    log_action('Called');
    
    if ( !defined( 'ABSPATH' ) ) {
        log_action('not defined');
    }
    
    // $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;  // Ensure $order_id is properly set
    
    $order = wc_get_order( $order_id );
    
    
    if ( ! $order ) {
        log_action('Order not found');
        return;
    }
  
    log_action($order);
    
    // Get the order status
    $order_status = $order->get_status();
    log_action($order_status);
    

?>
