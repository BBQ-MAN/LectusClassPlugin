<?php
/**
 * The template for displaying the footer - Inflearn style
 *
 * @package LectusAcademy
 */

?>

    </div><!-- #content -->

    <footer id="colophon" class="site-footer">
        <div class="footer-top">
            <div class="container">
                <div class="footer-grid">
                    <!-- Company Info -->
                    <div class="footer-company">
                        <div class="footer-logo">
                            <i class="fas fa-graduation-cap"></i>
                            <span><?php bloginfo('name'); ?></span>
                        </div>
                        <div class="footer-info">
                            <p>(주)렉투스에듀케이션 | 대표자: 홍길동</p>
                            <p>사업자번호: 123-45-67890 | 통신판매업: 2025-서울강남-1234</p>
                            <p>주소: 서울특별시 강남구 테헤란로 123 렉투스빌딩</p>
                            <p>이메일: support@lectusacademy.com | 전화: 1600-1234</p>
                        </div>
                        <div class="footer-badges">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/badge-1.png'); ?>" alt="Badge 1">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/badge-2.png'); ?>" alt="Badge 2">
                        </div>
                    </div>
                    
                    <!-- Footer Links -->
                    <div class="footer-links-section">
                        <div class="footer-column">
                            <h4><?php esc_html_e('렉투스', 'lectus-academy'); ?></h4>
                            <ul>
                                <li><a href="<?php echo esc_url(home_url('/about')); ?>"><?php esc_html_e('회사 소개', 'lectus-academy'); ?></a></li>
                                <li><a href="<?php echo esc_url(home_url('/careers')); ?>"><?php esc_html_e('채용', 'lectus-academy'); ?></a></li>
                                <li><a href="<?php echo esc_url(home_url('/press')); ?>"><?php esc_html_e('보도자료', 'lectus-academy'); ?></a></li>
                            </ul>
                        </div>
                        
                        <div class="footer-column">
                            <h4><?php esc_html_e('파트너', 'lectus-academy'); ?></h4>
                            <ul>
                                <li><a href="<?php echo esc_url(home_url('/business')); ?>"><?php esc_html_e('기업교육', 'lectus-academy'); ?></a></li>
                                <li><a href="<?php echo esc_url(home_url('/affiliate')); ?>"><?php esc_html_e('제휴기관', 'lectus-academy'); ?></a></li>
                                <li><a href="<?php echo esc_url(home_url('/apply-instructor')); ?>"><?php esc_html_e('지식공유 참여', 'lectus-academy'); ?></a></li>
                            </ul>
                        </div>
                        
                        <div class="footer-column">
                            <h4><?php esc_html_e('지원', 'lectus-academy'); ?></h4>
                            <ul>
                                <li><a href="<?php echo esc_url(home_url('/faq')); ?>"><?php esc_html_e('자주 묻는 질문', 'lectus-academy'); ?></a></li>
                                <li><a href="<?php echo esc_url(home_url('/support')); ?>"><?php esc_html_e('고객센터', 'lectus-academy'); ?></a></li>
                                <li><a href="<?php echo esc_url(home_url('/terms')); ?>"><?php esc_html_e('이용약관', 'lectus-academy'); ?></a></li>
                                <li><a href="<?php echo esc_url(home_url('/privacy')); ?>"><?php esc_html_e('개인정보처리방침', 'lectus-academy'); ?></a></li>
                            </ul>
                        </div>
                        
                        <div class="footer-column">
                            <h4><?php esc_html_e('커뮤니티', 'lectus-academy'); ?></h4>
                            <ul>
                                <li><a href="<?php echo esc_url(home_url('/community')); ?>"><?php esc_html_e('커뮤니티', 'lectus-academy'); ?></a></li>
                                <li><a href="<?php echo esc_url(home_url('/study-groups')); ?>"><?php esc_html_e('스터디', 'lectus-academy'); ?></a></li>
                                <li><a href="<?php echo esc_url(home_url('/roadmap')); ?>"><?php esc_html_e('로드맵', 'lectus-academy'); ?></a></li>
                                <li><a href="<?php echo esc_url(home_url('/mentoring')); ?>"><?php esc_html_e('멘토링', 'lectus-academy'); ?></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="container">
                <div class="footer-bottom-inner">
                    <div class="footer-copyright">
                        <p>&copy; <?php echo date('Y'); ?> Lectus Academy. All rights reserved.</p>
                    </div>
                    <div class="footer-social">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                        <a href="#" aria-label="GitHub"><i class="fab fa-github"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer><!-- #colophon -->
</div><!-- #page -->

<button id="back-to-top" aria-label="Back to top">
    <i class="fas fa-chevron-up"></i>
</button>

<style>
/* Footer Styles - Inflearn inspired */
.site-footer {
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
    margin-top: 80px;
}

.footer-top {
    padding: 60px 0;
}

.footer-grid {
    display: grid;
    grid-template-columns: 2fr 3fr;
    gap: 60px;
}

.footer-company {
    color: #495057;
}

.footer-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}

.footer-logo i {
    font-size: 28px;
    color: var(--primary-color);
}

.footer-logo span {
    font-size: 20px;
    font-weight: 700;
    color: var(--text-primary);
}

.footer-info p {
    font-size: 13px;
    line-height: 1.8;
    color: #6c757d;
    margin: 5px 0;
}

.footer-badges {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.footer-badges img {
    height: 40px;
    opacity: 0.6;
}

.footer-links-section {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 40px;
}

.footer-column h4 {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 16px;
}

.footer-column ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-column ul li {
    margin-bottom: 12px;
}

.footer-column ul li a {
    color: #6c757d;
    font-size: 14px;
    transition: color 0.2s;
}

.footer-column ul li a:hover {
    color: var(--primary-color);
}

.footer-bottom {
    background: #fff;
    border-top: 1px solid #e9ecef;
    padding: 20px 0;
}

.footer-bottom-inner {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.footer-copyright p {
    font-size: 13px;
    color: #6c757d;
    margin: 0;
}

.footer-social {
    display: flex;
    gap: 16px;
}

.footer-social a {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 50%;
    color: #6c757d;
    transition: all 0.2s;
}

.footer-social a:hover {
    background: var(--primary-color);
    color: white;
}

#back-to-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 48px;
    height: 48px;
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 50%;
    display: none;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
    z-index: 999;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}

#back-to-top:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 4px 20px rgba(0,196,113,0.3);
}

#back-to-top.show {
    display: flex;
}

/* Responsive */
@media (max-width: 768px) {
    .footer-grid {
        grid-template-columns: 1fr;
        gap: 40px;
    }
    
    .footer-links-section {
        grid-template-columns: repeat(2, 1fr);
        gap: 30px;
    }
    
    .footer-bottom-inner {
        flex-direction: column;
        gap: 16px;
    }
}
</style>

<script>
// Back to top button
document.addEventListener('DOMContentLoaded', function() {
    const backToTop = document.getElementById('back-to-top');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 300) {
            backToTop.classList.add('show');
        } else {
            backToTop.classList.remove('show');
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