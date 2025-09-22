// Calendar Override Logic for Traveler Theme
(function($) {
    'use strict';
    
    // Wait for DOM to be ready
    $(document).ready(function() {
        initCalendarOverrides();
    });
    
    function initCalendarOverrides() {
        // Common selectors for Traveler theme date pickers
        const startDateSelectors = [
            'input[name="checkin"]',
            'input[name="start_date"]', 
            '.st-checkin',
            '#checkin',
            '.booking-item-checkin input'
        ];
        
        const endDateSelectors = [
            'input[name="checkout"]',
            'input[name="end_date"]',
            '.st-checkout', 
            '#checkout',
            '.booking-item-checkout input'
        ];
        
        // Initialize for each possible selector combination
        startDateSelectors.forEach(startSelector => {
            endDateSelectors.forEach(endSelector => {
                const $startDate = $(startSelector);
                const $endDate = $(endSelector);
                
                if ($startDate.length && $endDate.length) {
                    setupCalendarLogic($startDate, $endDate);
                }
            });
        });
        
        // Handle dynamically loaded content (AJAX)
        $(document).on('DOMNodeInserted', function(e) {
            if ($(e.target).find('.datepicker, .calendar').length) {
                setTimeout(() => initCalendarOverrides(), 100);
            }
        });
        
        // Modern approach for dynamic content
        if (window.MutationObserver) {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length) {
                        $(mutation.addedNodes).find('.datepicker, .calendar').each(function() {
                            setTimeout(() => initCalendarOverrides(), 100);
                        });
                    }
                });
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
    }
    
    function setupCalendarLogic($startDate, $endDate) {
        // Prevent duplicate initialization
        if ($startDate.data('calendar-override-init')) return;
        $startDate.data('calendar-override-init', true);
        
        // Store original datepicker options
        const originalStartOptions = $startDate.data('datepicker') || {};
        const originalEndOptions = $endDate.data('datepicker') || {};
        
        // Override start date functionality
        $startDate.off('changeDate.calendarOverride').on('changeDate.calendarOverride', function(e) {
            const selectedDate = e.date || $(this).datepicker('getDate');
            if (!selectedDate) return;
            
            handleStartDateSelection(selectedDate, $startDate, $endDate);
        });
        
        // Override end date functionality  
        $endDate.off('changeDate.calendarOverride').on('changeDate.calendarOverride', function(e) {
            const selectedDate = e.date || $(this).datepicker('getDate');
            const startDate = $startDate.datepicker('getDate');
            
            if (selectedDate && startDate) {
                handleEndDateSelection(selectedDate, startDate, $startDate, $endDate);
            }
        });
        
        // Handle manual input changes
        $startDate.off('change.calendarOverride').on('change.calendarOverride', function() {
            const dateValue = $(this).val();
            if (dateValue) {
                const parsedDate = new Date(dateValue);
                if (!isNaN(parsedDate.getTime())) {
                    handleStartDateSelection(parsedDate, $startDate, $endDate);
                }
            }
        });
        
        $endDate.off('change.calendarOverride').on('change.calendarOverride', function() {
            const dateValue = $(this).val();
            const startDateValue = $startDate.val();
            
            if (dateValue && startDateValue) {
                const endDate = new Date(dateValue);
                const startDate = new Date(startDateValue);
                
                if (!isNaN(endDate.getTime()) && !isNaN(startDate.getTime())) {
                    handleEndDateSelection(endDate, startDate, $startDate, $endDate);
                }
            }
        });
        
        // Update datepicker options to disable same date selection
        updateDatepickerOptions($startDate, $endDate);
    }
    
    function handleStartDateSelection(selectedDate, $startDate, $endDate) {
        const nextDay = new Date(selectedDate);
        nextDay.setDate(nextDay.getDate() + 1);
        
        // Auto-fill next day as end date if end date is empty or same as start
        const currentEndDate = $endDate.datepicker('getDate');
        const currentEndValue = $endDate.val();
        
        if (!currentEndValue || 
            !currentEndDate || 
            currentEndDate.getTime() === selectedDate.getTime() ||
            currentEndDate.getTime() <= selectedDate.getTime()) {
            
            // Set next day as end date
            $endDate.datepicker('setDate', nextDay);
            $endDate.val(formatDate(nextDay));
            
            // Trigger change event for any additional theme logic
            $endDate.trigger('change');
        }
        
        // Update end date picker to disable dates before selected start date
        $endDate.datepicker('option', 'minDate', nextDay);
        
        // Update start date picker to disable dates after selected end date
        const endDate = $endDate.datepicker('getDate');
        if (endDate) {
            const maxStartDate = new Date(endDate);
            maxStartDate.setDate(maxStartDate.getDate() - 1);
            $startDate.datepicker('option', 'maxDate', maxStartDate);
        }
    }
    
    function handleEndDateSelection(selectedEndDate, startDate, $startDate, $endDate) {
        // Prevent selecting same date as start date
        if (selectedEndDate.getTime() === startDate.getTime()) {
            const nextDay = new Date(startDate);
            nextDay.setDate(nextDay.getDate() + 1);
            
            $endDate.datepicker('setDate', nextDay);
            $endDate.val(formatDate(nextDay));
            
            // Show user-friendly message
            showDateMessage('End date cannot be the same as start date. Auto-selected next day.');
            return;
        }
        
        // Prevent selecting date before start date
        if (selectedEndDate.getTime() < startDate.getTime()) {
            const nextDay = new Date(startDate);
            nextDay.setDate(nextDay.getDate() + 1);
            
            $endDate.datepicker('setDate', nextDay);
            $endDate.val(formatDate(nextDay));
            
            showDateMessage('End date cannot be before start date. Auto-selected next day.');
            return;
        }
        
        // Update start date picker max date
        const maxStartDate = new Date(selectedEndDate);
        maxStartDate.setDate(maxStartDate.getDate() - 1);
        $startDate.datepicker('option', 'maxDate', maxStartDate);
    }
    
    function updateDatepickerOptions($startDate, $endDate) {
        // Enhanced options for both desktop and mobile
        const commonOptions = {
            beforeShowDay: function(date) {
                return [true, '', '']; // Can be customized for specific date restrictions
            },
            onSelect: function(dateText, inst) {
                // Additional logic can be added here
            }
        };
        
        // Apply to start date picker
        if ($startDate.datepicker) {
            $startDate.datepicker('option', commonOptions);
        }
        
        // Apply to end date picker  
        if ($endDate.datepicker) {
            $endDate.datepicker('option', commonOptions);
        }
        
        // Handle mobile-specific enhancements
        if (isMobileDevice()) {
            $startDate.attr('readonly', 'readonly');
            $endDate.attr('readonly', 'readonly');
        }
    }
    
    function formatDate(date) {
        // Format date according to site settings or theme format
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        
        // Common formats - adjust based on your theme's date format
        return `${month}/${day}/${year}`; // MM/DD/YYYY
        // return `${day}/${month}/${year}`; // DD/MM/YYYY
        // return `${year}-${month}-${day}`; // YYYY-MM-DD
    }
    
    function showDateMessage(message) {
        // Create or update notification
        let $notification = $('.calendar-override-message');
        
        if (!$notification.length) {
            $notification = $('<div class="calendar-override-message" style="' +
                'position: fixed; top: 20px; right: 20px; background: #f0ad4e; ' +
                'color: white; padding: 10px 15px; border-radius: 4px; ' +
                'z-index: 9999; font-size: 14px; max-width: 300px;">' +
                '</div>');
            $('body').append($notification);
        }
        
        $notification.text(message).fadeIn();
        
        // Auto-hide after 3 seconds
        setTimeout(() => {
            $notification.fadeOut();
        }, 3000);
    }
    
    function isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
               window.innerWidth <= 768;
    }
    
    // Handle theme-specific calendar formats
    function handleTravelerThemeSpecifics() {
        // Override for Traveler theme's specific calendar implementations
        
        // Handle flatpickr if used
        if (window.flatpickr) {
            $('.flatpickr-input').each(function() {
                const fp = this._flatpickr;
                if (fp) {
                    fp.config.onChange.push(function(selectedDates, dateStr, instance) {
                        // Apply same logic for flatpickr
                        if (instance.element.name === 'checkin' || instance.element.classList.contains('st-checkin')) {
                            // Handle start date selection
                        }
                    });
                }
            });
        }
        
        // Handle any custom Traveler theme calendar widgets
        $('.st-date-range, .booking-date-range').each(function() {
            const $container = $(this);
            const $start = $container.find('input').first();
            const $end = $container.find('input').last();
            
            if ($start.length && $end.length) {
                setupCalendarLogic($start, $end);
            }
        });
    }
    
    // Initialize theme-specific handling
    $(document).ready(function() {
        setTimeout(handleTravelerThemeSpecifics, 500);
    });
    
})(jQuery);
