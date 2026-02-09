# Cart Flow Implementation Summary

## Overview
Implemented a complete cart management system for the GiftBox Android app that allows users to add items to a cart, manage quantities, and proceed to checkout where they submit orders to the backend API.

## Architecture

### 1. **CartManager** (app/src/main/java/com/example/giftbox/manager/CartManager.java)
- **Purpose**: Centralized cart management using SharedPreferences
- **Key Methods**:
  - `addToCart(Gift, quantity)` - Add item or update quantity if exists
  - `updateQuantity(giftId, quantity)` - Change item quantity
  - `removeFromCart(giftId)` - Delete item from cart
  - `clearCart()` - Empty all items (after order)
  - `getCartItems()` - Retrieve all items
  - `getSubtotal()` - Calculate cart subtotal
  - `isEmpty()` - Check if cart is empty

**Storage**: Uses Gson to serialize/deserialize CartItem list to JSON in SharedPreferences

### 2. **CartItem Model** (app/src/main/java/com/example/giftbox/model/CartItem.java)
- **Fields**: id, giftId, name, price, imageUrl, quantity
- **Methods**: 
  - `getLineTotal()` - Returns price × quantity
  - Constructor from `GiftListResponse.Gift` for easy conversion

### 3. **CartActivity** (app/src/main/java/com/example/giftbox/view/CartActivity.java)
- **Layout**: activity_cart2.xml (dynamically displays RecyclerView of items)
- **Features**:
  - RecyclerView displays all cart items dynamically
  - Shows empty cart message when no items
  - Real-time order summary (subtotal, shipping, total)
  - Increment/decrement quantity buttons
  - Remove item buttons
  - "Proceed to Checkout" button navigates to CheckoutActivity

**Implements**: `CartItemAdapter.OnCartItemListener` to handle quantity changes and removals

### 4. **CartItemAdapter** (app/src/main/java/com/example/giftbox/adapter/CartItemAdapter.java)
- **Purpose**: RecyclerView adapter for displaying cart items
- **Features**:
  - Displays item image (via Glide), name, price, quantity
  - Shows line total (price × qty)
  - Quantity +/- buttons with real-time UI updates
  - Remove button with callback

**Callback Interface**: `OnCartItemListener`
```java
void onQuantityChanged(CartItem item);
void onItemRemoved(CartItem item);
```

### 5. **CheckoutActivity** (app/src/main/java/com/example/giftbox/view/CheckoutActivity.java)
- **Purpose**: Collect delivery info and submit order
- **Layout**: activity_checkout.xml
- **Features**:
  - Shows items from cart (read-only summary)
  - Form fields: recipient name, phone, address
  - Toggles for gift wrapping (NPR 100) and personal note (NPR 100)
  - Personal note text input (enabled only if toggle on)
  - Order summary showing subtotal, delivery charge (NPR 100), total
  - "Place Order" button submits to `/api/orders` endpoint

**Order Creation Flow**:
1. Validates all required fields
2. Builds JsonObject with all order data including items from cart
3. Sends POST request to ApiService.createOrder()
4. On success: Clears cart, shows toast, finishes activity
5. On failure: Shows error, keeps form for retry

### 6. **HomeActivity Updates** (app/src/main/java/com/example/giftbox/view/HomeActivity.java)
- **Import**: Added `CartManager`
- **Add to Cart Listener**:
```java
gift -> {
    cartManager.addToCart(gift, 1);
    Toast.makeText(HomeActivity.this, gift.getName() + " added to cart!", Toast.LENGTH_SHORT).show();
}
```

### 7. **ApiService Updates** (app/src/main/java/com/example/giftbox/api/ApiService.java)
- **New Method**:
```java
@POST("api/orders")
Call<JsonObject> createOrder(@Body JsonObject orderBody);
```
- Allows flexible order creation with dynamic JSON payload

## Data Flow

### Adding to Cart
```
HomeActivity (Add to Cart button clicked)
    ↓
GiftAdapter.OnAddToCartListener.onAddToCart(gift)
    ↓
CartManager.addToCart(gift, quantity=1)
    ↓
SharedPreferences (JSON serialized)
```

### Viewing Cart
```
User clicks Cart icon
    ↓
CartActivity.onCreate()
    ↓
CartManager.getCartItems()
    ↓
CartItemAdapter displays items in RecyclerView
    ↓
Order summary calculated: subtotal + SHIPPING_FEE (100)
```

