(function (api) {
	function showHideAMPurl(isExpanded){
		var url;

		// if it's expanded
		if (isExpanded) {

			// strip trailing slash from the current preview URL
			url = api.previewer.previewUrl.get().replace(/\/$/, "");

			// add /amp/ if necessary
			if (!url.endsWith('/amp/')) {
				url += '/amp/';
			}

			// navigate to the Amp version of the post
			console.log('Navigating the preview frame to ' + url);
			api.previewer.previewUrl.set(url);

		} else {

			// get the current preview URL
			url = api.previewer.previewUrl.get();

			// if it's an amp version, remove /amp/
			if (url.endsWith('/amp/')) {
				url = url.replace(/\/amp\/$/, "");
			}

			// navigate to the standard version of the post
			console.log('Navigating the preview frame to ' + url);
			api.previewer.previewUrl.set(url);

		}
	}


	// bind to the AMP sections
	api.section('wpseo_amp_design_settings', function (section) {
		// bind to when the section is expanded
		section.expanded.bind(function (isExpanded) {

			// show or hide the AMP version
			showHideAMPurl(isExpanded);

		});
	});

	api.section('wpseo_amp_advanced_settings', function (section) {
		// bind to when the section is expanded
		section.expanded.bind(function (isExpanded) {

			// show or hide the AMP version
			showHideAMPurl(isExpanded);

		});
	});
}(wp.customize) );