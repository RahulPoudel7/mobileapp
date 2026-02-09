# Cart & Order Flow Analysis

## Current Architecture

### 1. **Cart & Cart Items Tables** (Currently Unused)

#### `carts` Table
```
- id
- user_id (FK → users)
- timestamps
```

#### `carts_items` Table
```
- id
- cart_id (FK → carts) [OBSOLETE - Migration shows migration away from this]
- gift_id (FK → gifts)
- quantity
- price
- timestamps
```

**Status**: ❌ **NOT IN USE** - These tables exist but are not being used for the actual order flow.

---

### 2. **Orders Table** (Currently Active)

```
- id
- user_id (FK → users)
- quantity (sum of all items)
- subtotal
- delivery_charge
- total_amount
- distance_km
- payment_method (enum: 'cod' | 'esewa')
- recipient_name
- recipient_phone
- delivery_address
- status (default: 'pending')
- delivered_at (nullable)
- has_personal_note
- has_gift_wrapping
- personal_note_text
- personal_note_fee
- gift_wrapping_fee
- payment_status (enum: 'paid' | 'unpaid')
- transaction_uuid
- timestamps
```

**Status**: ✅ **ACTIVE** - This is the main order table being used.

---

### 3. **Order Items** (Using `carts_items` table but with `order_id`)

The `carts_items` table has been repurposed to store order items via migration:
- `order_id` (FK → orders) - Was supposed to replace `cart_id`
- `gift_id` (FK → gifts)
- `quantity`
- `price`

**Migration Status**: ⚠️ **INCOMPLETE** - Migration `2026_01_28_082906_change_cart_items_from_cart_to_order.php` is **COMMENTED OUT**, meaning the foreign key change was never actually applied.

**Current Reality**: `carts_items` table still has `cart_id` column, but the application treats it as `order_id` in the Order model.

---

## Current Order Flow (Backend Logic)

### Flow Diagram
```
User Creates Order (POST /api/orders)
    ↓
Validate Items & Calculate Costs
    ├─ Validate recipient, address, items
    ├─ Calculate subtotal from items
    ├─ Calculate fees (gift wrapping, personal note)
    ├─ Calculate delivery charge based on distance
    ├─ Calculate total_amount
    ↓
Create Order Record (DB Transaction)
    ├─ Create order with status = 'pending'
    ├─ Create order items via items() relation
    ├─ Store payment_status = 'unpaid'
    ↓
If Payment Method = eSewa
    ├─ Generate HMAC signature
    ├─ Return eSewa payment URL
    └─ Order remains 'pending' until payment confirmed
    ↓
If Payment Method = Cash on Delivery (COD)
    ├─ Return success response
    └─ Order created with status = 'pending'
    ↓
Order Status: Still PENDING until admin confirms/prepares
```

---

## Key Finding: NO CART LOGIC EXISTS

### ❌ Missing Cart Implementation
1. **No Cart API Endpoints** - No `/api/cart/add`, `/api/cart/items`, `/api/cart/remove`
2. **Cart Table Unused** - `carts` and `carts_items` tables exist but are not populated
3. **Direct Order Creation** - Orders are created directly from client with full item list
4. **No Cart Session** - Users go from product selection → checkout → order creation (no intermediate cart)

---

## Order Creation Flow (Client → Server)

### OrderController::store() Details

**Input (from client):**
```json
{
  "recipient_name": "John Doe",
  "recipient_phone": "+977...",
  "delivery_address": "...",
  "delivery_lat": 27.7172,
  "delivery_lng": 85.3240,
  "payment_method": "esewa|cod",
  "has_personal_note": true|false,
  "personal_note_text": "...",
  "has_gift_wrapping": true|false,
  "items": [
    { "gift_id": 1, "quantity": 2 },
    { "gift_id": 3, "quantity": 1 }
  ]
}
```

**Processing:**
1. Validate all fields
2. Fetch gifts and calculate `subtotal = sum(gift.price * quantity)`
3. Calculate fees:
   - `personal_note_fee = 100` (if has_personal_note)
   - `gift_wrapping_fee = 100` (if has_gift_wrapping)
4. Calculate distance using DistanceService (Google Distance Matrix API)
5. Calculate delivery charge based on distance & subtotal:
   ```
   if subtotal >= 5000 → delivery = 0
   if distance <= 10km → delivery = 80
   if distance <= 20km → delivery = 120
   else → delivery = 160
   ```
