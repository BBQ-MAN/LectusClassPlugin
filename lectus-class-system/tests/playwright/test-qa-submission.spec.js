const { test, expect } = require('@playwright/test');

// Test configuration
const BASE_URL = 'http://localhost';
const ADMIN_USER = 'admin';
const ADMIN_PASS = 'password';
const STUDENT_USER = 'student1';
const STUDENT_PASS = 'password123';

test.describe('Q&A Submission System', () => {
    test.beforeEach(async ({ page }) => {
        // Start from a clean state
        await page.goto(BASE_URL);
    });

    test('Q&A form should appear once for enrolled students', async ({ page }) => {
        console.log('Testing Q&A form display...');
        
        // Login as student
        await page.goto(`${BASE_URL}/wp-login.php`);
        await page.fill('#user_login', STUDENT_USER);
        await page.fill('#user_pass', STUDENT_PASS);
        await page.click('#wp-submit');
        
        // Wait for redirect
        await page.waitForLoadState('networkidle');
        
        // Navigate to a course page
        await page.goto(`${BASE_URL}/coursesingle/test-course/`);
        
        // Check that Q&A form appears exactly once
        const qaForms = await page.locator('.lectus-qa-form').count();
        expect(qaForms).toBe(1);
        console.log(`✓ Q&A form count: ${qaForms}`);
        
        // Verify form elements are present
        await expect(page.locator('input[name="qa_title"]')).toBeVisible();
        await expect(page.locator('textarea[name="qa_content"]')).toBeVisible();
        await expect(page.locator('button[type="submit"]').filter({ hasText: /질문 등록|Submit Question/i })).toBeVisible();
        console.log('✓ Q&A form elements are visible');
    });

    test('Student can submit a question successfully', async ({ page }) => {
        console.log('Testing Q&A submission...');
        
        // Login as student
        await page.goto(`${BASE_URL}/wp-login.php`);
        await page.fill('#user_login', STUDENT_USER);
        await page.fill('#user_pass', STUDENT_PASS);
        await page.click('#wp-submit');
        
        await page.waitForLoadState('networkidle');
        
        // Navigate to course
        await page.goto(`${BASE_URL}/coursesingle/test-course/`);
        
        // Fill in Q&A form
        const questionTitle = `Test Question ${Date.now()}`;
        const questionContent = 'This is a test question submitted via Playwright';
        
        await page.fill('input[name="qa_title"]', questionTitle);
        await page.fill('textarea[name="qa_content"]', questionContent);
        
        // Listen for the AJAX response
        const responsePromise = page.waitForResponse(response => 
            response.url().includes('admin-ajax.php') && 
            response.request().postData()?.includes('lectus_submit_question')
        );
        
        // Submit the form
        await page.click('button[type="submit"]');
        
        // Wait for AJAX response
        const response = await responsePromise;
        const responseData = await response.json();
        
        console.log('AJAX Response:', responseData);
        
        // Check response status
        expect(response.status()).toBe(200);
        expect(responseData.success).toBe(true);
        expect(responseData.data?.message).toContain('질문이 등록되었습니다');
        
        console.log('✓ Question submitted successfully');
        
        // Wait for page update
        await page.waitForTimeout(2000);
        
        // Verify the question appears in the list
        await expect(page.locator('.lectus-qa-item').filter({ hasText: questionTitle })).toBeVisible();
        console.log('✓ Question appears in the list');
    });

    test('Admin can submit and view questions', async ({ page }) => {
        console.log('Testing admin Q&A functionality...');
        
        // Login as admin
        await page.goto(`${BASE_URL}/wp-login.php`);
        await page.fill('#user_login', ADMIN_USER);
        await page.fill('#user_pass', ADMIN_PASS);
        await page.click('#wp-submit');
        
        await page.waitForLoadState('networkidle');
        
        // Navigate to course
        await page.goto(`${BASE_URL}/coursesingle/test-course/`);
        
        // Admin should see Q&A form even if not enrolled
        await expect(page.locator('.lectus-qa-form')).toBeVisible();
        
        // Submit a question
        const adminQuestion = `Admin Question ${Date.now()}`;
        await page.fill('input[name="qa_title"]', adminQuestion);
        await page.fill('textarea[name="qa_content"]', 'Admin test question content');
        
        // Listen for AJAX response
        const responsePromise = page.waitForResponse(response => 
            response.url().includes('admin-ajax.php') && 
            response.request().postData()?.includes('lectus_submit_question')
        );
        
        await page.click('button[type="submit"]');
        
        const response = await responsePromise;
        const responseData = await response.json();
        
        expect(responseData.success).toBe(true);
        console.log('✓ Admin question submitted successfully');
    });

    test('Error handling for empty form submission', async ({ page }) => {
        console.log('Testing error handling...');
        
        // Login as student
        await page.goto(`${BASE_URL}/wp-login.php`);
        await page.fill('#user_login', STUDENT_USER);
        await page.fill('#user_pass', STUDENT_PASS);
        await page.click('#wp-submit');
        
        await page.waitForLoadState('networkidle');
        
        // Navigate to course
        await page.goto(`${BASE_URL}/coursesingle/test-course/`);
        
        // Try to submit empty form
        const responsePromise = page.waitForResponse(response => 
            response.url().includes('admin-ajax.php') && 
            response.request().postData()?.includes('lectus_submit_question')
        );
        
        await page.click('button[type="submit"]');
        
        const response = await responsePromise;
        const responseData = await response.json();
        
        // Should receive an error
        expect(responseData.success).toBe(false);
        expect(responseData.data?.message).toBeTruthy();
        console.log('✓ Empty form submission properly rejected');
    });

    test('AJAX nonce security verification', async ({ page }) => {
        console.log('Testing AJAX security...');
        
        // Login as student
        await page.goto(`${BASE_URL}/wp-login.php`);
        await page.fill('#user_login', STUDENT_USER);
        await page.fill('#user_pass', STUDENT_PASS);
        await page.click('#wp-submit');
        
        await page.waitForLoadState('networkidle');
        
        await page.goto(`${BASE_URL}/coursesingle/test-course/`);
        
        // Check that nonce is present in the page
        const nonceValue = await page.evaluate(() => {
            return window.lectus_ajax?.nonce || null;
        });
        
        expect(nonceValue).toBeTruthy();
        console.log('✓ Nonce is present in page');
        
        // Test with invalid nonce
        const invalidResponse = await page.evaluate(async () => {
            const formData = new FormData();
            formData.append('action', 'lectus_submit_question');
            formData.append('nonce', 'invalid_nonce');
            formData.append('course_id', '1');
            formData.append('title', 'Test');
            formData.append('content', 'Test');
            
            const response = await fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData
            });
            
            return await response.json();
        });
        
        expect(invalidResponse.success).toBe(false);
        console.log('✓ Invalid nonce properly rejected');
    });
});

// Helper function to create test data
async function setupTestData(page) {
    console.log('Setting up test data...');
    
    // Login as admin
    await page.goto(`${BASE_URL}/wp-login.php`);
    await page.fill('#user_login', ADMIN_USER);
    await page.fill('#user_pass', ADMIN_PASS);
    await page.click('#wp-submit');
    
    // Create a test course if needed
    await page.goto(`${BASE_URL}/wp-admin/post-new.php?post_type=coursesingle`);
    await page.fill('#title', 'Test Course');
    await page.fill('#content', 'This is a test course for Q&A testing');
    await page.click('#publish');
    
    console.log('✓ Test data setup complete');
}

test.describe.configure({ mode: 'serial' });