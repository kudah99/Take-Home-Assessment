# Test Data Management Strategy

## Overview

This document outlines the test data management strategy for the QA Assessment Application, covering test data organization, creation methodologies, and setup/teardown procedures across all testing layers (unit, widget, integration, and end-to-end testing).

---

## 1. Test Data Strategy

### 1.1 Principles

- **Isolation**: Test data is isolated per test case to prevent interference
- **Reproducibility**: Consistent, predictable data ensures reliable test results
- **Maintainability**: Centralized test data management simplifies updates
- **Performance**: Minimal test data footprint for faster test execution
- **Security**: No production data in test environments; use anonymized/masked data

### 1.2 Data Classification

| Data Type | Layer | Source | Lifecycle |
|-----------|-------|--------|-----------|
| **Unit Test Data** | Flutter Unit | Mocked/In-Memory | Test function scope |
| **Widget Test Data** | Flutter Widget | Fixtures/Mocks | Test function scope |
| **Integration Test Data** | Flutter Integration | Fixtures/Seeders | Test suite scope |
| **API Test Data** | Backend API | Seeders/Factories | Test suite scope |
| **E2E Test Data** | Full Stack | Database Seeders | Test run scope |

### 1.3 Data Ownership

| Component | Owner | Responsibility |
|-----------|-------|-----------------|
| **Flutter App Tests** | QA/Mobile Team | `test/fixtures/`, `test/helpers/` |
| **Laravel API Tests** | QA/Backend Team | `tests/Helpers/`, Database Factories |
| **Shared Test Fixtures** | QA Lead | `docs/test-data-strategy.md`, fixtures documentation |

---

## 2. Test Data Sets

### 2.1 User Test Data

#### Standard Test Users

```json
{
  "admin_user": {
    "email": "admin@test.local",
    "password": "TestAdmin@123",
    "name": "Admin User",
    "role": "admin",
    "permissions": ["read", "write", "delete"]
  },
  "regular_user": {
    "email": "user@test.local",
    "password": "TestUser@123",
    "name": "Regular User",
    "role": "user",
    "permissions": ["read"]
  },
  "guest_user": {
    "email": "guest@test.local",
    "password": "TestGuest@123",
    "name": "Guest User",
    "role": "guest",
    "permissions": []
  }
}
```

#### Edge Case Users

```json
{
  "long_name_user": {
    "name": "A Very Long User Name That Tests Character Limit Handling In The UI Component"
  },
  "special_char_user": {
    "name": "User@#$%^&*()"
  },
  "unicode_user": {
    "name": "用户名 Пользователь مستخدم"
  },
  "inactive_user": {
    "status": "inactive",
    "email": "inactive@test.local"
  }
}
```

### 2.2 API Test Data

#### Sample Request/Response Objects

```json
{
  "valid_product": {
    "name": "Test Product",
    "description": "A test product for QA",
    "price": 99.99,
    "stock": 10,
    "category": "electronics"
  },
  "invalid_product": {
    "name": "",
    "price": -50,
    "stock": "invalid"
  },
  "boundary_product": {
    "name": "A",
    "price": 0.01,
    "stock": 99999
  }
}
```

### 2.3 UI Test Data

#### Form Input Test Cases

```dart
const testData = {
  'valid_inputs': {
    'email': 'test@example.com',
    'password': 'SecurePass123!',
    'phone': '+1234567890',
  },
  'invalid_inputs': {
    'email': ['invalid-email', '', '@example.com'],
    'password': ['123', 'nouppercase', 'NOLOWERCASE'],
    'phone': ['abc', '123', '+'],
  },
  'boundary_inputs': {
    'email': 'a@b.c',
    'password': 'aB1!',
    'phone': '+1',
  }
};
```

### 2.4 Database Test Data

#### Entity Relationships

```sql
-- Users Table
INSERT INTO users (id, name, email, created_at) VALUES
(1, 'Admin User', 'admin@test.local', NOW()),
(2, 'Regular User', 'user@test.local', NOW()),
(3, 'Test User', 'test@test.local', NOW());

-- Products Table
INSERT INTO products (id, name, price, user_id, created_at) VALUES
(1, 'Product 1', 29.99, 1, NOW()),
(2, 'Product 2', 49.99, 2, NOW()),
(3, 'Product 3', 79.99, 1, NOW());

-- Orders Table
INSERT INTO orders (id, user_id, total, created_at) VALUES
(1, 1, 79.98, NOW()),
(2, 2, 49.99, NOW());
```

---

## 3. Data Setup Procedures

### 3.1 Flutter Unit Tests Setup

**Location**: `test/unit/`

