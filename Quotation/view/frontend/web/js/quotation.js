define([
    'jquery'
], function ($) {
    'use strict';

    $( "#product-addtoquote-button" ).click(function() {
        var textinput = '';

        getAttributeData();

        $.ajax ({
            url: 'http://m234.com/quotation/index/index',
            dataType: 'json',
            data: {
                input_val : getAttributeData()
            },
            success: function(data) {
                //console.log(data);
            }
        });
    });

    function getAttributeData () {
        var parameters = {};
        var selected_options = {};
        var product_id;
        var qty;

        $('div.swatch-attribute').each(function(k, v){
            var attribute_id    = $(v).attr('attribute-id');
            var option_selected = $(v).attr('option-selected');
            console.log(attribute_id, option_selected);
            if(!attribute_id || !option_selected){ return;}
            selected_options[attribute_id] = option_selected;
        });
        parameters['attributes'] = selected_options;

        product_id = $('.price-box').attr('data-product-id');
        parameters['product'] = product_id;

        qty = $('#qty').val();
        parameters['qty'] = qty;

        return parameters;
    }
});
