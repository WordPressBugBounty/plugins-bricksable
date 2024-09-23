/**
 * Bricksable admin js.
 *
 */
jQuery(document).ready(function(c){jQuery("#all_elements").click(function(){jQuery("input:checkbox").not(this).prop("checked",this.checked)}),jQuery("#all_bricks_builder_elements").click(function(){jQuery("input:checkbox").not(this).prop("checked",this.checked)})});