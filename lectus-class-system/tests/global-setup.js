/**
 * Global setup for Playwright tests
 * Runs before all tests
 */

async function globalSetup(config) {
  console.log('🚀 Starting Lectus Class System WooCommerce Integration Tests');
  console.log('WordPress URL:', process.env.WORDPRESS_URL || 'http://localhost:8000');
  
  // Validate environment variables
  const requiredEnvVars = ['WORDPRESS_URL', 'ADMIN_USER', 'ADMIN_PASS'];
  const missingVars = requiredEnvVars.filter(varName => !process.env[varName]);
  
  if (missingVars.length > 0) {
    console.warn('⚠️  Missing environment variables:', missingVars.join(', '));
    console.warn('ℹ️  Using default values for missing variables');
  }
  
  // Additional setup can be added here
  // For example: database seeding, creating test data, etc.
  
  console.log('✅ Global setup completed');
}

module.exports = globalSetup;