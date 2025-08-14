<?php
/**
 * Single Course Template - Inflearn style
 *
 * @package LectusAcademy
 */

get_header();

while (have_posts()) :
    the_post();
    
    $course_id = get_the_ID();
    $lessons = lectus_academy_get_course_lessons($course_id);
    $lesson_count = count($lessons);
    $enrolled_count = lectus_academy_get_enrolled_count($course_id);
    $is_enrolled = lectus_academy_is_enrolled($course_id);
    $course_progress = lectus_academy_get_course_progress($course_id);
    
    // Get course meta
    $duration = get_post_meta($course_id, '_course_duration', true);
    $difficulty = get_post_meta($course_id, '_course_difficulty', true);
    $certificate = get_post_meta($course_id, '_course_certificate', true);
    $access_duration = get_post_meta($course_id, '_course_access_duration', true) ?: 365;
?>

<!-- Course Hero Section -->
<div class="course-hero">
    <div class="container">
        <div class="course-hero-inner">
            <div class="course-hero-content">
                <!-- Breadcrumb -->
                <nav class="course-breadcrumb">
                    <a href="<?php echo esc_url(home_url()); ?>">홈</a>
                    <span>/</span>
                    <?php
                    $categories = get_the_terms($course_id, 'course_category');
                    if ($categories && !is_wp_error($categories)) :
                        $category = $categories[0];
                    ?>
                    <a href="<?php echo esc_url(get_term_link($category)); ?>">
                        <?php echo esc_html($category->name); ?>
                    </a>
                    <span>/</span>
                    <?php endif; ?>
                    <span><?php the_title(); ?></span>
                </nav>
                
                <!-- Course Title -->
                <h1 class="course-hero-title"><?php the_title(); ?></h1>
                
                <!-- Course Meta -->
                <div class="course-hero-meta">
                    <div class="course-hero-instructor">
                        <div class="instructor-avatar">
                            <img src="<?php echo lectus_academy_get_instructor_avatar(get_the_ID()); ?>" 
                                 alt="<?php echo lectus_academy_get_instructor_name(get_the_ID()); ?>">
                        </div>
                        <div class="instructor-info">
                            <span class="instructor-label">강사</span>
                            <span class="instructor-name">
                                <?php echo lectus_academy_get_instructor_name(get_the_ID()); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="course-hero-rating">
                        <div class="rating-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <span>4.8 (128개 평가)</span>
                    </div>
                    
                    <div class="course-hero-stats">
                        <span><i class="fas fa-users"></i> <?php echo number_format($enrolled_count); ?>명 수강</span>
                        <span><i class="fas fa-heart"></i> 256명</span>
                    </div>
                </div>
                
                <!-- Course Tags -->
                <div class="course-tags">
                    <?php
                    $tags = get_the_terms($course_id, 'course_tag');
                    if ($tags && !is_wp_error($tags)) :
                        foreach ($tags as $tag) :
                    ?>
                    <span class="course-tag">#<?php echo esc_html($tag->name); ?></span>
                    <?php 
                        endforeach;
                    endif;
                    ?>
                </div>
            </div>
            
            <!-- Purchase Card -->
            <div class="course-purchase-card">
                <?php if (has_post_thumbnail()) : ?>
                <div class="course-purchase-thumbnail">
                    <?php the_post_thumbnail('course-thumbnail'); ?>
                </div>
                <?php endif; ?>
                
                <div class="course-purchase-content">
                    <div class="purchase-price">
                        <span class="price-label">가격</span>
                        <span class="price-amount">
                            <?php echo lectus_academy_get_course_price($course_id); ?>
                        </span>
                    </div>
                    
                    <div class="purchase-buttons">
                        <?php if ($is_enrolled) : ?>
                            <a href="<?php echo esc_url(home_url('/my-courses')); ?>" 
                               class="btn btn-primary btn-lg btn-block">
                                <i class="fas fa-play"></i> 학습하기
                            </a>
                            <div class="progress-info">
                                <span>진도율: <?php echo $course_progress; ?>%</span>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $course_progress; ?>%"></div>
                                </div>
                            </div>
                        <?php else : ?>
                            <button class="btn btn-primary btn-lg btn-block enroll-btn" 
                                    data-course-id="<?php echo $course_id; ?>">
                                <i class="fas fa-shopping-cart"></i> 수강신청
                            </button>
                            <button class="btn btn-outline btn-lg btn-block">
                                <i class="fas fa-heart"></i> 위시리스트
                            </button>
                        <?php endif; ?>
                    </div>
                    
                    <div class="course-includes">
                        <h4>포함 내용</h4>
                        <ul class="course-includes-list">
                            <li><i class="fas fa-video"></i> 강의 <?php echo $lesson_count; ?>개</li>
                            <li><i class="fas fa-clock"></i> 총 <?php echo lectus_academy_format_duration($duration ?: 180); ?></li>
                            <li><i class="fas fa-calendar"></i> 수강기한 <?php echo $access_duration; ?>일</li>
                            <li><i class="fas fa-mobile-alt"></i> 모바일 지원</li>
                            <?php if ($certificate) : ?>
                            <li><i class="fas fa-certificate"></i> 수료증 발급</li>
                            <?php endif; ?>
                            <li><i class="fas fa-file-download"></i> 강의자료 제공</li>
                        </ul>
                    </div>
                    
                    <div class="share-buttons">
                        <button class="share-btn" data-network="facebook">
                            <i class="fab fa-facebook-f"></i>
                        </button>
                        <button class="share-btn" data-network="twitter">
                            <i class="fab fa-twitter"></i>
                        </button>
                        <button class="share-btn" data-network="linkedin">
                            <i class="fab fa-linkedin-in"></i>
                        </button>
                        <button class="share-btn" data-network="link">
                            <i class="fas fa-link"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Course Content -->
