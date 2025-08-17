<?php
/**
 * The template for displaying archive pages
 *
 * @package LectusAcademy
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="min-h-screen bg-gradient-to-b from-gray-50 to-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <?php if (have_posts()) : ?>
                
                <header class="page-header mb-8">
                    <h1 class="text-4xl font-bold text-gray-900 mb-4">
                        <?php
                        if (is_category()) {
                            single_cat_title();
                        } elseif (is_tag()) {
                            single_tag_title();
                        } elseif (is_author()) {
                            printf(esc_html__('Author: %s', 'lectus-academy'), '<span class="vcard">' . get_the_author() . '</span>');
                        } elseif (is_day()) {
                            printf(esc_html__('Day: %s', 'lectus-academy'), '<span>' . get_the_date() . '</span>');
                        } elseif (is_month()) {
                            printf(esc_html__('Month: %s', 'lectus-academy'), '<span>' . get_the_date(_x('F Y', 'monthly archives date format', 'lectus-academy')) . '</span>');
                        } elseif (is_year()) {
                            printf(esc_html__('Year: %s', 'lectus-academy'), '<span>' . get_the_date(_x('Y', 'yearly archives date format', 'lectus-academy')) . '</span>');
                        } elseif (is_tax()) {
                            $term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
                            echo esc_html($term->name);
                        } elseif (is_post_type_archive()) {
                            post_type_archive_title();
                        } else {
                            esc_html_e('Archives', 'lectus-academy');
                        }
                        ?>
                    </h1>
                    
                    <?php
                    the_archive_description('<div class="archive-description text-lg text-gray-600">', '</div>');
                    ?>
                </header>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php
                    while (have_posts()) :
                        the_post();
                        ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class('bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow overflow-hidden'); ?>>
                            <?php if (has_post_thumbnail()) : ?>
                                <a href="<?php the_permalink(); ?>" class="block">
                                    <?php the_post_thumbnail('medium', array('class' => 'w-full h-48 object-cover')); ?>
                                </a>
                            <?php endif; ?>
                            
                            <div class="p-6">
                                <h2 class="text-xl font-bold mb-2">
                                    <a href="<?php the_permalink(); ?>" class="text-gray-900 hover:text-blue-600 transition-colors">
                                        <?php the_title(); ?>
                                    </a>
                                </h2>
                                
                                <div class="text-sm text-gray-500 mb-3">
                                    <?php echo get_the_date(); ?>
                                </div>
                                
                                <div class="text-gray-600 mb-4">
                                    <?php the_excerpt(); ?>
                                </div>
                                
                                <a href="<?php the_permalink(); ?>" class="inline-flex items-center text-blue-600 hover:text-blue-700 font-medium">
                                    <?php esc_html_e('Read More', 'lectus-academy'); ?>
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </article>
                    <?php
                    endwhile;
                    ?>
                </div>

                <div class="mt-12">
                    <?php
                    the_posts_pagination(array(
                        'mid_size' => 2,
                        'prev_text' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>',
                        'next_text' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>',
                        'class' => 'flex justify-center space-x-2',
                    ));
                    ?>
                </div>

            <?php else : ?>

                <!-- Empty Archive -->
                <div class="flex flex-col items-center justify-center py-20">
                    <div class="text-center max-w-2xl mx-auto">
                        <!-- Icon -->
                        <div class="mb-8">
                            <div class="inline-flex items-center justify-center w-32 h-32 bg-gray-100 rounded-full">
                                <svg class="w-20 h-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        </div>

                        <h1 class="text-3xl font-bold text-gray-900 mb-4">
                            <?php
                            if (is_category()) {
                                printf('<span class="text-blue-600">%s</span> 카테고리에 아직 게시물이 없습니다', single_cat_title('', false));
                            } elseif (is_tag()) {
                                printf('<span class="text-blue-600">%s</span> 태그가 붙은 게시물이 아직 없습니다', single_tag_title('', false));
                            } elseif (is_tax('course-category')) {
                                $term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
                                printf('<span class="text-blue-600">%s</span> 카테고리에 아직 강의가 없습니다', esc_html($term->name));
                            } else {
                                echo '콘텐츠를 찾을 수 없습니다';
                            }
                            ?>
                        </h1>
                        
                        <p class="text-lg text-gray-600 mb-8">
                            곧 더 많은 콘텐츠가 추가될 예정입니다. 나중에 다시 확인하시거나 다른 섹션을 둘러보세요.
                        </p>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="<?php echo esc_url(home_url('/')); ?>" class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                홈으로 돌아가기
                            </a>
                            
                            <?php if (is_tax('course-category')) : ?>
                                <a href="<?php echo esc_url(get_post_type_archive_link('coursesingle')); ?>" class="inline-flex items-center justify-center px-6 py-3 bg-white text-blue-600 font-medium rounded-lg border-2 border-blue-600 hover:bg-blue-50 transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                    모든 강의 둘러보기
                                </a>
                            <?php else : ?>
                                <a href="<?php echo esc_url(home_url('/blog')); ?>" class="inline-flex items-center justify-center px-6 py-3 bg-white text-blue-600 font-medium rounded-lg border-2 border-blue-600 hover:bg-blue-50 transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                    </svg>
                                    블로그 둘러보기
                                </a>
                            <?php endif; ?>
                        </div>

                        <!-- Search Form -->
                        <div class="mt-12 max-w-xl mx-auto">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">
                                콘텐츠 검색
                            </h3>
                            <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
                                <div class="flex gap-2">
                                    <input type="search" 
                                           class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                           placeholder="검색어를 입력하세요..." 
                                           value="<?php echo get_search_query(); ?>" 
                                           name="s" />
                                    <button type="submit" 
                                            class="px-6 py-3 bg-gray-800 text-white font-medium rounded-lg hover:bg-gray-900 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            <?php endif; ?>
        </div>
    </div>
</main>

<?php
get_footer();