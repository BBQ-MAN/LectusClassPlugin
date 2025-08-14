# 🤝 Contributing to Lectus Class System

We love your input! We want to make contributing to Lectus Class System as easy and transparent as possible.

## 📋 Table of Contents
- [Development Process](#development-process)
- [Getting Started](#getting-started)
- [How to Contribute](#how-to-contribute)
- [Code Style](#code-style)
- [Testing](#testing)
- [Security](#security)
- [License](#license)

## 🚀 Development Process

We use GitHub to host code, track issues and feature requests, and accept pull requests.

### Our Workflow
1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Write/update tests
5. Ensure code follows standards
6. Commit your changes (`git commit -m 'Add amazing feature'`)
7. Push to the branch (`git push origin feature/amazing-feature`)
8. Open a Pull Request

## 🛠️ Getting Started

### Development Environment Setup

1. **Prerequisites**:
   ```
   - PHP 8.0+
   - WordPress 5.0+
   - MySQL 5.6+
   - Node.js 16+
   - Composer
   ```

2. **Local Setup**:
   ```bash
   # Clone the repository
   git clone https://github.com/BBQ-MAN/LectusClassSystem.git
   cd LectusClassSystem

   # Install dependencies
   npm install
   composer install

   # Set up local WordPress instance
   # Copy plugin to wp-content/plugins/
   ```

3. **Configuration**:
   ```bash
   # Enable debug mode in wp-config.php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```

### Project Structure
```
lectus-class-system/
├── admin/              # Admin functionality
├── assets/             # CSS, JS, images
├── includes/           # Core classes
├── templates/          # Template files
├── tests/              # Test files
├── docs/               # Documentation
└── languages/          # Translation files
```

## 🤝 How to Contribute

### Reporting Bugs

**Before Submitting**:
- Check existing issues
- Use latest version
- Test on clean WordPress install

**Bug Report Template**:
```markdown
**Bug Description**
Clear description of the bug

**Steps to Reproduce**
1. Go to '...'
2. Click on '....'
3. Scroll down to '....'
4. See error

**Expected Behavior**
What should happen

**Environment**
- WordPress Version: [e.g. 6.0]
- PHP Version: [e.g. 8.1]
- Plugin Version: [e.g. 1.0.0]
- WooCommerce Version: [e.g. 7.0] (if applicable)
```

### Suggesting Features

**Feature Request Template**:
```markdown
**Feature Description**
Clear description of the feature

**Problem It Solves**
Why is this feature needed?

**Proposed Solution**
How should it work?

**Alternative Solutions**
Other approaches considered

**Additional Context**
Screenshots, examples, etc.
```

### Pull Requests

**Good Pull Requests**:
- Focus on single feature/fix
- Include tests
- Update documentation
- Follow coding standards
- Include clear commit messages

**PR Template**:
```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Tests pass locally
- [ ] Added new tests
- [ ] Manual testing completed

## Checklist
- [ ] Code follows style guidelines
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] No console errors
```

## 📝 Code Style

### PHP Standards

Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/):

```php
<?php
/**
 * Class description
 *
 * @package LectusClassSystem
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lectus_Example {
    
    /**
     * Method description
     *
     * @param int    $user_id User ID
     * @param string $name    User name
     * @return bool Success status
     */
    public function example_method($user_id, $name) {
        // Validate input
        $user_id = absint($user_id);
        $name = sanitize_text_field($name);
        
        // Business logic
        if ($user_id > 0 && !empty($name)) {
            return true;
        }
        
        return false;
    }
}
```

### JavaScript Standards

```javascript
/**
 * Module description
 */
(function($) {
    'use strict';

    const LectusExample = {
        
        /**
         * Initialize the module
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            $('.example-button').on('click', this.handleClick.bind(this));
        },

        /**
         * Handle button click
         * @param {Event} e - Click event
         */
        handleClick: function(e) {
            e.preventDefault();
            // Handle click
        }
    };

    $(document).ready(function() {
        LectusExample.init();
    });

})(jQuery);
```

### CSS Standards

```css
/* Component styles */
.lectus-component {
    display: block;
    margin: 0;
    padding: 1rem;
}

.lectus-component__element {
    font-size: 1rem;
    line-height: 1.5;
}

.lectus-component__element--modifier {
    font-weight: bold;
}

/* Media queries */
@media (max-width: 768px) {
    .lectus-component {
        padding: 0.5rem;
    }
}
```

### Naming Conventions

- **Classes**: `Lectus_Class_Name`
- **Functions**: `lectus_function_name`
- **Variables**: `$variable_name`
- **Constants**: `LECTUS_CONSTANT_NAME`
- **Hooks**: `lectus_hook_name`
- **CSS Classes**: `.lectus-class-name`
- **JS Objects**: `LectusObjectName`

### Security Best Practices

```php
// Always sanitize input
$user_input = sanitize_text_field($_POST['user_input']);

// Always escape output
echo esc_html($user_data);

// Always verify nonces
if (!wp_verify_nonce($_POST['nonce'], 'action_name')) {
    wp_die('Security check failed');
}

// Always check capabilities
if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}

// Always use prepared statements
$wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}table WHERE id = %d",
    $id
);
```

## 🧪 Testing

### Running Tests

```bash
# Install test dependencies
npm install

# Run all tests
npm test

# Run specific test suite
npm test -- --grep "Enrollment"

# Run with coverage
npm run test:coverage
```

### Writing Tests

#### PHP Unit Tests
```php
class Test_Lectus_Feature extends WP_UnitTestCase {
    
    public function setUp(): void {
        parent::setUp();
        // Setup test data
    }
    
    public function test_feature_works() {
        $result = lectus_feature_function();
        $this->assertTrue($result);
    }
    
    public function test_feature_validates_input() {
        $result = lectus_feature_function('invalid');
        $this->assertWPError($result);
    }
}
```

#### JavaScript Tests
```javascript
describe('Lectus Frontend', function() {
    
    beforeEach(function() {
        // Setup DOM
        document.body.innerHTML = '<div id="test-container"></div>';
    });
    
    it('should initialize correctly', function() {
        const instance = new LectusFrontend();
        expect(instance).toBeDefined();
    });
    
    it('should handle events', function() {
        const spy = jasmine.createSpy('clickHandler');
        // Test event handling
    });
});
```

### Test Guidelines

1. **Write tests for**:
   - New features
   - Bug fixes
   - Edge cases
   - Security functions

2. **Test Structure**:
   - One assertion per test
   - Descriptive test names
   - Setup/teardown properly
   - Mock external dependencies

3. **Coverage Goals**:
   - Core functions: 90%+
   - Security functions: 100%
   - Admin functions: 80%+
   - Frontend functions: 70%+

## 🔒 Security

### Reporting Security Issues

**DO NOT** create public issues for security vulnerabilities.

Instead:
1. Email security@example.com
2. Include detailed description
3. Provide steps to reproduce
4. Wait for confirmation before disclosure

### Security Checklist

- [ ] Input validation
- [ ] Output escaping
- [ ] Nonce verification
- [ ] Capability checks
- [ ] SQL injection prevention
- [ ] XSS prevention
- [ ] CSRF protection
- [ ] File upload security

## 📜 License

By contributing, you agree that your contributions will be licensed under the GPL v2 License.

## 🙋‍♀️ Questions?

- **General**: Create a discussion on GitHub
- **Bugs**: Create an issue
- **Security**: Email security@example.com
- **Features**: Create a feature request

## 📚 Resources

- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [WooCommerce Developer Docs](https://woocommerce.github.io/code-reference/)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)

## 🏆 Recognition

Contributors are recognized in:
- CHANGELOG.md
- Plugin credits
- GitHub contributors page
- Annual contributor appreciation post

Thank you for contributing to Lectus Class System! 🎉