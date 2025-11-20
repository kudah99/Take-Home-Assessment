import 'package:flutter_test/flutter_test.dart';
import 'package:qa_assessment_app/models/user.dart';
import 'package:qa_assessment_app/models/product.dart';
import 'package:qa_assessment_app/models/order.dart';

import '../fixtures/mock_data.dart';

void main() {
  group('User Model Tests', () {
    test('User.fromJson creates a valid user with all fields', () {
      final user = User.fromJson(MockData.mockUserJson);

      expect(user.id, 1);
      expect(user.name, 'John Doe');
      expect(user.email, 'john@example.com');
      expect(user.role, 'customer');
    });

    test('User.fromJson handles missing optional role field', () {
      final user = User.fromJson(MockData.mockUserJsonWithoutRole);

      expect(user.id, 1);
      expect(user.name, 'John Doe');
      expect(user.email, 'john@example.com');
      expect(user.role, null);
    });

    test('User.toJson returns correct JSON structure', () {
      final user = MockData.mockUser;
      final json = user.toJson();

      expect(json['id'], 1);
      expect(json['name'], 'John Doe');
      expect(json['email'], 'john@example.com');
      expect(json['role'], 'customer');
    });

    test('User.toJson preserves null role field', () {
      final user = MockData.mockUserWithoutRole;
      final json = user.toJson();

      expect(json['role'], null);
    });

    test('User objects with same data are equal', () {
      final user1 = User(
        id: 1,
        name: 'John Doe',
        email: 'john@example.com',
        role: 'customer',
      );
      final user2 = User(
        id: 1,
        name: 'John Doe',
        email: 'john@example.com',
        role: 'customer',
      );

      expect(user1.id, user2.id);
      expect(user1.name, user2.name);
      expect(user1.email, user2.email);
      expect(user1.role, user2.role);
    });

    test('User roundtrip: fromJson -> toJson -> fromJson preserves data', () {
      final original = User.fromJson(MockData.mockUserJson);
      final json = original.toJson();
      final restored = User.fromJson(json);

      expect(restored.id, original.id);
      expect(restored.name, original.name);
      expect(restored.email, original.email);
      expect(restored.role, original.role);
    });
  });

  group('Product Model Tests', () {
    test('Product.fromJson creates a valid product with all fields', () {
      final product = Product.fromJson(MockData.mockProductJson);

      expect(product.id, 1);
      expect(product.name, 'Test Product');
      expect(product.description, 'A test product description');
      expect(product.price, 29.99);
      expect(product.stock, 10);
      expect(product.category, 'Electronics');
    });

    test('Product.fromJson handles missing optional fields', () {
      final product = Product.fromJson(MockData.mockProductJsonMinimal);

      expect(product.id, 3);
      expect(product.name, 'Minimal Product');
      expect(product.description, null);
      expect(product.price, 9.99);
      expect(product.stock, 5);
      expect(product.category, null);
    });

    test('Product.isInStock returns true when stock > 0', () {
      final product = Product.fromJson(MockData.mockProductJson);
      expect(product.isInStock, true);
    });

    test('Product.isInStock returns false when stock <= 0', () {
      final product = Product.fromJson(MockData.mockProductJsonNoStock);
      expect(product.isInStock, false);
    });

    test('Product.toJson returns correct JSON structure', () {
      final product = MockData.mockProduct;
      final json = product.toJson();

      expect(json['id'], 1);
      expect(json['name'], 'Test Product');
      expect(json['description'], 'A test product description');
      expect(json['price'], 29.99);
      expect(json['stock'], 10);
      expect(json['category'], 'Electronics');
    });

    test('Product with zero stock is identified as out of stock', () {
      final product = MockData.mockProductNoStock;
      expect(product.isInStock, false);
      expect(product.stock, 0);
    });

    test('Product price conversion handles double precision', () {
      final product = Product.fromJson({
        'id': 1,
        'name': 'Test',
        'price': 19.99,
        'stock': 1,
      });

      expect(product.price, 19.99);
    });

    test('Product roundtrip: fromJson -> toJson -> fromJson preserves data',
        () {
      final original = Product.fromJson(MockData.mockProductJson);
      final json = original.toJson();
      final restored = Product.fromJson(json);

      expect(restored.id, original.id);
      expect(restored.name, original.name);
      expect(restored.description, original.description);
      expect(restored.price, original.price);
      expect(restored.stock, original.stock);
      expect(restored.category, original.category);
    });
  });

  group('Order Model Tests', () {
    test('Order.fromJson creates a valid order with all fields', () {
      final order = Order.fromJson(MockData.mockOrderJson);

      expect(order.id, 1);
      expect(order.userId, 1);
      expect(order.status, 'completed');
      expect(order.totalAmount, 89.97);
      expect(order.items, isNotNull);
      expect(order.items!.length, 1);
    });

    test('Order.fromJson handles missing items field', () {
      final order = Order.fromJson(MockData.mockOrderJsonWithoutItems);

      expect(order.id, 2);
      expect(order.userId, 1);
      expect(order.status, 'pending');
      expect(order.totalAmount, 50.00);
      expect(order.items, null);
    });

    test('Order.toJson returns correct JSON structure', () {
      final order = MockData.mockOrder;
      final json = order.toJson();

      expect(json['id'], 1);
      expect(json['user_id'], 1);
      expect(json['status'], 'completed');
      expect(json['total_amount'], 89.97);
    });

    test('Order.toJson includes items when present', () {
      final order = MockData.mockOrderWithItems;
      final json = order.toJson();

      expect(json['items'], isNotNull);
      expect(json['items'].length, 1);
      expect(json['items'][0]['product_id'], 1);
    });

    test('Order roundtrip: fromJson -> toJson -> fromJson preserves data', () {
      final original = Order.fromJson(MockData.mockOrderJson);
      final json = original.toJson();
      final restored = Order.fromJson(json);

      expect(restored.id, original.id);
      expect(restored.userId, original.userId);
      expect(restored.status, original.status);
      expect(restored.totalAmount, original.totalAmount);
    });

    test('Order with different statuses are handled correctly', () {
      final statuses = ['pending', 'completed', 'cancelled', 'shipped'];
      for (final status in statuses) {
        final json = {
          'id': 1,
          'user_id': 1,
          'status': status,
          'total_amount': 100.00,
        };
        final order = Order.fromJson(json);
        expect(order.status, status);
      }
    });
  });

  group('OrderItem Model Tests', () {
    test('OrderItem.fromJson creates a valid order item', () {
      final item = OrderItem.fromJson(MockData.mockOrderItemJson);

      expect(item.id, 1);
      expect(item.productId, 1);
      expect(item.quantity, 3);
      expect(item.price, 29.99);
    });

    test('OrderItem.toJson returns correct JSON structure', () {
      final item = OrderItem(
        id: 1,
        productId: 1,
        quantity: 3,
        price: 29.99,
      );
      final json = item.toJson();

      expect(json['id'], 1);
      expect(json['product_id'], 1);
      expect(json['quantity'], 3);
      expect(json['price'], 29.99);
    });

    test('OrderItem roundtrip: fromJson -> toJson -> fromJson preserves data',
        () {
      final original = OrderItem.fromJson(MockData.mockOrderItemJson);
      final json = original.toJson();
      final restored = OrderItem.fromJson(json);

      expect(restored.id, original.id);
      expect(restored.productId, original.productId);
      expect(restored.quantity, original.quantity);
      expect(restored.price, original.price);
    });

    test('OrderItem with different quantities is handled correctly', () {
      final quantities = [1, 5, 10, 100];
      for (final qty in quantities) {
        final json = {
          'id': 1,
          'product_id': 1,
          'quantity': qty,
          'price': 29.99,
        };
        final item = OrderItem.fromJson(json);
        expect(item.quantity, qty);
      }
    });

    test('OrderItem price conversion handles decimal values', () {
      final item = OrderItem.fromJson({
        'id': 1,
        'product_id': 1,
        'quantity': 2,
        'price': 15.50,
      });

      expect(item.price, 15.50);
    });
  });
}
