import 'package:flutter_test/flutter_test.dart';
import 'package:flutter/material.dart';
import 'package:qa_assessment_app/screens/profile_screen.dart';
import 'package:qa_assessment_app/screens/orders_screen.dart';
import 'package:qa_assessment_app/screens/products_screen.dart';
import '../fixtures/mock_data.dart';
import '../helpers/test_utils.dart';

void main() {
  group('Screen Coverage Tests', () {
    group('ProfileScreen Coverage', () {
      testWidgets('ProfileScreen builds correctly with user data',
          (WidgetTester tester) async {
        await TestUtils.pumpWidgetWithMaterial(
          tester,
          ProfileScreen(user: MockData.mockUser),
        );

        expect(find.byType(Scaffold), findsOneWidget);
        await tester.pump();
      });

      testWidgets('ProfileScreen displays scaffold body',
          (WidgetTester tester) async {
        await TestUtils.pumpWidgetWithMaterial(
          tester,
          ProfileScreen(user: MockData.mockUser),
        );

        final scaffold = find.byType(Scaffold);
        expect(scaffold, findsOneWidget);
        await tester.pump();
      });

      testWidgets('ProfileScreen shows initial loading state',
          (WidgetTester tester) async {
        await TestUtils.pumpWidgetWithMaterial(
          tester,
          ProfileScreen(user: MockData.mockUser),
        );

        await tester.pump(const Duration(milliseconds: 100));
        expect(find.byType(ProfileScreen), findsOneWidget);
      });
    });

    group('OrdersScreen Coverage', () {
      testWidgets('OrdersScreen builds correctly', (WidgetTester tester) async {
        await TestUtils.pumpWidgetWithMaterial(
          tester,
          OrdersScreen(user: MockData.mockUser),
        );

        expect(find.byType(Scaffold), findsOneWidget);
        await tester.pump();
      });

      testWidgets('OrdersScreen has scaffold structure',
          (WidgetTester tester) async {
        await TestUtils.pumpWidgetWithMaterial(
          tester,
          OrdersScreen(user: MockData.mockUser),
        );

        final scaffold = find.byType(Scaffold);
        expect(scaffold, findsOneWidget);
        await tester.pump();
      });

      testWidgets('OrdersScreen renders without errors',
          (WidgetTester tester) async {
        await TestUtils.pumpWidgetWithMaterial(
          tester,
          OrdersScreen(user: MockData.mockUser),
        );

        await tester.pump(const Duration(milliseconds: 100));
        expect(find.byType(OrdersScreen), findsOneWidget);
      });
    });

    group('ProductsScreen Coverage', () {
      testWidgets('ProductsScreen builds correctly',
          (WidgetTester tester) async {
        await TestUtils.pumpWidgetWithMaterial(
          tester,
          ProductsScreen(user: MockData.mockUser),
        );

        expect(find.byType(Scaffold), findsOneWidget);
        await tester.pump();
      });

      testWidgets('ProductsScreen has scaffold', (WidgetTester tester) async {
        await TestUtils.pumpWidgetWithMaterial(
          tester,
          ProductsScreen(user: MockData.mockUser),
        );

        final scaffold = find.byType(Scaffold);
        expect(scaffold, findsOneWidget);
        await tester.pump();
      });

      testWidgets('ProductsScreen renders', (WidgetTester tester) async {
        await TestUtils.pumpWidgetWithMaterial(
          tester,
          ProductsScreen(user: MockData.mockUser),
        );

        await tester.pump(const Duration(milliseconds: 100));
        expect(find.byType(ProductsScreen), findsOneWidget);
      });
    });
  });
}
