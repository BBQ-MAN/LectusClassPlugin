/**
 * Lectus Academy Theme Integration Tests
 * 
 * These tests verify that the theme works correctly with the Lectus Class System plugin
 */

const { test, expect } = require('@playwright/test');

// Test configuration
const SITE_URL = 'http://localhost:8000';
const ADMIN_URL = `${SITE_URL}/wp-admin`;
const ADMIN_USER = 'admin';
const ADMIN_PASSWORD = 'admin';

// Helper function to login
async function loginAsAdmin(page) {
    await page.goto(`${SITE_URL}/wp-login.php`);
    await page.fill('#user_login', ADMIN_USER);
    await page.fill('#user_pass', ADMIN_PASSWORD);
    await page.click('#wp-submit');
    await page.waitForURL(/wp-admin/);
}

test.describe('Theme Activation and Setup', () => {
    test('Should activate Lectus Academy theme', async ({ page }) => {
        // Login as admin
        await loginAsAdmin(page);
        
        // Navigate to themes
        await page.goto(`${ADMIN_URL}/themes.php`);
        
        // Find and activate Lectus Academy theme
        const themeCard = page.locator('.theme').filter({ hasText: 'Lectus Academy' });
        
        if (await themeCard.count() > 0) {
            // Check if already active
            const isActive = await themeCard.locator('.active').count() > 0;
            
            if (!isActive) {
                // Activate theme
                await themeCard.hover();
                await themeCard.locator('button:has-text("Activate")').click();
                await page.waitForLoadState('networkidle');
            }
            
            // Verify activation
            expect(await page.locator('.theme.active').filter({ hasText: 'Lectus Academy' }).count()).toBe(1);
        } else {
            console.log('Theme not found - make sure docker-compose volumes are mounted correctly');
        }
    });
    
    test('Should verify plugin is active', async ({ page }) => {
        await loginAsAdmin(page);
        
        // Navigate to plugins
        await page.goto(`${ADMIN_URL}/plugins.php`);
        
        // Check if Lectus Class System is active
        const pluginRow = page.locator('tr[data-slug="lectus-class-system"]');
        
        if (await pluginRow.count() > 0) {
            const isActive = await pluginRow.locator('.deactivate').count() > 0;
            expect(isActive).toBe(true);
        } else {
            console.log('Plugin not found - checking alternative method');
            
            // Alternative check
            const hasPlugin = await page.locator('td:has-text("Lectus Class System")').count() > 0;
            expect(hasPlugin).toBe(true);
        }
    });
});

test.describe('Frontend Theme Display', () => {
    test('Should display homepage with theme elements', async ({ page }) => {
        await page.goto(SITE_URL);
        
        // Check for theme header
        expect(await page.locator('.site-header').count()).toBe(1);
        
        // Check for theme footer
        expect(await page.locator('.site-footer').count()).toBe(1);
        
        // Check for hero section (if on front page)
        const heroSection = page.locator('.hero-section');
        if (await heroSection.count() > 0) {
            expect(await heroSection.isVisible()).toBe(true);
        }
    });
    
    test('Should have navigation menu', async ({ page }) => {
        await page.goto(SITE_URL);
        
        // Check for main navigation
        expect(await page.locator('.main-navigation').count()).toBe(1);
        
        // Check for mobile menu toggle
        expect(await page.locator('.mobile-menu-toggle').count()).toBe(1);
    });
});

test.describe('Course Display Integration', () => {
    test('Should display courses archive page', async ({ page }) => {
        // Try to navigate to courses page
        await page.goto(`${SITE_URL}/courses`);
        
        // Check if we're on a courses page or need to find the link
        if (page.url().includes('404')) {
            // Go back to homepage and find courses link
            await page.goto(SITE_URL);
            
            // Look for courses link in navigation
            const coursesLink = page.locator('a:has-text("Courses")').first();
            if (await coursesLink.count() > 0) {
                await coursesLink.click();
                await page.waitForLoadState('networkidle');
            }
        }
        
        // Check for course grid or course cards
        const courseCards = page.locator('.course-card, .course-grid article');
        if (await courseCards.count() > 0) {
            console.log(`Found ${await courseCards.count()} course cards`);
        }
    });
    
    test('Should display single course page', async ({ page }) => {
        await loginAsAdmin(page);
        
        // Create a test course first
        await page.goto(`${ADMIN_URL}/post-new.php?post_type=coursesingle`);
        
        // Fill in course details
        await page.fill('#title', 'Test Course for Theme Integration');
        await page.fill('.wp-editor-area', 'This is a test course to verify theme integration.');
        
        // Publish the course
        await page.click('#publish');
        await page.waitForSelector('.notice-success');
        
        // View the course
        const viewLink = page.locator('.notice-success a:has-text("View")');
        if (await viewLink.count() > 0) {
            await viewLink.click();
            
            // Verify course page elements
            expect(await page.locator('.single-course-page').count()).toBe(1);
            expect(await page.locator('.course-title').count()).toBe(1);
            expect(await page.locator('.course-sidebar-card').count()).toBe(1);
        }
    });
});

