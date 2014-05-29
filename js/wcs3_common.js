/**
 * The Weekly Class Schedule 3 common JavaScript library.
 */

var WCS3_LIB = {
	/**
	 * builds the template object from WCS3_DATA and the class object
	 * passed from PHP.
	 */
	construct_template: function(template, data) {
		var class_a,
			instructor_a,
			location_a;
				
		class_a = '<span class="wcs3-qtip-box"><a href="#qtip" class="wcs3-qtip">' + data.class_title + '</a>';
		class_a += '<span class="wcs3-qtip-data">' + data.class_desc + '</span></span>';
		
		instructor_a = '<span class="wcs3-qtip-box"><a href="#qtip" class="wcs3-qtip">' + data.instructor_title + '</a>';
		instructor_a += '<span class="wcs3-qtip-data">' + data.instructor_desc + '</span></span>';
		
		location_a = '<span class="wcs3-qtip-box"><a href="#qtip" class="wcs3-qtip">' + data.location_title + '</a>';
		location_a += '<span class="wcs3-qtip-data">' + data.location_desc + '</span></span>';
		
		
		// Replace template placeholders
		template = template.replace('[class]', class_a);
		template = template.replace('[instructor]', instructor_a);
		template = template.replace('[location]', location_a);
		template = template.replace('[start hour]', data.start_hour);
		template = template.replace('[end hour]', data.end_hour);
		template = template.replace('[notes]', data.notes);
		
		return template;
	},
	
	/**
	 * Applies hover and qtip to table layouts.
	 */
	apply_qtip: function() {
		// Standard hover
		jQuery('.wcs3-class-container').each(function() {
			jQuery(this).hoverIntent(function() {
				// Hover on
				jQuery('.wcs3-details-box-container', this).fadeIn(200);
			},
			function() {
				// Hover off
				jQuery('.wcs3-details-box-container', this).hide()
			});
		});
		
		// qTip
		jQuery('.wcs3-qtip-box').each(function() {
			var html = jQuery('.wcs3-qtip-data', this).html();
			
			jQuery('a.wcs3-qtip', this).qtip({ 
			    content: {
			        text: html
			    },
			    show: {
			        event: 'click',
			    },
			    style: { classes: 'wcs3-qtip-tip' }
			})
		});
	},
	
	/**
     * Padding function
     */
    pad: function (n, width, z) {
        z = z || '0';
        n = n + '';
        return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
    }
}