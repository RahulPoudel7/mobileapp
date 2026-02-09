package com.example.giftbox.view;

import android.annotation.SuppressLint;
import android.content.Intent;
import android.os.Bundle;
import android.widget.CheckBox;
import android.widget.EditText;
import android.widget.LinearLayout;
import android.widget.RadioGroup;
import android.widget.Switch;
import android.widget.TextView;
import android.widget.Toast;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.app.AppCompatDelegate;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.example.giftbox.R;
import com.example.giftbox.adapter.CheckoutItemAdapter;
import com.example.giftbox.api.ApiClient;
import com.example.giftbox.api.ApiService;
import com.example.giftbox.manager.CartManager;
import com.example.giftbox.model.CartItem;
import com.example.giftbox.utils.SessionManager;
import com.google.android.material.button.MaterialButton;
import com.google.gson.JsonArray;
import com.google.gson.JsonObject;

import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;
import java.util.List;
import java.util.Locale;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class CheckoutActivity extends AppCompatActivity {

    private CartManager cartManager;
    private ApiService apiService;
    private SessionManager sessionManager;
    private RecyclerView rvCheckoutItems;
    private CheckoutItemAdapter checkoutItemAdapter;
    private EditText etRecipientName, etRecipientPhone, etDeliveryAddress;
    private EditText etPersonalNote, etDeliveryDate;
    private Switch swGiftWrapping, swPersonalNote;
    private CheckBox cbSameDayDelivery;
    private LinearLayout llSameDayDelivery;
    private RadioGroup rgPaymentMethod;
    private TextView tvSubtotal, tvDeliveryCharge, tvCheckoutAddons, tvTotal;
    private MaterialButton btnPlaceOrder;
    private MaterialButton btnSelectAddress;
    private MaterialButton btnUseDefaultAddress;
    private String selectedDeliveryDate = "";

    private double selectedLat = 0.0;
    private double selectedLng = 0.0;
    private boolean hasSelectedLocation = false;

    private double currentDeliveryCharge = 100.0; // Default, will be updated from API
    private double currentDistanceKm = 0.0;

    private static final int ESEWA_PAYMENT_REQUEST = 100;

    private final ActivityResultLauncher<Intent> addressLauncher = registerForActivityResult(
            new ActivityResultContracts.StartActivityForResult(),
            result -> {
                if (result.getResultCode() == RESULT_OK && result.getData() != null) {
                    String detailAddress = result.getData().getStringExtra("detailAddress");
                    double lat = result.getData().getDoubleExtra("latitude", 0.0);
                    double lng = result.getData().getDoubleExtra("longitude", 0.0);

                    if (detailAddress != null && !detailAddress.trim().isEmpty()) {
                        etDeliveryAddress.setText(detailAddress);
                        selectedLat = lat;
                        selectedLng = lng;
                        hasSelectedLocation = true;

                        // Fetch delivery charge from backend
                        fetchDeliveryCharge(lat, lng);
                    }

                    String customerName = result.getData().getStringExtra("customerName");
                    String phone = result.getData().getStringExtra("phone");

                    if (customerName != null && !customerName.trim().isEmpty()) {
                        etRecipientName.setText(customerName);
                    }
                    if (phone != null && !phone.trim().isEmpty()) {
                        etRecipientPhone.setText(phone);
                    }
                }
            }
    );

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_checkout);

        if (getSupportActionBar() != null) {
            getSupportActionBar().hide();
        }

        // Initialize
        cartManager = new CartManager(this);
        apiService = ApiClient.getApiService();
        sessionManager = new SessionManager(this);

        // Initialize views
        initializeViews();

        // Setup RecyclerView
        setupRecyclerView();

        // Load cart items
        loadCheckoutItems();

        // Setup listeners
        setupListeners();
    }

    private void initializeViews() {
        etRecipientName = findViewById(R.id.etRecipientName);
        etRecipientPhone = findViewById(R.id.etRecipientPhone);
        etDeliveryAddress = findViewById(R.id.etDeliveryAddress);
        etPersonalNote = findViewById(R.id.etPersonalNote);
        etDeliveryDate = findViewById(R.id.etDeliveryDate);
        swGiftWrapping = findViewById(R.id.swGiftWrapping);
        swPersonalNote = findViewById(R.id.swPersonalNote);
        cbSameDayDelivery = findViewById(R.id.cbSameDayDelivery);
        llSameDayDelivery = findViewById(R.id.llSameDayDelivery);
        rgPaymentMethod = findViewById(R.id.rgPaymentMethod);
        tvSubtotal = findViewById(R.id.tvCheckoutSubtotal);
        tvDeliveryCharge = findViewById(R.id.tvCheckoutDelivery);
        tvCheckoutAddons = findViewById(R.id.tvCheckoutAddons);
        tvTotal = findViewById(R.id.tvCheckoutTotal);
        btnPlaceOrder = findViewById(R.id.btnPlaceOrder);
        btnSelectAddress = findViewById(R.id.btnSelectAddress);
        btnUseDefaultAddress = findViewById(R.id.btnUseDefaultAddress);
        rvCheckoutItems = findViewById(R.id.rvCheckoutItems);
    }

    private void setupRecyclerView() {
        rvCheckoutItems.setLayoutManager(new LinearLayoutManager(this));
        checkoutItemAdapter = new CheckoutItemAdapter(new java.util.ArrayList<>(), this);
        rvCheckoutItems.setAdapter(checkoutItemAdapter);
    }

    private boolean isValidPhoneNumber(String phone) {
        // Nepal phone numbers should be 10 digits and start with 9 or 8
        return phone.matches("^[89]\\d{9}$");
    }

    @SuppressLint("SetTextI18n")
    private void loadCheckoutItems() {
        List<CartItem> cartItems = cartManager.getCartItems();

        if (cartItems.isEmpty()) {
            Toast.makeText(this, "Your cart is empty", Toast.LENGTH_SHORT).show();
            finish();
            return;
        }

        // Update adapter
        checkoutItemAdapter.updateItems(cartItems);

        // Update summary
        updateOrderSummary(cartItems);
    }

    @SuppressLint("SetTextI18n")
    private void updateOrderSummary(List<CartItem> items) {
        double subtotal = items.stream()
                .mapToDouble(CartItem::getLineTotal)
                .sum();

        // Add-on fees
        double giftWrappingFee = swGiftWrapping.isChecked() ? 100.0 : 0.0;
        double personalNoteFee = swPersonalNote.isChecked() ? 100.0 : 0.0;
        double addonTotal = giftWrappingFee + personalNoteFee;

        // Total calculation
        double total = subtotal + currentDeliveryCharge + addonTotal;

        tvSubtotal.setText(String.format("NPR %.0f", subtotal));
        tvDeliveryCharge.setText(String.format("NPR %.0f", currentDeliveryCharge));
        tvCheckoutAddons.setText(String.format("NPR %.0f", addonTotal));
        tvTotal.setText(String.format("NPR %.0f", total));
    }

    private void setupListeners() {
        swPersonalNote.setOnCheckedChangeListener((buttonView, isChecked) -> {
            etPersonalNote.setEnabled(isChecked);
            updateOrderSummary(cartManager.getCartItems());
        });

        swGiftWrapping.setOnCheckedChangeListener((buttonView, isChecked) -> {
            updateOrderSummary(cartManager.getCartItems());
        });

        btnSelectAddress.setOnClickListener(v -> {
            Intent intent = new Intent(CheckoutActivity.this, AddAddressActivity.class);
            addressLauncher.launch(intent);
        });

        btnUseDefaultAddress.setOnClickListener(v -> {
            loadDefaultAddress();
        });

        // Date picker for delivery date
        etDeliveryDate.setOnClickListener(v -> showDatePicker());

        // Same day delivery checkbox
        cbSameDayDelivery.setOnCheckedChangeListener((buttonView, isChecked) -> {
            if (isChecked) {
                // Set delivery date to today
                SimpleDateFormat dateFormat = new SimpleDateFormat("dd/MM/yyyy", Locale.getDefault());
                selectedDeliveryDate = dateFormat.format(new Date());
                etDeliveryDate.setText(selectedDeliveryDate);
            } else {
                selectedDeliveryDate = "";
                etDeliveryDate.setText("");
            }
        });

        btnPlaceOrder.setOnClickListener(v -> placeOrder());
    }

    private void showDatePicker() {
        final Calendar calendar = Calendar.getInstance();
        
        // Set minimum date to today
        calendar.set(Calendar.HOUR_OF_DAY, 0);
        calendar.set(Calendar.MINUTE, 0);
        calendar.set(Calendar.SECOND, 0);
        
        int year = calendar.get(Calendar.YEAR);
        int month = calendar.get(Calendar.MONTH);
        int dayOfMonth = calendar.get(Calendar.DAY_OF_MONTH);

        android.app.DatePickerDialog datePickerDialog = new android.app.DatePickerDialog(
                this,
                (view, selectedYear, selectedMonth, selectedDay) -> {
                    Calendar selectedCal = Calendar.getInstance();
                    selectedCal.set(selectedYear, selectedMonth, selectedDay);
                    
                    // Format date as dd/MM/yyyy
                    SimpleDateFormat dateFormat = new SimpleDateFormat("dd/MM/yyyy", Locale.getDefault());
                    selectedDeliveryDate = dateFormat.format(selectedCal.getTime());
                    etDeliveryDate.setText(selectedDeliveryDate);
                    
                    // Uncheck same day delivery if user selects different date
                    cbSameDayDelivery.setChecked(false);
                },
                year,
                month,
                dayOfMonth
        );

        // Set minimum date to today
        datePickerDialog.getDatePicker().setMinDate(System.currentTimeMillis() - 1000);
        datePickerDialog.show();
    }

    private void placeOrder() {
        // Validate inputs
        String recipientName = etRecipientName.getText().toString().trim();
        String recipientPhone = etRecipientPhone.getText().toString().trim();
        String deliveryAddress = etDeliveryAddress.getText().toString().trim();

        if (recipientName.isEmpty()) {
            Toast.makeText(this, "Please enter recipient name", Toast.LENGTH_SHORT).show();
            return;
        }

        if (recipientPhone.isEmpty()) {
            Toast.makeText(this, "Please enter phone number", Toast.LENGTH_SHORT).show();
            return;
        }

        // Validate phone number format
        if (!isValidPhoneNumber(recipientPhone)) {
            Toast.makeText(this, "Please enter a valid 10-digit phone number", Toast.LENGTH_SHORT).show();
            return;
        }

        if (deliveryAddress.isEmpty() || !hasSelectedLocation) {
            Toast.makeText(this, "Please select a delivery location on map", Toast.LENGTH_SHORT).show();
            return;
        }

        // Validate personal note if enabled
        String personalNote = "";
        boolean hasPersonalNote = swPersonalNote.isChecked();
        if (hasPersonalNote) {
            personalNote = etPersonalNote.getText().toString().trim();
            if (personalNote.isEmpty()) {
                Toast.makeText(this, "Please enter personal note text or uncheck the option", Toast.LENGTH_SHORT).show();
                return;
            }
        }

        double deliveryLat = hasSelectedLocation ? selectedLat : 27.7172;
        double deliveryLng = hasSelectedLocation ? selectedLng : 85.3240;

        // Get selected payment method
        int selectedPaymentId = rgPaymentMethod.getCheckedRadioButtonId();
        String paymentMethod = (selectedPaymentId == R.id.rbESEWA) ? "esewa" : "cod";

        // Build order JSON
        JsonObject orderBody = buildOrderJson(
                recipientName,
                recipientPhone,
                deliveryAddress,
                deliveryLat,
                deliveryLng,
                paymentMethod,
                swGiftWrapping.isChecked(),
                hasPersonalNote,
                personalNote
        );

        // Send to API
        submitOrder(orderBody);
    }

    private JsonObject buildOrderJson(String recipientName, String recipientPhone, String address,
                                      double lat, double lng, String paymentMethod,
                                      boolean hasGiftWrapping, boolean hasPersonalNote, String personalNote) {
        JsonObject json = new JsonObject();

        json.addProperty("recipient_name", recipientName);
        json.addProperty("recipient_phone", recipientPhone);
        json.addProperty("delivery_address", address);
        json.addProperty("delivery_lat", lat);
        json.addProperty("delivery_lng", lng);
        json.addProperty("payment_method", paymentMethod);
        json.addProperty("has_gift_wrapping", hasGiftWrapping);
        json.addProperty("has_personal_note", hasPersonalNote);
        json.addProperty("personal_note_text", personalNote);

        // Add items from cart
        JsonArray itemsArray = new JsonArray();
        List<CartItem> cartItems = cartManager.getCartItems();

        for (CartItem item : cartItems) {
            JsonObject itemObj = new JsonObject();
            itemObj.addProperty("gift_id", item.getGiftId());
            itemObj.addProperty("quantity", item.getQuantity());
            itemsArray.add(itemObj);
        }

        json.add("items", itemsArray);

        return json;
    }

    private void fetchDeliveryCharge(double lat, double lng) {
        double subtotal = cartManager.getCartItems().stream()
                .mapToDouble(CartItem::getLineTotal)
                .sum();

        JsonObject request = new JsonObject();
        request.addProperty("delivery_lat", lat);
        request.addProperty("delivery_lng", lng);
        request.addProperty("subtotal", subtotal);

        String token = sessionManager.getAuthToken();
        String bearerToken = "Bearer " + token;

        apiService.calculateDelivery(bearerToken, request).enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (response.isSuccessful() && response.body() != null) {
                    try {
                        JsonObject data = response.body().getAsJsonObject("data");
                        currentDeliveryCharge = data.get("delivery_charge").getAsDouble();
                        currentDistanceKm = data.get("distance_km").getAsDouble();
                        boolean isFree = data.get("is_free_delivery").getAsBoolean();

                        // Show same-day delivery option if distance < 30km
                        if (currentDistanceKm < 30) {
                            llSameDayDelivery.setVisibility(android.view.View.VISIBLE);
                            Toast.makeText(CheckoutActivity.this, "Same-day delivery is available for your location!", Toast.LENGTH_SHORT).show();
                        } else {
                            llSameDayDelivery.setVisibility(android.view.View.GONE);
                            cbSameDayDelivery.setChecked(false);
                            selectedDeliveryDate = "";
                            etDeliveryDate.setText("");
                        }

                        // Update UI
                        updateOrderSummary(cartManager.getCartItems());

                        String message = isFree ? 
                            String.format("Free delivery! (%.1f km)", currentDistanceKm) :
                            String.format("Delivery: NPR %.0f (%.1f km)", currentDeliveryCharge, currentDistanceKm);
                        
                        Toast.makeText(CheckoutActivity.this, message, Toast.LENGTH_SHORT).show();
                    } catch (Exception e) {
                        e.printStackTrace();
                        Toast.makeText(CheckoutActivity.this, "Could not calculate delivery charge", Toast.LENGTH_SHORT).show();
                    }
                } else if (response.code() == 422) {
                    // Location validation error
                    try {
                        JsonObject errorBody = new com.google.gson.JsonParser().parse(response.errorBody().string()).getAsJsonObject();
                        String message = errorBody.get("message").getAsString();
                        Toast.makeText(CheckoutActivity.this, message, Toast.LENGTH_LONG).show();
                    } catch (Exception e) {
                        Toast.makeText(CheckoutActivity.this, "Invalid delivery location", Toast.LENGTH_SHORT).show();
                    }
                } else {
                    Toast.makeText(CheckoutActivity.this, "Failed to calculate delivery charge", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                Toast.makeText(CheckoutActivity.this, "Error calculating delivery: " + t.getMessage(), Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void submitOrder(JsonObject orderBody) {
        btnPlaceOrder.setEnabled(false);
        Toast.makeText(this, "Processing your order...", Toast.LENGTH_SHORT).show();

        // Get bearer token
        String token = sessionManager.getAuthToken();
        String bearerToken = "Bearer " + token;

        apiService.createOrder(bearerToken, orderBody).enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                btnPlaceOrder.setEnabled(true);
                
                if (response.isSuccessful() && response.body() != null) {
                    JsonObject responseBody = response.body();
                    try {
                        // Check if response has success flag
                        if (responseBody.has("success") && responseBody.get("success").getAsBoolean()) {
                            JsonObject data = responseBody.getAsJsonObject("data");
                            int orderId = data.get("order_id").getAsInt();
                            
                            // Set delivery date if selected
                            if (!selectedDeliveryDate.isEmpty()) {
                                setDeliveryDate(orderId, bearerToken);
                            }
                            
                            // Get selected payment method
                            int selectedPaymentId = rgPaymentMethod.getCheckedRadioButtonId();
                            boolean isEsewa = (selectedPaymentId == R.id.rbESEWA);
                            
                            if (isEsewa && data.has("esewa_payment_url")) {
                                // eSewa payment - open WebView for payment
                                String paymentUrl = data.get("esewa_payment_url").getAsString();
                                String orderNumber = "ORD-" + String.format("%05d", orderId);
                                String totalAmount = data.get("total_amount").getAsString();
                                
                                // Clear cart before payment
                                cartManager.clearCart();
                                
                                // Launch eSewa payment activity
                                Intent intent = new Intent(CheckoutActivity.this, EsewaPaymentActivity.class);
                                intent.putExtra("payment_url", paymentUrl);
                                intent.putExtra("order_number", orderNumber);
                                intent.putExtra("total_amount", totalAmount);
                                intent.putExtra("order_id", orderId);
                                startActivityForResult(intent, ESEWA_PAYMENT_REQUEST);
                                finish();
                            } else {
                                // COD payment - go directly to success screen
                                String orderNumber = "ORD-" + String.format("%05d", orderId);
                                String totalAmount = data.get("total_amount").getAsString();
                                
                                // Clear cart
                                cartManager.clearCart();
                                
                                // Navigate to success screen
                                Intent intent = new Intent(CheckoutActivity.this, OrderSuccessActivity.class);
                                intent.putExtra("order_number", orderNumber);
                                intent.putExtra("total_amount", totalAmount);
                                intent.putExtra("payment_method", "cod");
                                intent.putExtra("order_id", orderId);
                                startActivity(intent);
                                finish();
                            }
                            return;
                        }
                    } catch (Exception e) {
                        e.printStackTrace();
                        Toast.makeText(CheckoutActivity.this, "Error processing response: " + e.getMessage(), Toast.LENGTH_SHORT).show();
                        return;
                    }
                } else if (response.errorBody() != null) {
                    // Handle error response
                    try {
                        String errorResponse = response.errorBody().string();
                        Toast.makeText(CheckoutActivity.this, "Error: " + errorResponse, Toast.LENGTH_LONG).show();
                    } catch (Exception e) {
                        Toast.makeText(CheckoutActivity.this, "Error: " + response.code(), Toast.LENGTH_SHORT).show();
                    }
                    return;
                }

                Toast.makeText(CheckoutActivity.this, "Failed to place order - unexpected response", Toast.LENGTH_SHORT).show();
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                Toast.makeText(CheckoutActivity.this, "Error: " + t.getMessage(), Toast.LENGTH_SHORT).show();
                btnPlaceOrder.setEnabled(true);
            }
        });
    }

    private void setDeliveryDate(int orderId, String bearerToken) {
        JsonObject dateBody = new JsonObject();
        dateBody.addProperty("delivery_date", selectedDeliveryDate);

        apiService.setDeliveryDate(bearerToken, orderId, dateBody).enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (response.isSuccessful() && response.body() != null) {
                    // Delivery date set successfully
                } else {
                    // Error setting delivery date - but order is still created
                    // Just log it
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                // Delivery date setting failed - but order is still created
                // Just log it silently
            }
        });
    }

    private void loadDefaultAddress() {
        String token = sessionManager.getAuthToken();
        String bearerToken = "Bearer " + token;

        apiService.getProfile(bearerToken).enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (response.isSuccessful() && response.body() != null) {
                    JsonObject data = response.body().getAsJsonObject("user");
                    
                    if (data.has("default_delivery_address") && !data.get("default_delivery_address").isJsonNull()) {
                        String defaultAddress = data.get("default_delivery_address").getAsString();
                        double lat = data.get("default_delivery_lat").getAsDouble();
                        double lng = data.get("default_delivery_lng").getAsDouble();
                        
                        if (!defaultAddress.isEmpty()) {
                            etDeliveryAddress.setText(defaultAddress);
                            selectedLat = lat;
                            selectedLng = lng;
                            hasSelectedLocation = true;
                            
                            // Fetch delivery charge
                            fetchDeliveryCharge(lat, lng);
                            
                            Toast.makeText(CheckoutActivity.this, "Default address loaded!", Toast.LENGTH_SHORT).show();
                        } else {
                            Toast.makeText(CheckoutActivity.this, "No default address set", Toast.LENGTH_SHORT).show();
                        }
                    } else {
                        Toast.makeText(CheckoutActivity.this, "No default address set", Toast.LENGTH_SHORT).show();
                    }
                } else {
                    Toast.makeText(CheckoutActivity.this, "Failed to load default address", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                Toast.makeText(CheckoutActivity.this, "Error loading address: " + t.getMessage(), Toast.LENGTH_SHORT).show();
            }
        });
    }
}
