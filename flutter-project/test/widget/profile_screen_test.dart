import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:qa_assessment_app/screens/profile_screen.dart';
import '../fixtures/mock_data.dart';
import '../helpers/test_utils.dart';

void main() {
  group('ProfileScreen Widget Tests', () {
    testWidgets('ProfileScreen builds with Scaffold',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
          tester, ProfileScreen(user: MockData.mockUser));
      expect(find.byType(Scaffold), findsOneWidget);
    });

    testWidgets('ProfileScreen has AppBar', (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
          tester, ProfileScreen(user: MockData.mockUser));
      expect(find.byType(AppBar), findsOneWidget);
    });

    testWidgets('ProfileScreen shows ElevatedButton',
        (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
          tester, ProfileScreen(user: MockData.mockUser));
      await tester.pump(const Duration(milliseconds: 200));
      expect(find.byType(ElevatedButton), findsWidgets);
    });

    testWidgets('ProfileScreen displays content', (WidgetTester tester) async {
      await TestUtils.pumpWidgetWithMaterial(
          tester, ProfileScreen(user: MockData.mockUser));
      await tester.pump(const Duration(milliseconds: 200));
      expect(find.byType(Text), findsWidgets);
    });
  });
}
