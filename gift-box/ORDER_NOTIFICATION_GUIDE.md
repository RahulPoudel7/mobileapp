# Order Status Notification System

Automatic notifications are now sent to users whenever their order status changes.

## Overview

When an order's delivery status is updated, the system automatically creates a notification for the user who placed the order. The notification appears in their notification list and updates the badge count on the bell icon.

## Supported Order Statuses

1. **pending** - Order placed, awaiting confirmation
2. **accepted** - Order confirmed and being prepared
3. **processing** - Order is being processed
4. **shipped** - Order dispatched and on its way
5. **delivered** - Order successfully delivered
6. **cancelled** - Order cancelled

## How It Works

### Backend Implementation

**OrderController Methods:**

1. **updateOrderStatus()** - Admin endpoint to update order status
   - Endpoint: `POST /api/orders/{id}/update-status`
   - Updates order status in database
   - Automatically creates notification for user
   - Updates `delivered_at` timestamp when status is "delivered"

2. **createOrderStatusNotification()** - Private helper method
   - Creates notification based on status change
   - Different messages for each status transition
   - Links notification to order details

### Automatic Notifications

When order status changes, users receive notifications with:

| Status | Title | Message |
|--------|-------|---------|
| accepted | Order Confirmed | Your order ORD-XXXXX has been confirmed and is being prepared. |
| processing | Order Processing | Your order ORD-XXXXX is now being processed. |
| shipped | Order Shipped | Great news! Your order ORD-XXXXX has been shipped and is on its way. |
| delivered | Order Delivered | Your order ORD-XXXXX has been successfully delivered. Thank you for your purchase! |
| cancelled | Order Cancelled | Your order ORD-XXXXX has been cancelled. |

## API Endpoint

### Update Order Status

**Request:**
```http
POST /api/orders/{id}/update-status
Authorization: Bearer {token}
Content-Type: application/json

{
  "status": "shipped"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Order status updated successfully",
  "data": {
    "order_id": 1,
    "old_status": "processing",
    "new_status": "shipped",
    "delivered_at": null
  }
}
```

## Testing

### Using the Test Script

```bash
# Update order status and create notification
php test_order_notification.php <order_id> <new_status>

# Example: Mark order #1 as shipped
php test_order_notification.php 1 shipped
```

The script will:
- Update the order status
- Create a notification for the user
- Display the notification details
- Show the user's unread notification count

### Manual Testing via API

```bash
# Using curl (replace {token} with actual auth token)
curl -X POST http://localhost:8000/api/orders/1/update-status \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"status": "shipped"}'
```

### Testing in the Android App

1. Place an order as a user
2. Update the order status using the test script or API
3. In the app:
   - Bell icon should show notification badge with count
   - Open NotificationActivity to see the new notification
   - Notification displays order number and status message
   - Badge count decreases when notifications are read

## User Flow

1. **User places order** → Status: pending
2. **Admin accepts order** → Notification: "Order Confirmed"
3. **Admin marks as processing** → Notification: "Order Processing"
4. **Admin ships order** → Notification: "Order Shipped"
5. **Admin marks delivered** → Notification: "Order Delivered"

## Implementation Details

### Database

- Notifications stored in `notifications` table
- Linked to users via `user_id` foreign key
- Type set to "order" for order-related notifications
- Additional data stored in `data` JSON column

### Notification Model

Helper method for creating notifications:
```php
Notification::createForUser(
    $userId,
    'Order Shipped',
    'Your order #ORD-00001 has been shipped',
    'order',
    ['order_id' => 1, 'order_number' => 'ORD-00001']
);
```

### Android Integration

- NotificationActivity displays all notifications with filtering
- Badge shows unread count on bell icon
- Pull-to-refresh updates notification list
- Mark as read/delete functionality

## Admin Workflow

For admin panel or backend management:

1. List orders with current status
2. Update order status via API endpoint
3. System automatically notifies customer
4. Customer sees notification in real-time

## Future Enhancements

Potential additions:
- Push notifications (FCM integration)
- Email notifications for critical status changes
- SMS notifications for delivery updates
- Notification preferences (user can choose which notifications to receive)
- Real-time notification updates via WebSockets

## Troubleshooting

**Notifications not appearing:**
- Check order status was actually changed (old ≠ new)
- Verify user_id exists and is correct
- Check notifications table has entries
- Ensure API endpoint is being called with valid token

**Badge count not updating:**
- Call loadNotificationCount() in HomeActivity
- Check API endpoint returns correct count
- Verify unread notifications exist in database

**Status update fails:**
- Validate status value is in allowed list
- Check order exists with correct ID
- Ensure auth token is valid
