<?php
/**
 * The template for displaying all pages
 *
 * @package LectusAcademy
 */

get_header();
?>

<main id="primary" class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <?php
                while (have_posts()) :
                    the_post();
                    ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('bg-white rounded-lg shadow-sm p-8'); ?>>
                        <header class="mb-6">
                            <?php the_title('<h1 class="text-3xl font-bold text-gray-900">', '</h1>'); ?>
                        </header>

                        <div class="prose prose-lg max-w-none text-gray-700">
                            <?php
                            the_content();
                            
                            wp_link_pages(array(
                                'before' => '<div class="flex items-center gap-2 mt-8 text-sm">' . esc_html__('Pages:', 'lectus-academy'),
                                'after'  => '</div>',
                            ));
                            ?>
                        </div>

                        <?php if (get_edit_post_link()) : ?>
                            <footer class="mt-8 pt-6 border-t border-gray-200">
                                <?php
                                edit_post_link(
                                    sprintf(
                                        wp_kses(
                                            /* translators: %s: Name of current post. Only visible to screen readers */
                                            __('Edit <span class="sr-only">%s</span>', 'lectus-academy'),
                                            array(
                                                'span' => array(
                                                    'class' => array(),
                                                ),
                                            )
                                        ),
                                        wp_kses_post(get_the_title())
                                    ),
                                    '<span class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-blue-600 transition-colors">',
                                    '</span>'
                                );
                                ?>
                            </footer>
                        <?php endif; ?>
                    </article>
                <?php endwhile; ?>
            </div>
            
            <div class="lg:col-span-1">

                <?php get_sidebar(); ?>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>