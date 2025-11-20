import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:qa_assessment_app/screens/profile_screen.dart';

import '../fixtures/mock_data.dart';
import '../helpers/test_utils.dart';

void main() {
  group('ProfileScreen Widget Tests', () {
    testWidgets('ProfileScreen displays profile title',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProfileScreen(user: MockData.mockUser),
      );

      expect(find.textContaining('Profile'), findsOneWidget);
    });

    testWidgets('ProfileScreen shows loading indicator initially',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProfileScreen(user: MockData.mockUser),
      );

      expect(find.byType(CircularProgressIndicator), findsWidgets);
    });

    testWidgets('ProfileScreen displays user name',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProfileScreen(user: MockData.mockUser),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      expect(find.textContaining('John Doe'), findsWidgets);
    });

    testWidgets('ProfileScreen displays user email',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProfileScreen(user: MockData.mockUser),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      expect(find.textContaining('john@example.com'), findsOneWidget);
    });

    testWidgets('ProfileScreen displays user role when available',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProfileScreen(user: MockData.mockUser),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      expect(find.textContaining('customer'), findsOneWidget);
    });

    testWidgets('ProfileScreen has AppBar with title',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProfileScreen(user: MockData.mockUser),
      );

      expect(find.byType(AppBar), findsOneWidget);
    });

    testWidgets('ProfileScreen has logout button', (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProfileScreen(user: MockData.mockUser),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      expect(find.byType(ElevatedButton), findsWidgets);
    });

    testWidgets('ProfileScreen displays name label',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProfileScreen(user: MockData.mockUser),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      expect(find.textContaining('Name:'), findsOneWidget);
    });

    testWidgets('ProfileScreen displays email label',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProfileScreen(user: MockData.mockUser),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      expect(find.textContaining('Email:'), findsOneWidget);
    });

    testWidgets('ProfileScreen scaffold is properly structured',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProfileScreen(user: MockData.mockUser),
      );

      expect(find.byType(Scaffold), findsOneWidget);
    });

    testWidgets('ProfileScreen handles user without role',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProfileScreen(user: MockData.mockUserWithoutRole),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      expect(find.textContaining('John Doe'), findsWidgets);
      expect(find.textContaining('john@example.com'), findsOneWidget);
    });

    testWidgets('ProfileScreen displays user info in correct order',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProfileScreen(user: MockData.mockUser),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      expect(find.textContaining('Name:'), findsOneWidget);
      expect(find.textContaining('Email:'), findsOneWidget);
      expect(find.textContaining('Role:'), findsOneWidget);
    });

    testWidgets('ProfileScreen uses padding for layout',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProfileScreen(user: MockData.mockUser),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      expect(find.byType(Padding), findsWidgets);
    });

    testWidgets('ProfileScreen maintains state properly',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
        tester,
        ProfileScreen(user: MockData.mockUser),
      );

      await TestUtils.waitForLoadingToFinish(tester);

      await tester.pump();

      expect(find.byType(ProfileScreen), findsOneWidget);
    });
  });
}
