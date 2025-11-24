import 'package:flutter_test/flutter_test.dart';
import 'package:qa_assessment_app/config/api_config.dart';

void main() {
  group('ApiConfig Tests', () {
    test('baseUrl is correctly configured', () {
      expect(ApiConfig.baseUrl, equals('http://localhost:8000/api'));
    });

    test('baseUrl is not empty', () {
      expect(ApiConfig.baseUrl.isNotEmpty, true);
    });

    test('baseUrl contains protocol', () {
      expect(ApiConfig.baseUrl.contains('http'), true);
    });

    test('baseUrl contains api path', () {
      expect(ApiConfig.baseUrl.contains('/api'), true);
    });
  });
}
