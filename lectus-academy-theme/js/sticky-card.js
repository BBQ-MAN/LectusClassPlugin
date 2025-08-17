/**
 * Sticky Purchase Card - Cross-browser compatible
 * Separated to prevent conflicts with other scripts
 */

(function() {
    'use strict';
    
    // Prevent multiple initializations
    if (window.stickyCardInitialized) {
        return;
    }
    window.stickyCardInitialized = true;
    
    function initStickyCard() {
        console.log('[StickyCard] Initializing sticky card');
        
        var stickyCard = document.querySelector('.sticky-card');
        if (!stickyCard) {
            console.log('[StickyCard] No sticky card found');
            return;
        }
        
        // Check if browser supports CSS sticky
        var supportsStickyPosition = (function() {
            var testEl = document.createElement('div');
            testEl.style.position = 'sticky';
            return testEl.style.position === 'sticky';
        })();
        
        console.log('[StickyCard] Sticky support:', supportsStickyPosition);
        
        // Detect Edge browser (all versions including Chromium-based)
        var userAgent = navigator.userAgent.toLowerCase();
        var isEdge = userAgent.indexOf('edge/') > -1 || 
                     userAgent.indexOf('edg/') > -1 ||
                     userAgent.indexOf('edga/') > -1 ||
                     userAgent.indexOf('edgios/') > -1;
        
        var isIE = userAgent.indexOf('msie ') > -1 || 
                   userAgent.indexOf('trident/') > -1;
        
        console.log('[StickyCard] Browser - Edge:', isEdge, 'IE:', isIE);
        console.log('[StickyCard] User Agent:', navigator.userAgent);
        
        // Force fallback for Edge and IE - Edge has inconsistent sticky support
        if (isEdge || isIE || !supportsStickyPosition) {
            console.log('[StickyCard] Using JavaScript fallback for sticky positioning');
            
            var header = document.querySelector('header') || document.getElementById('masthead');
            var headerHeight = header ? header.offsetHeight : 80;
            var stickyOffset = headerHeight + 16; // Header height + margin
            
            // Store initial position for proper restoration
            var initialPosition = {
                position: window.getComputedStyle(stickyCard).position || 'static',
                top: window.getComputedStyle(stickyCard).top || 'auto',
                left: window.getComputedStyle(stickyCard).left || 'auto',
                right: window.getComputedStyle(stickyCard).right || 'auto',
                width: window.getComputedStyle(stickyCard).width || 'auto',
                zIndex: window.getComputedStyle(stickyCard).zIndex || 'auto'
            };
            
            // Create placeholder to maintain layout
            var placeholder = document.createElement('div');
            placeholder.style.display = 'none';
            placeholder.style.height = stickyCard.offsetHeight + 'px';
            placeholder.style.width = stickyCard.offsetWidth + 'px';
            stickyCard.parentNode.insertBefore(placeholder, stickyCard.nextSibling);
            
            var isSticky = false;
            var cardRect = null;
            var parentRect = null;
            var originalOffsetTop = stickyCard.offsetTop;
            
            function updateCardDimensions() {
                // Only update when not sticky
                if (!isSticky) {
                    cardRect = stickyCard.getBoundingClientRect();
                    var parent = stickyCard.parentElement;
                    if (parent) {
                        parentRect = parent.getBoundingClientRect();
                    }
                    originalOffsetTop = stickyCard.offsetTop;
                }
            }
            
            function handleScroll() {
                var scrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
                
                // Update dimensions if needed
                if (!cardRect || !parentRect) {
                    updateCardDimensions();
                }
                
                // Dynamic trigger point based on card's original position
                var triggerPoint = originalOffsetTop - stickyOffset - 20;
                if (triggerPoint < 200) triggerPoint = 200; // Minimum trigger point
                
                if (scrollY > triggerPoint && !isSticky) {
                    isSticky = true;
                    
                    // Show placeholder to maintain layout
                    placeholder.style.display = 'block';
                    
                    // Apply fixed positioning
                    stickyCard.style.position = 'fixed';
                    stickyCard.style.top = stickyOffset + 'px';
                    stickyCard.style.width = (cardRect ? cardRect.width : 350) + 'px';
                    stickyCard.style.zIndex = '100';
                    
                    // Position based on parent container
                    if (parentRect) {
                        var viewportWidth = window.innerWidth || document.documentElement.clientWidth;
                        
                        if (viewportWidth >= 1024) {
                            // Large screens: calculate position based on grid
                            var containerMaxWidth = 1280;
                            var containerLeft = Math.max((viewportWidth - containerMaxWidth) / 2, 16);
                            var mainContentWidth = containerMaxWidth * 0.666667; // 2/3 of container
                            var sidebarLeft = containerLeft + mainContentWidth + 32; // Gap between columns
                            
                            stickyCard.style.left = sidebarLeft + 'px';
                            stickyCard.style.right = 'auto';
                        } else {
                            // Small screens: stick to right edge
                            stickyCard.style.left = 'auto';
                            stickyCard.style.right = '16px';
                        }
                    }
                    
                    // Add shadow class
                    if (stickyCard.classList) {
                        stickyCard.classList.add('shadow-2xl');
                    }
                    
                    console.log('[StickyCard] Sticky activated at scroll:', scrollY);
                    
                } else if (scrollY <= triggerPoint && isSticky) {
                    isSticky = false;
                    
                    // Hide placeholder
                    placeholder.style.display = 'none';
                    
                    // Clear all inline styles to restore original CSS
                    stickyCard.style.position = '';
                    stickyCard.style.top = '';
                    stickyCard.style.left = '';
                    stickyCard.style.right = '';
                    stickyCard.style.width = '';
                    stickyCard.style.zIndex = '';
                    
                    // Force reflow to ensure styles are applied
                    void stickyCard.offsetHeight;
                    
                    // Remove shadow class
                    if (stickyCard.classList) {
                        stickyCard.classList.remove('shadow-2xl');
                    }
                    
                    // Update dimensions after returning to normal position
                    setTimeout(updateCardDimensions, 100);
                    
                    console.log('[StickyCard] Sticky deactivated at scroll:', scrollY);
                }
            }
            
            // Attach scroll listener
            if (window.addEventListener) {
                window.addEventListener('scroll', handleScroll, false);
                window.addEventListener('resize', function() {
                    updateCardDimensions();
                    handleScroll();
                }, false);
            } else if (window.attachEvent) {
                window.attachEvent('onscroll', handleScroll);
                window.attachEvent('onresize', function() {
                    updateCardDimensions();
                    handleScroll();
                });
            }
            
            // Initial setup
            updateCardDimensions();
            handleScroll();
            
        } else {
            console.log('[StickyCard] Using native CSS sticky positioning');
            
            // For browsers that support sticky, just add enhancement
            function enhanceStickyCard() {
                var scrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
                
                if (scrollY > 200) {
                    if (stickyCard.classList && !stickyCard.classList.contains('shadow-2xl')) {
                        stickyCard.classList.add('shadow-2xl');
                    }
                } else {
                    if (stickyCard.classList && stickyCard.classList.contains('shadow-2xl')) {
                        stickyCard.classList.remove('shadow-2xl');
                    }
                }
            }
            
            if (window.addEventListener) {
                window.addEventListener('scroll', enhanceStickyCard, false);
            }
        }
        
        // Global function to recalculate sticky card (for AJAX updates)
        window.recalculateStickyCard = function() {
            console.log('[StickyCard] Recalculating position');
            if (!supportsStickyPosition || isEdge || isIE) {
                updateCardDimensions();
            }
        };
    }
    
    // DOM ready
    function domReady(callback) {
        if (document.readyState === 'complete' || 
           (document.readyState !== 'loading' && !document.documentElement.doScroll)) {
            setTimeout(callback, 10);
        } else if (document.addEventListener) {
            document.addEventListener('DOMContentLoaded', callback);
        } else {
            document.attachEvent('onreadystatechange', function() {
                if (document.readyState === 'complete') {
                    callback();
                }
            });
        }
    }
    
    // Initialize
    domReady(function() {
        console.log('[StickyCard] DOM ready');
        initStickyCard();
    });
    
    // Also try after delay for dynamic content
    setTimeout(function() {
        if (document.querySelector('.sticky-card') && !window.stickyCardActivated) {
            window.stickyCardActivated = true;
            console.log('[StickyCard] Delayed initialization');
            initStickyCard();
        }
    }, 1000);
    
})();