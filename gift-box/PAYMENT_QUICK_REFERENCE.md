# 🎁 Gift-Box Payment System - Quick Reference

## ✅ Status: WORKING & READY

Your payment system has been tested and verified. All components are functional.

---

## What Was Fixed

### ✓ Updated eSewa Endpoint
- Changed from deprecated UAT endpoint to modern RC endpoint
- **Old:** `https://uat.esewa.com.np/api/epay/transaction/status`
- **New:** `https://rc-epay.esewa.com.np/api/epay/transaction/status`

### ✓ Improved Configuration
- PaymentApiController now uses `.env` variable for flexibility
- Better production-ready code

---

## Payment Integration (Testing)

### Test Credentials
```
Merchant ID: EPAYTEST
Secret Key: 8gBm/:&EnhH.1/q
Environment: eSewa RC (Test)
```

### Test Payment URL
```
https://rc-epay.esewa.com.np/api/epay/main/v2/form
```

---

## API Endpoints

### Create Order (with eSewa payment)
```bash
POST /api/orders
Authorization: Bearer {token}

{
  "recipient_name": "John Doe",
  "recipient_phone": "9800000000",
  "delivery_address": "123 Main St",
  "delivery_lat": 27.689,
  "delivery_lng": 84.420,
  "payment_method": "esewa",
  "has_gift_wrapping": true,
  "has_personal_note": true,
  "personal_note_text": "Happy Birthday!",
  "items": [
    {
      "gift_id": 1,
      "quantity": 2
    }
  ]
}

Response includes:
- order_id
- transaction_uuid
- esewa_payment_url (ready to open)
- esewa_params (raw parameters)
```

### Verify Payment (Server-to-Server)
```bash
POST /api/payment/verify
Authorization: Bearer {token}

{
  "order_id": 1,
  "amount": 2080.00,
  "refId": "1234567890-1-abcd"
}

Response:
{
  "success": true,
  "message": "Payment Verified Successfully"
}
```

### Check Order Status
```bash
GET /api/orders/{order_id}
Authorization: Bearer {token}

Response includes:
- order details
- payment_status: "paid" | "unpaid"
- status: "pending" | "confirmed" | "delivered"
```

---

## Database Schema

### Orders Table
```
id (PK)
user_id (FK)
transaction_uuid (unique payment ID)
subtotal (amount of gifts)
delivery_charge (calculated by distance)
gift_wrapping_fee (100 if selected)
personal_note_fee (100 if selected)
total_amount (subtotal + fees)
payment_method ("esewa" | "cod")
payment_status ("unpaid" | "paid")
status ("pending" | "confirmed" | "delivered")
distance_km (from store to delivery)
recipient_name, recipient_phone, delivery_address
has_gift_wrapping, has_personal_note
personal_note_text
```

---

## Payment Flow Diagram

```
┌─────────────────────┐
│  Create Order API   │
│ POST /api/orders    │
└──────────┬──────────┘
           │
           ↓
┌─────────────────────────────────┐
│ OrderController::store()        │
│ - Calculate total amount        │
│ - Generate transaction UUID     │
│ - Generate eSewa signature      │
│ - Save order as "unpaid"        │
└──────────┬──────────────────────┘
           │
           ├─ Return: esewa_payment_url
           │
           ↓
┌─────────────────────────────────┐
│ User Opens Payment URL          │
│ Completes eSewa Payment         │
└──────────┬──────────────────────┘
           │
           ↓
┌─────────────────────────────────┐
│  Verify Payment API             │
│ POST /api/payment/verify        │
└──────────┬──────────────────────┘
           │
           ↓
┌─────────────────────────────────┐
│ PaymentApiController::verify()  │
│ - Validate amount               │
│ - Call eSewa server-to-server   │
│ - Update order to "paid"        │
└──────────┬──────────────────────┘
           │
           ├─ Success: {"success": true}
           │
           ↓
┌─────────────────────────────────┐
│ Order Status: PAID ✓            │
│ Ready for fulfillment           │
└─────────────────────────────────┘
```

---

## Troubleshooting

### Issue: eSewa payment fails
**Solution:**
- Verify eSewa credentials in `.env`
- Check internet connection
- Ensure merchant ID is EPAYTEST for testing

### Issue: Verification returns 400
**Solution:**
- Amount must match order total exactly
- Order must exist in database
- Transaction UUID must be correct

### Issue: Signature mismatch
**Solution:**
- Check secret key: `8gBm/:&EnhH.1/q`
- Message format must be: `total_amount=X,transaction_uuid=Y,product_code=Z`

---

## Files Reference

| File | Purpose | Status |
|------|---------|--------|
| `.env` | eSewa configuration | ✓ Updated |
| `app/Http/Controllers/Api/PaymentApiController.php` | Payment verification | ✓ Updated |
| `app/Http/Controllers/Api/OrderController.php` | Order creation | ✓ Working |
| `app/Services/EsewaService.php` | eSewa integration | ✓ Working |
| `app/Models/Order.php` | Order model | ✓ Has all payment fields |
| `routes/api.php` | API routes | ✓ Working |
| `database/migrations/*orders_table*` | Database schema | ✓ Complete |

---

## For Production

When deploying to production:

1. **Update Credentials**
   ```
   ESEWA_MERCHANT_ID=your_live_merchant_id
   ESEWA_SECRET_KEY=your_production_secret_key
   ```

2. **Update Callback URLs** (in OrderController)
   ```
   success_url: https://yourdomain.com/api/payment/success
   failure_url: https://yourdomain.com/api/payment/failure
   ```

3. **Update Payment URL** (if eSewa changes)
   ```
   https://epay.esewa.com.np/api/epay/main/v2/form (production endpoint)
   ```

4. **Enable Logging**
   ```php
   Log::info('Payment verified', ['order_id' => $order->id]);
   ```

---

## Test Scenarios

### Scenario 1: Successful Payment
1. Create order with eSewa method
2. Open payment URL
3. Complete eSewa test payment
4. Verify payment
5. Order status → "confirmed", payment → "paid" ✓

### Scenario 2: Cash on Delivery
1. Create order with COD method
2. Order saved as "pending"
3. Admin confirms delivery
4. Order status → "confirmed" ✓

### Scenario 3: Failed Payment
1. Create order
2. Payment fails/cancelled
3. Order remains "pending"
4. User can retry payment ✓

---

**Last Updated:** February 3, 2026  
**Verification Status:** ✓ All systems operational
