/**
 * Admin JavaScript for Lectus Class System
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Initialize color picker if available
    if ($.fn.wpColorPicker) {
        $('.color-field').wpColorPicker();
    }
    
    // Handle tabs in settings page
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        $('.tab-content').hide();
        $($(this).attr('href')).show();
    });
    
    // Handle lesson type change
    $('#lesson_type').on('change', function() {
        if ($(this).val() === 'video') {
            $('.video-url-row').show();
        } else {
            $('.video-url-row').hide();
        }
    });
    
    // Handle bulk upload modal
    window.lectusShowBulkUpload = function() {
        var modal = $('<div class="lectus-modal">' +
            '<div class="lectus-modal-content">' +
            '<span class="close">&times;</span>' +
            '<h2>CSV 레슨 벌크 업로드</h2>' +
            '<p>CSV 형식: 제목, 타입(text/video/quiz/assignment), 소요시간(분), 내용</p>' +
            '<textarea id="csv-data" rows="10" style="width:100%;"></textarea>' +
            '<button class="button button-primary" onclick="lectusUploadCSV()">업로드</button>' +
            '</div>' +
            '</div>');
        
        $('body').append(modal);
        
        modal.find('.close').on('click', function() {
            modal.remove();
        });
    };
    
    // Handle CSV upload
    window.lectusUploadCSV = function() {
        var csvData = $('#csv-data').val();
        var courseId = $('input[name="post_ID"]').val();
        
        if (!csvData) {
            alert('CSV 데이터를 입력하세요.');
            return;
        }
        
        $.ajax({
            url: lectus_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'lectus_bulk_upload_lessons',
                nonce: lectus_ajax.nonce,
                course_id: courseId,
                csv_data: csvData
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            },
            error: function() {
                alert('업로드 중 오류가 발생했습니다.');
            }
        });
    };
    
    // Handle student management modal
    window.lectusManageStudent = function(userId, courseId) {
        var modal = $('<div class="lectus-modal">' +
            '<div class="lectus-modal-content">' +
            '<span class="close">&times;</span>' +
            '<h2>수강생 관리</h2>' +
            '<div class="student-actions">' +
            '<button class="button" onclick="lectusResetProgress(' + userId + ', ' + courseId + ')">진도 초기화</button>' +
            '<button class="button" onclick="lectusExtendEnrollment(' + userId + ', ' + courseId + ')">수강 기간 연장</button>' +
            '<button class="button" onclick="lectusPauseEnrollment(' + userId + ', ' + courseId + ')">수강 일시정지</button>' +
            '<button class="button button-primary" onclick="lectusGenerateCertificate(' + userId + ', ' + courseId + ')">수료증 발급</button>' +
            '</div>' +
            '</div>' +
            '</div>');
        
        $('body').append(modal);
        
        modal.find('.close').on('click', function() {
            modal.remove();
        });
    };
    
    // Reset progress
    window.lectusResetProgress = function(userId, courseId) {
        if (!confirm('정말로 이 수강생의 진도를 초기화하시겠습니까?')) {
            return;
        }
        
        $.ajax({
            url: lectus_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'lectus_reset_progress',
                nonce: lectus_ajax.nonce,
                user_id: userId,
                course_id: courseId
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            }
        });
    };
    
    // Extend enrollment
    window.lectusExtendEnrollment = function(userId, courseId) {
        var days = prompt('연장할 일수를 입력하세요:');
        if (!days) return;
        
        // TODO: Implement extend enrollment AJAX
        alert('수강 기간 연장 기능 구현 예정');
    };
    
    // Pause enrollment
    window.lectusPauseEnrollment = function(userId, courseId) {
        // TODO: Implement pause enrollment AJAX
        alert('수강 일시정지 기능 구현 예정');
    };
    
    // Generate certificate
    window.lectusGenerateCertificate = function(userId, courseId) {
        $.ajax({
            url: lectus_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'lectus_generate_certificate',
                nonce: lectus_ajax.nonce,
                user_id: userId,
                course_id: courseId
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    if (response.data.certificate_url) {
                        window.open(response.data.certificate_url, '_blank');
                    }
                } else {
                    alert(response.data.message);
                }
            }
        });
    };
    
    // Handle create product button click (admin post list)
    $(document).on('click', '.lectus-create-product', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var courseId = button.data('course-id');
        var courseType = button.data('course-type');
        
        if (!confirm('이 강의에 대한 WooCommerce 상품을 생성하시겠습니까?')) {
            return;
        }
        
        button.text('생성 중...');
        button.prop('disabled', true);
        
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
                    alert(response.data.message);
                    
                    // Replace button with "View Product" link
                    var viewLink = '<a href="' + response.data.edit_url + '" class="button button-small" title="연결된 상품 보기">상품 보기</a>';
                    button.replaceWith(viewLink);
                } else {
                    alert(response.data.message || '상품 생성에 실패했습니다.');
                    button.text('상품 생성');
                    button.prop('disabled', false);
                }
            },
            error: function(xhr) {
                var message = '상품 생성 중 오류가 발생했습니다.';
                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    message = xhr.responseJSON.data.message;
                }
                alert(message);
                button.text('상품 생성');
                button.prop('disabled', false);
            }
        });
    });
    
    // Export to Excel
    window.lectusExportStudents = function() {
        var courseId = $('select[name="course_id"]').val();
        var status = $('select[name="status"]').val();
        
        // Create CSV content
        var csv = 'Name,Email,Course,Progress,Status,Enrolled,Expires\n';
        
        $('.wp-list-table tbody tr').each(function() {
            var row = [];
            $(this).find('td').each(function(index) {
                if (index < 7) { // Skip action column
                    row.push('"' + $(this).text().trim().replace(/"/g, '""') + '"');
                }
            });
            csv += row.join(',') + '\n';
        });
        
        // Download CSV
        var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'students-' + new Date().getTime() + '.csv';
        link.click();
    };
});

// Modal styles
(function() {
    var style = document.createElement('style');
    style.innerHTML = `
        .lectus-modal {
            display: block;
            position: fixed;
            z-index: 100000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        
        .lectus-modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 4px;
        }
        
        .lectus-modal .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .lectus-modal .close:hover,
        .lectus-modal .close:focus {
            color: black;
            text-decoration: none;
        }
        
        .student-actions {
            margin-top: 20px;
        }
        
        .student-actions button {
            margin-right: 10px;
            margin-bottom: 10px;
        }
    `;
    document.head.appendChild(style);
})();