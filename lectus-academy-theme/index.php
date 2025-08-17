<?php
/**
 * The main template file
 *
 * @package LectusAcademy
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="container">
        <?php if (is_home() && !is_front_page()) : ?>
            <header class="page-header">
                <h1 class="page-title"><?php single_post_title(); ?></h1>
            </header>
        <?php endif; ?>

        <?php if (have_posts()) : ?>
            <div class="row">
                <div class="col-8">
                    <div class="posts-grid">
                        <?php
                        while (have_posts()) :
                            the_post();
                            get_template_part('template-parts/content', get_post_format());
                        endwhile;
                        ?>
                    </div>

                    <div class="pagination">
                        <?php
                        the_posts_pagination(array(
                            'mid_size' => 2,
                            'prev_text' => '<i class="fas fa-chevron-left"></i>',
                            'next_text' => '<i class="fas fa-chevron-right"></i>',
                        ));
                        ?>
                    </div>
                </div>

                <div class="col-4">
                    <?php get_sidebar(); ?>
                </div>
            </div>
        <?php else : ?>
            <div class="no-results">
                <h2><?php esc_html_e('Nothing Found', 'lectus-academy'); ?></h2>
                <p><?php esc_html_e('It seems we can\'t find what you\'re looking for. Perhaps searching can help.', 'lectus-academy'); ?></p>
                <?php get_search_form(); ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php
get_footer();