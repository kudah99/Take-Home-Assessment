import 'package:flutter_test/flutter_test.dart';
import 'package:flutter/material.dart';
import 'package:qa_assessment_app/main.dart';

void main() {
  group('Main Application Tests', () {
    testWidgets('App initializes and renders MaterialApp',
        (WidgetTester tester) async {
      await tester.pumpWidget(const MyApp());

      // Should have MaterialApp
      expect(find.byType(MaterialApp), findsOneWidget);
    });

    testWidgets('App has proper theme configuration',
        (WidgetTester tester) async {
      await tester.pumpWidget(const MyApp());

      // Should have Material widget from MaterialApp
      expect(find.byType(Material), findsWidgets);
    });

    testWidgets('App renders without errors', (WidgetTester tester) async {
      await tester.pumpWidget(const MyApp());

      await tester.pump();

      // Should not throw any errors
      expect(find.byType(MyApp), findsOneWidget);
    });

    testWidgets('App has home widget', (WidgetTester tester) async {
      await tester.pumpWidget(const MyApp());

      // App should render its home widget
      expect(find.byType(MaterialApp), findsOneWidget);
    });

    testWidgets('App theme is properly configured',
        (WidgetTester tester) async {
      await tester.pumpWidget(const MyApp());

      final materialApp =
          find.byType(MaterialApp).evaluate().first.widget as MaterialApp;

      // Should have theme data
      expect(materialApp.theme, isNotNull);
    });

    testWidgets('App renders with useMaterial3', (WidgetTester tester) async {
      await tester.pumpWidget(const MyApp());

      final materialApp =
          find.byType(MaterialApp).evaluate().first.widget as MaterialApp;

      // Theme should use Material3
      expect(materialApp.theme?.useMaterial3, isTrue);
    });
  });
}
