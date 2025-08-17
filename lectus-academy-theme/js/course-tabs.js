/**
 * Course Tab System - Cross-browser compatible
 * Separated from main template to prevent conflicts
 */

(function() {
    'use strict';
    
    // Prevent multiple initializations
    if (window.courseTabsInitialized) {
        return;
    }
    window.courseTabsInitialized = true;
    
    // Cross-browser utilities
    var utils = {
        // Cross-browser event handler
        addEvent: function(element, event, handler) {
            if (!element) return;
            if (element.addEventListener) {
                element.addEventListener(event, handler, false);
            } else if (element.attachEvent) {
                element.attachEvent('on' + event, handler);
            }
        },
        
        // Cross-browser class manipulation
        hasClass: function(element, className) {
            if (!element) return false;
            if (element.classList) {
                return element.classList.contains(className);
            } else {
                return (' ' + element.className + ' ').indexOf(' ' + className + ' ') > -1;
            }
        },
        
        addClass: function(element, className) {
            if (!element) return;
            if (element.classList) {
                element.classList.add(className);
            } else if (!this.hasClass(element, className)) {
                element.className = (element.className + ' ' + className).trim();
            }
        },
        
        removeClass: function(element, className) {
            if (!element) return;
            if (element.classList) {
                element.classList.remove(className);
            } else {
                element.className = element.className.replace(new RegExp('(^|\\s)' + className + '(?:\\s|$)', 'g'), ' ').trim();
            }
        },
        
        // Cross-browser query selector
        queryAll: function(selector, context) {
            context = context || document;
            if (context.querySelectorAll) {
                return context.querySelectorAll(selector);
            } else {
                // Fallback for very old browsers
                var elements = context.getElementsByTagName('*');
                var results = [];
                for (var i = 0; i < elements.length; i++) {
                    if (this.matches(elements[i], selector)) {
                        results.push(elements[i]);
                    }
                }
                return results;
            }
        },
        
        // Element selector matching
        matches: function(element, selector) {
            if (!element) return false;
            
            // Modern browsers
            if (element.matches) return element.matches(selector);
            if (element.webkitMatchesSelector) return element.webkitMatchesSelector(selector);
            if (element.mozMatchesSelector) return element.mozMatchesSelector(selector);
            if (element.msMatchesSelector) return element.msMatchesSelector(selector);
            
            // Fallback
            var matches = (element.document || element.ownerDocument).querySelectorAll(selector);
            var i = matches.length;
            while (--i >= 0 && matches[i] !== element) {}
            return i > -1;
        },
        
        // Get parent element with class
        getParentWithClass: function(element, className) {
            var parent = element.parentElement || element.parentNode;
            while (parent && parent !== document) {
                if (this.hasClass(parent, className)) {
                    return parent;
                }
                parent = parent.parentElement || parent.parentNode;
            }
            return null;
        }
    };
    
    function initTabSwitching() {
        console.log('[CourseTabsJS] Initializing tab switching');
        
        // Find tab navigation
        var tabNavigation = document.querySelector('.tab-navigation');
        if (!tabNavigation) {
            console.error('[CourseTabsJS] Tab navigation not found');
            return;
        }
        
        // Get all tab links and panes
        var tabLinks = tabNavigation.querySelectorAll('[data-tab]');
        var tabPanes = document.querySelectorAll('.tab-pane');
        
        if (!tabLinks.length || !tabPanes.length) {
            console.error('[CourseTabsJS] No tab links or panes found');
            return;
        }
        
        console.log('[CourseTabsJS] Found ' + tabLinks.length + ' tabs and ' + tabPanes.length + ' panes');
        
        // Convert NodeList to Array for compatibility
        var tabLinksArray = [];
        var tabPanesArray = [];
        
        for (var i = 0; i < tabLinks.length; i++) {
            tabLinksArray.push(tabLinks[i]);
        }
        
        for (var j = 0; j < tabPanes.length; j++) {
            tabPanesArray.push(tabPanes[j]);
        }
        
        function switchTab(clickedLink) {
            var targetTab = clickedLink.getAttribute('data-tab');
            console.log('[CourseTabsJS] Switching to tab:', targetTab);
            
            // Get parent LI element
            var clickedLi = utils.getParentWithClass(clickedLink, 'active') || clickedLink.parentElement;
            
            // Remove active class from all parent LIs
            var allLis = tabNavigation.querySelectorAll('li');
            for (var i = 0; i < allLis.length; i++) {
                utils.removeClass(allLis[i], 'active');
            }
            
            // Remove active styles from all tab links
            for (var k = 0; k < tabLinksArray.length; k++) {
                var link = tabLinksArray[k];
                utils.removeClass(link, 'border-blue-600');
                utils.removeClass(link, 'text-blue-600');
                utils.addClass(link, 'border-transparent');
                utils.addClass(link, 'text-gray-600');
            }
            
            // Hide all tab panes
            for (var m = 0; m < tabPanesArray.length; m++) {
                var pane = tabPanesArray[m];
                pane.style.display = 'none';
                utils.removeClass(pane, 'active');
            }
            
            // Add active class to clicked LI
            if (clickedLi) {
                utils.addClass(clickedLi, 'active');
            }
            
            // Add active styles to clicked tab
            utils.removeClass(clickedLink, 'border-transparent');
            utils.removeClass(clickedLink, 'text-gray-600');
            utils.addClass(clickedLink, 'border-blue-600');
            utils.addClass(clickedLink, 'text-blue-600');
            
            // Show target tab pane
            var targetPane = document.getElementById(targetTab);
            if (targetPane) {
                targetPane.style.display = 'block';
                utils.addClass(targetPane, 'active');
                console.log('[CourseTabsJS] Tab pane shown:', targetTab);
            } else {
                console.error('[CourseTabsJS] Tab pane not found:', targetTab);
            }
        }
        
        // Attach click handlers
        for (var n = 0; n < tabLinksArray.length; n++) {
            (function(link) {
                utils.addEvent(link, 'click', function(e) {
                    console.log('[CourseTabsJS] Tab clicked:', link.getAttribute('data-tab'));
                    
                    // Prevent default
                    if (e && e.preventDefault) {
                        e.preventDefault();
                    } else if (window.event) {
                        window.event.returnValue = false;
                    }
                    
                    // Stop propagation
                    if (e && e.stopPropagation) {
                        e.stopPropagation();
                    } else if (window.event) {
                        window.event.cancelBubble = true;
                    }
                    
                    switchTab(link);
                    return false;
                });
            })(tabLinksArray[n]);
        }
        
        // Initialize first tab as active
        setTimeout(function() {
            if (tabLinksArray.length > 0) {
                var activeTab = tabNavigation.querySelector('li.active [data-tab]') || tabLinksArray[0];
                switchTab(activeTab);
                console.log('[CourseTabsJS] Initial tab activated');
            }
        }, 100);
    }
    
    // DOM ready function
    function domReady(callback) {
        if (document.readyState === 'complete' || 
           (document.readyState !== 'loading' && !document.documentElement.doScroll)) {
            setTimeout(callback, 10);
        } else if (document.addEventListener) {
            document.addEventListener('DOMContentLoaded', callback);
        } else {
            // IE8 fallback
            document.attachEvent('onreadystatechange', function() {
                if (document.readyState === 'complete') {
                    callback();
                }
            });
        }
    }
    
    // Initialize when DOM is ready
    domReady(function() {
        console.log('[CourseTabsJS] DOM ready, initializing tabs');
        initTabSwitching();
    });
    
    // Also try after a delay in case of dynamic content
    setTimeout(function() {
        if (document.querySelector('.tab-navigation') && !window.courseTabsActivated) {
            window.courseTabsActivated = true;
            console.log('[CourseTabsJS] Delayed initialization');
            initTabSwitching();
        }
    }, 1000);
    
})();