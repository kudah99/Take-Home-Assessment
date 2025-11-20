import 'package:flutter_test/flutter_test.dart';
import 'package:qa_assessment_app/models/user.dart';
import 'package:qa_assessment_app/models/product.dart';
import 'package:qa_assessment_app/models/order.dart';

import '../fixtures/mock_data.dart';

void main() {
  group('Data Serialization Integration Tests', () {
    test('User complete serialization flow', () {
      // Create user from JSON
      final userFromJson = User.fromJson(MockData.mockUserJson);

      // Convert back to JSON
      final jsonFromUser = userFromJson.toJson();

      // Create new user from that JSON
      final userFromReserialized = User.fromJson(jsonFromUser);

      // Verify all data is preserved
      expect(userFromReserialized.id, userFromJson.id);
      expect(userFromReserialized.name, userFromJson.name);
      expect(userFromReserialized.email, userFromJson.email);
      expect(userFromReserialized.role, userFromJson.role);
    });

    test('Product complete serialization flow', () {
      final productFromJson = Product.fromJson(MockData.mockProductJson);
      final jsonFromProduct = productFromJson.toJson();
      final productFromReserialized = Product.fromJson(jsonFromProduct);

      expect(productFromReserialized.id, productFromJson.id);
      expect(productFromReserialized.name, productFromJson.name);
      expect(productFromReserialized.description, productFromJson.description);
      expect(productFromReserialized.price, productFromJson.price);
      expect(productFromReserialized.stock, productFromJson.stock);
      expect(productFromReserialized.category, productFromJson.category);
      expect(productFromReserialized.isInStock, productFromJson.isInStock);
    });

    test('Order with items complete serialization flow', () {
      final orderFromJson = Order.fromJson(MockData.mockOrderJson);
      final jsonFromOrder = orderFromJson.toJson();
      final orderFromReserialized = Order.fromJson(jsonFromOrder);

      expect(orderFromReserialized.id, orderFromJson.id);
      expect(orderFromReserialized.userId, orderFromJson.userId);
      expect(orderFromReserialized.status, orderFromJson.status);
      expect(orderFromReserialized.totalAmount, orderFromJson.totalAmount);
    });

    test('Product list serialization', () {
      final products = MockData.mockProductsList;

      // Convert to JSON list
      final jsonList = products.map((p) => p.toJson()).toList();

      // Convert back to objects
      final reserialized =
          jsonList.map((json) => Product.fromJson(json)).toList();

      expect(reserialized.length, products.length);
      for (int i = 0; i < products.length; i++) {
        expect(reserialized[i].id, products[i].id);
        expect(reserialized[i].name, products[i].name);
        expect(reserialized[i].price, products[i].price);
      }
    });

    test('Order list serialization', () {
      final orders = MockData.mockOrdersList;

      final jsonList = orders.map((o) => o.toJson()).toList();
      final reserialized =
          jsonList.map((json) => Order.fromJson(json)).toList();

      expect(reserialized.length, orders.length);
      for (int i = 0; i < orders.length; i++) {
        expect(reserialized[i].id, orders[i].id);
        expect(reserialized[i].status, orders[i].status);
      }
    });

    test('Product in-stock status is preserved through serialization', () {
      final productInStock = Product.fromJson(MockData.mockProductJson);
      expect(productInStock.isInStock, true);

      final json = productInStock.toJson();
      final reserialized = Product.fromJson(json);
      expect(reserialized.isInStock, true);

      final productOutOfStock =
          Product.fromJson(MockData.mockProductJsonNoStock);
      expect(productOutOfStock.isInStock, false);

      final json2 = productOutOfStock.toJson();
      final reserialized2 = Product.fromJson(json2);
      expect(reserialized2.isInStock, false);
    });

    test('User without optional role serialization', () {
      final user = User.fromJson(MockData.mockUserJsonWithoutRole);
      expect(user.role, null);

      final json = user.toJson();
      expect(json['role'], null);

      final reserialized = User.fromJson(json);
      expect(reserialized.role, null);
    });

    test('Product without optional fields serialization', () {
      final product = Product.fromJson(MockData.mockProductJsonMinimal);
      expect(product.description, null);
      expect(product.category, null);

      final json = product.toJson();
      final reserialized = Product.fromJson(json);

      expect(reserialized.description, null);
      expect(reserialized.category, null);
    });

    test('Order item price precision is maintained', () {
      final itemJson = {
        'id': 1,
        'product_id': 1,
        'quantity': 2,
        'price': 15.99,
      };

      final item1 = OrderItem.fromJson(itemJson);
      final json1 = item1.toJson();
      final item2 = OrderItem.fromJson(json1);

      expect(item2.price, 15.99);
    });

    test('Multiple serialization rounds preserve data', () {
      var product = Product.fromJson(MockData.mockProductJson);

      // Serialize and deserialize 5 times
      for (int i = 0; i < 5; i++) {
        final json = product.toJson();
        product = Product.fromJson(json);
      }

      expect(product.id, MockData.mockProduct.id);
      expect(product.name, MockData.mockProduct.name);
      expect(product.price, MockData.mockProduct.price);
    });

    test('Order items array serialization', () {
      final order = Order.fromJson(MockData.mockOrderJson);
      expect(order.items, isNotNull);
      expect(order.items!.length, 1);

      final json = order.toJson();
      expect(json['items'], isNotNull);
      expect(json['items'].length, 1);

      final reserialized = Order.fromJson(json);
      expect(reserialized.items, isNotNull);
      expect(reserialized.items!.length, 1);
      expect(reserialized.items![0].productId, 1);
    });
  });
}
