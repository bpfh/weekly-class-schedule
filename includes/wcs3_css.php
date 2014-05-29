<?php

/**
 * Injecting our custom CSS
 */
function wcs3_load_dynamic_css() {
    $wcs3_options = wcs3_load_settings();
    
    $base_color = $wcs3_options['color_base'];
    $details_box = $wcs3_options['color_details_box'];
    $text = $wcs3_options['color_text'];
    $border = $wcs3_options['color_border'];
    $heading_text = $wcs3_options['color_heading_text'];
    $heading_bg = $wcs3_options['color_headings_background'];
    $bg = $wcs3_options['color_background'];
    $qtip_bg = $wcs3_options['color_qtip_background'];
    $links = $wcs3_options['color_links'];
    
    echo '';
    
    /* ------------- CSS ------------ */
    $dynamic_css =
    
    <<<CSS
<style>
	.wcs3-class-container {
	    background-color: #$base_color;
	    color: #$text;
	}
	.wcs3-class-container a {
	    color: #$links;
	}
	.wcs3-details-box-container {
	    background-color: #$details_box;
	}
	body .wcs3-qtip-tip {
	    background-color: #$qtip_bg;
	    border-color: #$border;
	}
	.wcs3-schedule-wrapper table th {
	    background-color: #$heading_bg;
	    color: #$heading_text;
	}
	.wcs3-schedule-wrapper table {
		    background-color: #$bg;
	}
	.wcs3-schedule-wrapper table,
	.wcs3-schedule-wrapper table td,
	.wcs3-schedule-wrapper table th {
	    border-color: #$border;
	}
</style>
CSS;
    
    /* ------------- END ------------ */
    
    echo $dynamic_css;

}
add_action('wp_head', 'wcs3_load_dynamic_css' );