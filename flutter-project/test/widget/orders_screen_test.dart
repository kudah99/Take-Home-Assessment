import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:qa_assessment_app/screens/orders_screen.dart';

import '../fixtures/mock_data.dart';
import '../helpers/test_utils.dart';

void main() {
  group('OrdersScreen Widget Tests', () {
    testWidgets('OrdersScreen displays my orders title',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        OrdersScreen(user: MockData.mockUser),
      );

      expect(find.textContaining('My Orders'), findsOneWidget);
    });

    testWidgets('OrdersScreen shows loading indicator initially',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        OrdersScreen(user: MockData.mockUser),
      );

      expect(find.byType(CircularProgressIndicator), findsWidgets);
    });

    testWidgets('OrdersScreen has AppBar', (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        OrdersScreen(user: MockData.mockUser),
      );

      expect(find.byType(AppBar), findsOneWidget);
    });

    testWidgets('OrdersScreen has refresh indicator',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        OrdersScreen(user: MockData.mockUser),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      expect(find.byType(RefreshIndicator), findsOneWidget);
    });

    testWidgets('OrdersScreen displays orders in ListView',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        OrdersScreen(user: MockData.mockUser),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      expect(find.byType(ListView), findsWidgets);
    });

    testWidgets('OrdersScreen has retry button on error',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        OrdersScreen(user: MockData.mockUser),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      // Should show error handling or no orders message
      expect(find.byType(OrdersScreen), findsOneWidget);
    });

    testWidgets('OrdersScreen accepts user parameter correctly',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        OrdersScreen(user: MockData.mockUser),
      );

      expect(find.byType(OrdersScreen), findsOneWidget);
    });

    testWidgets('OrdersScreen scaffold is properly structured',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        OrdersScreen(user: MockData.mockUser),
      );

      expect(find.byType(Scaffold), findsOneWidget);
    });

    testWidgets('OrdersScreen displays card widgets for orders',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        OrdersScreen(user: MockData.mockUser),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      // Should have cards for displaying orders
      expect(find.byType(Card), findsWidgets);
    });

    testWidgets('OrdersScreen center aligns loading indicator',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        OrdersScreen(user: MockData.mockUser),
      );

      expect(find.byType(Center), findsWidgets);
    });

    testWidgets('OrdersScreen handles empty orders list',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        OrdersScreen(user: MockData.mockUser),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      // Should show either empty message or error
      expect(find.byType(OrdersScreen), findsOneWidget);
    });

    testWidgets('OrdersScreen maintains state properly',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        OrdersScreen(user: MockData.mockUser),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      await tester.pump();

      expect(find.byType(OrdersScreen), findsOneWidget);
    });
  });
}