<div class="course-content-area">
    <div class="container">
        <div class="course-content-grid">
            <div class="course-main-content">
                <!-- Tabs Navigation -->
                <div class="course-tabs">
                    <ul class="tab-nav">
                        <li class="active">
                            <a href="#overview" data-tab="overview">
                                <i class="fas fa-info-circle"></i> 강의소개
                            </a>
                        </li>
                        <li>
                            <a href="#curriculum" data-tab="curriculum">
                                <i class="fas fa-list"></i> 커리큘럼
                            </a>
                        </li>
                        <li>
                            <a href="#reviews" data-tab="reviews">
                                <i class="fas fa-star"></i> 수강평
                            </a>
                        </li>
                        <li>
                            <a href="#qa" data-tab="qa">
                                <i class="fas fa-question-circle"></i> Q&A
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Overview Tab -->
                    <div id="overview" class="tab-pane active">
                        <div class="course-description">
                            <?php the_content(); ?>
                        </div>
                        
                        <!-- What You'll Learn -->
                        <div class="course-section">
                            <h2><i class="fas fa-check-circle"></i> 이런 걸 배워요</h2>
                            <ul class="learning-goals">
                                <li><i class="fas fa-check"></i> WordPress 플러그인 개발 기초</li>
                                <li><i class="fas fa-check"></i> LMS 시스템 구축 방법</li>
                                <li><i class="fas fa-check"></i> WooCommerce 연동 기법</li>
                                <li><i class="fas fa-check"></i> 커스텀 포스트 타입 활용</li>
                            </ul>
                        </div>
                        
                        <!-- Requirements -->
                        <div class="course-section">
                            <h2><i class="fas fa-list-ul"></i> 선수 지식</h2>
                            <ul class="requirements-list">
                                <li>기초적인 PHP 프로그래밍 지식</li>
                                <li>WordPress 기본 사용법</li>
                                <li>HTML/CSS 기초 지식</li>
                            </ul>
                        </div>
                        
                        <!-- Target Audience -->
                        <div class="course-section">
                            <h2><i class="fas fa-user-graduate"></i> 이런 분들께 추천해요</h2>
                            <ul class="target-list">
                                <li><i class="fas fa-user"></i> WordPress 개발을 시작하려는 개발자</li>
                                <li><i class="fas fa-user"></i> 온라인 교육 플랫폼을 구축하려는 기획자</li>
                                <li><i class="fas fa-user"></i> LMS 시스템에 관심있는 학습자</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Curriculum Tab -->
                    <div id="curriculum" class="tab-pane">
                        <div class="curriculum-header-info">
                            <p>총 <?php echo $lesson_count; ?>개 수업 · <?php echo lectus_academy_format_duration($duration ?: 180); ?></p>
                        </div>
                        
                        <div class="curriculum-sections">
                            <div class="curriculum-section open">
                                <div class="curriculum-header" onclick="toggleSection(this)">
                                    <div class="curriculum-title">
                                        <i class="fas fa-chevron-down"></i>
                                        <span>섹션 1. 시작하기</span>
                                    </div>
                                    <div class="curriculum-meta">
                                        <span><?php echo $lesson_count; ?>강</span>
                                    </div>
                                </div>
                                
                                <div class="curriculum-list" style="display: block;">
                                    <?php 
                                    $lesson_num = 1;
                                    foreach ($lessons as $lesson) : 
                                        $lesson_duration = get_post_meta($lesson->ID, '_lesson_duration', true) ?: 10;
                                        $is_preview = get_post_meta($lesson->ID, '_is_preview', true);
                                        $is_completed = false;
                                        
                                        if ($is_enrolled) {
                                            // Check if lesson is completed
                                            global $wpdb;
                                            $table_name = $wpdb->prefix . 'lectus_progress';
                                            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
                                                $is_completed = $wpdb->get_var($wpdb->prepare(
                                                    "SELECT status FROM $table_name WHERE user_id = %d AND lesson_id = %d",
                                                    get_current_user_id(),
                                                    $lesson->ID
                                                )) === 'completed';
                                            }
                                        }
                                    ?>
                                    <div class="curriculum-item <?php echo $is_completed ? 'completed' : ''; ?>">
                                        <div class="lesson-info">
                                            <span class="lesson-number">
                                                <?php if ($is_completed) : ?>
                                                    <i class="fas fa-check"></i>
                                                <?php else : ?>
                                                    <?php echo $lesson_num; ?>
                                                <?php endif; ?>
                                            </span>
                                            <span class="lesson-title">
                                                <?php if ($is_enrolled || $is_preview) : ?>
                                                    <a href="<?php echo get_permalink($lesson->ID); ?>">
                                                        <?php echo esc_html($lesson->post_title); ?>
                                                    </a>
                                                <?php else : ?>
                                                    <?php echo esc_html($lesson->post_title); ?>
                                                    <i class="fas fa-lock"></i>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <div class="lesson-meta">
                                            <?php if ($is_preview) : ?>
                                            <span class="preview-badge">미리보기</span>
                                            <?php endif; ?>
                                            <span><i class="fas fa-play-circle"></i> <?php echo $lesson_duration; ?>분</span>
                                        </div>
                                    </div>
                                    <?php 
                                    $lesson_num++;
                                    endforeach; 
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reviews Tab -->
                    <div id="reviews" class="tab-pane">
                        <div class="reviews-header">
                            <h3>수강평</h3>
                            <?php if ($is_enrolled) : ?>
                            <button class="btn btn-primary">수강평 작성</button>
                            <?php endif; ?>
                        </div>
                        
                        <div class="reviews-summary">
                            <div class="rating-overview">
                                <div class="rating-score">
                                    <span class="score-number">4.8</span>
                                    <div class="rating-stars">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                    </div>
                                    <span class="review-count">128개의 수강평</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="reviews-list">
                            <!-- Sample Review -->
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="reviewer-info">
                                        <?php echo get_avatar(1, 48); ?>
                                        <div>
                                            <span class="reviewer-name">김학생</span>
                                            <div class="review-rating">
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="review-date">2024년 1월 15일</span>
                                </div>
                                <div class="review-content">
                                    <p>정말 유익한 강의였습니다. 기초부터 실무까지 체계적으로 배울 수 있어서 좋았어요.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Q&A Tab -->
                    <div id="qa" class="tab-pane">
                        <div class="qa-header">
                            <h3>질문 & 답변</h3>
                            <?php if ($is_enrolled) : ?>
                            <button class="btn btn-primary">질문하기</button>
                            <?php endif; ?>
                        </div>
                        
                        <div class="qa-list">
                            <p class="no-qa">아직 등록된 질문이 없습니다.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="course-sidebar">
                <!-- Instructor Info -->
                <div class="sidebar-card instructor-card">
                    <h3>강사 소개</h3>
                    <div class="instructor-profile">
                        <img src="<?php echo lectus_academy_get_instructor_avatar(get_the_ID(), 80); ?>" 
                             alt="<?php echo lectus_academy_get_instructor_name(get_the_ID()); ?>">
                        <h4><?php echo lectus_academy_get_instructor_name(get_the_ID()); ?></h4>
                        <p class="instructor-bio">
                            <?php 
                            $author_id = get_post_field('post_author', $course_id);
                            $bio = get_the_author_meta('description', $author_id);
                            echo $bio ?: '전문 강사입니다.'; 
                            ?>
                        </p>
                    </div>
                </div>
                
                <!-- Related Courses -->
                <div class="sidebar-card">
                    <h3>연관 강의</h3>
                    <div class="related-courses">
                        <?php
                        $related_args = array(
                            'post_type' => 'coursesingle',
                            'posts_per_page' => 3,
                            'post__not_in' => array($course_id),
                            'orderby' => 'rand'
                        );
                        
                        $related_query = new WP_Query($related_args);
                        
                        if ($related_query->have_posts()) :
                            while ($related_query->have_posts()) : $related_query->the_post();
                        ?>
                        <div class="related-course-item">
                            <a href="<?php the_permalink(); ?>">
                                <?php if (has_post_thumbnail()) : ?>
                                <div class="related-course-thumb">
                                    <?php the_post_thumbnail('thumbnail'); ?>
                                </div>
                                <?php endif; ?>
                                <div class="related-course-info">
                                    <h4><?php the_title(); ?></h4>
                                    <span class="related-course-price">
                                        <?php echo lectus_academy_get_course_price(get_the_ID()); ?>
                                    </span>
                                </div>
                            </a>
                        </div>
                        <?php 
                            endwhile;
                            wp_reset_postdata();
                        endif;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Tab switching
document.addEventListener('DOMContentLoaded', function() {
    const tabLinks = document.querySelectorAll('.tab-nav a');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all
            tabLinks.forEach(l => l.parentElement.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));
            
            // Add active class to clicked
            this.parentElement.classList.add('active');
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });
});

// Curriculum section toggle
function toggleSection(header) {
    const section = header.parentElement;
    section.classList.toggle('open');
    const list = section.querySelector('.curriculum-list');
    const icon = header.querySelector('.fas');
    
    if (section.classList.contains('open')) {
        list.style.display = 'block';
        icon.classList.remove('fa-chevron-right');
        icon.classList.add('fa-chevron-down');
    } else {
        list.style.display = 'none';
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-right');
    }
}

// Enrollment
jQuery(document).ready(function($) {
    $('.enroll-btn').on('click', function() {
        const courseId = $(this).data('course-id');
        
        $.ajax({
            url: lectusAcademy.ajaxurl,
            type: 'POST',
            data: {
                action: 'lectus_academy_enroll',
                course_id: courseId,
                nonce: lectusAcademy.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
});
</script>

<?php
endwhile;

get_footer();
?>