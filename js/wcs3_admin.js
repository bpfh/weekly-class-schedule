/**
 * Javascript for WCS3 admin.
 */

(function($) {
	
	// WCS3_AJAX_OBJECT available
	
	$(document).ready(function() {
		wcs3_bind_schedule_submit_handler();
		wcs3_bind_schedule_delete_handler();
		wcs3_bind_schedule_edit_handler();
		
		wcs3_bind_colorpickers();
		
		wcs3_bind_import_update();
	});
	
	
	/**
	 * Handles the Add Item button click event.
	 */
	var wcs3_bind_schedule_submit_handler = function() {
		$('#wcs3-submit-item').click(function(e) {
			var day = $('#wcs3_weekday option:selected').val(),
				entry;
			
			e.preventDefault();
			
			entry = {
				action: 'add_or_update_schedule_entry',
				security: WCS3_AJAX_OBJECT.ajax_nonce,
				class_id: $('#wcs3_class option:selected').val(),
				instructor_id: $('#wcs3_instructor option:selected').val(),
				location_id: $('#wcs3_location option:selected').val(),
				weekday: day,
				start_hour: $('#wcs3_start_time_hours option:selected').val(),
				start_minute: $('#wcs3_start_time_minutes option:selected').val(),
				end_hour: $('#wcs3_end_time_hours option:selected').val(),
				end_minute: $('#wcs3_end_time_minutes option:selected').val(),
				visible: $('#wcs3_visibility option:selected').val(),
				notes: $('#wcs3_notes').val()
			};
			
			if ($('#wcs3-row-id').length > 0) {
				// We've got a hidden row field, that means this is an update
				// request and not a regular insert request.
				entry.row_id = $('#wcs3-row-id').val();
			}
						
			$('#wcs3-schedule-management-form-wrapper .wcs3-ajax-loader').show();
			
			// We can also pass the url value separately from ajaxurl for 
			// front end AJAX implementations
			jQuery.post(WCS3_AJAX_OBJECT.ajax_url, entry, function(data) {
				schedule_item_message(data.response, data.result);
				
				if (data.result == 'updated') {
					// Let's refresh the day
					for (var day_to_update in data.days_to_update) {
						update_day_schedule(day_to_update, 'add');
					}
					
					// Clear notes.
					$('#wcs3_notes').val('');
				}
				
			}).fail(function(err) {
				// Failed
				console.error(err);
				schedule_item_message(WCS3_AJAX_OBJECT.ajax_error, 'error');
				
			}).always(function() {
				exit_editing_mode();
				$('#wcs3-schedule-management-form-wrapper .wcs3-ajax-loader').hide();
			});
		});
	}
	
	/**
	 * Handles the delete button click event.
	 */
	var wcs3_bind_schedule_delete_handler = function() {
		$('.wcs3-delete-button').each(function() {
			// Check if element is already bound.
			if (is_elem_unbound($(this))) {
				// Bound, continue.
				return true;
			}
			
			// Re-bind new elements
			$(this).click(function(e) {
				var row_id,
					src,
					entry,
					confirm = true;
				
				if (typeof(e.target) != 'undefined') {
					src = e.target;
				}
				else {
					src = e.srcElement;
				}
				row_id = src.id.replace('delete-entry-', '')
				
				// Confirm delete operation.
				confirm = window.confirm(WCS3_AJAX_OBJECT.delete_warning);
				if (!confirm) {
					return;
				}
				
				entry = {
					action: 'delete_schedule_entry',
					security: WCS3_AJAX_OBJECT.ajax_nonce,
					row_id: row_id
				};
				
				$('#wcs3-schedule-management-form-wrapper .wcs3-ajax-loader').show();
				
				jQuery.post(WCS3_AJAX_OBJECT.ajax_url, entry, function(data) {
					var day,
						elem;
					
					if (typeof(e.target) != 'undefined') {
						elem = e.target;
					}
					else {
						elem = e.srcElement;
					}
					day = get_day_from_element(elem);
					
					if (day !== false) {
						// Let's refresh the day
						update_day_schedule(day, 'remove');
					}
					
				}).fail(function(err) {
					// Failed
					console.error(err);
					schedule_item_message(WCS3_AJAX_OBJECT.ajax_error, 'error');
					
				}).always(function() {
					$('#wcs3-schedule-management-form-wrapper .wcs3-ajax-loader').hide();
				});
			});
		});
	}
	
	/**
	 * Handles the edit button click event.
	 */
	var wcs3_bind_schedule_edit_handler = function() {
		$('.wcs3-edit-button').each(function() {
			if (is_elem_unbound($(this))) {
				// Bound, continue.
				return true;
			}
			
			// Re-bind new elements
			$(this).click(function(e) {
				var src_elem,
					row_id,
					entry;
				
				if (typeof(e.target) != 'undefined') {
					src_elem = e.target;
				}
				else {
					src_elem = e.srcElement;
				}
				
				row_id = src_elem.id.replace('edit-entry-', '');
				
				entry = {
					action: 'edit_schedule_entry',
					security: WCS3_AJAX_OBJECT.ajax_nonce,
					row_id: row_id
				};
				
				$('#wcs3-schedule-management-form-wrapper .wcs3-ajax-loader').show();
				
				jQuery.post(WCS3_AJAX_OBJECT.ajax_url, entry, function(data) {
					// Get row data
					var entry = data.response,
						start_array,
						end_array,
						start_hour,
						start_min,
						end_hour,
						end_min,
						visibility;
					
					if (entry.hasOwnProperty('class_id')) {
						// We got an entry.
						$('#wcs3_class').val(entry.class_id);
						$('#wcs3_instructor').val(entry.instructor_id);
						$('#wcs3_location').val(entry.location_id);
						$('#wcs3_weekday').val(entry.weekday);
						
						// Update time fields.
						start_array = entry.start_hour.split(':');
						start_hour = start_array[0].replace(/^[0]/g,"");
						start_min = start_array[1].replace(/^[0]/g,"");
						
						end_array = entry.end_hour.split(':');
						end_hour = end_array[0].replace(/^[0]/g,"");
						end_min = end_array[1].replace(/^[0]/g,"");
						
						$('#wcs3_start_time_hours').val(start_hour);
						$('#wcs3_start_time_minutes').val(start_min);
						
						$('#wcs3_end_time_hours').val(end_hour);
						$('#wcs3_end_time_minutes').val(end_min);
						
						if (entry.visible == '1') {
							visibility = 'visible';
						}
						else {
							visibility = 'hidden';
						}
						
						$('#wcs3_visibility').val(visibility);
						$('#wcs3_notes').val(entry.notes);
						
						// Let's add the row id and the save button.
						$('#wcs3-submit-item').attr('value', WCS3_AJAX_OBJECT.save_item);
						
						/* ------------ Change to editing mode --------- */
						enter_edit_mode(row_id);
						/* ----------------------------------------------- */					
					}
					
				}).fail(function(err) {
					// Failed
					console.error(err);
					schedule_item_message(WCS3_AJAX_OBJECT.ajax_error, 'error');
					
				}).always(function() {
					$('#wcs3-schedule-management-form-wrapper .wcs3-ajax-loader').hide();
				});
			});
		});
	
	}
	
	/**
	 * Updates dynamically a specific day schedule.
	 */
	var update_day_schedule = function(day, action) {
		entry = {
			action: 'get_day_schedule',
			security: WCS3_AJAX_OBJECT.ajax_nonce,
			day: day
		};
			
		jQuery.post(WCS3_AJAX_OBJECT.ajax_url, entry, function(data) {
			// Rebuild table
			var html = data.html,
				parent = $('#wcs3-schedule-day-' + day),
				to_remove;
			
			if (html.length > 0) {
				to_remove = $('.wcs3-day-content-wrapper', parent);
				
				if (action == 'add') {
					to_remove.remove();
					parent.append(html).hide().fadeIn('slow');
				}
				else if (action == 'remove') {
					to_remove.remove();
					parent.append(html);
				}
			}
			
			
		}).fail(function(err) {
			// Failed
			console.error(err);
			schedule_item_message(WCS3_AJAX_OBJECT.ajax_error, 'error');
			
		}).always(function() {	
			// Re-bind handlers
			wcs3_bind_schedule_delete_handler();
			wcs3_bind_schedule_edit_handler();
			
			$('#wcs3-schedule-management-form-wrapper .wcs3-ajax-loader').hide();
		});
	}
	
	/**
	 * Enter edit mode.
	 */
	var enter_edit_mode = function(row_id) {
		var row_hidden_field,
			cancel_button,
			msg;
		
		// Add editing mode message
		if ($('#wcs3-editing-mode-message').length == 0) {
			msg = '<div id="wcs3-editing-mode-message" class="wcs3-form-message">' + WCS3_AJAX_OBJECT.edit_mode + '</div>';
			$('#wcs3-schedule-management-form-wrapper').prepend(msg)
		}
		
		// Add hidden row field
		if ($('#wcs3-row-id').length > 0) {
			// Field already exists, let's update.
			$('#wcs3-row-id').attr('value', row_id);
		}
		else {
			// Field does not exist.
			row_hidden_field = '<input type="hidden" id="wcs3-row-id" name="wcs3-row-id" value="' + row_id + '">';
			$('#wcs3-schedule-management-form-wrapper').append(row_hidden_field);
		}
		
		// Add cancel editing button
		if ($('#wcs3-cancel-editing').length == 0) {
			cancel_button = '<div id="wcs3-cancel-editing-wrapper"><a href="#" id="wcs3-cancel-editing">' + WCS3_AJAX_OBJECT.cancel_editing + '</a></div>';
			$('#wcs3-schedule-management-form-wrapper').append(cancel_button);
			$('#wcs3-cancel-editing').click(function() {
				exit_editing_mode();
			})
		}
	}
	
	/**
	 * Exit edit mode.
	 */
	var exit_editing_mode = function() {
		$('#wcs3-editing-mode-message').remove();
		$('#wcs3-row-id').remove();
		$('#wcs3-cancel-editing-wrapper').remove();
		$('#wcs3-submit-item').val(WCS3_AJAX_OBJECT.add_item);
	}
	
	/**
	 * Handles the Ajax UI messaging.
	 */
	var schedule_item_message = function(message, status) {
		$('.wcs3-ajax-text').html('').show();
		$('.wcs3-ajax-text').removeClass('updated').removeClass('error')
		if (status == 'updated') {
			$('.wcs3-ajax-text').addClass('updated');
		}
		else if (status == 'error') {
			$('.wcs3-ajax-text').addClass('error');
		}
		$('.wcs3-ajax-text').html(message);
		setTimeout(function() {
			$('.wcs3-ajax-text').fadeOut('slow');
		}, 2000);
	}
	
	/**
	 * Extracts the day ID from an element (delete or edit).
	 */
	var get_day_from_element = function(elem) {
		var cls = elem.className,
			m,
			day;
		
		m = cls.match(/wcs3-action-button-day-(\d)+/g);
		if (m.length > 0) {
			m = m[0];
			day = m.replace('wcs3-action-button-day-', '');
			return parseInt(day);
		}
		else {
			return false;
		}
	}
	
	/**
	 * Checks if a jQuery element is already bound to the 'click' event.
	 * 
	 * @return bool: true if bound, false if not.
	 */
	var is_elem_unbound = function(elem) {
		// Check if element is already bound.
		var t = elem.data('events');
		
		if (typeof(t) != 'undefined') {
			if (t.hasOwnProperty('click')) {
				if (t['click'].length > 0) {
					// Element is already bound.
					// Continue to next iternation, no need to re-bind.
					return true;
				}
			}
		}
	}
	
	/**
	 * Binds the colorpicker plugin to the selectors
	 */
	var wcs3_bind_colorpickers = function() {
		$('.wcs_colorpicker').each(function(index) {
			var elementName = $(this).attr('id');
			$(this).ColorPicker({
				onChange: function (hsb, hex, rgb) {
					$('#' + elementName).val(hex);
					$('.' + elementName).css('background', '#' + hex);
				},
				onBeforeShow: function (hsb, hex, rgb) {
					$(this).ColorPickerSetColor(this.value);
				}
			});
		});
	}
		

	var wcs3_bind_import_update = function() {
		$('#wcs3_import_wcs2_data').click(function(e) {
			var confirm;
			
			e.preventDefault();
			
			entry = {
					action: 'import_update_data',
					security: WCS3_AJAX_OBJECT.ajax_nonce,
				};
			
			// Confirm delete operation.
			confirm = window.confirm(WCS3_AJAX_OBJECT.import_warning);
			if (!confirm) {
				return;
			}
			
			$('#wcs3-import-update-wrapper .wcs3-ajax-loader').show();
			
			jQuery.post(WCS3_AJAX_OBJECT.ajax_url, entry, function(data) {
				schedule_item_message(data.response, data.result);
				
			}).fail(function(err) {
				// Failed
				console.error(err);				
			}).always(function() {	
				// Re-bind handlers
				$('#wcs3-import-update-wrapper .wcs3-ajax-loader').hide();
			});
		});
		
	}
	
})(jQuery);