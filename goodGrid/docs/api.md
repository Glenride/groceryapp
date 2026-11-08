# Good Grid API Documentation

## Overview

Good Grid is a health-first grocery delivery platform with a multi-sided marketplace supporting:
- **Customers** - Browse merchants, manage cart, place orders, track deliveries
- **Shoppers** - Accept batches, pick orders, deliver groceries
- **Merchants** - Manage product catalogs, process orders, view analytics
- **Admins** - Platform oversight (web interface)

## Authentication

All API endpoints require authentication via Laravel Sanctum. Include the authentication token in the request header:

```
Authorization: Bearer {token}
```

Test users created by seeder:
- `customer@goodgrid.test` - Customer role
- `shopper@goodgrid.test` - Shopper role  
- `admin@goodgrid.test` - Admin role

## API Endpoints

### Customer Endpoints

#### Merchants
- `GET /api/merchants` - List approved merchants
  - Query: `search`, `latitude`, `longitude` (for distance filtering)
- `GET /api/merchants/{merchant}` - Get merchant details with products
- `GET /api/merchants/{merchant}/categories` - Get merchant categories

#### Products
- `GET /api/products` - List products with filtering
  - Query: `merchant_id`, `category_id`, `search`, `health_tags[]`, `sort_by`, `sort_order`
- `GET /api/products/{product}` - Get product details with Good Swaps
- `GET /api/health-filters` - Get available health tags for filtering

#### Cart
- `GET /api/cart` - View cart grouped by merchant
- `POST /api/cart/add` - Add item to cart
  - Body: `product_id`, `quantity`, `special_instructions`, `substitution_preference`
- `PUT /api/cart/{cartItem}` - Update cart item quantity
- `DELETE /api/cart/{cartItem}` - Remove item from cart
- `DELETE /api/cart` - Clear entire cart

#### Orders
- `GET /api/orders` - List customer orders
- `GET /api/orders/{order}` - Get order details with tracking
- `POST /api/orders` - Create new order
  - Body: `merchant_id`, `address_id`, `scheduled_delivery_time`, `delivery_instructions`, `substitution_preference`, `tip`
- `POST /api/orders/{order}/rate` - Rate completed order
  - Body: `rating` (1-5), `comment`

#### Addresses
- `GET /api/addresses` - List customer addresses
- `POST /api/addresses` - Create new address
  - Body: `label`, `street_address`, `apartment`, `city`, `state`, `zip_code`, `latitude`, `longitude`, `delivery_instructions`, `is_default`
- `PUT /api/addresses/{address}` - Update address
- `DELETE /api/addresses/{address}` - Delete address

#### Dietary Preferences
- `GET /api/dietary-preferences` - Get dietary preferences
- `PUT /api/dietary-preferences` - Update preferences
  - Body: `allergy_*` (booleans), `goal_*` (booleans), `good_swap_enabled`

### Shopper Endpoints (requires `shopper` role)

- `GET /api/shopper/dashboard` - Get shopper dashboard
- `GET /api/shopper/batches/available` - List available batches to accept
- `GET /api/shopper/batches` - List my batches (history)
- `POST /api/shopper/batches/{batch}/accept` - Accept a batch
- `PUT /api/shopper/batches/{batch}/status` - Update batch status
  - Body: `status` (shopping, in_transit, delivered)
- `GET /api/shopper/earnings` - Get earnings summary and history

### Merchant Endpoints (requires `merchant` role)

- `GET /api/merchant/dashboard` - Get merchant dashboard stats
- `GET /api/merchant/orders` - List merchant orders
  - Query: `status`
- `PUT /api/merchant/orders/status` - Update order status
  - Body: `order_id`, `status` (confirmed, preparing, ready_for_pickup)
- `GET /api/merchant/products` - List merchant products
  - Query: `search`, `category_id`
- `POST /api/merchant/products` - Create new product
  - Body: `name`, `description`, `price`, `sale_price`, `unit`, `size`, `sku`, `category_id`, `stock_quantity`, `track_stock`, `is_available`, `health_tags[]`
- `PUT /api/merchant/products/{product}` - Update product
- `DELETE /api/merchant/products/{product}` - Delete product
- `GET /api/merchant/analytics` - Get merchant analytics
  - Query: `days` (default 30)

## Key Features

### Health Tags
Products can be tagged with health attributes:
- Organic
- Low Sodium
- High Fiber
- Heart Healthy
- Plant Based
- No Added Sugar
- Gluten Free
- High Protein
- Non-GMO
- Local

### Good Swaps
When viewing a product, the API returns "good swap" suggestions for healthier alternatives. This is based on pre-configured product relationships.

### Dietary Preferences
Customers can set:
- **Allergies**: Nuts, Dairy, Gluten, Shellfish, Soy, Eggs
- **Goals**: Heart Healthy, Low Sodium, Low Sugar, Plant Based, Organic, High Protein
- **Good Swap enabled/disabled**

Products are automatically filtered based on these preferences.

### Order Flow
1. Customer creates order from cart
2. Order enters `pending` status
3. Batch is automatically created grouping orders
4. Shopper accepts batch
5. Shopper marks status: `shopping` → `in_transit` → `delivered`
6. Customer can rate the order

### Earnings
Shoppers earn:
- Base pay per batch
- Distance pay
- Time pay
- Tips (passed through from customers)
- Bonuses (funded from subscription/analytics revenue)

## Models

### User Roles
- `customer` - End consumers
- `shopper` - Delivery workers
- `merchant` - Store owners
- `admin` - Platform administrators

### Order Statuses
- `pending` - Order placed, awaiting confirmation
- `confirmed` - Merchant confirmed order
- `preparing` - Order being prepared
- `ready_for_pickup` - Ready for shopper
- `picked_up` - Shopper collected order
- `in_transit` - Out for delivery
- `delivered` - Successfully delivered
- `cancelled` - Order cancelled
- `refunded` - Order refunded

### Batch Statuses
- `pending` - Available for shoppers
- `offered` - Sent to specific shopper
- `accepted` - Shopper accepted
- `shopping` - Shopper in store
- `in_transit` - Delivering
- `delivered` - Complete
- `cancelled` - Cancelled

## Testing

The seeder creates:
- 5 merchants with 40 products each (200 total products)
- 10 health tags
- Categories with subcategories
- Test users for each role

Use these to test the full order flow:
1. Customer adds items to cart
2. Customer creates order
3. System creates batch
4. Shopper accepts and completes batch
5. Customer rates order
