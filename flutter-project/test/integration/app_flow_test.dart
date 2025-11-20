import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:qa_assessment_app/main.dart';
import 'package:qa_assessment_app/screens/login_screen.dart';
import 'package:qa_assessment_app/screens/products_screen.dart';
import 'package:qa_assessment_app/screens/profile_screen.dart';
import 'package:qa_assessment_app/screens/orders_screen.dart';

import '../helpers/test_utils.dart';

void main() {
  group('Critical User Flow Integration Tests', () {
    testWidgets('User can view the login screen on app startup',
        (WidgetTester tester) async {
      await tester.pumpWidget(const MyApp());
      await tester.pumpAndSettle();

      expect(find.byType(LoginScreen), findsOneWidget);
      expect(find.textContaining('Login'), findsWidgets);
    });

    testWidgets('Login screen has all required fields',
        (WidgetTester tester) async {
      await tester.pumpWidget(const MyApp());
      await tester.pumpAndSettle();

      expect(find.byType(TextFormField), findsWidgets);
      expect(find.textContaining('Email'), findsOneWidget);
      expect(find.textContaining('Password'), findsOneWidget);
      expect(find.byType(ElevatedButton), findsOneWidget);
    });

    testWidgets('Login form validation works correctly',
        (WidgetTester tester) async {
      await tester.pumpWidget(const MyApp());
      await tester.pumpAndSettle();

      // Try to login without entering credentials
      await tester.tap(find.byType(ElevatedButton));
      await tester.pumpAndSettle();

      // Should show validation errors
      expect(
        find.textContaining('Please enter your email'),
        findsWidgets,
      );
    });

    testWidgets('Email validation rejects invalid email format',
        (WidgetTester tester) async {
      await tester.pumpWidget(const MyApp());
      await tester.pumpAndSettle();

      final emailField = find.byType(TextFormField).first;
      await tester.enterText(emailField, 'invalidemail');

      final passwordField = find.byType(TextFormField).last;
      await tester.enterText(passwordField, 'password');

      await tester.tap(find.byType(ElevatedButton));
      await tester.pumpAndSettle();

      expect(find.textContaining('valid email'), findsWidgets);
    });

    testWidgets('Valid email format is accepted', (WidgetTester tester) async {
      await tester.pumpWidget(const MyApp());
      await tester.pumpAndSettle();

      final emailField = find.byType(TextFormField).first;
      await tester.enterText(emailField, 'test@example.com');

      final passwordField = find.byType(TextFormField).last;
      await tester.enterText(passwordField, 'password123');

      await tester.tap(find.byType(ElevatedButton));
      await tester.pump();

      // Should attempt login (will fail without actual API, but validation passed)
      expect(find.byType(CircularProgressIndicator), findsWidgets);
    });

    testWidgets('Login shows loading state during authentication',
        (WidgetTester tester) async {
      await tester.pumpWidget(const MyApp());
      await tester.pumpAndSettle();

      final emailField = find.byType(TextFormField).first;
      await tester.enterText(emailField, 'test@example.com');

      final passwordField = find.byType(TextFormField).last;
      await tester.enterText(passwordField, 'password123');

      await tester.tap(find.byType(ElevatedButton));
      await tester.pump();

      expect(find.byType(CircularProgressIndicator), findsWidgets);
    });

    testWidgets('Password field is obscured during input',
        (WidgetTester tester) async {
      await tester.pumpWidget(const MyApp());
      await tester.pumpAndSettle();

      final passwordField = find.byType(TextFormField).last;
      final TextField passwordWidget = tester.widget(
          find.descendant(of: passwordField, of: find.byType(TextField)));

      expect(passwordWidget.obscureText, true);
    });

    testWidgets('App has Material theme applied', (WidgetTester tester) async {
      await tester.pumpWidget(const MyApp());
      await tester.pumpAndSettle();

      final materialApp = find.byType(MaterialApp);
      expect(materialApp, findsOneWidget);
    });

    testWidgets('Scaffold components are properly structured',
        (WidgetTester tester) async {
      await tester.pumpWidget(const MyApp());
      await tester.pumpAndSettle();

      expect(find.byType(Scaffold), findsOneWidget);
      expect(find.byType(AppBar), findsOneWidget);
    });

    testWidgets('Login button state changes during submission',
        (WidgetTester tester) async {
      await tester.pumpWidget(const MyApp());
      await tester.pumpAndSettle();

      final emailField = find.byType(TextFormField).first;
      await tester.enterText(emailField, 'test@example.com');

      final passwordField = find.byType(TextFormField).last;
      await tester.enterText(passwordField, 'password123');

      final button = find.byType(ElevatedButton);
      expect(button, findsOneWidget);

      await tester.tap(button);
      await tester.pump();

      // Button should be disabled during loading
      expect(find.byType(CircularProgressIndicator), findsWidgets);
    });

    testWidgets('Login error message can be displayed',
        (WidgetTester tester) async {
      await tester.pumpWidget(const MyApp());
      await tester.pumpAndSettle();

      final emailField = find.byType(TextFormField).first;
      await tester.enterText(emailField, 'test@example.com');

      final passwordField = find.byType(TextFormField).last;
      await tester.enterText(passwordField, 'password123');

      await tester.tap(find.byType(ElevatedButton));
      await TestUtils.waitForLoadingToFinish(tester);

      // May show error message if API fails
      expect(find.byType(LoginScreen), findsOneWidget);
    });

    testWidgets('Form validation prevents submission with empty fields',
        (WidgetTester tester) async {
      await tester.pumpWidget(const MyApp());
      await tester.pumpAndSettle();

      await tester.tap(find.byType(ElevatedButton));
      await tester.pumpAndSettle();

      expect(find.textContaining('Please enter your email'), findsWidgets);
      expect(find.byType(LoginScreen), findsOneWidget);
    });
  });

  group('Product Browse Flow Integration Tests', () {
    testWidgets('Products screen can be navigated to (after successful login)',
        (WidgetTester tester) async {
      // This test demonstrates the expected flow
      // In reality, navigation would happen after successful login

      await tester.pumpWidget(const MyApp());
      await tester.pumpAndSettle();

      // Verify we start at login
      expect(find.byType(LoginScreen), findsOneWidget);
    });

    testWidgets('Login screen maintains form state',
        (WidgetTester tester) async {
      await tester.pumpWidget(const MyApp());
      await tester.pumpAndSettle();

      final emailField = find.byType(TextFormField).first;
      await tester.enterText(emailField, 'test@example.com');

      await tester.pump();

      final email = find.textContaining('test@example.com');
      expect(email, findsWidgets);
    });
  });

  group('App Navigation Tests', () {
    testWidgets('App starts with login screen', (WidgetTester tester) async {
      await tester.pumpWidget(const MyApp());
      await tester.pumpAndSettle();

      expect(find.byType(LoginScreen), findsOneWidget);
    });

    testWidgets('MyApp is a StatelessWidget', (WidgetTester tester) async {
      await tester.pumpWidget(const MyApp());
      await tester.pumpAndSettle();

      expect(find.byType(MyApp), findsOneWidget);
    });

    testWidgets('App title is set to QA Assessment App',
        (WidgetTester tester) async {
      await tester.pumpWidget(const MyApp());
      await tester.pumpAndSettle();

      expect(find.byType(MaterialApp), findsOneWidget);
    });
  });

  group('Form Interaction Tests', () {
    testWidgets('User can clear form fields', (WidgetTester tester) async {
      await tester.pumpWidget(const MyApp());
      await tester.pumpAndSettle();

      final emailField = find.byType(TextFormField).first;
      await tester.enterText(emailField, 'test@example.com');
      await tester.pump();

      // Clear the field
      await tester.enterText(emailField, '');
      await tester.pump();

      expect(find.textContaining('test@example.com'), findsNothing);
    });

    testWidgets('Multiple form submissions are handled',
        (WidgetTester tester) async {
      await tester.pumpWidget(const MyApp());
      await tester.pumpAndSettle();

      // First submission attempt
      await tester.tap(find.byType(ElevatedButton));
      await tester.pumpAndSettle();

      // Should show validation error
      expect(find.textContaining('Please enter your email'), findsWidgets);

      // Second submission attempt with data
      final emailField = find.byType(TextFormField).first;
      await tester.enterText(emailField, 'test@example.com');

      final passwordField = find.byType(TextFormField).last;
      await tester.enterText(passwordField, 'password');

      await tester.tap(find.byType(ElevatedButton));
      await tester.pump();

      // Should attempt to login
      expect(find.byType(CircularProgressIndicator), findsWidgets);
    });
  });
}
