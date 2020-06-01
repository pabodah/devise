define([
    'jquery'
], function ($) {
    'use strict';

    jQuery( "#product-addtoquote-button" ).click(function() {
        getAttributeData();
    });

    function getAttributeData () {
        var selected_options = {};
        jQuery('div.swatch-attribute').each(function(k,v){
            var attribute_id    = jQuery(v).attr('attribute-id');
            var option_selected = jQuery(v).attr('option-selected');
            console.log(attribute_id, option_selected);
            if(!attribute_id || !option_selected){ return;}
            selected_options[attribute_id] = option_selected;
        });

        var product_id_index = jQuery('[data-role=swatch-options]').data('mageSwatchRenderer').options.jsonConfig.index;
        var found_ids = [];
        jQuery.each(product_id_index, function(product_id,attributes){
            var productIsSelected = function(attributes, selected_options){
                return _.isEqual(attributes, selected_options);
            }
            if(productIsSelected(attributes, selected_options)){
                found_ids.push(product_id);
            }
        });
        console.log(found_ids);
    }
});

