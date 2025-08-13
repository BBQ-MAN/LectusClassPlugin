/**
 * Frontend JavaScript for Lectus Class System
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Handle lesson completion
    $('.lectus-complete-lesson').on('click', function() {
        var button = $(this);
        var lessonId = button.data('lesson-id');
        
        button.prop('disabled', true);
        button.text('처리 중...');
        
        $.ajax({
            url: lectus_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'lectus_complete_lesson',
                nonce: lectus_ajax.nonce,
                lesson_id: lessonId
            },
            success: function(response) {
                if (response.success) {
                    button.replaceWith('<p class="lesson-completed">✓ ' + '완료됨' + '</p>');
                    
                    if (response.data.course_completed) {
                        // Show course completion message
                        var message = $('<div class="lectus-notification success">' +
                            '<p>축하합니다! 강의를 완료하셨습니다!</p>' +
                            '</div>');
                        
                        $('.lectus-lesson-content').prepend(message);
                        
                        if (response.data.certificate_generated) {
                            message.append('<p><a href="' + response.data.certificate_url + '" target="_blank" class="button">수료증 보기</a></p>');
                        }
                    }
                    
                    // Update progress bar if exists
                    if ($('.course-progress').length) {
                        location.reload(); // Simple reload to update progress
                    }
                } else {
                    alert(response.data.message);
                    button.prop('disabled', false);
                    button.text('레슨 완료하기');
                }
            },
            error: function() {
                alert('오류가 발생했습니다. 다시 시도해주세요.');
                button.prop('disabled', false);
                button.text('레슨 완료하기');
            }
        });
    });
    
    // Handle free enrollment
    $('.lectus-enroll-btn').on('click', function() {
        var button = $(this);
        var courseId = button.data('course-id');
        
        button.prop('disabled', true);
        button.text('처리 중...');
        
        $.ajax({
            url: lectus_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'lectus_free_enroll',
                nonce: lectus_ajax.nonce,
                course_id: courseId
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.redirect) {
                        window.location.href = response.data.redirect;
                    } else {
                        location.reload();
                    }
                } else {
                    alert(response.data.message);
                    button.prop('disabled', false);
                    button.text('수강 신청');
                }
            },
            error: function() {
                alert('오류가 발생했습니다. 다시 시도해주세요.');
                button.prop('disabled', false);
                button.text('수강 신청');
            }
        });
    });
    
    // Track video progress
    var videos = $('.lesson-video video, .lesson-video iframe');
    if (videos.length) {
        videos.each(function() {
            var video = this;
            var lessonId = $('.lectus-complete-lesson').data('lesson-id');
            var lastUpdate = 0;
            
            // For HTML5 video
            if (video.tagName === 'VIDEO') {
                video.addEventListener('timeupdate', function() {
                    var progress = Math.floor((video.currentTime / video.duration) * 100);
                    
                    // Update every 10%
                    if (progress - lastUpdate >= 10) {
                        lastUpdate = progress;
                        updateLessonProgress(lessonId, progress);
                    }
                });
                
                video.addEventListener('ended', function() {
                    updateLessonProgress(lessonId, 100);
                });
            }
            // For YouTube/Vimeo iframe - requires additional API implementation
        });
    }
    
    // Update lesson progress
    function updateLessonProgress(lessonId, progress) {
        $.ajax({
            url: lectus_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'lectus_update_lesson_progress',
                nonce: lectus_ajax.nonce,
                lesson_id: lessonId,
                progress: progress
            },
            success: function(response) {
                // Silently update progress
                console.log('Progress updated: ' + progress + '%');
            }
        });
    }
    
    // Smooth scroll for course navigation
    $('.lessons-list a').on('click', function(e) {
        if ($(this).attr('href').charAt(0) === '#') {
            e.preventDefault();
            var target = $($(this).attr('href'));
            if (target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 500);
            }
        }
    });
    
    // Course filter
    $('.lectus-course-filter select').on('change', function() {
        var category = $(this).val();
        if (category) {
            $('.lectus-course-card').hide();
            $('.lectus-course-card[data-category*="' + category + '"]').show();
        } else {
            $('.lectus-course-card').show();
        }
    });
    
    // Responsive tables
    function makeTablesResponsive() {
        if ($(window).width() < 768) {
            $('.my-courses-table, .certificates-table').each(function() {
                var table = $(this);
                if (!table.parent().hasClass('table-responsive')) {
                    table.wrap('<div class="table-responsive"></div>');
                }
            });
        }
    }
    
    makeTablesResponsive();
    $(window).resize(makeTablesResponsive);
    
    // Lesson navigation keyboard shortcuts
    $(document).on('keydown', function(e) {
        if ($('.lectus-lesson-content').length) {
            // Left arrow - previous lesson
            if (e.keyCode === 37 && $('.prev-lesson').length) {
                window.location.href = $('.prev-lesson').attr('href');
            }
            // Right arrow - next lesson
            if (e.keyCode === 39 && $('.next-lesson').length) {
                window.location.href = $('.next-lesson').attr('href');
            }
        }
    });
    
    // Certificate print
    $('.print-certificate').on('click', function(e) {
        e.preventDefault();
        window.print();
    });
    
    // Handle purchase button clicks for WooCommerce integration
    $('.lectus-purchase-btn').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var courseId = button.data('course-id');
        var courseType = button.data('course-type') || 'coursesingle';
        
        if (!courseId) {
            alert('강의 정보를 찾을 수 없습니다.');
            return;
        }
        
        button.prop('disabled', true).text('처리 중...');
        
        // Check if product exists and redirect to WooCommerce product page
        $.ajax({
            url: lectus_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'lectus_get_course_product',
                nonce: lectus_ajax.nonce,
                course_id: courseId,
                course_type: courseType
            },
            success: function(response) {
                if (response.success && response.data.product_url) {
                    window.location.href = response.data.product_url;
                } else {
                    alert(response.data.message || '상품을 찾을 수 없습니다. 관리자에게 문의하세요.');
                }
            },
            error: function() {
                alert('오류가 발생했습니다. 다시 시도해주세요.');
            },
            complete: function() {
                button.prop('disabled', false).text('구매하기');
            }
        });
    });
    
    // Admin: Handle create product button clicks
    $('.lectus-create-product').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var courseId = button.data('course-id');
        var courseType = button.data('course-type');
        
        if (!courseId || !courseType) {
            alert('강의 정보를 찾을 수 없습니다.');
            return;
        }
        
        if (!confirm('이 강의를 WooCommerce 상품으로 생성하시겠습니까?')) {
            return;
        }
        
        button.prop('disabled', true);
        var originalText = button.text();
        button.text('상품 생성 중...');
        
        $.ajax({
            url: lectus_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'lectus_create_product',
                nonce: lectus_ajax.nonce,
                course_id: courseId,
                course_type: courseType
            },
            success: function(response) {
                if (response.success) {
                    alert('상품이 성공적으로 생성되었습니다.');
                    
                    // Replace button with "View Product" link
                    var newLink = $('<a>')
                        .attr('href', response.data.edit_url)
                        .attr('title', '연결된 상품 보기')
                        .text('상품 보기')
                        .addClass('row-title');
                    
                    button.parent().html(newLink);
                } else {
                    alert(response.data.message || '상품 생성에 실패했습니다.');
                    button.prop('disabled', false).text(originalText);
                }
            },
            error: function(xhr) {
                var message = '상품 생성 중 오류가 발생했습니다.';
                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    message = xhr.responseJSON.data.message;
                }
                alert(message);
                button.prop('disabled', false).text(originalText);
            }
        });
    });
    
    // Q&A System Functions
    // Handle Q&A form submission
    $('#lectus-qa-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitBtn = form.find('[type="submit"]');
        var status = $('#form-status');
        
        // Validate form
        var title = $('#qa-title').val().trim();
        var content = $('#qa-content').val().trim();
        
        if (title.length < 5 || title.length > 255) {
            showFormStatus('error', '제목은 5자 이상 255자 이하로 입력해주세요.');
            return;
        }
        
        if (content.length < 10 || content.length > 10000) {
            showFormStatus('error', '내용은 10자 이상 10,000자 이하로 입력해주세요.');
            return;
        }
        
        submitBtn.prop('disabled', true).text('등록 중...');
        
        $.ajax({
            url: lectus_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'lectus_submit_question',
                nonce: lectus_ajax.nonce,
                title: title,
                content: content,
                course_id: form.find('[name="course_id"]').val(),
                lesson_id: form.find('[name="lesson_id"]').val()
            },
            success: function(response) {
                if (response.success) {
                    showFormStatus('success', response.data.message);
                    form[0].reset();
                    updateCharCount();
                    // Refresh question list after a delay
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    showFormStatus('error', response.data.message);
                }
            },
            error: function(xhr) {
                var message = '오류가 발생했습니다. 다시 시도해주세요.';
                if (xhr.status === 429) {
                    message = '너무 자주 요청하고 있습니다. 잠시 후 다시 시도해주세요.';
                }
                showFormStatus('error', message);
            },
            complete: function() {
                submitBtn.prop('disabled', false).text('질문 등록');
            }
        });
    });
    
    // Character counter for Q&A form
    function updateCharCount() {
        $('#qa-title').on('input', function() {
            var length = $(this).val().length;
            var counter = $(this).siblings('.field-help').find('.char-count');
            if (counter.length === 0) {
                $(this).siblings('.field-help').append('<span class="char-count" aria-live="polite">' + length + ' / 255</span>');
            } else {
                counter.text(length + ' / 255');
            }
        });
        
        $('#qa-content').on('input', function() {
            var length = $(this).val().length;
            $('.char-count').text(length + ' / 10,000');
            
            if (length > 9500) {
                $('.char-count').addClass('warning');
            } else {
                $('.char-count').removeClass('warning');
            }
        });
    }
    
    // Show form status messages
    function showFormStatus(type, message) {
        var status = $('#form-status');
        status.removeClass('success error').addClass(type);
        status.html('<p>' + message + '</p>').show();
        
        // Auto-hide success messages
        if (type === 'success') {
            setTimeout(function() {
                status.fadeOut();
            }, 5000);
        }
    }
    
    // Initialize character counters
    updateCharCount();
});

// Global Q&A functions (accessible from inline JavaScript)
function voteQA(qaId, direction) {
    if (!lectus_ajax || !lectus_ajax.nonce) {
        alert('로그인이 필요합니다.');
        return;
    }
    
    jQuery.ajax({
        url: lectus_ajax.ajaxurl,
        type: 'POST',
        data: {
            action: 'lectus_vote_qa',
            nonce: lectus_ajax.nonce,
            qa_id: qaId,
            vote_type: direction
        },
        success: function(response) {
            if (response.success) {
                // Update vote count display
                var qaItem = jQuery('[data-question-id="' + qaId + '"]');
                var votesSpan = qaItem.find('.votes');
                votesSpan.text('추천 ' + response.data.votes);
            } else {
                alert(response.data.message);
            }
        },
        error: function() {
            alert('투표 처리 중 오류가 발생했습니다.');
        }
    });
}

function toggleAnswerForm(questionId) {
    var form = jQuery('#answer-form-' + questionId);
    form.toggle();
    
    if (form.is(':visible')) {
        form.find('textarea').focus();
    }
}

function submitAnswer(event, questionId) {
    event.preventDefault();
    
    if (!lectus_ajax || !lectus_ajax.nonce) {
        alert('로그인이 필요합니다.');
        return;
    }
    
    var form = jQuery(event.target);
    var content = form.find('[name="content"]').val().trim();
    var submitBtn = form.find('[type="submit"]');
    
    if (content.length < 10 || content.length > 10000) {
        alert('답변은 10자 이상 10,000자 이하로 입력해주세요.');
        return;
    }
    
    submitBtn.prop('disabled', true).text('답변 등록 중...');
    
    jQuery.ajax({
        url: lectus_ajax.ajaxurl,
        type: 'POST',
        data: {
            action: 'lectus_submit_answer',
            nonce: lectus_ajax.nonce,
            parent_id: questionId,
            content: content
        },
        success: function(response) {
            if (response.success) {
                alert(response.data.message);
                form[0].reset();
                toggleAnswerForm(questionId);
                // Refresh to show new answer
                setTimeout(function() {
                    location.reload();
                }, 1000);
            } else {
                alert(response.data.message);
            }
        },
        error: function(xhr) {
            var message = '답변 등록 중 오류가 발생했습니다.';
            if (xhr.status === 429) {
                message = '너무 자주 요청하고 있습니다. 잠시 후 다시 시도해주세요.';
            }
            alert(message);
        },
        complete: function() {
            submitBtn.prop('disabled', false).text('답변 등록');
        }
    });
}

// Notification styles
(function() {
    var style = document.createElement('style');
    style.innerHTML = `
        .lectus-notification {
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            animation: slideDown 0.3s ease-out;
        }
        
        .lectus-notification.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .lectus-notification.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .lectus-notification.info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    `;
    document.head.appendChild(style);
})();