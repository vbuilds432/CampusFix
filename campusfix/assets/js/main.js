$(document).ready(function() {
    // Auto-hide alert banners after 5 seconds
    setTimeout(function() {
        $('.alert-dismissible').fadeOut('slow');
    }, 5000);

    // Client-side validation for file uploads (max 2MB, allowed extensions)
    $('input[type="file"]').on('change', function() {
        let file = this.files[0];
        if (file) {
            let ext = file.name.split('.').pop().toLowerCase();
            let allowed = ['jpg', 'jpeg', 'png', 'pdf'];
            if (allowed.indexOf(ext) === -1) {
                alert('Invalid file format. Only JPG, PNG, and PDF files are permitted.');
                $(this).val('');
                return false;
            }
            if (file.size > 2 * 1024 * 1024) {
                alert('File size exceeds 2MB limit. Please choose a smaller file.');
                $(this).val('');
                return false;
            }
        }
    });
});
