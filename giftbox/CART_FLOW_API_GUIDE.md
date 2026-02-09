# Cart Flow Integration Points & API Communication

## Complete User Flow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                     HOME ACTIVITY                               │
│  - Shows featured/category gifts in RecyclerView                │
│  - Gift card with "Add to Cart" button                          │
└───────────────────────┬─────────────────────────────────────────┘
                        │ User clicks "Add to Cart"
                        ↓
        ┌───────────────────────────────────┐
        │ HomeActivity.addToCartListener:   │
        │ cartManager.addToCart(gift, qty)  │
        │ Toast: "Item added to cart"       │
        └───────────────┬───────────────────┘
                        │
                        ↓
        ┌───────────────────────────────────┐
        │  CartManager (SharedPreferences)  │
        │  - Stores item in JSON list       │
        │  - Updates qty if already exists  │
        └───────────────────────────────────┘
                        │
                        ↓
        ┌───────────────────────────────────┐
        │  User clicks Cart Icon/Menu       │
        │  Starts CartActivity              │
        └───────────────┬───────────────────┘
                        │
                        ↓
┌─────────────────────────────────────────────────────────────────┐
│                     CART ACTIVITY                               │
│  Layout: activity_cart2.xml                                     │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │ Empty State (if no items):                               │   │
│  │ - "Your cart is empty" message                           │   │
│  │ - "Proceed to Checkout" button disabled                  │   │
│  └──────────────────────────────────────────────────────────┘   │
│                           OR                                     │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │ RecyclerView (CartItemAdapter):                          │   │
│  │ ┌─────────────────────────────────────────────────────┐  │   │
│  │ │ Item 1: Image | Name | Price | Qty[+/-] | Remove   │  │   │
│  │ ├─────────────────────────────────────────────────────┤  │   │
│  │ │ Item 2: Image | Name | Price | Qty[+/-] | Remove   │  │   │
│  │ ├─────────────────────────────────────────────────────┤  │   │
│  │ │ Item N: Image | Name | Price | Qty[+/-] | Remove   │  │   │
│  │ └─────────────────────────────────────────────────────┘  │   │
│  └──────────────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │ Order Summary (CardView):                                │   │
│  │ Subtotal: NPR [calculated from items]                    │   │
│  │ Shipping:  NPR 100 (fixed)                               │   │
│  │ ────────────────────────                                 │   │
│  │ TOTAL:    NPR [subtotal + 100]                           │   │
│  └──────────────────────────────────────────────────────────┘   │
│  [Proceed to Checkout]                                          │
└────────────┬──────────────────────────────────────────────────────┘
             │ User clicks "Proceed to Checkout"
             │ OR
             │ User clicks +/- buttons → CartManager.updateQuantity()
             │ OR
             │ User clicks Remove → CartManager.removeFromCart()
             ↓
┌─────────────────────────────────────────────────────────────────┐
│                   CHECKOUT ACTIVITY                             │
│  Layout: activity_checkout.xml                                  │
│                                                                 │
│  ┌────────────────────────────────────────────────────────┐    │
│  │ FORM SECTION: Recipient Information                    │    │
│  │ ├─ Recipient Name: [____________]                      │    │
│  │ ├─ Recipient Phone: [____________]                     │    │
│  │ └─ Delivery Address: [____________]                    │    │
│  └────────────────────────────────────────────────────────┘    │
│                                                                 │
│  ┌────────────────────────────────────────────────────────┐    │
│  │ ORDER ITEMS (ReadOnly - CheckoutItemAdapter):          │    │
│  │ ├─ Gift 1: Image | Name | NPR 500 | Qty: 2 | Total 1K │    │
│  │ ├─ Gift 2: Image | Name | NPR 1500 | Qty: 1 | Total 1.5K│  │
│  │ └─ Gift 3: Image | Name | NPR 800 | Qty: 1 | Total 800│    │
│  └────────────────────────────────────────────────────────┘    │
│                                                                 │
│  ┌────────────────────────────────────────────────────────┐    │
│  │ ADD-ONS SECTION:                                        │    │
│  │ ├─ [x] Gift Wrapping (NPR 100)                          │    │
│  │ └─ [x] Personal Note (NPR 100)                          │    │
│  │        [Enter your note here____________]               │    │
│  └────────────────────────────────────────────────────────┘    │
│                                                                 │
│  ┌────────────────────────────────────────────────────────┐    │
│  │ ORDER SUMMARY:                                          │    │
│  │ Subtotal:         NPR 3300                              │    │
│  │ Delivery Charge:  NPR 100                               │    │
│  │ ────────────────────────────                            │    │
│  │ TOTAL:            NPR 3400                              │    │
│  └────────────────────────────────────────────────────────┘    │
│  [Place Order]                                                  │
└────────────┬──────────────────────────────────────────────────────┘
             │ User clicks "Place Order"
             │ Validates: Name, Phone, Address (not empty)
             │ Builds JsonObject with order payload
             │ Sends to backend
             ↓
