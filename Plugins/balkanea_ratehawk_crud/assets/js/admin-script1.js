jQuery(document).ready(function($) {
    // Global variables
    var currentProcess = null;
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
        var countryCode = $('#country_code').val().toUpperCase();
        
        if (countryCode.length !== 2) {
            alert('Please enter a valid 2-letter country code');
            return false;
        }
        
        // Show modal
        showModal('Importing Country', 'Starting import process for ' + countryCode);
        
        // Check status periodically
        statusCheckInterval = setInterval(function() {
            $.ajax({
                url: ratehawk_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'ratehawk_check_status',
                    nonce: ratehawk_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var currentScript = null;
                        
                        // Find the script for this country
                        $.each(response.data, function(index, script) {
                            if (script.country === countryCode && (script.type === 'extract' || script.type === 'import')) {
                                currentScript = script;
                                return false;
                            }
                        });
                        
                        if (currentScript) {
                            if (currentScript.status === 'running') {
                                updateModalMessage(currentScript.type.charAt(0).toUpperCase() + currentScript.type.slice(1) + 
                                                  'ing data for ' + countryCode + '...');
                                addModalDetails('Process ID: ' + currentScript.pid);
                            } else if (currentScript.status === 'completed') {
                                updateModalMessage(currentScript.type.charAt(0).toUpperCase() + currentScript.type.slice(1) + 
                                                  ' completed successfully for ' + countryCode);
                                $('#modal-stop').hide();
                                $('#modal-close').show();
                                clearInterval(statusCheckInterval);
                            } else if (currentScript.status === 'stopped') {
                                updateModalMessage(currentScript.type.charAt(0).toUpperCase() + currentScript.type.slice(1) + 
                                                  ' was stopped for ' + countryCode);
                                $('#modal-stop').hide();
                                $('#modal-close').show();
                                clearInterval(statusCheckInterval);
                            } else if (currentScript.status === 'failed') {
                                updateModalMessage(currentScript.type.charAt(0).toUpperCase() + currentScript.type.slice(1) + 
                                                  ' failed for ' + countryCode);
                                $('#modal-stop').hide();
                                $('#modal-close').show();
                                clearInterval(statusCheckInterval);
                            }
                        }
                    }
                }
            });
        }, 2000);
        
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
        var countryCode = $('#country_code').val().toUpperCase();
        if (!countryCode) return;
        
        // Find the running process for this country
        $.ajax({
            url: ratehawk_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ratehawk_check_status',
                nonce: ratehawk_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    var processToStop = null;
                    
                    // Find the script for this country
                    $.each(response.data, function(index, script) {
                        if (script.country === countryCode && script.status === 'running') {
                            processToStop = script;
                            return false;
                        }
                    });
                    
                    if (processToStop) {
                        stopScript(processToStop.pid, processToStop.country, processToStop.type);
                    } else {
                        alert('No running process found for ' + countryCode);
                    }
                }
            }
        });
    });
    
    // Initialize on page load
    toggleCountryField();
    
    // Set up periodic status checking
    setInterval(ratehawkCheckStatus, 5000);
});

// Function to toggle country field visibility
function toggleCountryField() {
    const specificGroup = document.getElementById('specific_country_group');
    const specificRadio = document.querySelector('input[name="update_type"][value="specific"]');
    
    if (specificGroup && specificRadio) {
        if (specificRadio.checked) {
            specificGroup.style.display = 'block';
        } else {
            specificGroup.style.display = 'none';
        }
    }
}