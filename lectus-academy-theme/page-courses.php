<?php
/**
 * Template Name: Courses Page
 * The template for displaying courses page without sidebar
 *
 * @package LectusAcademy
 */

get_header();
?>

<main id="primary" class="min-h-screen bg-gradient-to-b from-gray-50 to-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php
        while (have_posts()) :
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <!-- Page Header -->
                <header class="text-center mb-12">
                    <?php the_title('<h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">', '</h1>'); ?>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                        최고의 전문가들과 함께 성장하세요. 실무에 바로 적용 가능한 실전 강의를 만나보세요.
                    </p>
                </header>

                <!-- Category Filter (Optional) -->
                <div class="flex flex-wrap justify-center gap-3 mb-12">
                    <button class="px-6 py-2 bg-blue-600 text-white rounded-full font-medium hover:bg-blue-700 transition-colors">
                        전체
                    </button>
                    <button class="px-6 py-2 bg-white text-gray-700 rounded-full font-medium hover:bg-gray-100 transition-colors border border-gray-300">
                        프로그래밍
                    </button>
                    <button class="px-6 py-2 bg-white text-gray-700 rounded-full font-medium hover:bg-gray-100 transition-colors border border-gray-300">
                        디자인
                    </button>
                    <button class="px-6 py-2 bg-white text-gray-700 rounded-full font-medium hover:bg-gray-100 transition-colors border border-gray-300">
                        비즈니스
                    </button>
                    <button class="px-6 py-2 bg-white text-gray-700 rounded-full font-medium hover:bg-gray-100 transition-colors border border-gray-300">
                        마케팅
                    </button>
                </div>

                <!-- Course Content -->
                <div class="courses-content">
                    <?php
                    the_content();
                    
                    wp_link_pages(array(
                        'before' => '<div class="flex items-center gap-2 mt-8 text-sm">' . esc_html__('Pages:', 'lectus-academy'),
                        'after'  => '</div>',
                    ));
                    ?>
                </div>

                <?php if (get_edit_post_link()) : ?>
                    <footer class="mt-12 pt-6 border-t border-gray-200 text-center">
                        <?php
                        edit_post_link(
                            sprintf(
                                wp_kses(
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
</main>

<?php get_footer(); ?>