```dart
void main() {
  group('UserModel Tests', () {
    late UserModel user;

    setUp(() {
      // Setup: Create fresh test data
      user = UserModel(
        id: '1',
        name: 'Test User',
        email: 'test@example.com',
        role: UserRole.regular,
      );
    });

    tearDown(() {
      // Cleanup: Clear references
      user = null;
    });

    test('User creation with valid data', () {
      expect(user.name, 'Test User');
      expect(user.role, UserRole.regular);
    });
  });
}
```

### 3.2 Flutter Widget Tests Setup

**Location**: `test/widget/`

```dart
void main() {
  group('LoginScreen Widget Tests', () {
    testWidgets('Login form with valid credentials', (WidgetTester tester) async {
      // Arrange: Setup test data and mocks
      final mockAuthService = MockAuthService();
      when(mockAuthService.login(any, any))
        .thenAnswer((_) async => LoginResponse(success: true));

      await tester.pumpWidget(
        MultiProvider(
          providers: [
            Provider<AuthService>(create: (_) => mockAuthService),
          ],
          child: const MyApp(),
        ),
      );

      // Act: Interact with widget
      await tester.enterText(find.byType(TextField).at(0), 'user@test.local');
      await tester.enterText(find.byType(TextField).at(1), 'TestUser@123');
      await tester.tap(find.byType(ElevatedButton));
      await tester.pumpAndSettle();

      // Assert: Verify results
      expect(find.text('Welcome'), findsOneWidget);
    });
  });
}
```

### 3.3 Flutter Integration Tests Setup

**Location**: `test/integration/`

```dart
void main() {
  group('App Integration Tests', () {
    late IntegrationTestWidgetsFlutterBinding binding;

    setUpAll(() async {
      binding = IntegrationTestWidgetsFlutterBinding.ensureInitialized();
      // Seed initial test data
      await seedTestDatabase();
    });

    tearDownAll(() async {
      // Clear all test data
      await clearTestDatabase();
    });

    testWidgets('Complete user flow', (WidgetTester tester) async {
      app.main();
      await tester.pumpAndSettle();
      // Test implementation
    });
  });
}
```

### 3.4 Laravel API Tests Setup

**Location**: `tests/Feature/` or `tests/Integration/`

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase; // Refresh database for each test

    protected function setUp(): void
    {
        parent::setUp();
        // Setup: Seed test data
        $this->seedTestData();
    }

    protected function tearDown(): void
    {
        // Cleanup is handled by RefreshDatabase trait
        parent::tearDown();
    }

    protected function seedTestData(): void
    {
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
        
        $this->products = Product::factory()
            ->count(5)
            ->create(['user_id' => $this->admin->id]);
    }

    public function test_get_products(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }
}
```

### 3.5 Postman/API Tests Setup

**Location**: `docs/api-tests.postman_collection.json`

```json
{
  "info": {
    "name": "QA Assessment API Tests",
    "description": "API test collection with test data setup"
  },
  "event": [
    {
      "listen": "prerequest",
      "script": {
        "exec": [
          "// Setup: Create test data before each request",
          "pm.globals.set('testUserId', pm.variables.randomInt(1000, 9999));",
          "pm.globals.set('testEmail', `test-${pm.globals.get('testUserId')}@test.local`);",
          "pm.globals.set('testTimestamp', new Date().toISOString());"
        ]
      }
    }
  ]
}
```

---

## 4. Data Teardown Procedures

### 4.1 Automatic Cleanup

#### Flutter Tests

```dart
// Reset all mocks after each test
tearDown(() {
  resetMockitoState();
  clearInteractions(mockService);
});

// For integration tests
tearDownAll(() async {
  await WebDriver.close();
  await Firebase.instance.cleanup();
});
```

#### Laravel Tests

```php
protected function tearDown(): void
{
    // Database is automatically rolled back by RefreshDatabase
    // Files are cleaned up by temporary storage
    parent::tearDown();
}
```

### 4.2 Manual Cleanup Scripts

#### Database Reset Script (Laravel)

```bash
#!/bin/bash
# scripts/reset-test-database.sh

php artisan migrate:refresh --env=testing
php artisan db:seed --class=TestDatabaseSeeder --env=testing
echo "Test database reset complete"
```

#### Flutter Test Cleanup

```dart
Future<void> cleanupTestData() async {
  // Clear shared preferences
  final prefs = await SharedPreferences.getInstance();
  await prefs.clear();

  // Delete temporary files
  final tempDir = await getTemporaryDirectory();
  await tempDir.delete(recursive: true);

  // Reset mocks
  resetMockitoState();
}
```

### 4.3 Cleanup Checklist

- [ ] Delete temporary files created during tests
- [ ] Clear in-memory caches and mocks
- [ ] Rollback database transactions
- [ ] Remove uploaded test files
- [ ] Clear API mock server state
- [ ] Close open connections/streams
- [ ] Log cleanup completion

---

## 5. Test Data Fixtures

### 5.1 Flutter Fixtures

**Location**: `test/fixtures/`

```
test/fixtures/
├── mock_data.dart          # Reusable mock objects
├── sample_responses.dart   # API response samples
├── test_assets/            # Images, files, etc.
└── sql/                    # Database initialization scripts
```

**Example**: `test/fixtures/mock_data.dart`

```dart
class MockUserData {
  static const validUser = {
    'id': '1',
    'name': 'Test User',
    'email': 'test@example.com',
  };