┌─────────────────────────────────────────────────────────────────┐
│ BACKEND: POST /api/orders                                       │
│ ┌──────────────────────────────────────────────────────────┐   │
│ │ REQUEST BODY (JsonObject):                               │   │
│ │ {                                                         │   │
│ │   "recipient_name": "John Doe",                          │   │
│ │   "recipient_phone": "+977...",                          │   │
│ │   "delivery_address": "Kathmandu",                       │   │
│ │   "delivery_lat": 27.7172,                               │   │
│ │   "delivery_lng": 85.3240,                               │   │
│ │   "payment_method": "cod",                               │   │
│ │   "has_gift_wrapping": true,                             │   │
│ │   "has_personal_note": true,                             │   │
│ │   "personal_note_text": "Happy Birthday!",               │   │
│ │   "items": [                                             │   │
│ │     { "gift_id": 1, "quantity": 2 },                     │   │
│ │     { "gift_id": 2, "quantity": 1 }                      │   │
│ │   ]                                                      │   │
│ │ }                                                         │   │
│ └──────────────────────────────────────────────────────────┘   │
│ ┌──────────────────────────────────────────────────────────┐   │
│ │ RESPONSE (on success):                                   │   │
│ │ {                                                         │   │
│ │   "success": true,                                       │   │
│ │   "message": "Order placed successfully.",               │   │
│ │   "data": {                                              │   │
│ │     "order_id": 123,                                     │   │
│ │     "order_number": "ORD-00123",                         │   │
│ │     "total_amount": 3400,                                │   │
│ │     "status": "pending"                                  │   │
│ │   }                                                      │   │
│ │ }                                                         │   │
│ └──────────────────────────────────────────────────────────┘   │
│ Creates:                                                        │
│ - Order record in database                                     │
│ - Order items (carts_items table)                              │
│ - Payment record (if applicable)                               │
└────────────┬──────────────────────────────────────────────────────┘
             │ CheckoutActivity receives response
             ↓
    ┌─────────────────────────────────┐
    │ CheckoutActivity:               │
    │ 1. CartManager.clearCart()      │
    │ 2. Show toast: "Order placed!"  │
    │ 3. finish() - go back to home   │
    └─────────────────────────────────┘
             │
             ↓
    ┌─────────────────────────────────┐
    │ HomeActivity (back to home)     │
    │ - User can continue shopping    │
    │ - Cart is now empty             │
    └─────────────────────────────────┘
```

## API Communication Details

### Authentication
- **Bearer Token**: Retrieved from `SessionManager.getAuthToken()`
- **Format**: `Authorization: Bearer {token}`
- **Applied to**: All api service methods that require auth

### Request/Response Mapping

```java
// CheckoutActivity builds request
JsonObject orderBody = buildOrderJson(
    recipientName,
    recipientPhone,
    address,
    lat, lng,
    paymentMethod,
    hasGiftWrapping,
    hasPersonalNote,
    personalNote
);

