jQuery(document).ready(function($) {
    var statusCheckInterval = null;

    // Check script status
    function ratehawkCheckStatus() {
        $.post(ratehawk_ajax.ajax_url, {
            action: 'ratehawk_check_status',
            nonce: ratehawk_ajax.nonce
        }, function(response) {
            if(response.success) {
                updateStatusList(response.data);
            }
        });
    }

    // Update status list
   /* function updateStatusList(statuses) {
        var statusList = $('#script-status-list');
        statusList.empty();

        if (!statuses || statuses.length === 0) {
            statusList.append('<div class="status-item"><span class="status-text">No active scripts</span></div>');
            return;
        }

       $.each(statuses, function(index, status) {
    var statusClass = '';
    switch(status.status) {
        case 'waiting': statusClass = 'status-waiting'; break;
        case 'running': statusClass = 'status-running'; break;
        case 'completed': statusClass = 'status-completed'; break;
        case 'stopped': statusClass = 'status-stopped'; break;
        default: statusClass = 'status-failed';
    }

    var statusItem = $('<div class="status-item">');
    statusItem.append('<span class="status-type">' + (status.type ? status.type.toUpperCase() : 'UNKNOWN') + '</span>');
    statusItem.append('<span class="status-country">' + (status.country || '-') + '</span>');
    statusItem.append('<span class="status ' + statusClass + '">' + status.status + '</span>');

    // Stop button only if running AND key exists
    if (status.status === 'running' && status.key) {
        var stopButton = $('<button class="button button-small button-secondary">Stop</button>');
        stopButton.click(function() {
            stopScriptByKey(status.key);
        });
        statusItem.append(stopButton);
    }

    statusList.append(statusItem);
});
    }*/
    
    function updateStatusList(statuses) {
    var statusList = $('#script-status-list');
    statusList.empty();

    if (!statuses || statuses.length === 0) {
        statusList.append('<div class="status-item"><span class="status-text">No active scripts</span></div>');
        return;
    }

    $.each(statuses, function(index, status) {
        var statusClass = '';
        switch(status.status) {
            case 'waiting': statusClass = 'status-waiting'; break;
            case 'running': statusClass = 'status-running'; break;
            case 'completed': statusClass = 'status-completed'; break;
            case 'stopped': statusClass = 'status-stopped'; break;
            default: statusClass = 'status-failed';
        }

        var countries = status.country_arg || '-';

        var statusItem = $('<div class="status-item">');
        statusItem.append('<span class="status-type">' + (status.type ? status.type.toUpperCase() : 'UNKNOWN') + '</span>');
        statusItem.append('<span class="status-country">' + countries + '</span>');
        statusItem.append('<span class="status ' + statusClass + '">' + status.status + '</span>');

        if (status.status === 'running' && status.key) {
            var stopButton = $('<button class="button button-small button-secondary">Stop</button>');
            stopButton.click(function() { stopScriptByKey(status.key); });
            statusItem.append(stopButton);
        }

        statusList.append(statusItem);
    });
}
    
    
    
    
    

    // Stop script by key
    function stopScriptByKey(key) {
        $.post(ratehawk_ajax.ajax_url, {
            action: 'ratehawk_stop_script',
            nonce: ratehawk_ajax.nonce,
            key: key
        }, function(response) {
            if(response.success) {
                alert('Script stopped successfully');
                ratehawkCheckStatus();
            } else {
                alert(response.data.message);
            }
        });
    }

    // Clear all statuses
    $('#clear-statuses').on('click', function() {
        if (!confirm('Are you sure you want to clear all statuses?')) return;

        $.post(ratehawk_ajax.ajax_url, {
            action: 'ratehawk_clear_statuses',
            nonce: ratehawk_ajax.nonce
        }, function(response) {
            if(response.success){
                $('#script-status-list').html('<div class="status-item"><span class="status-text">Cleared.</span></div>');
            } else {
                alert(response.data.message);
            }
        });
    });

    // Modal stop button
    $('#modal-stop').click(function() {
        var updateType = $('input[name="update_type"]:checked').val();
        var codes = [];

        if (updateType === 'specific_country') {
            codes = $('#country_codes').val().toUpperCase().split(',').map(c => c.trim());
        }

        $.post(ratehawk_ajax.ajax_url, {
            action: 'ratehawk_check_status',
            nonce: ratehawk_ajax.nonce
        }, function(response) {
            if(response.success) {
                $.each(response.data, function(index, script) {
                    if ((updateType === 'all_countries' || codes.includes(script.country)) && script.status === 'running') {
                        stopScriptByKey(script.key);
                    }
                });
            }
        });
    });

    // Refresh button
    $('#refresh-status').click(ratehawkCheckStatus);

    // Auto check every 5 sec
    setInterval(ratehawkCheckStatus, 5000);

    // Modal helper functions
    function showModal(title, message) {
        $('#modal-title').text(title);
        $('#modal-message').text(message);
        $('#modal-details').empty();
        $('#modal-stop').show();
        $('#modal-close').hide();
        $('#ratehawk-modal').fadeIn();
    }

    function hideModal() { $('#ratehawk-modal').fadeOut(); }

    // Toggle specific country input
    function toggleCountryField() {
        const specificGroup = $('#specific_country_group');
        const specificRadio = $('input[name="update_type"][value="specific_country"]');
        specificGroup.toggle(specificRadio.is(':checked'));
    }
    $('input[name="update_type"]').change(toggleCountryField);
    toggleCountryField();

    // Form submit
    $('#import-form').on('submit', function(e) {
        let updateType = $('input[name="update_type"]:checked').val();

        if (updateType === 'specific_country') {
            let codes = $('#country_codes').val().toUpperCase().split(',')
                           .map(c => c.trim()).filter(c => c.length === 2);
            if (codes.length === 0) {
                alert('Please enter at least one valid 2-letter country code');
                return false;
            }
            showModal('Importing Countries', 'Starting import process for: ' + codes.join(', '));
        } else {
            showModal('Importing All', 'Starting import process for all countries from Balkanea settings');
        }

        statusCheckInterval = setInterval(ratehawkCheckStatus, 2000);
        return true;
    });
});