  static const adminUser = {
    'id': '2',
    'name': 'Admin User',
    'email': 'admin@example.com',
    'role': 'admin',
  };

  static List<User> generateUsers(int count) {
    return List.generate(
      count,
      (i) => User(
        id: '$i',
        name: 'User $i',
        email: 'user$i@example.com',
      ),
    );
  }
}
```

### 5.2 Laravel Factories

**Location**: `database/factories/`

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'role' => $this->faker->randomElement(['user', 'admin']),
        ];
    }

    public function admin(): self
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
```

### 5.3 Laravel Seeders

**Location**: `database/seeders/`

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;

class TestDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create test users
        $admin = User::factory()->admin()->create([
            'email' => 'admin@test.local',
        ]);

        $users = User::factory()->count(5)->create();

        // Create test products
        Product::factory()
            ->count(10)
            ->create(['user_id' => $admin->id]);

        // Create related data
        foreach ($users as $user) {
            Product::factory()
                ->count(3)
                ->create(['user_id' => $user->id]);
        }
    }
}
```

---

## 6. Test Data Best Practices

### 6.1 Do's

✅ **Do** use Factory/Builder patterns for generating test data  
✅ **Do** isolate test data per test case  
✅ **Do** document expected data values and ranges  
✅ **Do** use descriptive names for test fixtures  
✅ **Do** maintain version control for fixture updates  
✅ **Do** use database transactions for rollback  
✅ **Do** create data close to test execution (lazy loading)  

### 6.2 Don'ts

❌ **Don't** use production data in tests  
❌ **Don't** hard-code sensitive information (passwords, API keys)  
❌ **Don't** share test data state between tests  
❌ **Don't** rely on test execution order  
❌ **Don't** create excessive test data (slows tests down)  
❌ **Don't** commit large fixture files to version control  
❌ **Don't** ignore cleanup procedures  

---

## 7. Data Privacy & Security

### 7.1 Data Handling Guidelines

- **Anonymization**: Remove personally identifiable information (PII)
- **Encryption**: Encrypt sensitive test data at rest
- **Access Control**: Limit test data access to authorized testers
- **Audit Logging**: Log who accesses test databases
- **Retention**: Delete test data after test execution or per retention policy

### 7.2 Sensitive Data Examples

```dart
// ❌ Wrong - Exposed credentials
const testPassword = 'ActualPassword123';

// ✅ Correct - Use environment variables
final testPassword = String.fromEnvironment('TEST_PASSWORD', defaultValue: 'TestPassword123');
```

---

## 8. Maintenance & Updates

### 8.1 Regular Reviews

| Frequency | Task |
|-----------|------|
| **Weekly** | Review test data for relevance |
| **Monthly** | Update fixtures based on schema changes |
| **Quarterly** | Audit test data coverage |
| **Annually** | Comprehensive strategy review |

### 8.2 Version Control

- Store fixture definitions in Git
- Tag major data schema changes
- Document breaking changes in CHANGELOG
- Review fixture changes in pull requests

### 8.3 Documentation Updates

When updating test data:
1. Update this strategy document
2. Update fixture comments and examples
3. Update any related helper documentation
4. Document reasoning for data changes

---

## 9. Troubleshooting

### Common Issues

| Issue | Cause | Solution |
|-------|-------|----------|
| Tests fail intermittently | Shared test state | Use `setUp`/`tearDown` to isolate data |
| Test database is locked | Incomplete cleanup | Check for open transactions/connections |
| Fixtures are out of date | Schema changes not reflected | Regenerate fixtures from schema |
| Tests run slowly | Excessive test data | Reduce dataset size; use only required data |
| Data pollution between tests | Poor teardown | Verify `tearDownAll` is called |

---

## 10. References

- [Flutter Testing Documentation](https://flutter.dev/docs/testing)
- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [Postman Testing Documentation](https://learning.postman.com/docs/writing-scripts/test-scripts/)
- [Test Data Management Best Practices](https://testautomationu.applitools.com/)

---

**Last Updated**: November 24, 2025  
**Owner**: QA Assessment Team  
**Status**: Active
