$('.tags-list-item').on('click', function() {
	var id = $(this).data('tagId');
	$('.tags-list-item.active').removeClass('active');
	$(this).addClass('active');
	$('.tags-info.active').removeClass('active');
	$('.tags-info[data-tag-id=' + id + ']').addClass('active');
	$('.update-information').empty();
});

$('.activate-tag').on('click', function() {
	var id = $(this).data('tagId');
	$.ajax({
		type: 'POST',
		data: {
			checkout_tag: id
		},
		success : function(data) {
			var message;
			if (data.indexOf('HEAD is now at') !== -1) {
				message = 'Successfully updated this link with the selected version.';
			} else {
				message = 'There was an issue updating this link. Please contact White Lion.';
			}
			$('.tags-info[data-tag-id="' + id + '"]' + ' .update-information').html(message);
		}
	})
});