// Add items array
JsonArray itemsArray = new JsonArray();
for (CartItem item : cartManager.getCartItems()) {
    JsonObject itemObj = new JsonObject();
    itemObj.addProperty("gift_id", item.getGiftId());
    itemObj.addProperty("quantity", item.getQuantity());
    itemsArray.add(itemObj);
}
orderBody.add("items", itemsArray);

// Send via Retrofit
apiService.createOrder(orderBody).enqueue(new Callback<JsonObject>() {
    @Override
    public void onResponse(JsonObject response, Response<JsonObject> resp) {
        if (resp.isSuccessful()) {
            boolean success = response.get("success").getAsBoolean();
            if (success) {
                cartManager.clearCart();
                finish();
            }
        }
    }
});
```

## CartManager: SharedPreferences Storage

### Structure
```
SharedPreferences file: "giftbox_cart"
├─ Key: "cart_items"
└─ Value: JSON String
   [
     {
       "giftId": 1,
       "name": "Premium Gift Box",
       "price": 1499.0,
       "imageUrl": "https://...",
       "quantity": 2
     },
     {
       "giftId": 3,
       "name": "Chocolate Deluxe",
       "price": 500.0,
       "imageUrl": "https://...",
       "quantity": 1
     }
   ]
```

### Serialization/Deserialization
- **Library**: Gson (already in project)
- **Process**: 
  1. Create List<CartItem>
  2. Gson.toJson(list) → String
  3. SharedPreferences.putString(CART_KEY, json)
  4. On retrieval: SharedPreferences.getString() → Gson.fromJson() → List<CartItem>

## Cart Item Calculation Examples

### Example 1: Single Item
```
CartItem {
  giftId: 1,
  name: "Premium Box",
  price: 1500.0,
  quantity: 1
}
getLineTotal() = 1500 × 1 = 1500
```

### Example 2: Multiple Quantities
```
CartItem {
  giftId: 2,
  name: "Chocolate",
  price: 500.0,
  quantity: 3
}
getLineTotal() = 500 × 3 = 1500
```

### Example 3: Cart Summary
```
Items: [
  { price: 1000, qty: 2 } → lineTotal: 2000
  { price: 500, qty: 1 }  → lineTotal: 500
  { price: 200, qty: 3 }  → lineTotal: 600
]

CartManager.getSubtotal() = 2000 + 500 + 600 = 3100

Order Total = Subtotal + Shipping Fee
            = 3100 + 100
            = 3200 NPR
```

## Error Handling

### Validation (CheckoutActivity)
```
if (recipientName.isEmpty()) {
    Toast.makeText(this, "Please enter recipient name", Toast.LENGTH_SHORT).show();
    return;
}
// Similarly for phone and address
```

### Network Error (CheckoutActivity)
```
onFailure(JsonObject ignored, Throwable t) {
    Toast.makeText(CheckoutActivity.this, 
        "Error: " + t.getMessage(), 
        Toast.LENGTH_SHORT).show();
    btnPlaceOrder.setEnabled(true);  // Allow retry
}
```

### API Error Response
```
// If response.isSuccessful() but success = false
Toast.makeText(CheckoutActivity.this, 
    "Failed to place order", 
    Toast.LENGTH_SHORT).show();
```

## Performance Considerations

1. **SharedPreferences**: Fast, suitable for cart (not large dataset)
2. **Gson Serialization**: Minimal overhead for cart size
3. **RecyclerView**: Efficient for cart items display
4. **Offline Mode**: Cart works without network, order sends when connected

## Future Enhancements

1. **Saved for Later**: Use cart table for wishlist items
2. **Coupon Codes**: Add discount field to order
3. **Estimated Delivery**: Calculate ETA based on location
4. **Multiple Addresses**: Save address book
5. **Order Tracking**: Integrate with order status API
6. **Payment Methods**: eSewa, PayPal, etc.
7. **Cart Sharing**: Share cart link with friends

