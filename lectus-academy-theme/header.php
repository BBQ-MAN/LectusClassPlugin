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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Browser compatibility scripts -->
    <!--[if lt IE 9]>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    
    <!-- CSS Grid polyfill for IE11 -->
    <script>
        if (!window.CSS || !CSS.supports || !CSS.supports('display', 'grid')) {
            document.documentElement.className += ' no-grid';
        }
    </script>
    
    <?php wp_head(); ?>
</head>

<body <?php body_class('bg-gray-50'); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site min-h-screen">
    <a class="skip-link screen-reader-text" href="#content"><?php esc_html_e('Skip to content', 'lectus-academy'); ?></a>

    <header id="masthead" class="sticky top-0 z-50 bg-white shadow-sm">
        <!-- Top Bar -->
        <div class="header-top bg-gray-100 border-b border-gray-200">
            <div class="container mx-auto px-4">
                <div class="header-top-inner flex justify-between items-center py-2">
                    <div class="header-top-left flex gap-2 md:gap-4 text-xs md:text-sm overflow-x-auto scrollbar-hide">
                        <?php
                        // Display Top Menu if assigned
                        if (has_nav_menu('top-menu')) {
                            wp_nav_menu(array(
                                'theme_location' => 'top-menu',
                                'container' => false,
                                'menu_class' => 'flex gap-4',
                                'depth' => 1,
                                'fallback_cb' => false,
                                'link_before' => '<i class="fas fa-graduation-cap"></i> ',
                                'items_wrap' => '%3$s',
                                'walker' => new Lectus_Top_Menu_Walker(),
                            ));
                        } else {
                            // Fallback menu items
                            ?>
                            <a href="#" class="top-link text-sm text-gray-600 hover:text-lectus-primary transition-colors">
                                <i class="fas fa-graduation-cap"></i>
                                <?php esc_html_e('교육', 'lectus-academy'); ?>
                            </a>
                            <a href="#" class="top-link text-sm text-gray-600 hover:text-lectus-primary transition-colors">
                                <i class="fas fa-briefcase"></i>
                                <?php esc_html_e('커리어', 'lectus-academy'); ?>
                            </a>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="header-top-right flex items-center gap-2 md:gap-4 flex-shrink-0">
                        <?php if (is_user_logged_in()) : 
                            $current_user = wp_get_current_user();
                        ?>
                            <div class="user-dropdown relative">
                                <a href="#" class="user-dropdown-toggle flex items-center gap-2 text-sm text-gray-700 hover:text-lectus-primary transition-colors">
                                    <?php echo get_avatar($current_user->ID, 28); ?>
                                    <span><?php echo esc_html($current_user->display_name); ?></span>
                                    <i class="fas fa-chevron-down"></i>
                                </a>
                                <div class="user-dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 hidden">
                                    <a href="<?php echo esc_url(home_url('/student-dashboard')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-book"></i>
                                        <?php esc_html_e('내 강의실', 'lectus-academy'); ?>
                                    </a>
                                    <a href="<?php echo esc_url(home_url('/certificates')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-certificate"></i>
                                        <?php esc_html_e('수료증', 'lectus-academy'); ?>
                                    </a>
                                    <a href="<?php echo esc_url(get_edit_profile_url()); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-user-cog"></i>
                                        <?php esc_html_e('프로필', 'lectus-academy'); ?>
                                    </a>
                                    <?php if (current_user_can('manage_options')) : ?>
                                    <a href="<?php echo esc_url(admin_url()); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-cog"></i>
                                        <?php esc_html_e('관리자', 'lectus-academy'); ?>
                                    </a>
                                    <?php endif; ?>
                                    <div class="dropdown-divider border-t border-gray-200 my-2"></div>
                                    <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <?php esc_html_e('로그아웃', 'lectus-academy'); ?>
                                    </a>
                                </div>
                            </div>
                        <?php else : ?>
                            <a href="<?php echo esc_url(wp_login_url()); ?>" class="top-link text-sm text-gray-600 hover:text-lectus-primary transition-colors">
                                <?php esc_html_e('로그인', 'lectus-academy'); ?>
                            </a>
                            <a href="<?php echo esc_url(wp_registration_url()); ?>" class="btn btn-primary text-sm">
                                <?php esc_html_e('회원가입', 'lectus-academy'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Header -->
        <div class="header-main py-3 md:py-4">
            <div class="container mx-auto px-4">
                <div class="header-inner flex items-center gap-2 md:gap-6">
                    <!-- Logo - 고정 폭 -->
                    <div class="site-branding flex-shrink-0 w-32 sm:w-40">
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo flex items-center gap-2 text-lg md:text-2xl font-bold text-lectus-primary">
                            <?php if (has_custom_logo()) : ?>
                                <?php the_custom_logo(); ?>
                            <?php else : ?>
                                <i class="fas fa-graduation-cap text-blue-500 text-sm md:text-base"></i>
                                <span class="site-name"><?php bloginfo('name'); ?></span>
                            <?php endif; ?>
                        </a>
                    </div>

                    <!-- Search Bar - 480px 이상에서 표시 -->
                    <div class="header-search flex-1 hidden sm:flex">
                        <form role="search" method="get" class="search-form relative w-full" action="<?php echo esc_url(home_url('/')); ?>">
                            <input type="search" 
                                   class="search-input w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lectus-primary focus:border-transparent" 
                                   placeholder="<?php esc_attr_e('배우고 싶은 지식을 입력해보세요', 'lectus-academy'); ?>" 
                                   value="<?php echo get_search_query(); ?>" 
                                   name="s">
                            <button type="submit" class="search-submit absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-lectus-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    
                    <!-- Mobile Controls - 480px 미만에서만 표시 -->
                    <div class="mobile-controls flex items-center gap-2 sm:hidden">
                        <!-- Mobile Search Toggle -->
                        <button id="mobile-search-toggle" class="p-2 text-gray-700 hover:text-lectus-primary border border-gray-300 rounded" aria-label="<?php esc_attr_e('Search', 'lectus-academy'); ?>">
                            <i class="fas fa-search text-lg"></i>
                        </button>
                        
                        <!-- Mobile Menu Toggle -->
                        <button id="mobile-menu-toggle" class="p-2 flex flex-col items-center justify-center w-10 h-10 border-2 border-gray-800 rounded bg-white shadow-sm" aria-label="<?php esc_attr_e('Menu', 'lectus-academy'); ?>">
                            <div class="w-5 h-px bg-gray-900 mb-1"></div>
                            <div class="w-5 h-px bg-gray-900 mb-1"></div>
                            <div class="w-5 h-px bg-gray-900"></div>
                        </button>
                    </div>
                    
                    <!-- Header Actions - 고정 폭 -->
                    <div class="header-actions flex-shrink-0 flex items-center gap-3">
                        <!-- 480px-767px: 햄버거 메뉴만 표시 -->
                        <div class="sm:block md:hidden">
                            <button id="tablet-menu-toggle" class="p-2 flex flex-col items-center justify-center w-10 h-10 border-2 border-gray-800 rounded bg-white shadow-sm" aria-label="<?php esc_attr_e('Menu', 'lectus-academy'); ?>">
                                <div class="w-5 h-px bg-gray-900 mb-1"></div>
                                <div class="w-5 h-px bg-gray-900 mb-1"></div>
                                <div class="w-5 h-px bg-gray-900"></div>
                            </button>
                        </div>
                        
                        <!-- 768px 이상: 전체 메뉴 표시 -->
                        <div class="hidden md:flex items-center gap-3">
                        <?php
                        // Display Main Menu if assigned
                        if (has_nav_menu('main-menu')) {
                            wp_nav_menu(array(
                                'theme_location' => 'main-menu',
                                'container' => false,
                                'menu_class' => 'flex items-center gap-3',
                                'depth' => 1,
                                'fallback_cb' => false,
                                'items_wrap' => '%3$s',
                                'walker' => new Lectus_Main_Menu_Walker(),
                            ));
                        } else {
                            // Fallback menu items
                            ?>
                            <a href="<?php echo esc_url(get_post_type_archive_link('coursesingle')); ?>" class="btn btn-ghost text-gray-700 hover:text-lectus-primary transition-colors">
                                <?php esc_html_e('강의', 'lectus-academy'); ?>
                            </a>
                            <a href="<?php echo esc_url(home_url('/roadmap')); ?>" class="btn btn-ghost text-gray-700 hover:text-lectus-primary transition-colors">
                                <?php esc_html_e('로드맵', 'lectus-academy'); ?>
                            </a>
                            <a href="<?php echo esc_url(home_url('/mentoring')); ?>" class="btn btn-ghost text-gray-700 hover:text-lectus-primary transition-colors">
                                <?php esc_html_e('멘토링', 'lectus-academy'); ?>
                            </a>
                            <a href="<?php echo esc_url(home_url('/community')); ?>" class="btn btn-ghost text-gray-700 hover:text-lectus-primary transition-colors">
                                <?php esc_html_e('커뮤니티', 'lectus-academy'); ?>
                            </a>
                            <?php
                        }
                        ?>
                        <a href="<?php echo esc_url(home_url('/student-dashboard')); ?>" class="btn border-2 border-lectus-primary text-lectus-primary hover:bg-lectus-primary hover:text-white transition-all">
                            <?php esc_html_e('내 강의실', 'lectus-academy'); ?>
                        </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Search Bar (Hidden by default) -->
        <div id="mobile-search-bar" class="mobile-search bg-white border-t border-gray-200 px-4 py-3 hidden">
            <form role="search" method="get" class="search-form relative" action="<?php echo esc_url(home_url('/')); ?>">
                <input type="search" 
                       class="search-input w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lectus-primary focus:border-transparent" 
                       placeholder="<?php esc_attr_e('검색어를 입력하세요', 'lectus-academy'); ?>" 
                       value="<?php echo get_search_query(); ?>" 
                       name="s">
                <button type="submit" class="search-submit absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-lectus-primary">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
        
        <!-- Category Navigation -->
        <nav class="category-nav bg-white border-t border-gray-200">
            <div class="container mx-auto px-4">
                <?php
                // Display Category Menu if assigned
                if (has_nav_menu('category-menu')) {
                    wp_nav_menu(array(
                        'theme_location' => 'category-menu',
                        'container' => false,
                        'menu_class' => 'category-list flex items-center gap-1 py-3 overflow-x-auto',
                        'depth' => 1,
                        'fallback_cb' => false,
                        'walker' => new Lectus_Category_Menu_Walker(),
                    ));
                } else {
                    // Fallback to dynamic categories
                    ?>
                    <ul class="category-list flex items-center gap-1 py-2 md:py-3 overflow-x-auto scrollbar-hide">
                        <li class="category-item flex-shrink-0">
                            <a href="<?php echo esc_url(get_post_type_archive_link('coursesingle')); ?>" class="category-link flex items-center gap-1 md:gap-2 px-4 md:px-4 py-2 md:py-2 rounded-lg bg-blue-50 text-lectus-primary font-medium transition-colors text-sm lg:text-base min-w-0">
                                <i class="fas fa-th category-icon text-xs md:text-sm"></i>
                                <span class="whitespace-nowrap"><?php esc_html_e('전체강의', 'lectus-academy'); ?></span>
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
                            'programming' => 'fa-code',
                        );
                        
                        if ($categories && !is_wp_error($categories)) :
                            foreach ($categories as $category) :
                                $icon = isset($category_icons[$category->slug]) ? $category_icons[$category->slug] : 'fa-folder';
                        ?>
                        <li class="category-item flex-shrink-0">
                            <a href="<?php echo esc_url(get_term_link($category)); ?>" class="category-link flex items-center gap-1 md:gap-2 px-4 md:px-4 py-2 md:py-2 rounded-lg hover:bg-gray-100 text-gray-700 transition-colors text-sm lg:text-base min-w-0">
                                <i class="fas <?php echo esc_attr($icon); ?> category-icon text-xs md:text-sm"></i>
                                <span class="whitespace-nowrap"><?php echo esc_html($category->name); ?></span>
                            </a>
                        </li>
                        <?php 
                            endforeach;
                        endif; 
                        ?>
                        <li class="category-item flex-shrink-0 hidden md:block">
                            <a href="<?php echo esc_url(home_url('/all-categories')); ?>" class="category-link flex items-center gap-1 md:gap-2 px-4 md:px-4 py-2 md:py-2 rounded-lg hover:bg-gray-100 text-gray-700 transition-colors text-sm lg:text-base min-w-0">
                                <i class="fas fa-ellipsis-h category-icon text-xs md:text-sm"></i>
                                <span class="whitespace-nowrap"><?php esc_html_e('더보기', 'lectus-academy'); ?></span>
                            </a>
                        </li>
                    </ul>
                    <?php
                }
                ?>
            </div>
        </nav>
    </header>
    
    <!-- Mobile Menu - 768px 미만에서 표시 -->
    <div class="mobile-menu fixed inset-0 bg-black bg-opacity-50 z-50 hidden md:hidden">
        <div class="mobile-menu-inner bg-white w-80 h-full overflow-y-auto">
            <div class="mobile-search p-4 border-b border-gray-200">
                <form role="search" method="get" class="search-form relative" action="<?php echo esc_url(home_url('/')); ?>">
                    <input type="search" 
                           class="search-input w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lectus-primary" 
                           placeholder="<?php esc_attr_e('검색어를 입력하세요', 'lectus-academy'); ?>" 
                           value="<?php echo get_search_query(); ?>" 
                           name="s">
                    <button type="submit" class="search-submit absolute right-2 top-1/2 -translate-y-1/2 text-gray-500">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            
            <ul class="mobile-nav p-4 space-y-2">
                <?php if (is_user_logged_in()) : 
                    $current_user = wp_get_current_user();
                ?>
                <li class="mobile-user-info border-b border-gray-200 pb-4 mb-4">
                    <div class="mobile-user-header flex items-center gap-3">
                        <?php echo get_avatar($current_user->ID, 40); ?>
                        <span><?php echo esc_html($current_user->display_name); ?></span>
                    </div>
                </li>
                <li><a href="<?php echo esc_url(home_url('/student-dashboard')); ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-book"></i> <?php esc_html_e('내 강의실', 'lectus-academy'); ?>
                </a></li>
                <?php else : ?>
                <li><a href="<?php echo esc_url(wp_login_url()); ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-sign-in-alt"></i> <?php esc_html_e('로그인', 'lectus-academy'); ?>
                </a></li>
                <li><a href="<?php echo esc_url(wp_registration_url()); ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-user-plus"></i> <?php esc_html_e('회원가입', 'lectus-academy'); ?>
                </a></li>
                <?php endif; ?>
                
                <li class="menu-divider border-t border-gray-200 my-2"></li>
                
                <li><a href="<?php echo esc_url(get_post_type_archive_link('coursesingle')); ?>" class="block px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <?php esc_html_e('강의', 'lectus-academy'); ?>
                </a></li>
                <li><a href="<?php echo esc_url(home_url('/roadmap')); ?>" class="block px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <?php esc_html_e('로드맵', 'lectus-academy'); ?>
                </a></li>
                <li><a href="<?php echo esc_url(home_url('/mentoring')); ?>" class="block px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <?php esc_html_e('멘토링', 'lectus-academy'); ?>
                </a></li>
                <li><a href="<?php echo esc_url(home_url('/community')); ?>" class="block px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <?php esc_html_e('커뮤니티', 'lectus-academy'); ?>
                </a></li>
                
                <li class="menu-divider border-t border-gray-200 my-2"></li>
                
                <li class="has-children">
                    <a href="#" class="block px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors"><?php esc_html_e('카테고리', 'lectus-academy'); ?></a>
                    <ul class="sub-menu ml-6 mt-2 space-y-1">
                        <?php
                        $categories = get_terms(array(
                            'taxonomy' => 'course_category',
                            'hide_empty' => false,
                        ));
                        
                        if ($categories && !is_wp_error($categories)) :
                            foreach ($categories as $category) :
                        ?>
                        <li><a href="<?php echo esc_url(get_term_link($category)); ?>" class="block px-3 py-2 text-sm rounded-lg hover:bg-gray-100 transition-colors">
                            <?php echo esc_html($category->name); ?>
                        </a></li>
                        <?php 
                            endforeach;
                        endif; 
                        ?>
                    </ul>
                </li>
                
                <?php if (is_user_logged_in()) : ?>
                <li class="menu-divider border-t border-gray-200 my-2"></li>
                <li><a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors text-red-600">
                    <i class="fas fa-sign-out-alt"></i> <?php esc_html_e('로그아웃', 'lectus-academy'); ?>
                </a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <div id="content" class="site-content">