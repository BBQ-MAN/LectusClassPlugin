<?php
/**
 * Template Name: 위시리스트
 * 
 * @package LectusAcademy
 */

get_header();

// Redirect if not logged in
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

// Enqueue wishlist JavaScript
wp_enqueue_script('wishlist', get_template_directory_uri() . '/js/wishlist.js', array('jquery'), '1.0.0', true);

// Get current user
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Get wishlist items
$wishlist_items = array();
if (class_exists('Lectus_Wishlist')) {
    $wishlist_items = Lectus_Wishlist::get_user_wishlist($user_id);
}

?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">나의 위시리스트</h1>
                    <p class="text-gray-600 mt-1">관심있는 강의를 모아보세요</p>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-blue-600"><?php echo count($wishlist_items); ?>개</div>
                    <div class="text-sm text-gray-500">저장된 강의</div>
                </div>
            </div>
        </div>

        <?php if (empty($wishlist_items)) : ?>
            
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <div class="max-w-md mx-auto">
                    <div class="text-6xl text-gray-300 mb-4">
                        <i class="far fa-heart"></i>
                    </div>
                    <h3 class="text-xl font-medium text-gray-900 mb-2">위시리스트가 비어있습니다</h3>
                    <p class="text-gray-600 mb-8">관심있는 강의를 위시리스트에 추가해보세요.</p>
                    <a href="<?php echo esc_url(home_url('/courses/')); ?>" 
                       class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-book mr-2"></i>
                        강의 둘러보기
                    </a>
                </div>
            </div>

        <?php else : ?>

            <!-- Wishlist Controls -->
            <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <label class="flex items-center">
                            <input type="checkbox" id="select-all-wishlist" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">전체 선택</span>
                        </label>
                        <button class="bulk-remove-wishlist text-sm text-red-600 hover:text-red-700 font-medium disabled:text-gray-400 disabled:cursor-not-allowed">
                            <i class="fas fa-trash mr-1"></i>
                            선택 항목 제거
                        </button>
                    </div>
                    
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <span>정렬:</span>
                        <select class="border-gray-300 rounded text-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" id="wishlist-sort">
                            <option value="recent">최근 추가순</option>
                            <option value="title">강의명순</option>
                            <option value="price">가격순</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Wishlist Items -->
            <div class="space-y-4" id="wishlist-container">
                <?php foreach ($wishlist_items as $item) : 
                    $course = $item['course'];
                    $course_id = $course->ID;
                    $instructor_name = lectus_academy_get_instructor_name($course_id);
                    $price = lectus_academy_get_course_price($course_id);
                    $lesson_count = count(lectus_academy_get_course_lessons($course_id));
                    $enrolled_count = lectus_academy_get_enrolled_count($course_id);
                    $is_enrolled = lectus_academy_is_enrolled($course_id);
                    $added_date = date('Y.m.d', strtotime($item['added_at']));
                ?>
                
                <div class="wishlist-item bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                    <div class="flex">
                        <!-- Checkbox -->
                        <div class="flex items-start p-4">
                            <input type="checkbox" value="<?php echo $course_id; ?>" 
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        </div>
                        
                        <!-- Course Thumbnail -->
                        <div class="flex-shrink-0">
                            <?php if (has_post_thumbnail($course_id)) : ?>
                                <div class="w-32 h-24 bg-gray-200">
                                    <a href="<?php echo get_permalink($course_id); ?>">
                                        <?php echo get_the_post_thumbnail($course_id, 'medium', [
                                            'class' => 'w-full h-full object-cover hover:scale-105 transition-transform duration-300'
                                        ]); ?>
                                    </a>
                                </div>
                            <?php else : ?>
                                <div class="w-32 h-24 bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-book text-gray-400 text-2xl"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Course Info -->
                        <div class="flex-1 p-4">
                            <div class="flex justify-between items-start">
                                <div class="flex-1 min-w-0">
                                    <!-- Course Title -->
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                                        <a href="<?php echo get_permalink($course_id); ?>" 
                                           class="hover:text-blue-600 transition-colors line-clamp-2">
                                            <?php echo esc_html($course->post_title); ?>
                                        </a>
                                    </h3>
                                    
                                    <!-- Instructor -->
                                    <div class="flex items-center text-sm text-gray-600 mb-2">
                                        <i class="fas fa-user-tie mr-1"></i>
                                        <span><?php echo esc_html($instructor_name); ?></span>
                                    </div>
                                    
                                    <!-- Course Meta -->
                                    <div class="flex items-center gap-4 text-sm text-gray-500 mb-3">
                                        <span class="flex items-center">
                                            <i class="fas fa-play-circle mr-1"></i>
                                            <?php echo $lesson_count; ?>강
                                        </span>
                                        <span class="flex items-center">
                                            <i class="fas fa-users mr-1"></i>
                                            <?php echo number_format($enrolled_count); ?>명
                                        </span>
                                        <span class="flex items-center">
                                            <i class="fas fa-calendar-plus mr-1"></i>
                                            <?php echo $added_date; ?> 추가
                                        </span>
                                    </div>
                                    
                                    <!-- Rating -->
                                    <div class="flex items-center gap-2 mb-3">
                                        <div class="flex text-yellow-400 text-sm">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star-half-alt"></i>
                                        </div>
                                        <span class="text-sm text-gray-600">4.5 (128개)</span>
                                    </div>
                                </div>
                                
                                <!-- Price and Actions -->
                                <div class="text-right ml-4">
                                    <div class="text-xl font-bold text-gray-900 mb-3">
                                        <?php echo $price; ?>
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <?php if ($is_enrolled) : ?>
                                            <a href="<?php echo get_permalink($course_id); ?>" 
                                               class="block w-full text-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                                                <i class="fas fa-play mr-1"></i>
                                                학습하기
                                            </a>
                                        <?php else : ?>
                                            <button class="w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors enroll-btn" 
                                                    data-course-id="<?php echo $course_id; ?>">
                                                <i class="fas fa-shopping-cart mr-1"></i>
                                                수강신청
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button class="wishlist-btn active in-wishlist w-full px-4 py-2 border-2 border-red-300 text-red-600 text-sm font-medium rounded-lg hover:bg-red-50 transition-colors" 
                                                data-course-id="<?php echo $course_id; ?>" 
                                                title="위시리스트에서 제거">
                                            <i class="fas fa-heart mr-1"></i>
                                            <span class="btn-text">제거</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination would go here if needed -->
            
        <?php endif; ?>

        <!-- Related Actions -->
        <div class="mt-12 text-center">
            <div class="max-w-2xl mx-auto">
                <h3 class="text-lg font-medium text-gray-900 mb-4">더 많은 강의를 찾아보세요</h3>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="<?php echo esc_url(home_url('/courses/')); ?>" 
                       class="inline-flex items-center px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-search mr-2"></i>
                        전체 강의 보기
                    </a>
                    <a href="<?php echo esc_url(home_url('/categories/')); ?>" 
                       class="inline-flex items-center px-6 py-2 border-2 border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-tags mr-2"></i>
                        카테고리별 보기
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
// Wishlist page specific JavaScript
jQuery(document).ready(function($) {
    
    // Handle sorting
    $('#wishlist-sort').change(function() {
        var sortBy = $(this).val();
        var $container = $('#wishlist-container');
        var $items = $container.find('.wishlist-item').detach();
        
        $items.sort(function(a, b) {
            var aVal, bVal;
            
            switch(sortBy) {
                case 'title':
                    aVal = $(a).find('h3 a').text().toLowerCase();
                    bVal = $(b).find('h3 a').text().toLowerCase();
                    return aVal.localeCompare(bVal);
                    
                case 'price':
                    aVal = $(a).find('.font-bold').text().replace(/[^\d]/g, '');
                    bVal = $(b).find('.font-bold').text().replace(/[^\d]/g, '');
                    return parseInt(aVal || 0) - parseInt(bVal || 0);
                    
                case 'recent':
                default:
                    // Already in recent order
                    return 0;
            }
        });
        
        $container.append($items);
    });
    
    // Update bulk remove button state
    function updateBulkRemoveButton() {
        var $checkedItems = $('.wishlist-item input[type="checkbox"]:checked');
        var $bulkBtn = $('.bulk-remove-wishlist');
        
        if ($checkedItems.length > 0) {
            $bulkBtn.removeClass('disabled').prop('disabled', false);
            $bulkBtn.text($checkedItems.length + '개 항목 제거');
        } else {
            $bulkBtn.addClass('disabled').prop('disabled', true);
            $bulkBtn.html('<i class="fas fa-trash mr-1"></i>선택 항목 제거');
        }
    }
    
    // Listen for checkbox changes
    $(document).on('change', '.wishlist-item input[type="checkbox"]', updateBulkRemoveButton);
    $(document).on('change', '#select-all-wishlist', updateBulkRemoveButton);
    
    // Listen for wishlist changes to update UI
    $(document).on('wishlist-changed', function(e, data) {
        if (data.action === 'removed') {
            // Remove the item from the page
            var $item = $('.wishlist-item').find('input[value="' + data.courseId + '"]').closest('.wishlist-item');
            $item.fadeOut(300, function() {
                $(this).remove();
                
                // Update count
                var currentCount = parseInt($('.text-2xl.font-bold.text-blue-600').text());
                $('.text-2xl.font-bold.text-blue-600').text((currentCount - 1) + '개');
                
                // Show empty state if no items left
                if ($('.wishlist-item').length === 0) {
                    location.reload();
                }
            });
        }
    });
    
    // Initial button state
    updateBulkRemoveButton();
});
</script>

<?php get_footer(); ?>