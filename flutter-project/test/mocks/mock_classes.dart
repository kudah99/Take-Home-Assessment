import 'package:http/http.dart' as http;
import 'dart:typed_data';
import 'dart:convert';

/// Mock HTTP client for testing API calls
class MockHttpClient implements http.Client {
  late http.Response _mockResponse;
  late Exception? _throwException;

  /// Set the response to return
  void setMockResponse(http.Response response) {
    _mockResponse = response;
    _throwException = null;
  }

  /// Set an exception to throw
  void setThrowException(Exception exception) {
    _throwException = exception;
  }

  @override
  Future<http.Response> get(
    Uri url, {
    Map<String, String>? headers,
  }) async {
    if (_throwException != null) throw _throwException!;
    return _mockResponse;
  }

  @override
  Future<http.Response> post(
    Uri url, {
    Map<String, String>? headers,
    Object? body,
    Encoding? encoding,
  }) async {
    if (_throwException != null) throw _throwException!;
    return _mockResponse;
  }

  @override
  Future<http.Response> put(
    Uri url, {
    Map<String, String>? headers,
    Object? body,
    Encoding? encoding,
  }) async {
    if (_throwException != null) throw _throwException!;
    return _mockResponse;
  }

  @override
  Future<http.Response> delete(
    Uri url, {
    Map<String, String>? headers,
    Object? body,
    Encoding? encoding,
  }) async {
    if (_throwException != null) throw _throwException!;
    return _mockResponse;
  }

  @override
  Future<http.Response> head(
    Uri url, {
    Map<String, String>? headers,
  }) async {
    throw UnimplementedError();
  }

  @override
  Future<http.Response> patch(
    Uri url, {
    Map<String, String>? headers,
    Object? body,
    Encoding? encoding,
  }) async {
    throw UnimplementedError();
  }

  @override
  Future<String> read(
    Uri url, {
    Map<String, String>? headers,
  }) async {
    throw UnimplementedError();
  }

  @override
  Future<Uint8List> readBytes(
    Uri url, {
    Map<String, String>? headers,
  }) async {
    throw UnimplementedError();
  }

  @override
  Future<http.StreamedResponse> send(http.BaseRequest request) async {
    throw UnimplementedError();
  }

  @override
  void close() {}
}
