import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

/// Utility functions for testing
class TestUtils {
  /// Pumps a widget with Material wrapping and waits for it to be rendered
  static Future<void> pumpWidgetWithMaterial(
    WidgetTester tester,
    Widget widget, {
    Duration timeout = const Duration(seconds: 1),
  }) async {
    await tester.pumpWidget(
      MaterialApp(
        home: widget,
        theme: ThemeData(
          colorScheme: ColorScheme.fromSeed(seedColor: Colors.blue),
          useMaterial3: true,
        ),
      ),
    );
    try {
      await tester.pumpAndSettle(timeout);
    } catch (_) {
      // Ignore timeout - just pump once to settle
      await tester.pump();
    }
  }

  /// Wait for loading indicators to disappear
  static Future<void> waitForLoadingToFinish(WidgetTester tester) async {
    try {
      await tester.pumpAndSettle(const Duration(milliseconds: 500));
    } catch (_) {
      // Ignore timeout, just pump
      await tester.pump(const Duration(milliseconds: 500));
    }
  }

  /// Finds and taps a button by text
  static Future<void> tapButtonByText(WidgetTester tester, String text) async {
    await tester.tap(find.textContaining(text));
    try {
      await tester.pumpAndSettle(const Duration(milliseconds: 500));
    } catch (_) {
      await tester.pump();
    }
  }

  /// Enters text into a TextFormField
  static Future<void> enterText(
    WidgetTester tester,
    String text, {
    String? hintText,
  }) async {
    if (hintText != null) {
      await tester.enterText(find.byType(TextFormField).last, text);
    } else {
      await tester.enterText(find.byType(TextFormField), text);
    }
    await tester.pump();
  }

  /// Finds text in the widget tree
  static Finder findText(String text) {
    return find.textContaining(text);
  }

  /// Finds widget by key
  static Finder findByKey(String key) {
    return find.byKey(ValueKey(key));
  }

  /// Expects text to be found
  static void expectTextExists(String text) {
    expect(find.textContaining(text), findsWidgets);
  }

  /// Expects text to NOT be found
  static void expectTextNotExists(String text) {
    expect(find.textContaining(text), findsNothing);
  }

  /// Scrolls to the end of a scrollable widget
  static Future<void> scrollToEnd(WidgetTester tester) async {
    await tester.drag(find.byType(ListView), const Offset(0, -5000));
    try {
      await tester.pumpAndSettle(const Duration(milliseconds: 500));
    } catch (_) {
      await tester.pump();
    }
  }

  /// Scrolls to a specific child in a list by text
  static Future<void> scrollUntilVisible(
    WidgetTester tester,
    String text,
  ) async {
    await tester.dragUntilVisible(
      find.textContaining(text),
      find.byType(ListView),
      const Offset(0, -300),
    );
    try {
      await tester.pumpAndSettle(const Duration(milliseconds: 500));
    } catch (_) {
      await tester.pump();
    }
  }

  /// Gets the text content of a widget
  static String getText(WidgetTester tester, String text) {
    final finder = find.textContaining(text);
    return (tester.widget(finder) as Text).data ?? '';
  }

  /// Verifies that an error message is displayed
  static void expectErrorMessage(WidgetTester tester, String errorText) {
    expect(find.textContaining(errorText), findsWidgets);
  }

  /// Pumps n frames
  static Future<void> pumpFrames(WidgetTester tester, int frames) async {
    for (int i = 0; i < frames; i++) {
      await tester.pump();
    }
  }
}
