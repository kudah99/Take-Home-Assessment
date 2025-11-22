import 'package:qa_assessment_app/models/user.dart';
import 'package:qa_assessment_app/models/product.dart';
import 'package:qa_assessment_app/models/order.dart';

/// Mock data fixtures for testing
class MockData {
  // User fixtures
  static const Map<String, dynamic> mockUserJson = {
    'id': 1,
  'name': 'Kudakwashe Chris Chipangura',
    'email': 'kcchipangura@gmail.com',
    'role': 'customer',
  };

  static const Map<String, dynamic> mockUserJsonWithoutRole = {
    'id': 1,
    'name': 'Kudakwashe Chris Chipangura',
    'email': 'kcchipangura@gmail.com',
  };

  static final User mockUser = User(
    id: 1,
    name: 'Kudakwashe Chris Chipangura',
    email: 'kcchipangura@gmail.com',
    role: 'customer',
  );

  static final User mockUserWithoutRole = User(
    id: 1,
    name: 'Kudakwashe Chris Chipangura',
    email: 'kcchipangura@gmail.com',
  );

  // Product fixtures
  static const Map<String, dynamic> mockProductJson = {
    'id': 1,
    'name': 'Test Product',
    'description': 'A test product description',
    'price': 29.99,
    'stock': 10,
    'category': 'Electronics',
  };

  static const Map<String, dynamic> mockProductJsonNoStock = {
    'id': 2,
    'name': 'Out of Stock Product',
    'description': 'A product with no stock',
    'price': 19.99,
    'stock': 0,
    'category': 'Books',
  };

  static const Map<String, dynamic> mockProductJsonMinimal = {
    'id': 3,
    'name': 'Minimal Product',
    'price': 9.99,
    'stock': 5,
  };

  static final Product mockProduct = Product(
    id: 1,
    name: 'Test Product',
    description: 'A test product description',
    price: 29.99,
    stock: 10,
    category: 'Electronics',
  );

  static final Product mockProductNoStock = Product(
    id: 2,
    name: 'Out of Stock Product',
    description: 'A product with no stock',
    price: 19.99,
    stock: 0,
    category: 'Books',
  );

  static final Product mockProductMinimal = Product(
    id: 3,
    name: 'Minimal Product',
    price: 9.99,
    stock: 5,
  );

  static final List<Product> mockProductsList = [
    mockProduct,
    mockProductNoStock,
    mockProductMinimal,
  ];

  // Order fixtures
  static const Map<String, dynamic> mockOrderJson = {
    'id': 1,
    'user_id': 1,
    'status': 'completed',
    'total_amount': 89.97,
    'items': [
      {
        'id': 1,
        'product_id': 1,
        'quantity': 3,
        'price': 29.99,
      }
    ],
  };

  static const Map<String, dynamic> mockOrderJsonWithoutItems = {
    'id': 2,
    'user_id': 1,
    'status': 'pending',
    'total_amount': 50.00,
  };

  static const Map<String, dynamic> mockOrderItemJson = {
    'id': 1,
    'product_id': 1,
    'quantity': 3,
    'price': 29.99,
  };

  static final Order mockOrder = Order(
    id: 1,
    userId: 1,
    status: 'completed',
    totalAmount: 89.97,
  );

  static final Order mockOrderWithItems = Order(
    id: 1,
    userId: 1,
    status: 'completed',
    totalAmount: 89.97,
    items: [
      OrderItem(
        id: 1,
        productId: 1,
        quantity: 3,
        price: 29.99,
      ),
    ],
  );

  static final List<Order> mockOrdersList = [
    mockOrder,
    Order(
      id: 2,
      userId: 1,
      status: 'pending',
      totalAmount: 50.00,
    ),
  ];

  // API response fixtures
  static const Map<String, dynamic> mockLoginResponse = {
    'access_token': 'test-token-123',
    'user': mockUserJson,
  };

  static const List<Map<String, dynamic>> mockProductsResponse = [
    mockProductJson,
    mockProductJsonNoStock,
    mockProductJsonMinimal,
  ];
}
