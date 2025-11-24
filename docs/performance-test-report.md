#  Performance Test Report

**QA Assessment API**
 ![LADS Africa Logo](https://ladsafrica.co.zw/wp-content/uploads/2021/11/imageedit_5_3463869995.png) 
---

- **BY:** Kudakwashe Chris Chipangura


---

---

## Overview

Performance testing was conducted on critical API endpoints to identify bottlenecks and measure response times. The analysis reveals several endpoints with concerning response times, ranging from 3.68 seconds to 20.29 seconds. The primary performance issues stem from:

1. **Query Problems** - Missing eager loading relationships
2. **Inefficient Data Aggregation** - Looping through collections in-memory
3. **Lack of Pagination** - Loading entire datasets without limits
4. **Missing Database Indexes** - No optimization for frequently queried fields
5. **Serialization Overhead** - Converting large datasets to JSON without optimization

---

## Top 10 Slowest APIs

| Rank | Endpoint | Test Case | Response Time | Status | Issue Severity |
|------|----------|-----------|---------------|--------|-----------------|
| 1 | POST /api/orders | order total calculation with large quantities | 20.29s | 🔴 Critical | High |
| 2 | GET /api/users | user with many orders | 7.57s | 🔴 Critical | High |
| 3 | GET /api/users/{id} | api user endpoint serializes correctly | 7.57s | 🔴 Critical | High |
| 4 | GET /api/orders | user with many orders (order list) | 7.04s | 🔴 Critical | High |
| 5 | GET /api/products/{id} | product with large stock | 6.46s | 🟠 Warning | Medium |
| 6 | GET /api/users | authenticated user can list users | 5.86s | 🟠 Warning | Medium |
| 7 | GET /api/users | list users includes all created users | 5.05s | 🟠 Warning | Medium |
| 8 | PATCH /api/orders/{id} | order status flexibility | 4.24s | 🟠 Warning | Medium |
| 9 | GET /api/users | user collection serializes correctly | 3.96s | 🟠 Warning | Medium |
| 10 | GET /api/orders/{id} | order has default status of pending | 3.91s | 🟠 Warning | Medium |

---

## Performance Analysis by Endpoint

### 1. POST /api/orders - Order Total Calculation with Large Quantities (20.29s)

**Issue:** Slowest endpoint in the application

**Root Causes:**
- **In-Memory Collection Aggregation:** Using `items->sum(fn())` loads all order items into memory and sums via Laravel collection
- **Missing Transaction Handling:** Creates order first, then items separately without atomic operation
- **No Input Validation:** Skips validation of product existence before processing
- **Loop Through Items:** Multiple database queries per order item instead of batch insertion

**Current Code Pattern:**
```php
public function calculateTotal()
{
    return $this->items->sum(function ($item) {
        return $item->quantity * $item->price;  // Collection aggregation
    });
}
```

**Performance Impact:**
- For 1,000 items: ~20+ seconds
- Each item sum operation requires in-memory processing
- No database-level aggregation (SUM at SQL level)

**Recommendations:**
1. Use database-level SUM aggregation
2. Implement eager loading with select optimization
3. Use raw database queries for calculations
4. Batch insert order items

---

### 2. GET /api/users & GET /api/users/{id} - Serialization Issues (7.04-7.57s)

**Issue:** Multiple endpoints returning user data experiencing slowdowns

**Root Causes:**
- **N+1 Query Problem:** `User::all()` loads all users, then accessing related data (orders) triggers additional queries
- **No Eager Loading:** Missing `with()` method to pre-fetch relationships
- **Unoptimized Serialization:** Converting all user objects with relationships to JSON
- **No Pagination:** Returning all users in single response

**Current Code Pattern:**
```php
public function index(Request $request)
{
    $users = User::all();  // N+1 if relationships accessed
    return response()->json($users);
}
```

**Performance Impact:**
- With 100 users: 1 base query + 100 orders queries = 101 total queries
- Exponential growth with data size
- Memory consumption increases linearly

**Recommendations:**
1. Implement eager loading: `User::with('orders')->get()`
2. Add pagination: `User::with('orders')->paginate(20)`
3. Use select to limit fields: `User::select('id', 'name', 'email')->get()`
4. Implement response caching for frequently accessed endpoints
5. Create API resources/transformers for consistent serialization

---

### 3. GET /api/orders - User with Many Orders (7.04s)

**Issue:** Listing orders when users have multiple orders

**Root Causes:**
- **N+1 Query Problem:** `Order::with('user')->get()` loads orders, then for each order, user data triggers query
- **Missing Pagination:** Could return thousands of orders
- **No Column Selection:** Loading entire user records with all fields
- **Inefficient Relationship Loading:** Loading user for every order even if not needed

**Current Code Pattern:**
```php
public function index(Request $request)
{
    $orders = Order::with('user')->get();  // Loads all orders + user for each
    return response()->json($orders);
}
```

**Performance Impact:**
- 1,000 orders = multiple queries per order
- Unnecessary user data fetched
- No pagination limits

**Recommendations:**
1. Add pagination: `Order::with('user')->paginate(50)`
2. Select specific columns: `Order::with(['user:id,name,email'])->paginate(50)`
3. Filter by authenticated user scope
4. Implement caching with Redis
5. Add database indexes on user_id and status fields

---

### 4. GET /api/products/{id} - Product with Large Stock (6.46s)

**Issue:** Retrieving product information with large inventory

**Root Causes:**
- **Eager Loading Missing:** Loading order items for product triggers additional queries
- **No Pagination for Related Items:** All order items loaded into memory
- **Stock Calculation:** Iterating through items to calculate available stock
- **Unoptimized JSON Serialization:** Converting large related data structures

**Recommendations:**
1. Select relevant stock fields only
2. Paginate orderItems if included in response
3. Use database views for calculated fields (available_stock)
4. Denormalize stock data to product table
5. Implement materialized aggregates with scheduled updates

---

### 5. PATCH /api/orders/{id} - Order Status Flexibility (4.24s)

**Issue:** Updating order status takes excessive time

**Root Causes:**
- **No Transaction Handling:** Status update without atomic guarantees
- **Missing Validation:** Accepts any status value
- **Potential Related Updates:** May trigger cascading updates
- **No Query Optimization:** Full order load before update

**Current Code Pattern:**
```php
public function update(Request $request, $id)
{
    $order = Order::findOrFail($id);  // Loads full order
    $order->status = $request->input('status');
    $order->save();
    return response()->json($order);
}
```

**Recommendations:**
1. Use direct update queries: `Order::find($id)->update(['status' => $status])`
2. Validate status against enum/allowed values
3. Implement event listeners for status changes
4. Return only updated fields in response

---

### 6-10. General Serialization Issues (3.96-5.86s)

**Common Issues Across Multiple Endpoints:**
- **Lack of Response Caching:** Same data fetched repeatedly
- **Inefficient Hydration:** Creating full model instances when partial data needed
- **Missing Database Indexes:** Queries full table scan instead of indexed lookup
- **No Connection Pooling:** Each request opens new database connection
- **Inadequate Query Optimization:** Missing EXPLAIN plan analysis

---

## Performance Benchmarks

### Baseline Response Times (Current)

| Endpoint | Current (ms) | Target (ms) | Gap |
|----------|-------------|------------|-----|
| POST /api/orders (large qty) | 20,290 | 500 | -97.5% |
| GET /api/users (many orders) | 7,570 | 300 | -96.0% |
| GET /api/users/{id} (serialize) | 7,570 | 300 | -96.0% |
| GET /api/orders (list) | 7,040 | 400 | -94.3% |
| GET /api/products/{id} (stock) | 6,460 | 400 | -93.8% |
| GET /api/users (list) | 5,860 | 300 | -94.9% |
| GET /api/users (all users) | 5,050 | 300 | -94.1% |
| PATCH /api/orders/{id} | 4,240 | 200 | -95.3% |
| GET /api/users (collection) | 3,960 | 300 | -92.4% |
| GET /api/orders/{id} | 3,910 | 200 | -94.9% |

---

## Root Cause Analysis

### Primary Issues

#### 1. N+1 Query Problem (Affects 7/10 slow endpoints)
**Impact:** 70% of performance issues  
**Severity:** Critical

The application eagerly loads relationships without using Laravel's `with()` method or loads relationships selectively. For example:
- Loading 100 users triggers 1 query to get users + 100 queries for their orders
- This multiplies exponentially with nested relationships

#### 2. Missing Pagination (Affects 6/10 slow endpoints)
**Impact:** 60% of performance issues  
**Severity:** Critical

No limits on result sets. The `index` methods return all records:
```php
User::all();  // Could return 10,000+ records
Order::with('user')->get();  // Returns all orders
```

#### 3. In-Memory Aggregation (Affects 3/10 slow endpoints)
**Impact:** 30% of performance issues  
**Severity:** High

Calculations done in PHP instead of database:
```php
$orders->sum(fn($item) => $item->quantity * $item->price);  // PHP loop
// Should be: SELECT SUM(quantity * price) FROM order_items
```

#### 4. Missing Database Indexes
**Impact:** 40% of performance issues  
**Severity:** High

Frequent queries without indexes force full table scans:
- `user_id` on orders table
- `status` on orders table
- `product_id` on order_items table

#### 5. Inefficient Serialization (Affects 4/10 slow endpoints)
**Impact:** 35% of performance issues  
**Severity:** Medium

Converting large model instances with all properties:
```php
return response()->json($users);  // Returns all columns including sensitive data
```

---

## Recommended Improvements

### Imediate Improvements

#### 1. Add Eager Loading with Column Selection

#### 2. Implement Database-Level Aggregation

#### 3. Add Pagination to All List Endpoints

#### 4. Create Database Indexes


### Other Improvements 

#### 5. Implement Response Caching
#### 6. Create API Resources for Serialization
#### 7. Implement Query Optimization
#### 8. Add Database Connection Pooling