6. Calculate `total_amount = subtotal + delivery + fees`
7. Create order with `status = 'pending'` and `payment_status = 'unpaid'`
8. Create order items in `carts_items` table

**Output (to client):**

If eSewa:
```json
{
  "success": true,
  "data": {
    "order_id": 123,
    "total_amount": "2500",
    "transaction_uuid": "1707...-1-A3k2",
    "product_code": "EPAYTEST",
    "signature": "...",
    "esewa_payment_url": "https://rc-epay.esewa.com.np/...",
    "esewa_params": { ... }
  }
}
```

If COD:
```json
{
  "success": true,
  "message": "Order placed successfully.",
  "data": {
    "order_id": 123,
    "order_number": "ORD-00123",
    "total_amount": 2500,
    "status": "pending"
  }
}
```

---

## Database Relationships

### Order → Items
```php
// Order.php
public function items() {
    return $this->hasMany(carts_items::class, 'order_id');
}

// carts_items.php
public function order() {
    return $this->belongsTo(Order::class);
}

public function gift() {
    return $this->belongsTo(Gift::class);
}
```

**Issue**: The relationship expects `order_id` but migration wasn't applied, so column might still be `cart_id`.

---

## API Endpoints

### Available Endpoints
```
POST   /api/orders              → Create order (direct, no cart)
GET    /api/my-orders          → Get user's orders
GET    /api/orders/{id}        → Get order details
GET    /api/orders/{id}/status → Get order status
POST   /api/orders/{id}/cancel → Cancel pending order
```

### Missing Endpoints (Not Implemented)
```
❌ POST   /api/cart/add          → Add item to cart
❌ POST   /api/cart/remove       → Remove item from cart
❌ GET    /api/cart              → Get cart contents
❌ POST   /api/cart/clear        → Clear cart
```

---

## Status & Recommendations

### Current Issues

| Issue | Severity | Description |
|-------|----------|-------------|
| Cart tables unused | 🔴 HIGH | Tables exist but not integrated |
| Migration incomplete | 🔴 HIGH | `order_id` migration commented out |
| No cart API | 🟡 MEDIUM | Can't implement cart in mobile app |
| Direct order creation | 🟡 MEDIUM | No intermediate cart step |
| Relationship mismatch | 🔴 HIGH | `carts_items` model expects `order_id` but column is `cart_id` |

### How Orders Are Currently Placed

```
Mobile App
    ↓
    User browses gifts
    ↓
    User taps "Add to Cart" (not yet fully implemented)
    ↓
    User navigates to Checkout
    ↓
    User enters delivery details
    ↓
    POST /api/orders (with all items, address, payment method)
    ↓
    Server creates order directly (skipping cart)
    ↓
    Server creates order items (in carts_items table)
    ↓
    Response with payment URL (if eSewa) or success (if COD)
    ↓
    Order status = 'pending' until payment confirmed or admin processes
```

### ✅ **Correct Answer**: Orders ARE placed AFTER cart selection, BUT there is NO intermediate cart storage system.

The flow is:
1. User adds to cart (currently just UI - no backend storage)
2. User goes to checkout (collects all items from UI state)
3. User submits order with delivery details
4. Server creates order directly (items not stored in cart table first)

---

## Recommendations

### Option 1: Keep Current Direct Order Flow (Recommended)
- Continue using current approach (no cart table needed)
- Just implement frontend cart UI with local storage/SharedPreferences
- Send all items directly when creating order
- Delete unused `carts` and `carts_items` tables (or repurpose later)

### Option 2: Implement Full Cart System
- Create cart endpoints: `/api/cart/add`, `/api/cart/items`, `/api/cart/remove`
- Store items in database `carts_items` table before checkout
- Retrieve cart items when user proceeds to checkout
- Create order from saved cart items

### Option 3: Hybrid Approach
- Keep direct order creation as is
- Add cart API endpoints for "Save for Later" feature
- Use `carts` table for wishlist/later
- Use `carts_items` for temporary order items

---

## Summary Table

| Aspect | Status | Location |
|--------|--------|----------|
| Cart Table | ❌ Unused | `carts`, `carts_items` |
| Order Creation | ✅ Working | `OrderController::store()` |
| Order Items | ⚠️ Mixed | Using `carts_items` with `order_id` (migration incomplete) |
| Cart API | ❌ Missing | Not in `api.php` routes |
| Payment Integration | ✅ Partial | eSewa & COD supported |
| Order Status | ✅ Complete | pending → preparing → on_the_way → delivered |
| Distance Service | ✅ Active | Google Maps Distance Matrix |

