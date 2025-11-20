import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import '../models/user.dart';
import '../models/product.dart';
import '../models/order.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  // BUG: No singleton pattern - creates new instance every time
  // BUG: No token refresh logic
  // BUG: No error handling wrapper

  Future<String?> _getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('auth_token');
  }

  Future<void> _saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', token);
  }

  Future<void> _removeToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
  }

  Future<Map<String, String>> _getHeaders() async {
    final token = await _getToken();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  // BUG: No timeout handling
  // BUG: No retry logic
  // BUG: No network connectivity check
  Future<dynamic> _makeRequest(
    String method,
    String endpoint, {
    Map<String, dynamic>? body,
  }) async {
    final url = Uri.parse('${ApiConfig.baseUrl}$endpoint');
    final headers = await _getHeaders();

    http.Response response;

    switch (method.toUpperCase()) {
      case 'GET':
        response = await http.get(url, headers: headers);
        break;
      case 'POST':
        response = await http.post(
          url,
          headers: headers,
          body: body != null ? jsonEncode(body) : null,
        );
        break;
      case 'PUT':
        response = await http.put(
          url,
          headers: headers,
          body: body != null ? jsonEncode(body) : null,
        );
        break;
      case 'DELETE':
        response = await http.delete(url, headers: headers);
        break;
      default:
        throw Exception('Unsupported HTTP method');
    }

    // BUG: No handling for different status codes (401, 403, 500, etc.)
    // BUG: Assumes all responses are JSON
    if (response.statusCode >= 200 && response.statusCode < 300) {
      return jsonDecode(response.body);
    } else {
      // BUG: Generic error - doesn't provide useful error messages
      throw Exception('Request failed with status: ${response.statusCode}');
    }
  }

  Future<User> login(String email, String password) async {
    try {
      final response = await _makeRequest(
        'POST',
        '/login',
        body: {'email': email, 'password': password},
      );

      // BUG: No validation that response contains access_token
      // BUG: No validation that response contains user
      final token = response['access_token'];
      await _saveToken(token);

      // BUG: Assumes user object exists - will crash if missing
      return User.fromJson(response['user']);
    } catch (e) {
      // BUG: Doesn't handle specific error types
      // BUG: Error message not user-friendly
      rethrow;
    }
  }

  Future<void> logout() async {
    try {
      await _makeRequest('POST', '/logout');
      await _removeToken();
    } catch (e) {
      // BUG: Even if logout fails, token is removed
      await _removeToken();
      rethrow;
    }
  }

  Future<User> getCurrentUser() async {
    final response = await _makeRequest('GET', '/user');
    // BUG: No null check - will crash if user is null
    return User.fromJson(response);
  }

  Future<List<Product>> getProducts() async {
    final response = await _makeRequest('GET', '/products');
    // BUG: Assumes response is a list - will crash if it's not
    // BUG: No handling for empty list
    return (response as List).map((json) => Product.fromJson(json)).toList();
  }

  Future<Product> getProduct(int id) async {
    final response = await _makeRequest('GET', '/products/$id');
    // BUG: No null check
    return Product.fromJson(response);
  }

  Future<List<Order>> getOrders() async {
    final response = await _makeRequest('GET', '/orders');
    // BUG: Assumes response is a list
    // BUG: No error handling if orders is null
    if (response is List) {
      return response.map((json) => Order.fromJson(json)).toList();
    }
    return [];
  }

  Future<Order> getOrder(int id) async {
    final response = await _makeRequest('GET', '/orders/$id');
    // BUG: No null check
    return Order.fromJson(response);
  }
}
