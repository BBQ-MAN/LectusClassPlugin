/**
 * Course Enrollment Handler
 * Handles enrollment button clicks and AJAX requests
 */

(function() {
    'use strict';
    
    // Check jQuery availability
    if (typeof jQuery === 'undefined') {
        console.error('[Enrollment] jQuery is required for enrollment functionality');
        return;
    }
    
    jQuery(document).ready(function($) {
        console.log('[Enrollment] Initializing enrollment handler');
        
        // Handle enrollment button clicks
        $(document).on('click', '.enroll-btn', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var courseId = $button.data('course-id');
            
            if (!courseId) {
                console.error('[Enrollment] No course ID found');
                return;
            }
            
            // Check if lectusAcademy object exists
            if (typeof lectusAcademy === 'undefined') {
                console.error('[Enrollment] lectusAcademy object not found');
                alert('수강 신청 기능을 사용할 수 없습니다. 페이지를 새로고침 해주세요.');
                return;
            }
            
            // Disable button and show loading state
            $button.prop('disabled', true);
            var originalText = $button.html();
            $button.html('<i class="fas fa-spinner fa-spin"></i> 처리 중...');
            
            // Send AJAX request
            $.ajax({
                url: lectusAcademy.ajaxurl,
                type: 'POST',
                data: {
                    action: 'lectus_academy_enroll',
                    course_id: courseId,
                    nonce: lectusAcademy.nonce
                },
                success: function(response) {
                    console.log('[Enrollment] Response:', response);
                    
                    if (response.success) {
                        // Show success message
                        alert(response.data.message || '수강 신청이 완료되었습니다.');
                        
                        // Reload page to show enrolled status
                        location.reload();
                    } else {
                        // Show error message
                        alert(response.data.message || '수강 신청 중 오류가 발생했습니다.');
                        
                        // Re-enable button
                        $button.prop('disabled', false);
                        $button.html(originalText);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('[Enrollment] AJAX Error:', error);
                    
                    // Show error message
                    alert('서버 연결 중 오류가 발생했습니다. 잠시 후 다시 시도해주세요.');
                    
                    // Re-enable button
                    $button.prop('disabled', false);
                    $button.html(originalText);
                }
            });
        });
        
        // Handle wishlist button clicks
        $(document).on('click', '[data-action="add-wishlist"]', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var courseId = $button.data('course-id');
            
            if (!courseId) {
                console.error('[Enrollment] No course ID found for wishlist');
                return;
            }
            
            // Toggle wishlist state
            if ($button.hasClass('active')) {
                $button.removeClass('active');
                $button.find('i').removeClass('fas').addClass('far');
            } else {
                $button.addClass('active');
                $button.find('i').removeClass('far').addClass('fas');
            }
            
            // Here you would normally send an AJAX request to save wishlist state
            console.log('[Enrollment] Wishlist toggled for course:', courseId);
        });
        
        // Listen for enrollment status changes (for other components)
        $(document).on('enrollment-status-changed', function(e, data) {
            console.log('[Enrollment] Status changed:', data);
            
            // Recalculate sticky card position if needed
            if (window.recalculateStickyCard) {
                setTimeout(window.recalculateStickyCard, 500);
            }
        });
    });
    
})();