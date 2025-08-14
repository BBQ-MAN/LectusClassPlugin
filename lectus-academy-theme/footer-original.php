    </div><!-- #content -->

    <footer id="colophon" class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4><?php esc_html_e('About Us', 'lectus-academy'); ?></h4>
                    <p><?php echo esc_html(get_bloginfo('description')); ?></p>
                    
                    <?php if (has_custom_logo()) : ?>
                        <div class="footer-logo">
                            <?php the_custom_logo(); ?>
                        </div>
                    <?php else : ?>
                        <div class="footer-logo">
                            <i class="fas fa-graduation-cap"></i>
                            <span><?php bloginfo('name'); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>

                <div class="footer-section">
                    <h4><?php esc_html_e('Quick Links', 'lectus-academy'); ?></h4>
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'footer',
                        'menu_class'     => 'footer-menu',
                        'container'      => false,
                        'depth'          => 1,
                        'fallback_cb'    => false,
                    ));
                    ?>
                </div>

                <div class="footer-section">
                    <h4><?php esc_html_e('Categories', 'lectus-academy'); ?></h4>
                    <ul class="footer-menu">
                        <?php
                        $categories = get_terms(array(
                            'taxonomy' => 'course_category',
                            'hide_empty' => true,
                            'number' => 8,
                        ));
                        
                        if (!empty($categories) && !is_wp_error($categories)) {
                            foreach ($categories as $category) {
                                echo '<li><a href="' . esc_url(get_term_link($category)) . '">' . esc_html($category->name) . '</a></li>';
                            }
                        }
                        ?>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4><?php esc_html_e('Contact Info', 'lectus-academy'); ?></h4>
                    <ul class="contact-info">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo esc_html(get_theme_mod('lectus_address', '123 Education St, Learning City, LC 12345')); ?></span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <span><?php echo esc_html(get_theme_mod('lectus_phone', '+1 (555) 123-4567')); ?></span>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <span><?php echo esc_html(get_theme_mod('lectus_email', 'info@lectusacademy.com')); ?></span>
                        </li>
                        <li>
                            <i class="fas fa-clock"></i>
                            <span><?php echo esc_html(get_theme_mod('lectus_hours', 'Mon-Fri: 9AM-6PM')); ?></span>
                        </li>
                    </ul>
                </div>
            </div>

            <?php if (is_active_sidebar('footer-1') || is_active_sidebar('footer-2') || is_active_sidebar('footer-3') || is_active_sidebar('footer-4')) : ?>
            <div class="footer-widgets">
                <div class="row">
                    <?php if (is_active_sidebar('footer-1')) : ?>
                    <div class="col-3">
                        <?php dynamic_sidebar('footer-1'); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (is_active_sidebar('footer-2')) : ?>
                    <div class="col-3">
                        <?php dynamic_sidebar('footer-2'); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (is_active_sidebar('footer-3')) : ?>
                    <div class="col-3">
                        <?php dynamic_sidebar('footer-3'); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (is_active_sidebar('footer-4')) : ?>
                    <div class="col-3">
                        <?php dynamic_sidebar('footer-4'); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <div class="copyright">
                        <p>
                            &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. 
                            <?php esc_html_e('All rights reserved.', 'lectus-academy'); ?>
                        </p>
                        <p class="powered-by">
                            <?php
                            printf(
                                esc_html__('Powered by %1$s and %2$s', 'lectus-academy'),
                                '<a href="https://wordpress.org/" target="_blank" rel="noopener">WordPress</a>',
                                '<a href="https://github.com/BBQ-MAN/LectusClassSystem" target="_blank" rel="noopener">Lectus Class System</a>'
                            );
                            ?>
                        </p>
                    </div>
                    
                    <div class="footer-links">
                        <a href="<?php echo esc_url(get_permalink(get_page_by_path('privacy-policy'))); ?>">
                            <?php esc_html_e('Privacy Policy', 'lectus-academy'); ?>
                        </a>
                        <a href="<?php echo esc_url(get_permalink(get_page_by_path('terms-of-service'))); ?>">
                            <?php esc_html_e('Terms of Service', 'lectus-academy'); ?>
                        </a>
                        <a href="<?php echo esc_url(get_permalink(get_page_by_path('sitemap'))); ?>">
                            <?php esc_html_e('Sitemap', 'lectus-academy'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</div><!-- #page -->

<!-- Back to Top Button -->
<button id="back-to-top" class="back-to-top" aria-label="<?php esc_attr_e('Back to top', 'lectus-academy'); ?>">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- Loading Overlay -->
<div id="loading-overlay" class="loading-overlay">
    <div class="spinner"></div>
</div>

<?php wp_footer(); ?>

</body>
</html>