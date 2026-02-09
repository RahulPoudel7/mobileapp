# GiftBox Order Implementation Summary

## ✅ Completed Tasks

### 1. **Environment Configuration**
Added to `.env`:
```
GOOGLE_MAPS_API_KEY=                          # Add your Google Maps API key
STORE_LAT=27.689408726710088                  # Store latitude (Kathmandu)
STORE_LNG=84.42032634065433                   # Store longitude (Kathmandu)
```

### 2. **Database Migration**
Created migration: `2026_01_30_000000_add_payment_and_delivery_fields_to_orders_table.php`

New fields added to `orders` table:
- `subtotal` (decimal 10,2): Order subtotal before delivery charge
- `delivery_charge` (decimal 10,2): Calculated delivery cost
- `total_amount` (decimal 10,2): Subtotal + delivery_charge
- `distance_km` (float): Distance from store to delivery address
- `payment_method` (string, nullable): 'esewa' or 'cod'

Status now includes:
- `pending` (COD orders)
- `pending_payment` (eSewa orders waiting for payment)

### 3. **Order Model Updates**
Updated `app/Models/Order.php` with new fillable fields:
- subtotal, delivery_charge, total_amount, distance_km, payment_method

### 4. **OrderController Implementation**

#### `store()` Method Features:
1. **Input Validation**
   - Validates `payment_method` (required: 'esewa' or 'cod')
   - Validates recipient details and delivery address
   - Validates items array with gift_id and quantity

2. **Subtotal Calculation**
   - Loops through items, fetches gift prices
   - Calculates total quantity and price

3. **Distance Calculation**
   - Uses `DistanceService::getDistanceInKm()` to geocode address
   - Gets distance between store coordinates and delivery location
   - Enforces maximum delivery distance of 12 km
   - Returns error: "Contact support for delivery charge" for distances > 12 km

4. **Delivery Charge Logic**
   - **Subtotal >= Rs. 5000**: Free delivery (Rs. 0)
   - **0–3 km**: Rs. 80
   - **3–7 km**: Rs. 120
   - **7–12 km**: Rs. 160
   - **12+ km**: Rejected with validation error

5. **Database Transaction**
   - All order operations wrapped in `DB::transaction()`
   - Creates order with calculated totals
   - Creates order items with individual prices
   - Ensures data consistency

6. **Payment Method Handling**
   - **eSewa**: Returns status `pending_payment`, includes `esewa_redirect_url`
   - **COD**: Returns status `pending`, includes all order details

#### `myOrders()` Method
Returns authenticated user's orders with:
- Order totals (subtotal, delivery_charge, total_amount)
- Distance and payment method
- Related items and gift details

#### `calculateDeliveryCharge()` Private Method
Implements the distance/subtotal-based pricing logic

### 5. **Distance Service** 
`app/Services/DistanceService.php` (already created):
- Geocodes address using Google Geocoding API
- Calculates distance using Distance Matrix API
- Falls back to haversine formula if API fails
- Caches results per address for 30 minutes
- Throws `DistanceException` on failures

### 6. **Test Results**
All 13 test cases passed:
- ✅ Free delivery for subtotal >= 5000
- ✅ 0-3 km: Rs. 80
- ✅ 3-7 km: Rs. 120
- ✅ 7-12 km: Rs. 160
- ✅ Edge cases validated

## 📋 Business Rules Implemented

| Distance | Subtotal < 5000 | Subtotal >= 5000 |
|----------|-----------------|------------------|
| 0–3 km | Rs. 80 | Free |
| 3–7 km | Rs. 120 | Free |
| 7–12 km | Rs. 160 | Free |
| 12+ km | ❌ Rejected | ❌ Rejected |

## 🔧 API Endpoint Response Examples

### COD Order (Success)
```json
{
  "success": true,
  "message": "Order placed successfully.",
  "data": {
    "order_id": 1,
    "order_number": "ORD-00001",
    "status": "pending",
    "subtotal": 2500,
    "delivery_charge": 120,
    "total_amount": 2620,
    "distance_km": 5,
    "payment_method": "cod"
  }
}
```

### eSewa Order (Success)
```json
{
  "success": true,
  "message": "Order created. Proceed to eSewa payment.",
  "data": {
    "order_id": 2,
    "order_number": "ORD-00002",
    "status": "pending_payment",
    "subtotal": 1000,
    "delivery_charge": 80,
    "total_amount": 1080,
    "distance_km": 2,
    "payment_method": "esewa",
    "esewa_redirect_url": "/api/esewa/redirect?order_id=2"
  }
}
```

### Distance Exceeded (Error)
```json
{
  "success": false,
  "message": "Contact support for delivery charge",
  "distance_km": 15
}
```

## 📝 Files Modified/Created

1. `.env` - Added Google Maps config
2. `config/store.php` - Created store coordinates config
3. `app/Exceptions/DistanceException.php` - Created exception class
4. `app/Services/DistanceService.php` - Created distance service (from previous task)
5. `app/Models/Order.php` - Updated fillable fields
6. `app/Http/Controllers/Api/OrderController.php` - Complete rewrite with new logic
7. `database/migrations/2026_01_30_000000_add_payment_and_delivery_fields_to_orders_table.php` - Created migration
8. `test_delivery_charge.php` - Created test suite (13/13 tests passing)

## 🚀 Next Steps

1. Add your Google Maps API key to `.env`:
   ```
   GOOGLE_MAPS_API_KEY=your_actual_api_key_here
   ```

2. Verify store coordinates are correct in `.env`

3. Test the endpoint with a POST request:
   ```bash
   POST /api/orders
   Content-Type: application/json
   Authorization: Bearer {token}
   
   {
     "recipient_name": "John Doe",
     "recipient_phone": "+977-1234567890",
     "delivery_address": "Kathmandu, Nepal",
     "payment_method": "cod",
     "items": [
       {
         "gift_id": 1,
         "quantity": 2
       }
     ]
   }
   ```

4. Implement eSewa redirect endpoint (`api.esewa.redirect` route)

5. Add order tracking/status update endpoints as needed
