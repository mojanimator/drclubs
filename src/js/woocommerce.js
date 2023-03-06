jQuery(document).ready(() => {

        let registerButtonInCheckout = document.getElementById('drclubs-create-register-form');


        let field = jQuery('#drclubs_checkout_consumed_balance_field');

        field.find('.optional').text('');
        let balance = field.find('strong');
        let firstBalanceValue = jQuery('#drclubs_checkout_first_balance').val();
        let consumeTextField = jQuery('#drclubs_checkout_consumed_balance');


        jQuery('#drclubs-update-checkout-ui').click((e) => {

            updateCheckout()
        });


        // jQuery('#drclubs_checkout_field strong').text('hi');

        // jQuery(function ($) {
        jQuery("form.woocommerce-checkout")
            .on('submit', function () {
                updateCheckout();
            });
        // });

        const updateCheckout = () => {
            if (consumeTextField.val() < 0) consumeTextField.val(0);
            jQuery('body').trigger('update_checkout');
            let consume = firstBalanceValue - consumeTextField.val();

            if (consume >= 0)
                balance.text(consume);
            else {
                if (firstBalanceValue >= 0)
                    consumeTextField.val(firstBalanceValue);
                else
                    consumeTextField.val('');
                balance.text(0);
            }
        };
    }
);

