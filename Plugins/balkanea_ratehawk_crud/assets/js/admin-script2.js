jQuery(document).ready(function($) {
    var statusCheckInterval = null;

    // Check script status
    window.ratehawkCheckStatus = function() {
        $.ajax({
            url: ratehawk_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ratehawk_check_status',
                nonce: ratehawk_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateStatusList(response.data);
                }
            }
        });
    };

    // Update status list
    function updateStatusList(statuses) {
        var statusList = $('#script-status-list');
        statusList.empty();

        if (statuses.length === 0) {
            statusList.append('<div class="status-item"><span class="status-text">No active scripts</span></div>');
            return;
        }

        $.each(statuses, function(index, status) {
            var statusClass = status.status === 'running' ? 'status-running' :
                              status.status === 'completed' ? 'status-completed' :
                              status.status === 'stopped' ? 'status-stopped' : 'status-failed';

            var statusItem = $('<div class="status-item">');
            statusItem.append('<span class="status-type">' + status.type.toUpperCase() + '</span>');
            statusItem.append('<span class="status-country">' + status.country + '</span>');
            statusItem.append('<span class="status ' + statusClass + '">' + status.status + '</span>');

            if (status.status === 'running') {
                var stopButton = $('<button class="button button-small button-secondary">Stop</button>');
                stopButton.click(function() {
                    stopScript(status.pid, status.country, status.type);
                });
                statusItem.append(stopButton);
            }

            statusList.append(statusItem);
        });
    }

    // Stop a script
    function stopScript(pid, country, type) {
        $.ajax({
            url: ratehawk_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ratehawk_stop_script',
                nonce: ratehawk_ajax.nonce,
                pid: pid,
                country: country,
                type: type
            },
            success: function(response) {
                if (response.success) {
                    alert('Script stopped successfully');
                    ratehawkCheckStatus();
                } else {
                    alert('Failed to stop script');
                }
            }
        });
    }
    
    $('.stop-script-btn').on('click', function(){
    var key = $(this).data('key'); // e.g., update_import_GR
    $.post(ratehawk_ajax.ajax_url, {
        action: 'ratehawk_stop_script',
        nonce: ratehawk_ajax.nonce,
        key: key
    }, function(response){
        if(response.success){
            alert('Stopped successfully');
        } else {
            alert(response.data.message);
        }
    });
});
     $('#clear-statuses').on('click', function(){
        if(!confirm('Are you sure you want to clear all statuses?')) return;

        $.post(ratehawk_ajax.ajax_url, {
            action: 'ratehawk_clear_statuses',
            nonce: ratehawk_ajax.nonce
        }, function(response){
            if(response.success){
                $('#script-status-list').html('<div class="status-item"><span class="status-text">Cleared.</span></div>');
            } else {
                alert(response.data.message);
            }
        });
    });

    // Show modal
    function showModal(title, message) {
        $('#modal-title').text(title);
        $('#modal-message').text(message);
        $('#modal-details').empty();
        $('#modal-stop').show();
        $('#modal-close').hide();
        $('#ratehawk-modal').fadeIn();
    }

    // Hide modal
    function hideModal() {
        $('#ratehawk-modal').fadeOut();
    }

    // Update modal message
    function updateModalMessage(message) {
        $('#modal-message').text(message);
    }

    // Add modal details
    function addModalDetails(details) {
        $('#modal-details').append('<p>' + details + '</p>');
    }

    // Form submission handling
    $('#import-form').on('submit', function(e) {
        let updateType = $('input[name="update_type"]:checked').val();

        // Ако е specific_country → земи од textarea
        if (updateType === 'specific_country') {
            let codes = $('#country_codes').val().toUpperCase().split(',');
            codes = codes.map(c => c.trim()).filter(c => c.length === 2);

            if (codes.length === 0) {
                alert('Please enter at least one valid 2-letter country code');
                return false;
            }

            // Прикажи кои држави ќе се процесираат
            showModal('Importing Countries', 'Starting import process for: ' + codes.join(', '));
        } else {
            showModal('Importing All', 'Starting import process for all countries from Balkanea settings');
        }

        // Стартувај периодична проверка
        statusCheckInterval = setInterval(ratehawkCheckStatus, 2000);

        return true;
    });

    // Refresh status button
    $('#refresh-status').click(function() {
        ratehawkCheckStatus();
    });

    // Modal close button
    $('#modal-close').click(function() {
        hideModal();
        ratehawkCheckStatus();
    });

    // Modal stop button
    $('#modal-stop').click(function() {
        let updateType = $('input[name="update_type"]:checked').val();
        let codes = [];

        if (updateType === 'specific_country') {
            codes = $('#country_codes').val().toUpperCase().split(',').map(c => c.trim());
        }

        $.ajax({
            url: ratehawk_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ratehawk_check_status',
                nonce: ratehawk_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $.each(response.data, function(index, script) {
                        if ((updateType === 'all_countries' || codes.includes(script.country)) && script.status === 'running') {
                            stopScript(script.pid, script.country, script.type);
                        }
                    });
                }
            }
        });
    });

    // Toggle input fields
    function toggleCountryField() {
        const specificGroup = $('#specific_country_group');
        const specificRadio = $('input[name="update_type"][value="specific_country"]');

        if (specificRadio.is(':checked')) {
            specificGroup.show();
        } else {
            specificGroup.hide();
        }
    }

    $('input[name="update_type"]').change(toggleCountryField);
    toggleCountryField();

    // Автоматска проверка на статуси на секои 5 сек
    setInterval(ratehawkCheckStatus, 5000);
});
