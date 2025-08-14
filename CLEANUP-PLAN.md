# Lectus Class System - Cleanup Plan

## Files to Remove (Debug/Test Files in Root)
- [ ] quick-fix.php - Emergency fix script
- [ ] fix-table-now.php - Database fix script  
- [ ] test-ajax-direct.php - AJAX test script
- [ ] test-external-link.php - External link test
- [ ] test-lectus-system.php - System test script
- [ ] enable-debug.php - Debug enabler
- [ ] enable-wordpress-debug.bat - Windows debug script
- [ ] emergency-fix.sql - SQL fix script

## Files to Move to Tests Directory
- [ ] lectus-class-system/plugin-activation-test.php → tests/
- [ ] lectus-class-system/full-system-test.php → tests/
- [ ] lectus-class-system/test/setup-test-data.php → tests/

## Directories to Organize
- [ ] Consolidate all test files in lectus-class-system/tests/
- [ ] Remove SuperClaude_Framework/ (not part of plugin)
- [ ] Remove mcp-servers/ (not part of plugin)

## Code Cleanup
- [ ] Remove console.log statements from JS files
- [ ] Remove var_dump/print_r from PHP files
- [ ] Complete TODO items or document them properly
- [ ] Remove commented-out code blocks

## Documentation Cleanup
- [ ] Keep only README.md and DEVELOPER.md
- [ ] Archive other reports to docs/ folder

## .gitignore Updates
- Add test files pattern
- Add debug files pattern
- Add backup files pattern
- Add IDE specific files