$(document).ready(function() {
	$('#photo-upload').on('change', function(event) {
		const $fileInput = $(this);
		const $photoList = $('#photo-list');
		const $errorMessage = $('#error-message');
		const files = event.target.files;
		const $upload = $('#photo-upload');
		
		$upload.removeClass('is-invalid');
		$photoList.empty();
		$errorMessage.empty();

		if (files.length > 5) {
			showError('Możesz wybrać maksymalnie 5 zdjęć.');
			$fileInput.val(''); 
			return;
		}

		$.each(files, function(i, file) {
			const fileType = file.type;
			const validTypes = ['image/jpeg', 'image/png', 'image/gif'];

			if (!validTypes.includes(fileType)) {
				showError('Nieprawidłowy typ pliku: ' + file.name);
				$fileInput.val(''); 
				return false;
			}

			const maxSize = 3 * 1024 * 1024;
			if (file.size > maxSize) {
				showError('Plik ' + file.name + ' przekracza maksymalny rozmiar 3MB.');
				$fileInput.val(''); 
				return false;
			}

			const $listItem = $('<li>').addClass('list-group-item d-flex align-items-center');

			const $icon = $('<i>').addClass('bi bi-camera-fill')
								  .css({'color': '#070D1F', 'margin-right': '10px'});

			const $fileName = $('<span>').text(file.name);

			$listItem.append($icon).append($fileName);
			$photoList.append($listItem);
		});
	});

	function showError(message) {
		const $alertDiv = $('<div>').addClass('alert alert-danger')
									.attr('role', 'alert')
									.text(message);
		$('#error-message').append($alertDiv);
	}
});