### Proceeding to Checkout
```
User clicks "Proceed to Checkout" in CartActivity
    ↓
Starts CheckoutActivity
    ↓
CheckoutActivity reads from CartManager
    ↓
Shows items in CheckoutItemAdapter (read-only)
    ↓
Displays form for delivery details
```

### Creating Order
```
User fills form + clicks "Place Order"
    ↓
CheckoutActivity validates fields
    ↓
Builds JsonObject with:
    - recipient info
    - delivery address
    - gift wrapping/note flags
    - items[] array from cart
    - payment_method: "cod"
    ↓
ApiService.createOrder(jsonObject)
    ↓
Backend (/api/orders endpoint)
    ↓
Creates Order + Order Items
    ↓
CartManager.clearCart()
    ↓
Finish CheckoutActivity
```

## Files Created

| File | Type | Purpose |
|------|------|---------|
| CartManager.java | Manager | Cart state management |
| CartItem.java | Model | Cart item data structure |
| CartActivity.java | Activity | Display cart (replaced old static version) |
| CartItemAdapter.java | Adapter | RecyclerView for cart items |
| CheckoutActivity.java | Activity | Order submission |
| CheckoutItemAdapter.java | Adapter | Readonly items display in checkout |
| item_cart.xml | Layout | Single cart item card |
| item_checkout.xml | Layout | Single checkout item card |
| activity_cart2.xml | Layout | Updated with RecyclerView (was static hardcoded) |
| activity_checkout.xml | Layout | Completely redesigned for form + items |

## Files Modified

| File | Change |
|------|--------|
| HomeActivity.java | Added CartManager, updated Add to Cart listener |
| ApiService.java | Added overloaded createOrder(JsonObject) method |

## Order Payload Example

```json
{
  "recipient_name": "John Doe",
  "recipient_phone": "+977...",
  "delivery_address": "Kathmandu",
  "delivery_lat": 27.7172,
  "delivery_lng": 85.3240,
  "payment_method": "cod",
  "has_gift_wrapping": true,
  "has_personal_note": true,
  "personal_note_text": "Happy Birthday!",
  "items": [
    { "gift_id": 1, "quantity": 2 },
    { "gift_id": 3, "quantity": 1 }
  ]
}
```

## Backend Integration

### API Endpoint: POST /api/orders
- **Authentication**: Bearer token (from SessionManager)
- **Request**: JsonObject with order details
- **Response**: 
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

## Constants

| Name | Value | Purpose |
|------|-------|---------|
| SHIPPING_FEE | 100.0 | Fixed delivery charge |
| PREFS_NAME | "giftbox_cart" | SharedPreferences file name |
| CART_KEY | "cart_items" | SharedPreferences key |

## User Journey

1. **Browse Products** → HomeActivity shows featured/category gifts
2. **Add to Cart** → Click "Add to Cart" button → Item added to CartManager
3. **View Cart** → Click Cart icon → CartActivity shows items with +/- controls
4. **Modify Cart** → Adjust quantities, remove items → CartManager updates
5. **Checkout** → Click "Proceed to Checkout" → CheckoutActivity
6. **Fill Details** → Enter name, phone, address, add-ons → Form validation
7. **Submit Order** → Click "Place Order" → API call → Order created
8. **Success** → Cart cleared → Back to home

## Testing Checklist

- [ ] Add item to cart → Verify CartManager stores it
- [ ] Add same item twice → Verify quantity increases
- [ ] View cart → Verify all items display correctly
- [ ] Modify quantity in cart → Verify total recalculates
- [ ] Remove item from cart → Verify removed from display
- [ ] Clear cart → Cart shows empty message
- [ ] Checkout with valid data → Order submitted, cart cleared
- [ ] Checkout with empty required field → Shows validation error
- [ ] Multiple items → Correct items sent in order payload
- [ ] Order summary → Subtotal + shipping = total

## Notes

- **Cart Persistence**: Survives app restart (SharedPreferences)
- **Local Storage**: No backend cart API needed, items stored locally
- **Offline Support**: Cart items persist offline, order only sends when connected
- **Currency**: All amounts displayed as NPR
- **Shipping**: Fixed at NPR 100 (can be made dynamic based on distance)
- **Default Payment**: Uses "cod" (Cash on Delivery), can be extended to eSewa

