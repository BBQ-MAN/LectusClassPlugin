/**
 * Wishlist functionality for Lectus Academy
 * Handles wishlist toggle, status updates, and UI interactions
 */

(function() {
    'use strict';
    
    // Check jQuery availability
    if (typeof jQuery === 'undefined') {
        console.error('[Wishlist] jQuery is required for wishlist functionality');
        return;
    }
    
    jQuery(document).ready(function($) {
        console.log('[Wishlist] Initializing wishlist handler');
        
        // Detect Edge browser
        var isEdge = navigator.userAgent.indexOf('Edg') > -1;
        if (isEdge) {
            console.log('[Wishlist] Edge browser detected - applying compatibility mode');
        }
        
        // Initialize wishlist buttons
        initWishlistButtons();
        
        // Handle wishlist button clicks
        $(document).on('click', '.wishlist-btn, .wishlist-toggle', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var $button = $(this);
            var courseId = $button.data('course-id');
            
            console.log('[Wishlist] Button clicked', {
                courseId: courseId,
                button: $button[0],
                lectusAcademy: typeof lectusAcademy !== 'undefined' ? lectusAcademy : 'undefined'
            });
            
            if (!courseId) {
                console.error('[Wishlist] No course ID found');
                showMessage('강의 ID를 찾을 수 없습니다.', 'error');
                return;
            }
            
            // Check if lectusAcademy object exists
            if (typeof lectusAcademy === 'undefined') {
                console.error('[Wishlist] lectusAcademy object not found');
                showMessage('위시리스트 기능을 사용할 수 없습니다. 페이지를 새로고침 해주세요.', 'error');
                return;
            }
            
            toggleWishlist($button, courseId);
        });
        
        /**
         * Initialize wishlist button states
         */
        function initWishlistButtons() {
            $('.wishlist-btn, .wishlist-toggle').each(function() {
                var $button = $(this);
                var courseId = $button.data('course-id');
                
                if (courseId && typeof lectusAcademy !== 'undefined') {
                    updateWishlistStatus($button, courseId);
                }
            });
        }
        
        /**
         * Toggle wishlist status
         */
        function toggleWishlist($button, courseId) {
            // Check if user is logged in (show login modal if not)
            if (!$('body').hasClass('logged-in') && !isUserLoggedIn()) {
                showLoginMessage();
                return;
            }
            
            // Prevent double clicks
            if ($button.prop('disabled') || $button.hasClass('loading')) {
                console.log('[Wishlist] Button already processing, ignoring click');
                return;
            }
            
            // Disable button and show loading state
            var $icon = $button.find('i');
            var $text = $button.find('.btn-text');
            
            // Save original state before any changes
            var originalIcon = $icon.attr('class') || 'far fa-heart';
            var originalText = $text.text() || '위시리스트';
            var wasInWishlist = $button.hasClass('in-wishlist');
            
            // Apply loading state
            $button.prop('disabled', true).addClass('loading');
            
            // Edge browser detection
            var isEdge = navigator.userAgent.indexOf('Edg') > -1;
            
            // Complete icon reset and apply spinner
            $icon.attr('class', 'fas fa-spinner fa-spin');
            
            if ($text.length) {
                $text.text('처리중...');
            }
            
            // Send AJAX request
            console.log('[Wishlist] Sending AJAX request', {
                url: lectusAcademy.ajaxurl,
                data: {
                    action: 'lectus_toggle_wishlist',
                    course_id: courseId,
                    nonce: lectusAcademy.nonce
                }
            });
            
            $.ajax({
                url: lectusAcademy.ajaxurl,
                type: 'POST',
                data: {
                    action: 'lectus_toggle_wishlist',
                    course_id: courseId,
                    nonce: lectusAcademy.nonce
                },
                success: function(response) {
                    console.log('[Wishlist] AJAX Success Response:', response);
                    
                    // Process response
                    var processResponse = function() {
                        // Always restore button state first
                        $button.prop('disabled', false).removeClass('loading');
                        
                        if (response && response.success) {
                            var data = response.data || {};
                            
                            // Update button state - this will also handle icon restoration
                            if (data.action) {
                                updateButtonState($button, data.action === 'added');
                            } else {
                                // Fallback - determine state based on previous state
                                var shouldBeInWishlist = !wasInWishlist;
                                updateButtonState($button, shouldBeInWishlist);
                            }
                            
                            // Show success message
                            if (data.message) {
                                showMessage(data.message, 'success');
                            }
                            
                            // Update wishlist counts
                            if (data.count !== undefined || data.course_count !== undefined) {
                                updateWishlistCounts(data.count, data.course_count, courseId);
                            }
                            
                            // Trigger custom event
                            $(document).trigger('wishlist-changed', {
                                courseId: courseId,
                                action: data.action || 'unknown',
                                userCount: data.count || 0,
                                courseCount: data.course_count || 0
                            });
                            
                        } else {
                            // Handle errors
                            var errorData = response && response.data ? response.data : {};
                            
                            if (errorData.require_login) {
                                showLoginMessage();
                            } else {
                                showMessage(errorData.message || '오류가 발생했습니다.', 'error');
                            }
                            
                            // Restore to previous state
                            updateButtonState($button, wasInWishlist);
                        }
                    };
                    
                    // Edge browser - add extra delay
                    if (isEdge) {
                        setTimeout(processResponse, 100);
                    } else {
                        setTimeout(processResponse, 0);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('[Wishlist] AJAX Error:', {
                        xhr: xhr,
                        status: status,
                        error: error,
                        responseText: xhr.responseText
                    });
                    
                    var processError = function() {
                        // Always restore button state on error
                        $button.prop('disabled', false).removeClass('loading');
                        
                        // Restore to previous state
                        updateButtonState($button, wasInWishlist);
                        
                        var errorMessage = '서버 연결 중 오류가 발생했습니다.';
                        if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                            errorMessage = xhr.responseJSON.data.message;
                        } else if (xhr.responseText) {
                            errorMessage = '서버 오류: ' + xhr.status;
                        }
                        
                        showMessage(errorMessage, 'error');
                    };
                    
                    // Edge browser - add extra delay
                    if (isEdge) {
                        setTimeout(processError, 100);
                    } else {
                        setTimeout(processError, 0);
                    }
                },
                complete: function() {
                    // Safety cleanup - ensure button is never stuck in loading state
                    var safetyCheck = function() {
                        if ($button.prop('disabled') || $button.hasClass('loading')) {
                            console.log('[Wishlist] Safety check: Force restoring button state');
                            $button.prop('disabled', false).removeClass('loading');
                            
                            // Force icon restoration
                            var $currentIcon = $button.find('i');
                            if ($currentIcon.hasClass('fa-spinner') || $currentIcon.hasClass('fa-spin')) {
                                console.log('[Wishlist] Force removing spinner classes');
                                // Complete class reset
                                $currentIcon.attr('class', '');
                                
                                // Determine correct icon based on button state
                                if ($button.hasClass('in-wishlist')) {
                                    $currentIcon.attr('class', 'fas fa-heart');
                                } else {
                                    $currentIcon.attr('class', 'far fa-heart');
                                }
                            }
                        }
                    };
                    
                    // Edge browser - longer delay
                    if (isEdge) {
                        setTimeout(safetyCheck, 200);
                    } else {
                        setTimeout(safetyCheck, 100);
                    }
                }
            });
        }
        
        /**
         * Update wishlist status for a button
         */
        function updateWishlistStatus($button, courseId) {
            $.ajax({
                url: lectusAcademy.ajaxurl,
                type: 'POST',
                data: {
                    action: 'lectus_get_wishlist_status',
                    course_id: courseId,
                    nonce: lectusAcademy.nonce
                },
                success: function(response) {
                    if (response.success) {
                        updateButtonState($button, response.data.in_wishlist);
                        
                        // Update course wishlist count if element exists
                        var $countElement = $('.course-wishlist-count[data-course-id="' + courseId + '"]');
                        if ($countElement.length && response.data.count !== undefined) {
                            $countElement.text(response.data.count);
                        }
                    }
                },
                error: function() {
                    console.log('[Wishlist] Failed to get status for course:', courseId);
                }
            });
        }
        
        /**
         * Update button visual state
         */
        function updateButtonState($button, isInWishlist) {
            var $icon = $button.find('i');
            var $text = $button.find('.btn-text');
            
            console.log('[Wishlist] updateButtonState called', {
                isInWishlist: isInWishlist,
                iconClasses: $icon.attr('class'),
                buttonClasses: $button.attr('class')
            });
            
            // Edge browser detection
            var isEdge = navigator.userAgent.indexOf('Edg') > -1;
            
            var updateIcon = function() {
                // Force complete class reset
                $icon.attr('class', '');
                
                if (isInWishlist) {
                    // In wishlist - show filled heart
                    $button.addClass('active in-wishlist');
                    $icon.attr('class', 'fas fa-heart');
                    if ($text.length) {
                        $text.text('위시리스트에서 제거');
                    }
                    $button.attr('title', '위시리스트에서 제거');
                } else {
                    // Not in wishlist - show empty heart
                    $button.removeClass('active in-wishlist');
                    $icon.attr('class', 'far fa-heart');
                    if ($text.length) {
                        $text.text('위시리스트에 추가');
                    }
                    $button.attr('title', '위시리스트에 추가');
                }
                
                console.log('[Wishlist] Icon updated', {
                    newClasses: $icon.attr('class'),
                    isEdge: isEdge
                });
            };
            
            if (isEdge) {
                // Edge browser - use double RAF for guaranteed update
                requestAnimationFrame(function() {
                    requestAnimationFrame(updateIcon);
                });
            } else {
                updateIcon();
            }
        }
        
        /**
         * Update wishlist counts in UI
         */
        function updateWishlistCounts(userCount, courseCount, courseId) {
            // Update user's wishlist count (in header, sidebar, etc.)
            $('.user-wishlist-count').text(userCount || 0);
            
            // Update specific course's wishlist count
            var $courseCountElements = $('.course-wishlist-count[data-course-id="' + courseId + '"]');
            if ($courseCountElements.length && courseCount !== undefined) {
                $courseCountElements.text(courseCount);
            }
        }
        
        /**
         * Show message to user
         */
        function showMessage(message, type) {
            type = type || 'info';
            
            // Try to use existing notification system first
            if (typeof showNotification === 'function') {
                showNotification(message, type);
                return;
            }
            
            // Fallback to alert for now (can be enhanced with toast notifications)
            alert(message);
        }
        
        /**
         * Show login message
         */
        function showLoginMessage() {
            var message = '위시리스트 기능을 사용하려면 로그인이 필요합니다.';
            
            // If there's a login modal, show it
            if ($('#loginModal').length) {
                $('#loginModal').modal('show');
                return;
            }
            
            // Otherwise redirect to login page
            if (confirm(message + '\n로그인 페이지로 이동하시겠습니까?')) {
                var loginUrl = lectusAcademy.loginUrl || '/wp-login.php';
                var redirectUrl = encodeURIComponent(window.location.href);
                window.location.href = loginUrl + '?redirect_to=' + redirectUrl;
            }
        }
        
        /**
         * Check if user is logged in
         */
        function isUserLoggedIn() {
            // Check various indicators
            return $('body').hasClass('logged-in') || 
                   $('.logged-in').length > 0 || 
                   $('#wpadminbar').length > 0;
        }
        
        /**
         * Handle wishlist page specific functionality
         */
        if ($('body').hasClass('page-template-page-wishlist') || $('body').hasClass('wishlist-page')) {
            // Handle bulk remove
            $(document).on('click', '.bulk-remove-wishlist', function(e) {
                e.preventDefault();
                
                var selectedItems = $('.wishlist-item input[type="checkbox"]:checked');
                if (selectedItems.length === 0) {
                    showMessage('제거할 항목을 선택해주세요.', 'warning');
                    return;
                }
                
                if (!confirm(selectedItems.length + '개의 항목을 위시리스트에서 제거하시겠습니까?')) {
                    return;
                }
                
                // Remove each selected item
                selectedItems.each(function() {
                    var $checkbox = $(this);
                    var courseId = $checkbox.val();
                    var $button = $('.wishlist-btn[data-course-id="' + courseId + '"]');
                    
                    if ($button.length) {
                        toggleWishlist($button, courseId);
                    }
                });
            });
            
            // Handle select all
            $(document).on('change', '#select-all-wishlist', function() {
                $('.wishlist-item input[type="checkbox"]').prop('checked', this.checked);
            });
        }
        
        // Listen for enrollment status changes to update wishlist UI
        $(document).on('enrollment-status-changed', function(e, data) {
            console.log('[Wishlist] Enrollment status changed:', data);
            
            // If user enrolled in a course, you might want to update wishlist UI
            // For example, show different button states or messaging
        });
        
        console.log('[Wishlist] Initialization complete');
    });
    
})();