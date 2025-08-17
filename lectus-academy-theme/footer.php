<?php
/**
 * The template for displaying the footer - Inflearn style
 *
 * @package LectusAcademy
 */

?>

    </div><!-- #content -->

    <footer id="colophon" class="site-footer bg-gray-50 border-t border-gray-200 mt-20">
        <div class="footer-top py-16">
            <div class="container mx-auto px-4">
                <div class="footer-grid grid grid-cols-1 lg:grid-cols-5 gap-8">
                    <!-- Company Info -->
                    <div class="footer-company lg:col-span-2 text-gray-600">
                        <div class="footer-logo flex items-center gap-3 mb-5">
                            <i class="fas fa-graduation-cap text-3xl text-lectus-primary"></i>
                            <span class="text-xl font-bold text-gray-900"><?php bloginfo('name'); ?></span>
                        </div>
                        <div class="footer-info space-y-1">
                            <p class="text-sm text-gray-600">(주)렉투스에듀케이션 | 대표자: 홍길동</p>
                            <p class="text-sm text-gray-600">사업자번호: 123-45-67890 | 통신판매업: 2025-서울강남-1234</p>
                            <p class="text-sm text-gray-600">주소: 서울특별시 강남구 테헤란로 123 렉투스빌딩</p>
                            <p class="text-sm text-gray-600">이메일: support@lectusacademy.com | 전화: 1600-1234</p>
                        </div>
                        <div class="footer-badges flex gap-2 mt-5">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/badge-1.png'); ?>" alt="Badge 1" class="h-10 opacity-60">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/badge-2.png'); ?>" alt="Badge 2" class="h-10 opacity-60">
                        </div>
                    </div>
                    
                    <!-- Footer Links -->
                    <div class="footer-links-section lg:col-span-3 grid grid-cols-2 md:grid-cols-4 gap-8">
                        <div class="footer-column">
                            <h4 class="text-sm font-bold text-gray-900 mb-4"><?php esc_html_e('렉투스', 'lectus-academy'); ?></h4>
                            <ul class="space-y-3">
                                <li><a href="<?php echo esc_url(home_url('/about')); ?>" class="text-sm text-gray-600 hover:text-lectus-primary transition-colors"><?php esc_html_e('회사 소개', 'lectus-academy'); ?></a></li>
                                <li><a href="<?php echo esc_url(home_url('/careers')); ?>" class="text-sm text-gray-600 hover:text-lectus-primary transition-colors"><?php esc_html_e('채용', 'lectus-academy'); ?></a></li>
                                <li><a href="<?php echo esc_url(home_url('/press')); ?>" class="text-sm text-gray-600 hover:text-lectus-primary transition-colors"><?php esc_html_e('보도자료', 'lectus-academy'); ?></a></li>
                            </ul>
                        </div>
                        
                        <div class="footer-column">
                            <h4 class="text-sm font-bold text-gray-900 mb-4"><?php esc_html_e('파트너', 'lectus-academy'); ?></h4>
                            <ul class="space-y-3">
                                <li><a href="<?php echo esc_url(home_url('/business')); ?>" class="text-sm text-gray-600 hover:text-lectus-primary transition-colors"><?php esc_html_e('기업교육', 'lectus-academy'); ?></a></li>
                                <li><a href="<?php echo esc_url(home_url('/affiliate')); ?>" class="text-sm text-gray-600 hover:text-lectus-primary transition-colors"><?php esc_html_e('제휴기관', 'lectus-academy'); ?></a></li>
                                <li><a href="<?php echo esc_url(home_url('/apply-instructor')); ?>" class="text-sm text-gray-600 hover:text-lectus-primary transition-colors"><?php esc_html_e('지식공유 참여', 'lectus-academy'); ?></a></li>
                            </ul>
                        </div>
                        
                        <div class="footer-column">
                            <h4 class="text-sm font-bold text-gray-900 mb-4"><?php esc_html_e('지원', 'lectus-academy'); ?></h4>
                            <ul class="space-y-3">
                                <li><a href="<?php echo esc_url(home_url('/faq')); ?>" class="text-sm text-gray-600 hover:text-lectus-primary transition-colors"><?php esc_html_e('자주 묻는 질문', 'lectus-academy'); ?></a></li>
                                <li><a href="<?php echo esc_url(home_url('/support')); ?>" class="text-sm text-gray-600 hover:text-lectus-primary transition-colors"><?php esc_html_e('고객센터', 'lectus-academy'); ?></a></li>
                                <li><a href="<?php echo esc_url(home_url('/terms')); ?>" class="text-sm text-gray-600 hover:text-lectus-primary transition-colors"><?php esc_html_e('이용약관', 'lectus-academy'); ?></a></li>
                                <li><a href="<?php echo esc_url(home_url('/privacy')); ?>" class="text-sm text-gray-600 hover:text-lectus-primary transition-colors"><?php esc_html_e('개인정보처리방침', 'lectus-academy'); ?></a></li>
                            </ul>
                        </div>
                        
                        <div class="footer-column">
                            <h4 class="text-sm font-bold text-gray-900 mb-4"><?php esc_html_e('커뮤니티', 'lectus-academy'); ?></h4>
                            <ul class="space-y-3">
                                <li><a href="<?php echo esc_url(home_url('/community')); ?>" class="text-sm text-gray-600 hover:text-lectus-primary transition-colors"><?php esc_html_e('커뮤니티', 'lectus-academy'); ?></a></li>
                                <li><a href="<?php echo esc_url(home_url('/study-groups')); ?>" class="text-sm text-gray-600 hover:text-lectus-primary transition-colors"><?php esc_html_e('스터디', 'lectus-academy'); ?></a></li>
                                <li><a href="<?php echo esc_url(home_url('/roadmap')); ?>" class="text-sm text-gray-600 hover:text-lectus-primary transition-colors"><?php esc_html_e('로드맵', 'lectus-academy'); ?></a></li>
                                <li><a href="<?php echo esc_url(home_url('/mentoring')); ?>" class="text-sm text-gray-600 hover:text-lectus-primary transition-colors"><?php esc_html_e('멘토링', 'lectus-academy'); ?></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom bg-white border-t border-gray-200 py-5">
            <div class="container mx-auto px-4">
                <div class="footer-bottom-inner flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="footer-copyright">
                        <p class="text-sm text-gray-600">&copy; <?php echo date('Y'); ?> Lectus Academy. All rights reserved.</p>
                    </div>
                    <div class="footer-social flex gap-4">
                        <a href="#" aria-label="Facebook" class="w-9 h-9 flex items-center justify-center bg-gray-100 rounded-full text-gray-600 hover:bg-lectus-primary hover:text-white transition-all"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter" class="w-9 h-9 flex items-center justify-center bg-gray-100 rounded-full text-gray-600 hover:bg-lectus-primary hover:text-white transition-all"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="Instagram" class="w-9 h-9 flex items-center justify-center bg-gray-100 rounded-full text-gray-600 hover:bg-lectus-primary hover:text-white transition-all"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="YouTube" class="w-9 h-9 flex items-center justify-center bg-gray-100 rounded-full text-gray-600 hover:bg-lectus-primary hover:text-white transition-all"><i class="fab fa-youtube"></i></a>
                        <a href="#" aria-label="GitHub" class="w-9 h-9 flex items-center justify-center bg-gray-100 rounded-full text-gray-600 hover:bg-lectus-primary hover:text-white transition-all"><i class="fab fa-github"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer><!-- #colophon -->
</div><!-- #page -->

<button id="back-to-top" aria-label="Back to top" class="fixed bottom-8 right-8 w-12 h-12 bg-white border border-gray-200 rounded-full hidden items-center justify-center cursor-pointer transition-all hover:bg-lectus-primary hover:text-white hover:-translate-y-1 hover:shadow-lg z-50 shadow-md">
    <i class="fas fa-chevron-up"></i>
</button>


<script>
// Back to top button
document.addEventListener('DOMContentLoaded', function() {
    const backToTop = document.getElementById('back-to-top');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 300) {
            backToTop.classList.remove('hidden');
            backToTop.classList.add('flex');
        } else {
            backToTop.classList.add('hidden');
            backToTop.classList.remove('flex');
        }
    });
    
    backToTop.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});
</script>

<?php wp_footer(); ?>

</body>
</html>