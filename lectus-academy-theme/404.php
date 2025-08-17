<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package LectusAcademy
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="min-h-screen bg-gradient-to-b from-gray-50 to-white flex items-center justify-center px-4 py-20">
        <div class="max-w-4xl mx-auto text-center">
            <!-- 404 Icon -->
            <div class="mb-8">
                <div class="inline-flex items-center justify-center w-32 h-32 bg-blue-100 rounded-full mb-6">
                    <svg class="w-20 h-20 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                
                <!-- 404 Number -->
                <h1 class="text-9xl font-bold text-gray-900 mb-4">404</h1>
            </div>

            <!-- Error Message -->
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                <?php esc_html_e('페이지를 찾을 수 없습니다', 'lectus-academy'); ?>
            </h2>
            
            <p class="text-lg text-gray-600 mb-8 max-w-2xl mx-auto">
                <?php esc_html_e('요청하신 페이지가 존재하지 않거나 이동되었을 수 있습니다. URL을 다시 확인하시거나 아래 버튼을 통해 홈페이지로 이동해주세요.', 'lectus-academy'); ?>
            </p>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <?php esc_html_e('홈으로 돌아가기', 'lectus-academy'); ?>
                </a>
                
                <a href="<?php echo esc_url(get_post_type_archive_link('coursesingle')); ?>" class="inline-flex items-center justify-center px-6 py-3 bg-white text-blue-600 font-medium rounded-lg border-2 border-blue-600 hover:bg-blue-50 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <?php esc_html_e('강의 둘러보기', 'lectus-academy'); ?>
                </a>
            </div>

            <!-- Search Form -->
            <div class="max-w-xl mx-auto">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">
                    <?php esc_html_e('원하시는 내용을 검색해보세요', 'lectus-academy'); ?>
                </h3>
                <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
                    <div class="flex gap-2">
                        <input type="search" 
                               class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                               placeholder="<?php echo esc_attr_x('검색어를 입력하세요...', 'placeholder', 'lectus-academy'); ?>" 
                               value="<?php echo get_search_query(); ?>" 
                               name="s" />
                        <button type="submit" 
                                class="px-6 py-3 bg-gray-800 text-white font-medium rounded-lg hover:bg-gray-900 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Popular Links -->
            <div class="mt-12 pt-12 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-700 mb-6">
                    <?php esc_html_e('인기 페이지', 'lectus-academy'); ?>
                </h3>
                <div class="flex flex-wrap gap-3 justify-center">
                    <a href="<?php echo esc_url(home_url('/about')); ?>" 
                       class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition-colors">
                        <?php esc_html_e('회사 소개', 'lectus-academy'); ?>
                    </a>
                    <a href="<?php echo esc_url(home_url('/faq')); ?>" 
                       class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition-colors">
                        <?php esc_html_e('자주 묻는 질문', 'lectus-academy'); ?>
                    </a>
                    <a href="<?php echo esc_url(home_url('/support')); ?>" 
                       class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition-colors">
                        <?php esc_html_e('고객센터', 'lectus-academy'); ?>
                    </a>
                    <a href="<?php echo esc_url(home_url('/community')); ?>" 
                       class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition-colors">
                        <?php esc_html_e('커뮤니티', 'lectus-academy'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
get_footer();