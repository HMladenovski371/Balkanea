jQuery(document).ready(function($) {
    if ($('body').hasClass('woocommerce-checkout')) {
        $('#billing_postcode').attr("type", "number");
        $('#billing_email_field').attr("type", "email");
        
        // Define the regular expression for phone number validation
            const phoneRegex = /^[+]?[0-9\s()-]{10,15}$/; 
            const $phoneInput = $('#billing_phone');
            const $phoneMessage = $('#phoneMessage');

            // Real-time validation as the user types
            $phoneInput.on('input', function () {
                const phone = $phoneInput.val();
                if (!phoneRegex.test(phone)) {
                    return false;
                }
            });
    }
});
