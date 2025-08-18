/**
 * Lectus Class System - Admin Student Management
 * Enhanced version with improved UI/UX
 */

(function($) {
    'use strict';
    
    // Student Management Class
    class StudentManagement {
        constructor() {
            this.currentUserId = 0;
            this.currentCourseId = 0;
            this.ajaxUrl = lectusAdmin.ajaxUrl;
            this.nonce = lectusAdmin.nonce;
            this.initEvents();
        }
        
        initEvents() {
            // Manage button clicks
            $(document).on('click', '.lectus-manage-student', (e) => {
                e.preventDefault();
                const $btn = $(e.currentTarget);
                this.openManagementModal(
                    $btn.data('user-id'),
                    $btn.data('course-id'),
                    $btn.data('user-name'),
                    $btn.data('course-name'),
                    $btn.data('status'),
                    $btn.data('expires')
                );
            });
            
            // Export button
            $(document).on('click', '#lectus-export-btn', (e) => {
                e.preventDefault();
                this.exportStudents();
            });
            
            // Modal actions
            $(document).on('click', '#lectus-extend-btn', () => this.extendAccess());
            $(document).on('click', '#lectus-change-status-btn', () => this.changeStatus());
            $(document).on('click', '#lectus-reset-progress-btn', () => this.resetProgress());
            $(document).on('click', '#lectus-generate-cert-btn', () => this.generateCertificate());
            $(document).on('click', '.lectus-modal-close', () => this.closeModal());
            
            // Close modal on overlay click
            $(document).on('click', '#lectus-modal-overlay', (e) => {
                if (e.target.id === 'lectus-modal-overlay') {
                    this.closeModal();
                }
            });
            
            // ESC key to close modal
            $(document).keyup((e) => {
                if (e.key === "Escape") {
                    this.closeModal();
                }
            });
        }
        
        openManagementModal(userId, courseId, userName, courseName, status, expires) {
            this.currentUserId = userId;
            this.currentCourseId = courseId;
            
            // Update modal content
            $('#modal-student-name').text(userName);
            $('#modal-course-name').text(courseName);
            $('#modal-expires').text(expires || '무제한');
            $('#change-status').val(status);
            
            // Update status badge
            const statusLabels = {
                'active': '활성',
                'paused': '일시정지',
                'expired': '만료',
                'cancelled': '취소'
            };
            $('#modal-current-status')
                .removeClass()
                .addClass('status-badge status-' + status)
                .text(statusLabels[status] || status);
            
            // Show modal with animation
            $('#lectus-modal-overlay').fadeIn(300);
            $('body').addClass('lectus-modal-open');
        }
        
        closeModal() {
            $('#lectus-modal-overlay').fadeOut(300);
            $('body').removeClass('lectus-modal-open');
        }
        
        showLoading(buttonId) {
            const $btn = $(buttonId);
            $btn.prop('disabled', true).addClass('loading');
            $btn.find('.spinner').show();
        }
        
        hideLoading(buttonId) {
            const $btn = $(buttonId);
            $btn.prop('disabled', false).removeClass('loading');
            $btn.find('.spinner').hide();
        }
        
        showNotice(message, type = 'success') {
            const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
            const $notice = $(`<div class="notice ${noticeClass} is-dismissible"><p>${message}</p></div>`);
            
            $('.wrap > h1').after($notice);
            
            setTimeout(() => {
                $notice.fadeOut(() => $notice.remove());
            }, 5000);
        }
        
        extendAccess() {
            const days = $('#extend-days').val();
            
            if (!days || days < 1) {
                alert('올바른 일수를 입력하세요.');
                return;
            }
            
            if (!confirm(`수강 기간을 ${days}일 연장하시겠습니까?`)) {
                return;
            }
            
            this.showLoading('#lectus-extend-btn');
            
            $.ajax({
                url: this.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'lectus_extend_access',
                    user_id: this.currentUserId,
                    course_id: this.currentCourseId,
                    days: days,
                    nonce: this.nonce
                },
                success: (response) => {
                    this.hideLoading('#lectus-extend-btn');
                    
                    if (response.success) {
                        this.showNotice(response.data.message);
                        
                        // Update expiry date in table
                        const $row = $(`.lectus-student-row[data-user="${this.currentUserId}"][data-course="${this.currentCourseId}"]`);
                        $row.find('.expiry-date').text(response.data.new_expiry);
                        
                        // Update modal
                        $('#modal-expires').text(response.data.new_expiry);
                        
                        // Reset input
                        $('#extend-days').val('30');
                    } else {
                        this.showNotice(response.data.message || '오류가 발생했습니다.', 'error');
                    }
                },
                error: () => {
                    this.hideLoading('#lectus-extend-btn');
                    this.showNotice('오류가 발생했습니다. 다시 시도해주세요.', 'error');
                }
            });
        }
        
        changeStatus() {
            const status = $('#change-status').val();
            const statusLabels = {
                'active': '활성',
                'paused': '일시정지',
                'expired': '만료',
                'cancelled': '취소'
            };
            
            if (!confirm(`상태를 "${statusLabels[status]}"(으)로 변경하시겠습니까?`)) {
                return;
            }
            
            this.showLoading('#lectus-change-status-btn');
            
            $.ajax({
                url: this.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'lectus_change_status',
                    user_id: this.currentUserId,
                    course_id: this.currentCourseId,
                    status: status,
                    nonce: this.nonce
                },
                success: (response) => {
                    this.hideLoading('#lectus-change-status-btn');
                    
                    if (response.success) {
                        this.showNotice(response.data.message);
                        
                        // Update status in table
                        const $row = $(`.lectus-student-row[data-user="${this.currentUserId}"][data-course="${this.currentCourseId}"]`);
                        const $statusBadge = $row.find('.status-badge');
                        $statusBadge.removeClass().addClass('status-badge status-' + status).text(response.data.status_label || statusLabels[status]);
                        
                        // Update modal
                        $('#modal-current-status')
                            .removeClass()
                            .addClass('status-badge status-' + status)
                            .text(response.data.status_label || statusLabels[status]);
                    } else {
                        this.showNotice(response.data.message || '오류가 발생했습니다.', 'error');
                    }
                },
                error: () => {
                    this.hideLoading('#lectus-change-status-btn');
                    this.showNotice('오류가 발생했습니다. 다시 시도해주세요.', 'error');
                }
            });
        }
        
        resetProgress() {
            if (!confirm('정말로 진도를 초기화하시겠습니까?\n\n이 작업은 되돌릴 수 없습니다.')) {
                return;
            }
            
            this.showLoading('#lectus-reset-progress-btn');
            
            $.ajax({
                url: this.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'lectus_reset_progress',
                    user_id: this.currentUserId,
                    course_id: this.currentCourseId,
                    nonce: this.nonce
                },
                success: (response) => {
                    this.hideLoading('#lectus-reset-progress-btn');
                    
                    if (response.success) {
                        this.showNotice(response.data.message);
                        
                        // Update progress in table
                        const $row = $(`.lectus-student-row[data-user="${this.currentUserId}"][data-course="${this.currentCourseId}"]`);
                        $row.find('.progress-bar-fill').css('width', '0%');
                        $row.find('.progress-text').text('0%');
                    } else {
                        this.showNotice(response.data.message || '오류가 발생했습니다.', 'error');
                    }
                },
                error: () => {
                    this.hideLoading('#lectus-reset-progress-btn');
                    this.showNotice('오류가 발생했습니다. 다시 시도해주세요.', 'error');
                }
            });
        }
        
        generateCertificate() {
            if (!confirm('수료증을 발급하시겠습니까?')) {
                return;
            }
            
            this.showLoading('#lectus-generate-cert-btn');
            
            $.ajax({
                url: this.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'lectus_generate_certificate',
                    user_id: this.currentUserId,
                    course_id: this.currentCourseId,
                    nonce: this.nonce
                },
                success: (response) => {
                    this.hideLoading('#lectus-generate-cert-btn');
                    
                    if (response.success) {
                        this.showNotice(response.data.message);
                        
                        if (response.data.certificate_url) {
                            window.open(response.data.certificate_url, '_blank');
                        }
                    } else {
                        this.showNotice(response.data.message || '오류가 발생했습니다.', 'error');
                    }
                },
                error: () => {
                    this.hideLoading('#lectus-generate-cert-btn');
                    this.showNotice('오류가 발생했습니다. 다시 시도해주세요.', 'error');
                }
            });
        }
        
        exportStudents() {
            const courseId = $('select[name="course_id"]').val() || '';
            const status = $('select[name="status"]').val() || '';
            
            if (!confirm('수강생 데이터를 CSV 파일로 내보내시겠습니까?')) {
                return;
            }
            
            const exportUrl = `${this.ajaxUrl}?action=lectus_export_students&course_id=${courseId}&status=${status}&nonce=${this.nonce}`;
            window.location.href = exportUrl;
            
            this.showNotice('CSV 파일 다운로드가 시작되었습니다.');
        }
    }
    
    // Initialize on document ready
    $(document).ready(() => {
        // Check if we have the right elements
        if (typeof lectusAdmin !== 'undefined' && $('.lectus-students-table').length > 0) {
            window.lectusStudentManagement = new StudentManagement();
        }
    });
    
})(jQuery);