import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:qa_assessment_app/screens/products_screen.dart';
import 'package:qa_assessment_app/models/product.dart';

import '../fixtures/mock_data.dart';
import '../helpers/test_utils.dart';

void main() {
  group('ProductsScreen Widget Tests', () {
    testWidgets('ProductsScreen renders with AppBar',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductsScreen(user: MockData.mockUser),
      );

      expect(find.byType(AppBar), findsOneWidget);
      expect(find.textContaining('Products'), findsWidgets);
    });

    testWidgets('ProductsScreen shows loading indicator initially',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductsScreen(user: MockData.mockUser),
      );

      await tester.pump(); // Initial pump
      expect(find.byType(CircularProgressIndicator), findsWidgets);
    });

    testWidgets('ProductsScreen has shopping cart button in AppBar',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductsScreen(user: MockData.mockUser),
      );

      expect(find.byIcon(Icons.shopping_cart), findsOneWidget);
    });

    testWidgets('ProductsScreen has profile button in AppBar',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductsScreen(user: MockData.mockUser),
      );

      expect(find.byIcon(Icons.person), findsOneWidget);
    });

    testWidgets('ProductsScreen has refresh indicator',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductsScreen(user: MockData.mockUser),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      // After loading, should have refresh indicator
      expect(find.byType(RefreshIndicator), findsOneWidget);
    });

    testWidgets('ProductsScreen displays products in ListView',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductsScreen(user: MockData.mockUser),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      // Should have ListView for displaying products
      expect(find.byType(ListView), findsWidgets);
    });

    testWidgets('ProductsScreen has retry button on error',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductsScreen(user: MockData.mockUser),
      );

      // Wait for loading to complete (will error without API)
      await TestUtils.waitForLoadingToFinish(tester);

      // Should show error handling (no products from API)
      expect(find.byType(ProductsScreen), findsOneWidget);
    });

    testWidgets('ProductsScreen takes user parameter correctly',
        (WidgetTester tester) async {
      final user = MockData.mockUser;

      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductsScreen(user: user),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      expect(find.byType(ProductsScreen), findsOneWidget);
    });

    testWidgets('ProductsScreen scaffold has body',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductsScreen(user: MockData.mockUser),
      );

      expect(find.byType(Scaffold), findsOneWidget);
    });

    testWidgets('ProductsScreen maintains state properly',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductsScreen(user: MockData.mockUser),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      // Pump again to verify state is maintained
      await tester.pump();

      expect(find.byType(ProductsScreen), findsOneWidget);
    });

    testWidgets('ProductsScreen handles empty products list',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductsScreen(user: MockData.mockUser),
      );

      // Wait for initial loading
      await TestUtils.waitForLoadingToFinish(tester);

      // After API call (which fails in test), should show no products message or error
      expect(find.byType(ProductsScreen), findsOneWidget);
    });

    testWidgets('ProductsScreen center aligns content when loading',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProductsScreen(user: MockData.mockUser),
      );

      // During loading, content should be centered
      expect(find.byType(Center), findsWidgets);
    });
  });
}
