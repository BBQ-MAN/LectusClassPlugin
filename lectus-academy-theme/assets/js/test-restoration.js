/**
 * Test Restoration JavaScript
 * 
 * Handles AJAX requests for test data generation
 */

jQuery(document).ready(function($) {
    
    // Handle test data generation button click
    $('#generate-test-data').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $spinner = $('#test-data-spinner');
        var $result = $('#test-data-result');
        var nonce = $button.data('nonce');
        
        // Show confirmation dialog
        if (!confirm(lectusTestData.messages.confirm)) {
            return;
        }
        
        // Disable button and show spinner
        $button.prop('disabled', true);
        $spinner.show();
        $result.hide();
        
        // Update button text
        var originalText = $button.text();
        $button.text(lectusTestData.messages.generating);
        
        // Make AJAX request
        $.ajax({
            url: lectusTestData.ajax_url,
            type: 'POST',
            data: {
                action: 'generate_test_data',
                nonce: nonce
            },
            dataType: 'json',
            timeout: 60000, // 60 seconds timeout
            success: function(response) {
                if (response.success) {
                    showResult('success', response.data.message, response.data.results);
                } else {
                    showResult('error', response.data.message || lectusTestData.messages.error);
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = lectusTestData.messages.error;
                
                if (status === 'timeout') {
                    errorMessage += ' (시간 초과)';
                } else if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    errorMessage = xhr.responseJSON.data.message;
                } else {
                    errorMessage += ' (' + error + ')';
                }
                
                showResult('error', errorMessage);
            },
            complete: function() {
                // Re-enable button and hide spinner
                $button.prop('disabled', false);
                $button.text(originalText);
                $spinner.hide();
            }
        });
    });
    
    /**
     * Show result message
     */
    function showResult(type, message, results) {
        var $result = $('#test-data-result');
        var resultClass = type === 'success' ? 'notice-success' : 'notice-error';
        
        var html = '<div class="notice ' + resultClass + ' is-dismissible">';
        html += '<p>' + message + '</p>';
        
        // Show detailed results if available
        if (results && type === 'success') {
            html += '<div class="test-data-details">';
            html += '<h4>생성된 데이터 상세:</h4>';
            html += '<ul>';
            html += '<li>카테고리: ' + results.categories + '개</li>';
            html += '<li>WooCommerce 상품: ' + results.products + '개</li>';
            html += '<li>단과강의: ' + results.courses + '개</li>';
            html += '<li>레슨: ' + results.lessons + '개</li>';
            html += '<li>사용자: ' + results.users + '명</li>';
            html += '<li>수강신청: ' + results.enrollments + '건</li>';
            html += '</ul>';
            html += '<p><em>기본 비밀번호: testpass123</em></p>';
            html += '</div>';
        }
        
        html += '</div>';
        
        $result.html(html).show();
        
        // Auto-dismiss after 10 seconds for success messages
        if (type === 'success') {
            setTimeout(function() {
                $result.fadeOut();
            }, 10000);
        }
        
        // Handle dismiss button
        $result.find('.is-dismissible').on('click', '.notice-dismiss', function() {
            $result.fadeOut();
        });
        
        // Scroll to result
        $('html, body').animate({
            scrollTop: $result.offset().top - 100
        }, 500);
    }
    
    /**
     * Add progress indicator styles
     */
    if (!$('#test-restoration-styles').length) {
        $('<style id="test-restoration-styles">')
            .html(`
                .test-data-details {
                    margin-top: 15px;
                    padding: 15px;
                    background: #f9f9f9;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                }
                
                .test-data-details h4 {
                    margin: 0 0 10px 0;
                    color: #23282d;
                }
                
                .test-data-details ul {
                    margin: 0 0 10px 20px;
                }
                
                .test-data-details li {
                    margin-bottom: 5px;
                }
                
                .test-data-details em {
                    color: #666;
                    font-size: 0.9em;
                }
                
                #test-data-spinner {
                    margin-left: 10px;
                    float: none;
                    visibility: visible;
                }
                
                #generate-test-data:disabled {
                    opacity: 0.6;
                    cursor: not-allowed;
                }
                
                .notice.is-dismissible {
                    position: relative;
                    padding-right: 38px;
                }
                
                .notice.is-dismissible .notice-dismiss {
                    position: absolute;
                    top: 0;
                    right: 1px;
                    border: none;
                    margin: 0;
                    padding: 9px;
                    background: none;
                    color: #72777c;
                    cursor: pointer;
                }
                
                .notice.is-dismissible .notice-dismiss:before {
                    content: "\\f153";
                    font-family: dashicons;
                    font-size: 16px;
                }
                
                .notice.is-dismissible .notice-dismiss:hover {
                    color: #c00;
                }
            `)
            .appendTo('head');
    }
});