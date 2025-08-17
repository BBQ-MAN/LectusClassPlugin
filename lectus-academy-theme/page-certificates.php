<?php
/**
 * Template Name: Certificates
 * Template for displaying user certificates
 *
 * @package LectusAcademy
 */

get_header();

// Check if user is logged in
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$current_user_id = get_current_user_id();
?>

<main id="primary" class="site-main">
    <div class="min-h-screen bg-gradient-to-b from-gray-50 to-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            
            <!-- Page Header -->
            <header class="mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">나의 수료증</h1>
                <p class="text-lg text-gray-600">
                    수강 완료한 강의의 수료증을 확인하고 다운로드할 수 있습니다.
                </p>
            </header>

            <!-- Certificates Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Main Content -->
                <div class="lg:col-span-2">
                    
                    <?php
                    // Get user's certificates
                    global $wpdb;
                    $certificates_table = $wpdb->prefix . 'lectus_certificates';
                    
                    // Check if certificates table exists
                    if ($wpdb->get_var("SHOW TABLES LIKE '$certificates_table'") == $certificates_table) {
                        $certificates = $wpdb->get_results($wpdb->prepare(
                            "SELECT * FROM $certificates_table WHERE user_id = %d ORDER BY generated_at DESC",
                            $current_user_id
                        ));
                    } else {
                        $certificates = array();
                    }
                    
                    if ($certificates) : ?>
                        
                        <!-- Certificates List -->
                        <div class="space-y-6">
                            <?php foreach ($certificates as $certificate) : 
                                $course = get_post($certificate->course_id);
                                if (!$course) continue;
                                
                                // Get course thumbnail
                                $thumbnail_url = get_the_post_thumbnail_url($course->ID, 'medium');
                                if (!$thumbnail_url) {
                                    $thumbnail_url = 'https://via.placeholder.com/300x200?text=No+Image';
                                }
                                
                                // Get course category
                                $categories = get_the_terms($course->ID, 'course-category');
                                $category_name = $categories && !is_wp_error($categories) ? $categories[0]->name : '미분류';
                                
                                // Format date
                                $issue_date = date('Y년 m월 d일', strtotime($certificate->generated_at));
                            ?>
                                
                                <article class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow overflow-hidden">
                                    <div class="flex flex-col md:flex-row">
                                        <!-- Course Thumbnail -->
                                        <div class="md:w-48 h-48 md:h-auto">
                                            <img src="<?php echo esc_url($thumbnail_url); ?>" 
                                                 alt="<?php echo esc_attr($course->post_title); ?>" 
                                                 class="w-full h-full object-cover">
                                        </div>
                                        
                                        <!-- Certificate Info -->
                                        <div class="flex-1 p-6">
                                            <!-- Certificate Badge -->
                                            <div class="flex items-center gap-2 mb-3">
                                                <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    수료 완료
                                                </span>
                                                <span class="text-sm text-gray-500"><?php echo esc_html($category_name); ?></span>
                                            </div>
                                            
                                            <!-- Course Title -->
                                            <h2 class="text-xl font-bold text-gray-900 mb-2">
                                                <?php echo esc_html($course->post_title); ?>
                                            </h2>
                                            
                                            <!-- Certificate Details -->
                                            <div class="space-y-2 mb-4">
                                                <p class="text-sm text-gray-600">
                                                    <span class="font-medium">수료증 번호:</span> 
                                                    <span class="font-mono text-blue-600"><?php echo esc_html($certificate->certificate_number); ?></span>
                                                </p>
                                                <p class="text-sm text-gray-600">
                                                    <span class="font-medium">발급일:</span> 
                                                    <?php echo esc_html($issue_date); ?>
                                                </p>
                                            </div>
                                            
                                            <!-- Actions -->
                                            <div class="flex flex-wrap gap-3">
                                                <a href="<?php echo esc_url(add_query_arg(array(
                                                    'action' => 'download_certificate',
                                                    'certificate_id' => $certificate->id,
                                                    'nonce' => wp_create_nonce('download_certificate_' . $certificate->id)
                                                ), admin_url('admin-ajax.php'))); ?>" 
                                                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                    PDF 다운로드
                                                </a>
                                                <a href="<?php echo esc_url(add_query_arg(array(
                                                    'action' => 'view_certificate',
                                                    'certificate_id' => $certificate->id
                                                ), home_url('/certificate-view'))); ?>" 
                                                   class="inline-flex items-center px-4 py-2 bg-white text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    온라인 보기
                                                </a>
                                                <button onclick="shareCertificate('<?php echo esc_js($certificate->certificate_number); ?>')" 
                                                        class="inline-flex items-center px-4 py-2 bg-white text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m9.032 4.026a9.001 9.001 0 01-7.432 0m9.032-4.026A9.001 9.001 0 0112 3c-4.474 0-8.268 2.943-9.543 7a9.97 9.97 0 011.827 3.342M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    </svg>
                                                    공유하기
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                                
                            <?php endforeach; ?>
                        </div>
                        
                    <?php else : ?>
                        
                        <!-- No Certificates -->
                        <div class="bg-white rounded-lg shadow-md p-12 text-center">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-gray-100 rounded-full mb-6">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-3">아직 수료증이 없습니다</h2>
                            <p class="text-gray-600 mb-6">
                                강의를 완주하고 수료 기준을 달성하면<br>
                                수료증이 자동으로 발급됩니다.
                            </p>
                            <div class="flex justify-center gap-4">
                                <a href="<?php echo esc_url(home_url('/my-courses')); ?>" 
                                   class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                    내 강의 보기
                                </a>
                                <a href="<?php echo esc_url(get_post_type_archive_link('coursesingle')); ?>" 
                                   class="inline-flex items-center px-6 py-3 bg-white text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    강의 둘러보기
                                </a>
                            </div>
                        </div>
                        
                    <?php endif; ?>
                    
                </div>
                
                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <div class="sticky top-24 space-y-6">
                        
                        <!-- Certificate Stats -->
                        <div class="bg-white rounded-lg p-6 shadow-md">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">수료 현황</h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">총 수료증</span>
                                    <span class="text-2xl font-bold text-blue-600"><?php echo count($certificates); ?></span>
                                </div>
                                <?php if ($certificates) : 
                                    $latest_cert = $certificates[0];
                                    $latest_date = date('Y.m.d', strtotime($latest_cert->generated_at));
                                ?>
                                <div class="pt-4 border-t border-gray-200">
                                    <p class="text-sm text-gray-600 mb-1">최근 수료</p>
                                    <p class="font-medium text-gray-900"><?php echo esc_html($latest_date); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Certificate Info -->
                        <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                            <h3 class="text-lg font-semibold text-blue-900 mb-3">수료증 안내</h3>
                            <ul class="space-y-2 text-sm text-blue-800">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>수료증은 강의 진도율 80% 이상 달성 시 자동 발급됩니다</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>PDF 형식으로 다운로드 가능합니다</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>수료증 번호로 진위 확인이 가능합니다</span>
                                </li>
                            </ul>
                        </div>
                        
                        <!-- Verification -->
                        <div class="bg-white rounded-lg p-6 shadow-md">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">수료증 검증</h3>
                            <p class="text-sm text-gray-600 mb-4">
                                수료증 번호를 입력하여 진위를 확인할 수 있습니다.
                            </p>
                            <form action="<?php echo esc_url(home_url('/certificate-verify')); ?>" method="get">
                                <div class="space-y-3">
                                    <input type="text" 
                                           name="certificate_number" 
                                           placeholder="수료증 번호 입력" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           required>
                                    <button type="submit" 
                                            class="w-full px-4 py-2 bg-gray-900 text-white font-medium rounded-lg hover:bg-gray-800 transition-colors">
                                        검증하기
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</main>

<!-- Share Modal Script -->
<script>
function shareCertificate(certificateNumber) {
    const shareUrl = '<?php echo esc_url(home_url('/certificate-verify')); ?>?certificate_number=' + certificateNumber;
    const shareText = '렉투스 아카데미에서 강의를 수료했습니다! 수료증 번호: ' + certificateNumber;
    
    if (navigator.share) {
        navigator.share({
            title: '수료증 공유',
            text: shareText,
            url: shareUrl
        }).catch(console.error);
    } else {
        // Fallback: Copy to clipboard
        const tempInput = document.createElement('input');
        tempInput.value = shareUrl;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        alert('수료증 링크가 클립보드에 복사되었습니다!');
    }
}
</script>

<?php
get_footer();