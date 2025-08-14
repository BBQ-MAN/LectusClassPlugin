<?php
/**
 * The header for our theme - Inflearn style
 *
 * @package LectusAcademy
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#content"><?php esc_html_e('Skip to content', 'lectus-academy'); ?></a>

    <header id="masthead" class="site-header">
        <!-- Top Bar -->
        <div class="header-top">
            <div class="container">
                <div class="header-top-inner">
                    <div class="header-top-left">
                        <a href="#" class="top-link">
                            <i class="fas fa-graduation-cap"></i>
                            <?php esc_html_e('교육', 'lectus-academy'); ?>
                        </a>
                        <a href="#" class="top-link">
                            <i class="fas fa-briefcase"></i>
                            <?php esc_html_e('커리어', 'lectus-academy'); ?>
                        </a>
                    </div>
                    <div class="header-top-right">
                        <?php if (is_user_logged_in()) : 
                            $current_user = wp_get_current_user();
                        ?>
                            <a href="<?php echo esc_url(home_url('/my-courses')); ?>" class="top-link">
                                <?php esc_html_e('내 강의', 'lectus-academy'); ?>
                            </a>
                            <div class="user-dropdown">
                                <a href="#" class="user-dropdown-toggle">
                                    <?php echo get_avatar($current_user->ID, 28); ?>
                                    <span><?php echo esc_html($current_user->display_name); ?></span>
                                    <i class="fas fa-chevron-down"></i>
                                </a>
                                <div class="user-dropdown-menu">
                                    <a href="<?php echo esc_url(home_url('/student-dashboard')); ?>">
                                        <i class="fas fa-tachometer-alt"></i>
                                        <?php esc_html_e('대시보드', 'lectus-academy'); ?>
                                    </a>
                                    <a href="<?php echo esc_url(home_url('/my-courses')); ?>">
                                        <i class="fas fa-book"></i>
                                        <?php esc_html_e('내 강의', 'lectus-academy'); ?>
                                    </a>
                                    <a href="<?php echo esc_url(home_url('/certificates')); ?>">
                                        <i class="fas fa-certificate"></i>
                                        <?php esc_html_e('수료증', 'lectus-academy'); ?>
                                    </a>
                                    <a href="<?php echo esc_url(get_edit_profile_url()); ?>">
                                        <i class="fas fa-user-cog"></i>
                                        <?php esc_html_e('프로필', 'lectus-academy'); ?>
                                    </a>
                                    <?php if (current_user_can('manage_options')) : ?>
                                    <a href="<?php echo esc_url(admin_url()); ?>">
                                        <i class="fas fa-cog"></i>
                                        <?php esc_html_e('관리자', 'lectus-academy'); ?>
                                    </a>
                                    <?php endif; ?>
                                    <div class="dropdown-divider"></div>
                                    <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <?php esc_html_e('로그아웃', 'lectus-academy'); ?>
                                    </a>
                                </div>
                            </div>
                        <?php else : ?>
                            <a href="<?php echo esc_url(wp_login_url()); ?>" class="top-link">
                                <?php esc_html_e('로그인', 'lectus-academy'); ?>
                            </a>
                            <a href="<?php echo esc_url(wp_registration_url()); ?>" class="btn btn-primary btn-sm">
                                <?php esc_html_e('회원가입', 'lectus-academy'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Header -->
        <div class="header-main">
            <div class="container">
                <div class="header-inner">
                    <!-- Logo -->
                    <div class="site-branding">
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo">
                            <?php if (has_custom_logo()) : ?>
                                <?php the_custom_logo(); ?>
                            <?php else : ?>
                                <i class="fas fa-graduation-cap" style="color: #30b2e5;"></i>
                                <span><?php bloginfo('name'); ?></span>
                            <?php endif; ?>
                        </a>
                    </div>

                    <!-- Search Bar -->
                    <div class="header-search">
                        <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
                            <input type="search" 
                                   class="search-input" 
                                   placeholder="<?php esc_attr_e('배우고 싶은 지식을 입력해보세요', 'lectus-academy'); ?>" 
                                   value="<?php echo get_search_query(); ?>" 
                                   name="s">
                            <button type="submit" class="search-submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>

                    <!-- Mobile Menu Toggle -->
                    <button id="mobile-menu-toggle" aria-label="<?php esc_attr_e('Menu', 'lectus-academy'); ?>">
                        <span></span>
                    </button>
                    
                    <!-- Header Actions -->
                    <div class="header-actions">
                        <a href="<?php echo esc_url(get_post_type_archive_link('coursesingle')); ?>" class="btn btn-ghost">
                            <?php esc_html_e('강의', 'lectus-academy'); ?>
                        </a>
                        <a href="<?php echo esc_url(home_url('/roadmap')); ?>" class="btn btn-ghost">
                            <?php esc_html_e('로드맵', 'lectus-academy'); ?>
                        </a>
                        <a href="<?php echo esc_url(home_url('/mentoring')); ?>" class="btn btn-ghost">
                            <?php esc_html_e('멘토링', 'lectus-academy'); ?>
                        </a>
                        <a href="<?php echo esc_url(home_url('/community')); ?>" class="btn btn-ghost">
                            <?php esc_html_e('커뮤니티', 'lectus-academy'); ?>
                        </a>
                        <a href="<?php echo esc_url(home_url('/apply-instructor')); ?>" class="btn btn-outline">
                            <?php esc_html_e('지식공유참여', 'lectus-academy'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Navigation -->
        <nav class="category-nav">
            <div class="container">
                <ul class="category-list">
                    <li class="category-item">
                        <a href="<?php echo esc_url(get_post_type_archive_link('coursesingle')); ?>" class="category-link active">
                            <i class="fas fa-th category-icon"></i>
                            <span><?php esc_html_e('전체', 'lectus-academy'); ?></span>
                        </a>
                    </li>
                    <?php
                    // Get course categories
                    $categories = get_terms(array(
                        'taxonomy' => 'course_category',
                        'hide_empty' => false,
                        'number' => 10,
                    ));
                    
                    $category_icons = array(
                        'development' => 'fa-code',
                        'design' => 'fa-palette',
                        'business' => 'fa-briefcase',
                        'marketing' => 'fa-bullhorn',
                        'it' => 'fa-server',
                        'photo' => 'fa-camera',
                        'music' => 'fa-music',
                        'language' => 'fa-language',
                    );
                    
                    if ($categories && !is_wp_error($categories)) :
                        foreach ($categories as $category) :
                            $icon = isset($category_icons[$category->slug]) ? $category_icons[$category->slug] : 'fa-folder';
                    ?>
                    <li class="category-item">
                        <a href="<?php echo esc_url(get_term_link($category)); ?>" class="category-link">
                            <i class="fas <?php echo esc_attr($icon); ?> category-icon"></i>
                            <span><?php echo esc_html($category->name); ?></span>
                        </a>
                    </li>
                    <?php 
                        endforeach;
                    endif; 
                    ?>
                    <li class="category-item">
                        <a href="<?php echo esc_url(home_url('/all-categories')); ?>" class="category-link">
                            <i class="fas fa-ellipsis-h category-icon"></i>
                            <span><?php esc_html_e('더보기', 'lectus-academy'); ?></span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    
    <!-- Mobile Menu -->
    <div class="mobile-menu">
        <div class="mobile-menu-inner">
            <div class="mobile-search">
                <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
                    <input type="search" 
                           class="search-input" 
                           placeholder="<?php esc_attr_e('검색어를 입력하세요', 'lectus-academy'); ?>" 
                           value="<?php echo get_search_query(); ?>" 
                           name="s">
                    <button type="submit" class="search-submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            
            <ul class="mobile-nav">
                <?php if (is_user_logged_in()) : 
                    $current_user = wp_get_current_user();
                ?>
                <li class="mobile-user-info">
                    <div class="mobile-user-header">
                        <?php echo get_avatar($current_user->ID, 40); ?>
                        <span><?php echo esc_html($current_user->display_name); ?></span>
                    </div>
                </li>
                <li><a href="<?php echo esc_url(home_url('/student-dashboard')); ?>">
                    <i class="fas fa-tachometer-alt"></i> <?php esc_html_e('대시보드', 'lectus-academy'); ?>
                </a></li>
                <li><a href="<?php echo esc_url(home_url('/my-courses')); ?>">
                    <i class="fas fa-book"></i> <?php esc_html_e('내 강의', 'lectus-academy'); ?>
                </a></li>
                <?php else : ?>
                <li><a href="<?php echo esc_url(wp_login_url()); ?>">
                    <i class="fas fa-sign-in-alt"></i> <?php esc_html_e('로그인', 'lectus-academy'); ?>
                </a></li>
                <li><a href="<?php echo esc_url(wp_registration_url()); ?>">
                    <i class="fas fa-user-plus"></i> <?php esc_html_e('회원가입', 'lectus-academy'); ?>
                </a></li>
                <?php endif; ?>
                
                <li class="menu-divider"></li>
                
                <li><a href="<?php echo esc_url(get_post_type_archive_link('coursesingle')); ?>">
                    <?php esc_html_e('강의', 'lectus-academy'); ?>
                </a></li>
                <li><a href="<?php echo esc_url(home_url('/roadmap')); ?>">
                    <?php esc_html_e('로드맵', 'lectus-academy'); ?>
                </a></li>
                <li><a href="<?php echo esc_url(home_url('/mentoring')); ?>">
                    <?php esc_html_e('멘토링', 'lectus-academy'); ?>
                </a></li>
                <li><a href="<?php echo esc_url(home_url('/community')); ?>">
                    <?php esc_html_e('커뮤니티', 'lectus-academy'); ?>
                </a></li>
                
                <li class="menu-divider"></li>
                
                <li class="has-children">
                    <a href="#"><?php esc_html_e('카테고리', 'lectus-academy'); ?></a>
                    <ul class="sub-menu">
                        <?php
                        $categories = get_terms(array(
                            'taxonomy' => 'course_category',
                            'hide_empty' => false,
                        ));
                        
                        if ($categories && !is_wp_error($categories)) :
                            foreach ($categories as $category) :
                        ?>
                        <li><a href="<?php echo esc_url(get_term_link($category)); ?>">
                            <?php echo esc_html($category->name); ?>
                        </a></li>
                        <?php 
                            endforeach;
                        endif; 
                        ?>
                    </ul>
                </li>
                
                <?php if (is_user_logged_in()) : ?>
                <li class="menu-divider"></li>
                <li><a href="<?php echo esc_url(wp_logout_url(home_url())); ?>">
                    <i class="fas fa-sign-out-alt"></i> <?php esc_html_e('로그아웃', 'lectus-academy'); ?>
                </a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <div id="content" class="site-content">