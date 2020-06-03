define([
    'jquery',
    'loader'
], function ($) {
    'use strict';

    $( "#product-addtoquote-button" ).click(function() {
        var body = $('body');
        body.loader('show');

        $.ajax ({
            showLoader: true,
            url: BASE_URL + ('quotation/index/index'),
            dataType: 'json',
            data: {
                attribute_data : getAttributeData()
            },
            success: function(data) {
                window.location.replace(BASE_URL + ('quotation/index/pdf/id/') + data.status);
                body.loader('hide');
            }
        });
    });

    $( "#product-addtoquote-button-checkout" ).click(function() {
        var body = $('body');
        body.loader('show');

        $.ajax ({
            showLoader: true,
            url: BASE_URL + ('quotation/index/index'),
            dataType: 'json',
            data: {},
            success: function(data) {
                window.location.replace(BASE_URL + ('quotation/index/pdf/id/') + data.status);
                body.loader('hide');
            }
        });
    });

    function getAttributeData ()
    {
        var parameters = {};
        var selected_options = {};
        var selected_options_values = {};
        var product_id;
        var qty;

        $('div.swatch-attribute').each(function(k, v){
            var attribute_id    = $(v).attr('attribute-id');
            var option_selected = $(v).attr('option-selected');

            var attribute_name    = $(this).find('.swatch-attribute-label').text();
            var option_selected_name    = $(this).find('.swatch-attribute-selected-option').text();

            //console.log(attribute_id, option_selected);
            if(!attribute_id || !option_selected){ return;}
            selected_options[attribute_id] = option_selected;
            selected_options_values[attribute_name] = option_selected_name;
        });
        parameters['attributes'] = selected_options;
        parameters['attribute_values'] = selected_options_values;

        product_id = $('.price-box').attr('data-product-id');
        parameters['product'] = product_id;

        qty = $('#qty').val();
        parameters['qty'] = qty;

        return parameters;
    }
});
