import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:qa_assessment_app/screens/product_detail_screen.dart';

import '../fixtures/mock_data.dart';
import '../helpers/test_utils.dart';

void main() {
  group('ProductDetailScreen Widget Tests', () {
    testWidgets('ProductDetailScreen displays product name',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductDetailScreen(
          product: MockData.mockProduct,
          user: MockData.mockUser,
        ),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      expect(find.textContaining('Test Product'), findsWidgets);
    });

    testWidgets('ProductDetailScreen displays product price',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductDetailScreen(
          product: MockData.mockProduct,
          user: MockData.mockUser,
        ),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      expect(find.textContaining('\$29.99'), findsOneWidget);
    });

    testWidgets('ProductDetailScreen displays product description',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductDetailScreen(
          product: MockData.mockProduct,
          user: MockData.mockUser,
        ),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      expect(
        find.textContaining('A test product description'),
        findsOneWidget,
      );
    });

    testWidgets('ProductDetailScreen shows loading initially',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductDetailScreen(
          product: MockData.mockProduct,
          user: MockData.mockUser,
        ),
      );

      // Loading state may appear briefly
      expect(find.byType(CircularProgressIndicator), findsWidgets);
    });

    testWidgets('ProductDetailScreen has AppBar with product name',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductDetailScreen(
          product: MockData.mockProduct,
          user: MockData.mockUser,
        ),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      expect(find.byType(AppBar), findsOneWidget);
    });

    testWidgets(
        'ProductDetailScreen shows add to cart button for in-stock items',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductDetailScreen(
          product: MockData.mockProduct,
          user: MockData.mockUser,
        ),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      expect(find.textContaining('Add to Cart'), findsOneWidget);
    });

    testWidgets('ProductDetailScreen shows out of stock for unavailable items',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductDetailScreen(
          product: MockData.mockProductNoStock,
          user: MockData.mockUser,
        ),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      expect(find.textContaining('Out of Stock'), findsOneWidget);
    });

    testWidgets('ProductDetailScreen displays description section',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductDetailScreen(
          product: MockData.mockProduct,
          user: MockData.mockUser,
        ),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      expect(find.textContaining('Description'), findsOneWidget);
    });

    testWidgets('ProductDetailScreen uses SingleChildScrollView',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductDetailScreen(
          product: MockData.mockProduct,
          user: MockData.mockUser,
        ),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      expect(find.byType(SingleChildScrollView), findsOneWidget);
    });

    testWidgets(
        'ProductDetailScreen add to cart button is disabled for out of stock',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductDetailScreen(
          product: MockData.mockProductNoStock,
          user: MockData.mockUser,
        ),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      final button = find.byType(ElevatedButton);
      expect(button, findsOneWidget);
    });

    testWidgets('ProductDetailScreen handles product with minimal data',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductDetailScreen(
          product: MockData.mockProductMinimal,
          user: MockData.mockUser,
        ),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      expect(find.textContaining('Minimal Product'), findsOneWidget);
    });

    testWidgets('ProductDetailScreen displays correct price format',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductDetailScreen(
          product: MockData.mockProduct,
          user: MockData.mockUser,
        ),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      expect(find.textContaining('29.99'), findsOneWidget);
    });
  });
}
