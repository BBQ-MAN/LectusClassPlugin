/**
 * Lectus Academy Theme JavaScript
 */

(function($) {
    'use strict';

    // Theme object
    const LectusAcademy = {
        
        init: function() {
            this.bindEvents();
            this.initMobileMenu();
            this.initTabs();
            this.initEnrollment();
            this.initLessonCompletion();
            this.initQA();
            this.initBackToTop();
            this.initDropdowns();
        },

        bindEvents: function() {
            // Window scroll
            $(window).on('scroll', this.handleScroll.bind(this));
            
            // Document ready
            $(document).ready(function() {
                // Initialize tooltips
                $('[data-toggle="tooltip"]').tooltip();
            });
        },

        initMobileMenu: function() {
            const $toggle = $('.mobile-menu-toggle');
            const $menu = $('#mobile-menu');
            
            $toggle.on('click', function(e) {
                e.preventDefault();
                $(this).toggleClass('active');
                $menu.toggleClass('active');
                $('body').toggleClass('mobile-menu-open');
            });
            
            // Close on outside click
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.mobile-menu-toggle, #mobile-menu').length) {
                    $toggle.removeClass('active');
                    $menu.removeClass('active');
                    $('body').removeClass('mobile-menu-open');
                }
            });
        },

        initTabs: function() {
            $('.tab-nav a').on('click', function(e) {
                e.preventDefault();
                
                const $this = $(this);
                const tabId = $this.data('tab');
                
                // Update nav
                $this.parent().addClass('active').siblings().removeClass('active');
                
                // Update content
                $('#' + tabId).addClass('active').siblings('.tab-pane').removeClass('active');
            });
        },

        initEnrollment: function() {
            $('.enroll-button').on('click', function(e) {
                e.preventDefault();
                
                const $btn = $(this);
                const courseId = $btn.data('course-id');
                
                if (!lectusAcademy.is_user_logged_in) {
                    window.location.href = '/wp-login.php?redirect_to=' + encodeURIComponent(window.location.href);
                    return;
                }
                
                $btn.prop('disabled', true).html('<span class="spinner"></span> ' + lectusAcademy.translations.loading);
                
                $.ajax({
                    url: lectusAcademy.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'lectus_academy_enroll',
                        course_id: courseId,
                        nonce: lectusAcademy.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $btn.removeClass('btn-primary').addClass('btn-success')
                                .html('<i class="fas fa-check"></i> ' + response.data.message);
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        } else {
                            alert(response.data.message);
                            $btn.prop('disabled', false).html('<i class="fas fa-user-plus"></i> Enroll for Free');
                        }
                    },
                    error: function() {
                        alert(lectusAcademy.translations.error);
                        $btn.prop('disabled', false).html('<i class="fas fa-user-plus"></i> Enroll for Free');
                    }
                });
            });
        },

        initLessonCompletion: function() {
            $('#complete-lesson').on('click', function(e) {
                e.preventDefault();
                
                const $btn = $(this);
                const lessonId = $btn.data('lesson-id');
                const courseId = $btn.data('course-id');
                
                $btn.prop('disabled', true).html('<span class="spinner"></span> ' + lectusAcademy.translations.loading);
                
                $.ajax({
                    url: lectusAcademy.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'lectus_complete_lesson',
                        lesson_id: lessonId,
                        course_id: courseId,
                        nonce: lectusAcademy.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $btn.html('<i class="fas fa-check"></i> Completed');
                            
                            // Update progress bar
                            if (response.data.progress) {
                                $('.progress-bar').css('width', response.data.progress + '%');
                                $('.progress-info span:last').text(response.data.progress + '%');
                            }
                            
                            // Update lesson list
                            $('.lesson-list-item.current').addClass('completed');
                        } else {
                            alert(response.data.message);
                            $btn.prop('disabled', false).html('<i class="fas fa-check"></i> Mark as Complete');
                        }
                    },
                    error: function() {
                        alert(lectusAcademy.translations.error);
                        $btn.prop('disabled', false).html('<i class="fas fa-check"></i> Mark as Complete');
                    }
                });
            });
        },

        initQA: function() {
            // Q&A form submission
            $('#qa-question-form, #lesson-qa-form').on('submit', function(e) {
                e.preventDefault();
                
                const $form = $(this);
                const $textarea = $form.find('textarea');
                const $btn = $form.find('button[type="submit"]');
                const courseId = $form.data('course-id');
                const lessonId = $form.data('lesson-id') || 0;
                
                if (!$textarea.val().trim()) {
                    return;
                }
                
                $btn.prop('disabled', true).html('<span class="spinner"></span> ' + lectusAcademy.translations.loading);
                
                $.ajax({
                    url: lectusAcademy.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'lectus_submit_question',
                        course_id: courseId,
                        lesson_id: lessonId,
                        question: $textarea.val(),
                        nonce: lectusAcademy.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $textarea.val('');
                            
                            // Add question to list
                            const questionHtml = response.data.html;
                            $('.qa-list').prepend(questionHtml);
                            
                            $btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Submit Question');
                        } else {
                            alert(response.data.message);
                            $btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Submit Question');
                        }
                    },
                    error: function() {
                        alert(lectusAcademy.translations.error);
                        $btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Submit Question');
                    }
                });
            });
            
            // Q&A voting
            $(document).on('click', '.qa-vote', function(e) {
                e.preventDefault();
                
                const $this = $(this);
                const postId = $this.data('post-id');
                const voteType = $this.data('vote');
                
                $.ajax({
                    url: lectusAcademy.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'lectus_vote_qa',
                        post_id: postId,
                        vote_type: voteType,
                        nonce: lectusAcademy.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $this.addClass('voted');
                            const $count = $this.find('.vote-count');
                            $count.text(parseInt($count.text()) + 1);
                        }
                    }
                });
            });
        },

        initBackToTop: function() {
            const $btn = $('#back-to-top');
            
            $(window).on('scroll', function() {
                if ($(this).scrollTop() > 300) {
                    $btn.fadeIn();
                } else {
                    $btn.fadeOut();
                }
            });
            
            $btn.on('click', function(e) {
                e.preventDefault();
                $('html, body').animate({ scrollTop: 0 }, 600);
            });
        },

        initDropdowns: function() {
            // User dropdown is handled by header.js - removed to prevent conflicts
        },

        handleScroll: function() {
            const scrollTop = $(window).scrollTop();
            
            // Sticky header
            if (scrollTop > 100) {
                $('.site-header').addClass('scrolled');
            } else {
                $('.site-header').removeClass('scrolled');
            }
        }
    };

    // Initialize
    $(function() {
        LectusAcademy.init();
    });

})(jQuery);