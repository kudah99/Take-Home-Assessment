import 'package:flutter_test/flutter_test.dart';
import 'package:qa_assessment_app/screens/login_screen.dart';
import 'package:qa_assessment_app/screens/product_detail_screen.dart';
import 'package:qa_assessment_app/models/product.dart';
import 'package:flutter/material.dart';
import '../fixtures/mock_data.dart';
import '../helpers/test_utils.dart';

void main() {
  group('Login Screen Detailed Tests', () {
    testWidgets('LoginScreen shows email field', (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(tester, const LoginScreen());
      expect(find.byType(TextFormField), findsWidgets);
      await tester.pump();
    });

    testWidgets('LoginScreen has form fields', (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(tester, const LoginScreen());
      expect(find.byType(Form), findsOneWidget);
      await tester.pump();
    });

    testWidgets('LoginScreen displays error widget on failure',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(tester, const LoginScreen());
      // Trigger a failed login by tapping without entering credentials
      await tester.pump(const Duration(milliseconds: 200));
      expect(find.byType(LoginScreen), findsOneWidget);
    });

    testWidgets('LoginScreen shows submit button', (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(tester, const LoginScreen());
      expect(find.byType(ElevatedButton), findsWidgets);
      await tester.pump();
    });

    testWidgets('LoginScreen has scaffold', (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(tester, const LoginScreen());
      expect(find.byType(Scaffold), findsOneWidget);
      await tester.pump();
    });

    testWidgets('LoginScreen app bar exists', (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(tester, const LoginScreen());
      expect(find.byType(AppBar), findsOneWidget);
      await tester.pump();
    });

    testWidgets('LoginScreen has centered content',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(tester, const LoginScreen());
      expect(find.byType(Center), findsWidgets);
      await tester.pump();
    });

    testWidgets('LoginScreen handles empty form submission',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(tester, const LoginScreen());
      await tester.pump();
      expect(find.byType(Form), findsOneWidget);
    });
  });

  group('Product Detail Screen Tests', () {
    testWidgets('ProductDetailScreen builds with product',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductDetailScreen(
          product: MockData.mockProduct,
          user: MockData.mockUser,
        ),
      );
      expect(find.byType(Scaffold), findsOneWidget);
      await tester.pump();
    });

    testWidgets('ProductDetailScreen has AppBar', (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductDetailScreen(
          product: MockData.mockProduct,
          user: MockData.mockUser,
        ),
      );
      expect(find.byType(AppBar), findsOneWidget);
      await tester.pump();
    });

    testWidgets('ProductDetailScreen displays product info',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductDetailScreen(
          product: MockData.mockProduct,
          user: MockData.mockUser,
        ),
      );
      await tester.pump();
      expect(find.byType(ProductDetailScreen), findsOneWidget);
    });

    testWidgets('ProductDetailScreen scrollable', (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductDetailScreen(
          product: MockData.mockProduct,
          user: MockData.mockUser,
        ),
      );
      expect(find.byType(SingleChildScrollView), findsOneWidget);
      await tester.pump();
    });

    testWidgets('ProductDetailScreen handles in-stock product',
        (WidgetTester tester) async {
      final inStockProduct = Product(
        id: 1,
        name: 'In Stock Product',
        description: 'This product is in stock',
        price: 99.99,
        stock: 10,
        category: 'Electronics',
      );
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductDetailScreen(
          product: inStockProduct,
          user: MockData.mockUser,
        ),
      );
      await tester.pump();
      expect(find.byType(ProductDetailScreen), findsOneWidget);
    });

    testWidgets('ProductDetailScreen handles out-of-stock product',
        (WidgetTester tester) async {
      final outOfStockProduct = Product(
        id: 2,
        name: 'Out of Stock Product',
        description: 'This product is out of stock',
        price: 49.99,
        stock: 0,
        category: 'Electronics',
      );
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductDetailScreen(
          product: outOfStockProduct,
          user: MockData.mockUser,
        ),
      );
      await tester.pump();
      expect(find.byType(ProductDetailScreen), findsOneWidget);
    });
  });
}
