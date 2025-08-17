<?php
/**
 * Archive template for Courses
 *
 * @package LectusAcademy
 */

get_header();
?>

<div class="course-layout max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">
            <?php
            if (is_post_type_archive('coursesingle')) {
                echo 'All Courses';
            } else {
                the_archive_title();
            }
            ?>
        </h1>
        <?php if (get_the_archive_description()) : ?>
            <div class="text-lg text-gray-600">
                <?php echo wp_kses_post(wpautop(get_the_archive_description())); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="course-filters flex flex-wrap gap-4 mb-8 p-4 bg-white rounded-lg shadow-sm">
        <div class="filter-group flex-1 min-w-[200px]">
            <label for="category-filter" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
            <select id="category-filter" class="filter-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Categories</option>
                <?php
                $categories = get_terms(array(
                    'taxonomy' => 'course_category',
                    'hide_empty' => true,
                ));
                if ($categories && !is_wp_error($categories)) {
                    foreach ($categories as $category) {
                        echo '<option value="' . esc_attr($category->slug) . '">' . esc_html($category->name) . '</option>';
                    }
                }
                ?>
            </select>
        </div>
        
        <div class="filter-group flex-1 min-w-[200px]">
            <label for="level-filter" class="block text-sm font-medium text-gray-700 mb-2">Level</label>
            <select id="level-filter" class="filter-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Levels</option>
                <?php
                $levels = get_terms(array(
                    'taxonomy' => 'course_level',
                    'hide_empty' => true,
                ));
                if ($levels && !is_wp_error($levels)) {
                    foreach ($levels as $level) {
                        echo '<option value="' . esc_attr($level->slug) . '">' . esc_html($level->name) . '</option>';
                    }
                }
                ?>
            </select>
        </div>
        
        <div class="filter-group flex-1 min-w-[200px]">
            <label for="sort-filter" class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
            <select id="sort-filter" class="filter-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="date">Newest First</option>
                <option value="title">Title A-Z</option>
                <option value="popular">Most Popular</option>
            </select>
        </div>
    </div>

    <?php if (have_posts()) : ?>
        <div class="course-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            while (have_posts()) :
                the_post();
                ?>
                <article class="course-card bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="course-thumbnail relative">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('medium', ['class' => 'w-full h-48 object-cover']); ?>
                            </a>
                            <?php
                            // Get course level
                            $levels = get_the_terms(get_the_ID(), 'course_level');
                            if ($levels && !is_wp_error($levels)) {
                                $level = $levels[0];
                                echo '<span class="course-level absolute top-2 right-2 bg-blue-600 text-white px-3 py-1 rounded-full text-sm">' . esc_html($level->name) . '</span>';
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="course-content p-6">
                        <h2 class="course-title text-xl font-bold text-gray-900 mb-2">
                            <a href="<?php the_permalink(); ?>" class="hover:text-blue-600 transition-colors"><?php the_title(); ?></a>
                        </h2>
                        
                        <?php
                        // Get course categories
                        $categories = get_the_terms(get_the_ID(), 'course_category');
                        if ($categories && !is_wp_error($categories)) : ?>
                            <div class="course-categories flex flex-wrap gap-2 mb-3">
                                <?php foreach ($categories as $category) : ?>
                                    <span class="category-tag px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs"><?php echo esc_html($category->name); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="course-excerpt text-gray-600 mb-4">
                            <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                        </div>
                        
                        <div class="course-meta flex items-center gap-4 text-sm text-gray-500 mb-4">
                            <?php
                            // Get lesson count
                            $lessons = get_posts(array(
                                'post_type' => 'lesson',
                                'meta_key' => '_course_id',
                                'meta_value' => get_the_ID(),
                                'posts_per_page' => -1,
                                'fields' => 'ids'
                            ));
                            $lesson_count = count($lessons);
                            
                            // Get enrolled count
                            global $wpdb;
                            $enrolled_count = $wpdb->get_var($wpdb->prepare(
                                "SELECT COUNT(*) FROM {$wpdb->prefix}lectus_enrollment WHERE course_id = %d AND status = 'active'",
                                get_the_ID()
                            ));
                            ?>
                            <span class="meta-item flex items-center gap-1">
                                <i class="fas fa-book"></i> <?php echo $lesson_count; ?> Lessons
                            </span>
                            <span class="meta-item flex items-center gap-1">
                                <i class="fas fa-users"></i> <?php echo $enrolled_count ?: 0; ?> Students
                            </span>
                        </div>
                        
                        <div class="course-footer flex items-center justify-between">
                            <?php
                            // Get course price
                            $product_id = get_post_meta(get_the_ID(), '_linked_product_id', true);
                            if ($product_id && function_exists('wc_get_product')) {
                                $product = wc_get_product($product_id);
                                if ($product) {
                                    echo '<span class="course-price text-xl font-bold text-gray-900">' . $product->get_price_html() . '</span>';
                                }
                            } else {
                                echo '<span class="course-price text-xl font-bold text-green-600">Free</span>';
                            }
                            ?>
                            <a href="<?php the_permalink(); ?>" class="course-link px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">View Course</a>
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
        
        <?php
        // Pagination
        echo '<div class="mt-8">';
        the_posts_pagination(array(
            'mid_size' => 2,
            'prev_text' => '<i class="fas fa-chevron-left"></i>',
            'next_text' => '<i class="fas fa-chevron-right"></i>',
            'class' => 'flex justify-center gap-2',
        ));
        echo '</div>';
        ?>
        
    <?php else : ?>
        <div class="text-center py-16">
            <i class="fas fa-graduation-cap text-6xl text-gray-300 mb-4"></i>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">No Courses Found</h2>
            <p class="text-gray-600">We're currently preparing amazing courses for you. Please check back soon!</p>
        </div>
    <?php endif; ?>
</div>

<script>
// Cross-browser compatible JavaScript
(function() {
    'use strict';
    
    // Cross-browser event handling
    function addEvent(element, event, handler) {
        if (element.addEventListener) {
            element.addEventListener(event, handler, false);
        } else if (element.attachEvent) {
            element.attachEvent('on' + event, handler);
        }
    }
    
    // DOM ready function for cross-browser compatibility
    function domReady(callback) {
        if (document.readyState === 'complete' || 
           (document.readyState !== 'loading' && !document.documentElement.doScroll)) {
            callback();
        } else {
            addEvent(document, 'DOMContentLoaded', callback);
        }
    }
    
    // Main initialization
    domReady(function() {
        // Check if jQuery is available
        if (typeof jQuery !== 'undefined') {
            jQuery(document).ready(function($) {
                // Filter functionality with jQuery
                $('.filter-select').on('change', function() {
                    var category = $('#category-filter').val();
                    var level = $('#level-filter').val();
                    var sort = $('#sort-filter').val();
                    
                    var url = '<?php echo get_post_type_archive_link('coursesingle'); ?>';
                    var params = [];
                    
                    if (category) params.push('course_category=' + category);
                    if (level) params.push('course_level=' + level);
                    if (sort) params.push('orderby=' + sort);
                    
                    if (params.length > 0) {
                        url += '?' + params.join('&');
                    }
                    
                    window.location.href = url;
                });
            });
        } else {
            // Fallback vanilla JavaScript implementation
            var filterSelects = document.querySelectorAll('.filter-select');
            
            for (var i = 0; i < filterSelects.length; i++) {
                addEvent(filterSelects[i], 'change', function() {
                    var categoryFilter = document.getElementById('category-filter');
                    var levelFilter = document.getElementById('level-filter');
                    var sortFilter = document.getElementById('sort-filter');
                    
                    var category = categoryFilter ? categoryFilter.value : '';
                    var level = levelFilter ? levelFilter.value : '';
                    var sort = sortFilter ? sortFilter.value : '';
                    
                    var url = '<?php echo get_post_type_archive_link('coursesingle'); ?>';
                    var params = [];
                    
                    if (category) params.push('course_category=' + category);
                    if (level) params.push('course_level=' + level);
                    if (sort) params.push('orderby=' + sort);
                    
                    if (params.length > 0) {
                        url += '?' + params.join('&');
                    }
                    
                    window.location.href = url;
                });
            }
        }
    });
})();
</script>

<?php
get_footer();