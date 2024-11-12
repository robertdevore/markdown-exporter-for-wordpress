jQuery(document).ready(function ($) {
    $('#export-button').on('click', function () {
        // Serialize the form data.
        var formData = $('#markdown-exporter-form').serialize();

        // Show and reset the progress bar.
        $('#progress-bar-wrapper').show();
        $('#progress-bar-container').css('background-color', '#e0e0e0');
        $('#progress-bar').css({
            'width': '0%',
            'height': '20px',
            'background-color': '#4caf50',
            'transition': 'width 0.4s ease'
        });
        $('#progress-text').text('0%');

        // Show and reset the log area.
        $('#export-log-wrapper').show();
        $('#export-log').text('');

        // Disable the export button to prevent multiple clicks.
        $('#export-button').attr('disabled', true).text('Exporting...');

        // Prepare data for AJAX request.
        var data = {
            action: 'markdown_export',
            nonce: markdownExporter.nonce,
            form_data: formData // Include serialized form data.
        };

        // Send AJAX request.
        $.post(markdownExporter.ajax_url, data, function (response) {
            if (response.success) {
                // Update progress bar to 100%.
                $('#progress-bar').css('width', '100%');
                $('#progress-text').text('100%');

                // Display logs.
                response.data.logs.forEach(function (log) {
                    $('#export-log').append(log + '\n');
                });

                // Display statistics.
                var stats = response.data.stats;
                $('#export-log').append('\n');
                $('#export-log').append('---\n');
                $('#export-log').append('Total Exported: ' + stats.total_exported + '\n');
                $('#export-log').append('Time Taken: ' + stats.total_time + ' seconds\n');

                // Provide download link after a short delay.
                setTimeout(function () {
                    window.location.href = response.data.download;
                }, 1000);
            } else {
                alert('Export failed: ' + (response.data || 'An unknown error occurred.'));
                $('#progress-bar-wrapper').hide();
                $('#export-log-wrapper').hide();
            }

            // Re-enable the export button.
            $('#export-button').attr('disabled', false).text('Export');
        }).fail(function (xhr) {
            alert('Export failed: ' + xhr.responseText);
            $('#progress-bar-wrapper').hide();
            $('#export-log-wrapper').hide();
            $('#export-button').attr('disabled', false).text('Export');
        });
    });

    // Initialize Select2 for multi-select fields.
    $('select[multiple]').select2({
        width: '100%',
    });
});
