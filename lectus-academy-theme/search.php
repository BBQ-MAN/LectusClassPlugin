<?php
/**
 * The template for displaying search results pages
 *
 * @package LectusAcademy
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="min-h-screen bg-gradient-to-b from-gray-50 to-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            
            <header class="page-header mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">
                    <?php
                    printf(
                        '"<span class="text-blue-600">%s</span>" 검색결과',
                        get_search_query()
                    );
                    ?>
                </h1>
                <?php if (have_posts()) : ?>
                    <p class="text-lg text-gray-600">
                        <?php
                        global $wp_query;
                        printf(
                            '총 <span class="font-semibold">%d</span>개의 결과를 찾았습니다.',
                            $wp_query->found_posts
                        );
                        ?>
                    </p>
                <?php endif; ?>
            </header>

            <?php if (have_posts()) : ?>
                
                <!-- Search Results Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Main Content -->
                    <div class="lg:col-span-2">
                        <div class="space-y-6">
                            <?php
                            while (have_posts()) :
                                the_post();
                                
                                // Get post type for proper styling
                                $post_type = get_post_type();
                                $post_type_obj = get_post_type_object($post_type);
                                $post_type_label = $post_type_obj ? $post_type_obj->labels->singular_name : '';
                                ?>
                                
                                <article id="post-<?php the_ID(); ?>" <?php post_class('bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow overflow-hidden'); ?>>
                                    <div class="flex">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <a href="<?php the_permalink(); ?>" class="flex-shrink-0">
                                                <?php the_post_thumbnail('thumbnail', array('class' => 'w-48 h-full object-cover')); ?>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <div class="flex-1 p-6">
                                            <!-- Post Type Badge -->
                                            <?php if ($post_type_label) : ?>
                                                <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full mb-2
                                                    <?php echo $post_type == 'coursesingle' ? 'bg-blue-100 text-blue-800' : 
                                                              ($post_type == 'lesson' ? 'bg-green-100 text-green-800' : 
                                                              'bg-gray-100 text-gray-800'); ?>">
                                                    <?php echo esc_html($post_type_label); ?>
                                                </span>
                                            <?php endif; ?>
                                            
                                            <h2 class="text-xl font-bold mb-2">
                                                <a href="<?php the_permalink(); ?>" class="text-gray-900 hover:text-blue-600 transition-colors">
                                                    <?php the_title(); ?>
                                                </a>
                                            </h2>
                                            
                                            <div class="text-sm text-gray-500 mb-3">
                                                <?php echo get_the_date(); ?>
                                                <?php if ($post_type == 'post') : ?>
                                                    | <?php the_category(', '); ?>
                                                <?php elseif ($post_type == 'coursesingle') : ?>
                                                    <?php
                                                    $course_categories = get_the_terms(get_the_ID(), 'course-category');
                                                    if ($course_categories && !is_wp_error($course_categories)) :
                                                        echo ' | ';
                                                        $cat_names = array();
                                                        foreach ($course_categories as $cat) {
                                                            $cat_names[] = $cat->name;
                                                        }
                                                        echo implode(', ', $cat_names);
                                                    endif;
                                                    ?>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="text-gray-600 mb-4 line-clamp-3">
                                                <?php the_excerpt(); ?>
                                            </div>
                                            
                                            <a href="<?php the_permalink(); ?>" class="inline-flex items-center text-blue-600 hover:text-blue-700 font-medium">
                                                자세히 보기
                                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </article>
                                
                            <?php endwhile; ?>
                        </div>
                        
                        <!-- Pagination -->
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
                    </div>
                    
                    <!-- Sidebar -->
                    <div class="lg:col-span-1">
                        <div class="sticky top-24 space-y-6">
                            
                            <!-- Search Again -->
                            <div class="bg-white rounded-lg p-6 shadow-md">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">새로 검색</h3>
                                <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                                    <div class="flex gap-2">
                                        <input type="search" 
                                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                               placeholder="검색어를 입력하세요..." 
                                               value="<?php echo get_search_query(); ?>" 
                                               name="s" />
                                        <button type="submit" 
                                                class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Filter by Post Type -->
                            <div class="bg-white rounded-lg p-6 shadow-md">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">결과 필터</h3>
                                <ul class="space-y-2">
                                    <?php
                                    // Get all public post types
                                    $post_types = get_post_types(array('public' => true), 'objects');
                                    foreach ($post_types as $type) :
                                        if ($type->name == 'attachment') continue;
                                        ?>
                                        <li>
                                            <a href="<?php echo esc_url(add_query_arg('post_type', $type->name)); ?>" 
                                               class="flex items-center justify-between text-gray-700 hover:text-blue-600 transition-colors">
                                                <span><?php echo esc_html($type->labels->name); ?></span>
                                                <span class="text-sm text-gray-500">
                                                    <?php
                                                    // Count posts of this type in search results
                                                    $type_query = new WP_Query(array(
                                                        's' => get_search_query(),
                                                        'post_type' => $type->name,
                                                        'posts_per_page' => -1,
                                                    ));
                                                    echo $type_query->found_posts;
                                                    wp_reset_postdata();
                                                    ?>
                                                </span>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            
                            <!-- Popular Searches -->
                            <div class="bg-white rounded-lg p-6 shadow-md">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">인기 검색어</h3>
                                <div class="flex flex-wrap gap-2">
                                    <?php
                                    $popular_searches = array('React', 'JavaScript', 'Python', 'HTML', 'CSS', 'Node.js', 'Vue.js', 'Django');
                                    foreach ($popular_searches as $term) :
                                        ?>
                                        <a href="<?php echo esc_url(home_url('/?s=' . urlencode($term))); ?>" 
                                           class="inline-block px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-blue-100 hover:text-blue-700 transition-colors">
                                            <?php echo esc_html($term); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>

            <?php else : ?>

                <!-- No Results Found -->
                <div class="flex flex-col items-center justify-center py-20">
                    <div class="text-center max-w-2xl mx-auto">
                        <!-- Icon -->
                        <div class="mb-8">
                            <div class="inline-flex items-center justify-center w-32 h-32 bg-gray-100 rounded-full">
                                <svg class="w-20 h-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>

                        <h2 class="text-3xl font-bold text-gray-900 mb-4">
                            검색 결과가 없습니다
                        </h2>
                        
                        <p class="text-lg text-gray-600 mb-8">
                            "<span class="font-semibold text-blue-600"><?php echo get_search_query(); ?></span>"에 대한 검색 결과를 찾을 수 없습니다.<br>
                            다른 키워드로 다시 검색해보세요.
                        </p>

                        <!-- Search Suggestions -->
                        <div class="bg-gray-50 rounded-lg p-6 mb-8">
                            <h3 class="font-semibold text-gray-900 mb-3">검색 팁:</h3>
                            <ul class="text-left text-gray-600 space-y-2">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>정확한 철자를 확인하세요</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>더 일반적인 키워드를 사용해보세요</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>동의어나 관련 용어를 시도하세요</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Search Form -->
                        <div class="max-w-xl mx-auto mb-8">
                            <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                                <div class="flex gap-2">
                                    <input type="search" 
                                           class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                           placeholder="다시 검색하기..." 
                                           value="" 
                                           name="s" />
                                    <button type="submit" 
                                            class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                        검색
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Popular Content -->
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-4">인기 콘텐츠 둘러보기</h3>
                            <div class="flex flex-wrap gap-3 justify-center">
                                <a href="<?php echo esc_url(get_post_type_archive_link('coursesingle')); ?>" 
                                   class="inline-flex items-center px-4 py-2 bg-white text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                    모든 강의
                                </a>
                                <a href="<?php echo esc_url(home_url('/')); ?>" 
                                   class="inline-flex items-center px-4 py-2 bg-white text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                    </svg>
                                    홈으로
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endif; ?>
        </div>
    </div>
</main>

<?php
get_footer();