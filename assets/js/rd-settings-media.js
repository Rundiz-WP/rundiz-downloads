/**
 * Media js is working with media fields page where there are upload fields and it is not media button that comes with editor.
 */


// on dom ready --------------------------------------------------------------------------------------------------------
(function($) {
	$('.upload-media-button').click(function(e) {
		e.preventDefault();

		target_input = $(this).data('input_target');

		var image = wp.media({
			// mutiple: true if you want to upload multiple files at once
			multiple: false
		}).open()
		.on('select', function(e) {
			// This will return the selected image from the Media Uploader, the result is an object
			var uploaded_image = image.state().get('selection').first();
			// We convert uploaded_image to a JSON object to make accessing it easier
			var media_json = uploaded_image.toJSON();
			console.log(media_json);
			// Let's assign the url value to the input field
			$('#preview-media-url-'+target_input).val(media_json.url);
			$('#media-id-'+target_input).val(media_json.id);
			$('#media-height-'+target_input).val(media_json.height);
			$('#media-width-'+target_input).val(media_json.width);
			$('#media-url-'+target_input).val(media_json.url);
			if (typeof(media_json.sizes) != 'undefined' && typeof(media_json.sizes.large) != 'undefined' && typeof(media_json.sizes.large.url) != 'undefined') {
				$('#media-large-'+target_input).val(media_json.sizes.large.url);
			}
			if (typeof(media_json.sizes) != 'undefined' && typeof(media_json.sizes.medium) != 'undefined' && typeof(media_json.sizes.medium.url) != 'undefined') {
				$('#media-medium-'+target_input).val(media_json.sizes.medium.url);
			}
			if (typeof(media_json.sizes) != 'undefined' && typeof(media_json.sizes.thumbnail) != 'undefined' && typeof(media_json.sizes.thumbnail.url) != 'undefined') {
				$('#media-thumbnail-'+target_input).val(media_json.sizes.thumbnail.url);
				$('.image-preview-'+target_input).html('<img src="'+media_json.sizes.thumbnail.url+'" alt="">');
			}
		});
	});

	$('.remove-media-button').click(function(e) {
		e.preventDefault();

		target_input = $(this).data('input_target');
		$('#preview-media-url-'+target_input).val('');
		$('#media-id-'+target_input).val('');
		$('#media-height-'+target_input).val('');
		$('#media-width-'+target_input).val('');
		$('#media-url-'+target_input).val('');
		$('#media-large-'+target_input).val('');
		$('#media-medium-'+target_input).val('');
		$('#media-thumbnail-'+target_input).val('');
		$('.image-preview-'+target_input).html('');
	});
})(jQuery);