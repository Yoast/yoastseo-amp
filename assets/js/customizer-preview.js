// if wp.customize exists
if ('undefined' !== typeof wp && wp.customize) {

	// wait for window load - no iframe ready event (yet)
	jQuery(window).load(function () {

		// loop through all the CSS selectors
		jQuery.each(wpseoCSSselectors, function (key, cssValues) {

			// bind each one to the correct option
			wp.customize('wpseo_amp[' + key + ']', function (value) {

				// update the CSS selector on value change
				value.bind(function (newval) {
						jQuery(cssValues.selector).css(cssValues.property, newval);
				});

			});

		});

	});

}