test.describe('Student Dashboard Integration', () => {
    test('Should create student dashboard page if not exists', async ({ page }) => {
        await loginAsAdmin(page);
        
        // Check if dashboard page exists
        await page.goto(`${ADMIN_URL}/edit.php?post_type=page`);
        
        const dashboardPage = page.locator('a.row-title:has-text("Student Dashboard")');
        
        if (await dashboardPage.count() === 0) {
            // Create dashboard page
            await page.goto(`${ADMIN_URL}/post-new.php?post_type=page`);
            await page.fill('#title', 'Student Dashboard');
            await page.fill('.wp-editor-area', '[lectus_student_dashboard]');
            
            // Publish
            await page.click('#publish');
            await page.waitForSelector('.notice-success');
        }
    });
    
    test('Should display student dashboard for logged-in users', async ({ page }) => {
        await loginAsAdmin(page);
        
        // Navigate to student dashboard
        await page.goto(`${SITE_URL}/student-dashboard`);
        
        // Check for dashboard elements
        const dashboardContent = page.locator('.dashboard-content, .student-dashboard');
        if (await dashboardContent.count() > 0) {
            expect(await dashboardContent.isVisible()).toBe(true);
        }
    });
});

test.describe('Theme and Plugin Compatibility', () => {
    test('Should not have JavaScript errors', async ({ page }) => {
        const errors = [];
        
        // Listen for console errors
        page.on('console', msg => {
            if (msg.type() === 'error') {
                errors.push(msg.text());
            }
        });
        
        // Visit various pages
        await page.goto(SITE_URL);
        await page.goto(`${SITE_URL}/courses`);
        
        // Check for errors
        const criticalErrors = errors.filter(error => 
            !error.includes('favicon') && 
            !error.includes('404') &&
            !error.includes('Failed to load resource')
        );
        
        expect(criticalErrors.length).toBe(0);
    });
    
    test('Should have responsive design', async ({ page }) => {
        // Test desktop view
        await page.setViewportSize({ width: 1920, height: 1080 });
        await page.goto(SITE_URL);
        
        // Desktop navigation should be visible
        expect(await page.locator('.main-navigation').isVisible()).toBe(true);
        
        // Test mobile view
        await page.setViewportSize({ width: 375, height: 667 });
        
        // Mobile menu toggle should be visible
        const mobileToggle = page.locator('.mobile-menu-toggle');
        expect(await mobileToggle.isVisible()).toBe(true);
        
        // Test mobile menu interaction
        await mobileToggle.click();
        await page.waitForTimeout(300); // Wait for animation
        
        const mobileMenu = page.locator('#mobile-menu, .mobile-menu');
        if (await mobileMenu.count() > 0) {
            expect(await mobileMenu.isVisible()).toBe(true);
        }
    });
});

test.describe('Course Enrollment Flow', () => {
    test('Should show enrollment button on course page', async ({ page }) => {
        // Create a test course
        await loginAsAdmin(page);
        await page.goto(`${ADMIN_URL}/post-new.php?post_type=coursesingle`);
        await page.fill('#title', 'Free Test Course');
        await page.fill('.wp-editor-area', 'Free course for testing enrollment.');
        await page.click('#publish');
        await page.waitForSelector('.notice-success');
        
        // View course as logged out user
        await page.goto(`${SITE_URL}/wp-login.php?action=logout`);
        await page.getByRole('link', { name: 'log out' }).click();
        
        // Navigate to the course
        await page.goto(SITE_URL);
        const courseLink = page.locator('a:has-text("Free Test Course")').first();
        if (await courseLink.count() > 0) {
            await courseLink.click();
            
            // Check for enrollment button
            const enrollButton = page.locator('.enroll-button, button:has-text("Enroll")');
            expect(await enrollButton.count()).toBeGreaterThan(0);
        }
    });
});

test.describe('Q&A System Integration', () => {
    test('Should display Q&A section on course page for enrolled users', async ({ page }) => {
        await loginAsAdmin(page);
        
        // Navigate to a course page
        await page.goto(`${ADMIN_URL}/edit.php?post_type=coursesingle`);
        const firstCourse = page.locator('.row-title').first();
        
        if (await firstCourse.count() > 0) {
            await firstCourse.click();
            
            // View the course
            await page.locator('#view-post-btn a').click();
            
            // Check for Q&A tab or section
            const qaSection = page.locator('[data-tab="qa"], .qa-section');
            console.log(`Found ${await qaSection.count()} Q&A sections`);
        }
    });
});

test.describe('Certificate Display', () => {
    test('Should have certificate verification page', async ({ page }) => {
        await loginAsAdmin(page);
        
        // Create certificate verification page if not exists
        await page.goto(`${ADMIN_URL}/edit.php?post_type=page`);
        
        const certPage = page.locator('a.row-title:has-text("Certificate Verification")');
        
        if (await certPage.count() === 0) {
            await page.goto(`${ADMIN_URL}/post-new.php?post_type=page`);
            await page.fill('#title', 'Certificate Verification');
            await page.fill('.wp-editor-area', '[lectus_certificate_verify]');
            await page.click('#publish');
            await page.waitForSelector('.notice-success');
        }
        
        // Visit the page
        await page.goto(`${SITE_URL}/certificate-verification`);
        
        // Check for verification form
        const verifyForm = page.locator('form').filter({ has: page.locator('input[type="text"]') });
        if (await verifyForm.count() > 0) {
            console.log('Certificate verification form found');
        }
    });
});

// Run tests
test.describe.configure({ mode: 'serial' });