/**
 * Global teardown for Playwright tests
 * Runs after all tests
 */

async function globalTeardown(config) {
  console.log('🧹 Cleaning up after Lectus Class System tests');
  
  // Additional cleanup can be added here
  // For example: cleaning test data, closing connections, etc.
  
  console.log('✅ Global teardown completed');
}

module.exports = globalTeardown;