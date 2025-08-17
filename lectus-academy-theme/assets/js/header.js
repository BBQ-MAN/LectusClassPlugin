/**
 * Header functionality for Lectus Academy theme
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        
        // User dropdown toggle
        $(document).on('click', '.user-dropdown-toggle', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Find the dropdown menu - it should be next sibling
            const $dropdown = $(this).next('.user-dropdown-menu');
            const isOpen = $dropdown.hasClass('show');
            
            // Close all dropdowns
            $('.user-dropdown-menu').removeClass('show');
            
            // Toggle current dropdown
            if (!isOpen) {
                $dropdown.addClass('show');
            }
            
            return false; // Extra prevention of default behavior
        });
        
        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.user-dropdown').length) {
                $('.user-dropdown-menu').removeClass('show');
            }
        });
        
        // Category navigation hover effect
        $('.category-link').hover(
            function() {
                $(this).addClass('hover');
            },
            function() {
                if (!$(this).hasClass('active')) {
                    $(this).removeClass('hover');
                }
            }
        );
        
        // Search bar focus effect
        $('.search-input').on('focus', function() {
            $(this).closest('.search-form').addClass('focused');
        }).on('blur', function() {
            $(this).closest('.search-form').removeClass('focused');
        });
        
        // Mobile menu toggle
        $('#mobile-menu-toggle').on('click', function(e) {
            e.preventDefault();
            $('body').toggleClass('mobile-menu-open');
            $('.mobile-menu').toggleClass('show');
            $(this).toggleClass('active');
            
            // Prevent body scroll when menu is open
            if ($('body').hasClass('mobile-menu-open')) {
                $('body').css('overflow', 'hidden');
            } else {
                $('body').css('overflow', '');
            }
        });
        
        // Close mobile menu when clicking outside
        $(document).on('click', function(e) {
            if ($('body').hasClass('mobile-menu-open')) {
                if (!$(e.target).closest('.mobile-menu').length && 
                    !$(e.target).closest('#mobile-menu-toggle').length) {
                    $('body').removeClass('mobile-menu-open').css('overflow', '');
                    $('.mobile-menu').removeClass('show');
                    $('#mobile-menu-toggle').removeClass('active');
                }
            }
        });
        
        // Mobile submenu toggle
        $('.mobile-menu .has-children > a').on('click', function(e) {
            if ($(window).width() <= 768) {
                e.preventDefault();
                $(this).siblings('.sub-menu').slideToggle(300);
                $(this).parent().toggleClass('open');
                
                // Close other open submenus
                $(this).parent().siblings('.has-children.open').removeClass('open')
                    .find('.sub-menu').slideUp(300);
            }
        });
        
        // Header scroll behavior - simplified for CSS sticky compatibility
        // Removed auto-hide/show logic that conflicts with CSS sticky positioning
        // CSS handles sticky positioning, JavaScript only handles visual effects
        $(window).on('scroll', function() {
            const currentScroll = $(this).scrollTop();
            const header = $('#masthead');
            
            // Only add visual effects, don't interfere with positioning
            if (currentScroll > 50) {
                header.addClass('scrolled');
            } else {
                header.removeClass('scrolled');
            }
        });
        
        // Window resize handler
        $(window).on('resize', function() {
            if ($(window).width() > 768) {
                $('body').removeClass('mobile-menu-open');
                $('.mobile-menu').removeClass('show');
                $('#mobile-menu-toggle').removeClass('active');
            }
        });
        
    });

})(jQuery);