<?php
/**
 * Archive template for Courses
 *
 * @package LectusAcademy
 */

get_header();
?>

<div class="container">
    <div class="page-header">
        <h1 class="page-title">
            <?php
            if (is_post_type_archive('coursesingle')) {
                echo 'All Courses';
            } else {
                the_archive_title();
            }
            ?>
        </h1>
        <?php if (get_the_archive_description()) : ?>
            <div class="archive-description">
                <?php echo wp_kses_post(wpautop(get_the_archive_description())); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="course-filters">
        <div class="filter-group">
            <label for="category-filter">Category</label>
            <select id="category-filter" class="filter-select">
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
        
        <div class="filter-group">
            <label for="level-filter">Level</label>
            <select id="level-filter" class="filter-select">
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
        
        <div class="filter-group">
            <label for="sort-filter">Sort By</label>
            <select id="sort-filter" class="filter-select">
                <option value="date">Newest First</option>
                <option value="title">Title A-Z</option>
                <option value="popular">Most Popular</option>
            </select>
        </div>
    </div>

    <?php if (have_posts()) : ?>
        <div class="course-grid">
            <?php
            while (have_posts()) :
                the_post();
                ?>
                <article class="course-card">
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="course-thumbnail">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('medium'); ?>
                            </a>
                            <?php
                            // Get course level
                            $levels = get_the_terms(get_the_ID(), 'course_level');
                            if ($levels && !is_wp_error($levels)) {
                                $level = $levels[0];
                                echo '<span class="course-level-badge">' . esc_html($level->name) . '</span>';
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="course-content">
                        <h2 class="course-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                        
                        <?php
                        // Get course categories
                        $categories = get_the_terms(get_the_ID(), 'course_category');
                        if ($categories && !is_wp_error($categories)) : ?>
                            <div class="course-categories">
                                <?php foreach ($categories as $category) : ?>
                                    <span class="course-category"><?php echo esc_html($category->name); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="course-excerpt">
                            <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                        </div>
                        
                        <div class="course-meta">
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
                            <span class="course-lessons">
                                <i class="fas fa-book"></i> <?php echo $lesson_count; ?> Lessons
                            </span>
                            <span class="course-students">
                                <i class="fas fa-users"></i> <?php echo $enrolled_count ?: 0; ?> Students
                            </span>
                        </div>
                        
                        <div class="course-footer">
                            <?php
                            // Get course price
                            $product_id = get_post_meta(get_the_ID(), '_linked_product_id', true);
                            if ($product_id && function_exists('wc_get_product')) {
                                $product = wc_get_product($product_id);
                                if ($product) {
                                    echo '<span class="course-price">' . $product->get_price_html() . '</span>';
                                }
                            } else {
                                echo '<span class="course-price">Free</span>';
                            }
                            ?>
                            <a href="<?php the_permalink(); ?>" class="btn btn-primary btn-sm">View Course</a>
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
        
        <?php
        // Pagination
        the_posts_pagination(array(
            'mid_size' => 2,
            'prev_text' => '<i class="fas fa-chevron-left"></i>',
            'next_text' => '<i class="fas fa-chevron-right"></i>',
        ));
        ?>
        
    <?php else : ?>
        <div class="no-courses">
            <i class="fas fa-graduation-cap fa-3x"></i>
            <h2>No Courses Found</h2>
            <p>We're currently preparing amazing courses for you. Please check back soon!</p>
        </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Filter functionality
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
</script>

<?php
get_footer();