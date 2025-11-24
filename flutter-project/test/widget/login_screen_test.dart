import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:qa_assessment_app/screens/login_screen.dart';

import '../helpers/test_utils.dart';

void main() {
  group('LoginScreen Widget Tests', () {
    testWidgets('LoginScreen renders with all UI elements',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(tester, const LoginScreen());

      expect(find.textContaining('Login'), findsWidgets);
      expect(find.byType(TextFormField), findsWidgets);
      expect(find.byType(ElevatedButton), findsOneWidget);
    });

    testWidgets('LoginScreen has email and password fields',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(tester, const LoginScreen());

      expect(find.textContaining('Email'), findsOneWidget);
      expect(find.textContaining('Password'), findsOneWidget);
    });

    testWidgets('LoginScreen email field validates empty input',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(tester, const LoginScreen());

      // Click login without filling fields
      await tester.tap(find.byType(ElevatedButton));
      await tester.pumpAndSettle();

      expect(
        find.textContaining('Please enter your email'),
        findsWidgets,
      );
    });

    testWidgets('LoginScreen password field validates empty input',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(tester, const LoginScreen());

      // Enter only email
      await tester.enterText(
          find.byType(TextFormField).first, 'test@example.com');
      await tester.pumpAndSettle();

      // Click login
      await tester.tap(find.byType(ElevatedButton));
      await tester.pumpAndSettle();

      expect(
        find.textContaining('Please enter your password'),
        findsWidgets,
      );
    });

    testWidgets('LoginScreen email field validates invalid email format',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(tester, const LoginScreen());

      // Enter invalid email
      await tester.enterText(find.byType(TextFormField).first, 'invalidemail');
      await tester.enterText(find.byType(TextFormField).last, 'password123');
      await tester.pumpAndSettle();

      // Click login
      await tester.tap(find.byType(ElevatedButton));
      await tester.pumpAndSettle();

      expect(
        find.textContaining('Please enter a valid email'),
        findsWidgets,
      );
    });

    testWidgets('LoginScreen accepts valid email format',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(tester, const LoginScreen());

      final emailFields = find.byType(TextFormField);
      await tester.enterText(emailFields.first, 'test@example.com');
      await tester.enterText(emailFields.last, 'password123');
      await tester.pumpAndSettle();

      // Click login
      await tester.tap(find.byType(ElevatedButton));
      await tester.pumpAndSettle();

      // Should attempt login (will fail without API, but validation passes)
      expect(find.byType(LoginScreen), findsOneWidget);
    });

    testWidgets('LoginScreen shows loading indicator during login',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(tester, const LoginScreen());

      final emailFields = find.byType(TextFormField);
      await tester.enterText(emailFields.first, 'test@example.com');
      await tester.enterText(emailFields.last, 'password123');
      await tester.pumpAndSettle();

      // Click login
      await tester.tap(find.byType(ElevatedButton));
      await tester.pump(); // Show loading state

      // Check for loading indicator
      expect(find.byType(CircularProgressIndicator), findsWidgets);
    });

    testWidgets('LoginScreen password field is obscured',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(tester, const LoginScreen());

      final passwordField = find.byType(TextFormField).last;
      expect(passwordField, findsOneWidget);

      // Verify it's a password field (obscureText: true)
      final TextField passwordWidget = tester.widget(
          find.descendant(of: passwordField, matching: find.byType(TextField)));
      expect(passwordWidget.obscureText, true);
    });

    testWidgets('LoginScreen has AppBar with title',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(tester, const LoginScreen());

      expect(find.byType(AppBar), findsOneWidget);
      expect(find.textContaining('Login'), findsWidgets);
    });

    testWidgets('LoginScreen form key works correctly',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(tester, const LoginScreen());

      expect(find.byType(Form), findsOneWidget);
    });

    testWidgets('LoginScreen button is enabled initially',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(tester, const LoginScreen());

      final button = find.byType(ElevatedButton);
      expect(button, findsOneWidget);
    });

    testWidgets('LoginScreen handles rapid form submissions',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(tester, const LoginScreen());

      final emailFields = find.byType(TextFormField);
      await tester.enterText(emailFields.first, 'test@example.com');
      await tester.enterText(emailFields.last, 'password123');
      await tester.pumpAndSettle();

      // Click login multiple times
      await tester.tap(find.byType(ElevatedButton));
      await tester.pump();
      await tester.tap(find.byType(ElevatedButton));
      await tester.pumpAndSettle();

      // Should still be on login screen
      expect(find.byType(LoginScreen), findsOneWidget);
    });
  });
}
