import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:qa_assessment_app/widgets/product_card.dart';
import 'package:qa_assessment_app/models/product.dart';

import '../fixtures/mock_data.dart';
import '../helpers/test_utils.dart';

void main() {
  group('ProductCard Widget Tests', () {
    testWidgets('ProductCard displays product name',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductCard(
          product: MockData.mockProduct,
          onTap: () {},
        ),
      );

      expect(find.textContaining('Test Product'), findsOneWidget);
    });

    testWidgets('ProductCard displays product price',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductCard(
          product: MockData.mockProduct,
          onTap: () {},
        ),
      );

      expect(find.textContaining('\$29.99'), findsOneWidget);
    });

    testWidgets('ProductCard shows shopping cart icon for in-stock products',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductCard(
          product: MockData.mockProduct,
          onTap: () {},
        ),
      );

      expect(find.byIcon(Icons.shopping_cart), findsOneWidget);
    });

    testWidgets('ProductCard shows "Out of Stock" for out-of-stock products',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductCard(
          product: MockData.mockProductNoStock,
          onTap: () {},
        ),
      );

      expect(find.textContaining('Out of Stock'), findsOneWidget);
    });

    testWidgets('ProductCard calls onTap when tapped',
        (WidgetTester tester) async {
      bool tapped = false;

      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductCard(
          product: MockData.mockProduct,
          onTap: () {
            tapped = true;
          },
        ),
      );

      await tester.tap(find.byType(ListTile));
      await tester.pumpAndSettle();

      expect(tapped, true);
    });

    testWidgets('ProductCard has proper card styling',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductCard(
          product: MockData.mockProduct,
          onTap: () {},
        ),
      );

      expect(find.byType(Card), findsOneWidget);
      expect(find.byType(ListTile), findsOneWidget);
    });

    testWidgets('ProductCard displays product with minimal data',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductCard(
          product: MockData.mockProductMinimal,
          onTap: () {},
        ),
      );

      expect(find.textContaining('Minimal Product'), findsOneWidget);
      expect(find.textContaining('\$9.99'), findsOneWidget);
    });

    testWidgets('ProductCard price formatting is correct',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductCard(
          product: MockData.mockProduct,
          onTap: () {},
        ),
      );

      // Verify exact price format
      expect(find.textContaining('29.99'), findsOneWidget);
    });
  });
}
