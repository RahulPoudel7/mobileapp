package com.example.giftbox.view;

import android.content.Intent;
import android.graphics.Bitmap;
import android.net.Uri;
import android.os.Bundle;
import android.util.Base64;
import android.view.View;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.ImageView;
import android.widget.ProgressBar;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

import com.example.giftbox.R;
import com.example.giftbox.api.ApiClient;
import com.example.giftbox.api.ApiService;
import com.example.giftbox.utils.SessionManager;

import java.io.UnsupportedEncodingException;
import java.net.URLEncoder;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class EsewaPaymentActivity extends AppCompatActivity {

    private WebView webViewEsewa;
    private ProgressBar progressBar;
    private ImageView btnClose;

    private String paymentUrl;
    private String orderNumber;
    private String totalAmount;
    private int orderId;
    
    private SessionManager sessionManager;
    private ApiService apiService;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_esewa_payment);

        if (getSupportActionBar() != null) {
            getSupportActionBar().hide();
        }

        // Initialize API client
        sessionManager = new SessionManager(this);
        apiService = ApiClient.getApiService();

        initializeViews();
        loadPaymentData();
        setupWebView();
        setupListeners();
    }

    private void initializeViews() {
        webViewEsewa = findViewById(R.id.webViewEsewa);
        progressBar = findViewById(R.id.progressBar);
        btnClose = findViewById(R.id.btnClose);
    }

    private void loadPaymentData() {
        Intent intent = getIntent();
        paymentUrl = intent.getStringExtra("payment_url");
        orderNumber = intent.getStringExtra("order_number");
        totalAmount = intent.getStringExtra("total_amount");
        orderId = intent.getIntExtra("order_id", 0);

        if (paymentUrl == null || paymentUrl.isEmpty()) {
            Toast.makeText(this, "Invalid payment URL", Toast.LENGTH_SHORT).show();
            finish();
        }
    }

    private void setupWebView() {
        webViewEsewa.getSettings().setJavaScriptEnabled(true);
        webViewEsewa.getSettings().setDomStorageEnabled(true);
        webViewEsewa.getSettings().setLoadWithOverviewMode(true);
        webViewEsewa.getSettings().setUseWideViewPort(true);

        webViewEsewa.setWebViewClient(new WebViewClient() {
            @Override
            public void onPageStarted(WebView view, String url, Bitmap favicon) {
                super.onPageStarted(view, url, favicon);
                progressBar.setVisibility(View.VISIBLE);
            }

            @Override
            public void onPageFinished(WebView view, String url) {
                super.onPageFinished(view, url);
                progressBar.setVisibility(View.GONE);
                
                // Check success/failure AFTER page finishes loading
                // This ensures the backend callback has completed
                if (url.contains("/api/payment/esewa/success")) {
                    // Give backend 1 second to process the callback
                    view.postDelayed(() -> handlePaymentSuccess(url), 1000);
                } else if (url.contains("/api/payment/esewa/failure")) {
                    handlePaymentFailure(url);
                }
            }

            @Override
            public boolean shouldOverrideUrlLoading(WebView view, String url) {
                // Don't override - let the WebView load all URLs normally
                // This allows the backend callback to complete
                return false;
            }
        });

        // Submit payment via POST instead of GET
        submitPaymentPost();
    }

    private void submitPaymentPost() {
        try {
            // Parse the URL to extract base URL and parameters
            Uri uri = Uri.parse(paymentUrl);
            String baseUrl = uri.getScheme() + "://" + uri.getHost() + uri.getPath();
            
            // Build POST data from query parameters
            StringBuilder postData = new StringBuilder();
            for (String paramName : uri.getQueryParameterNames()) {
                if (postData.length() > 0) {
                    postData.append("&");
                }
                String paramValue = uri.getQueryParameter(paramName);
                postData.append(URLEncoder.encode(paramName, "UTF-8"));
                postData.append("=");
                postData.append(URLEncoder.encode(paramValue, "UTF-8"));
            }
            
            // Submit POST request
            webViewEsewa.postUrl(baseUrl, postData.toString().getBytes());
            
        } catch (Exception e) {
            e.printStackTrace();
            Toast.makeText(this, "Error loading payment: " + e.getMessage(), Toast.LENGTH_SHORT).show();
            finish();
        }
    }

    private void setupListeners() {
        btnClose.setOnClickListener(v -> {
            // Confirm before closing
            new androidx.appcompat.app.AlertDialog.Builder(this)
                    .setTitle("Cancel Payment")
                    .setMessage("Are you sure you want to cancel the payment?")
                    .setPositiveButton("Yes", (dialog, which) -> {
                        setResult(RESULT_CANCELED);
                        finish();
                    })
                    .setNegativeButton("No", null)
                    .show();
        });
    }

    private void handlePaymentSuccess(String url) {
        // Extract transaction details from URL
        Uri uri = Uri.parse(url);
        
        // eSewa sends data in base64 encoded 'data' parameter
        String dataParam = uri.getQueryParameter("data");
        String transactionId = null;
        
        android.util.Log.d("EsewaPayment", "Success URL: " + url);
        android.util.Log.d("EsewaPayment", "Data param: " + dataParam);
        
        if (dataParam != null) {
            try {
                // Decode base64 and parse JSON
                String decoded = new String(android.util.Base64.decode(dataParam, android.util.Base64.DEFAULT));
                android.util.Log.d("EsewaPayment", "Decoded data: " + decoded);
                
                org.json.JSONObject json = new org.json.JSONObject(decoded);
                transactionId = json.getString("transaction_uuid");
            } catch (Exception e) {
                android.util.Log.e("EsewaPayment", "Error decoding data", e);
            }
        }
        
        // Fallback: try to get transaction_uuid from query params
        if (transactionId == null) {
            transactionId = uri.getQueryParameter("transaction_uuid");
        }
        
        // Last resort: use the stored transaction UUID from order creation
        if (transactionId == null) {
            android.util.Log.w("EsewaPayment", "Transaction UUID not found in URL, using order's transaction UUID");
            // We don't have it stored, so we'll need to fetch it from the order
            // For now, just show success without verification
            Intent intent = new Intent(EsewaPaymentActivity.this, OrderSuccessActivity.class);
            intent.putExtra("order_number", orderNumber);
            intent.putExtra("total_amount", totalAmount);
            intent.putExtra("payment_method", "esewa");
            intent.putExtra("order_id", orderId);
            startActivity(intent);
            setResult(RESULT_OK);
            finish();
            return;
        }
        
        progressBar.setVisibility(View.VISIBLE);
        
        // Call verification API
        updatePaymentStatusDirectly(transactionId);
    }
    
    private void updatePaymentStatusDirectly(String transactionUuid) {
        String token = "Bearer " + sessionManager.getAuthToken();
        
        // Log the request details for debugging
        android.util.Log.d("EsewaPayment", "Calling verifyEsewaPayment");
        android.util.Log.d("EsewaPayment", "Order ID: " + orderId);
        android.util.Log.d("EsewaPayment", "Amount: " + totalAmount);
        android.util.Log.d("EsewaPayment", "Transaction UUID: " + transactionUuid);
        android.util.Log.d("EsewaPayment", "Token: " + (token != null && !token.isEmpty() ? "Present" : "Missing"));
        
        // Call the verification endpoint to mark the order as paid
        double amount = Double.parseDouble(totalAmount);
        Call<com.google.gson.JsonObject> call = apiService.verifyEsewaPayment(token, orderId, amount, transactionUuid);
        
        call.enqueue(new Callback<com.google.gson.JsonObject>() {
            @Override
            public void onResponse(Call<com.google.gson.JsonObject> call, Response<com.google.gson.JsonObject> response) {
                progressBar.setVisibility(View.GONE);
                
                android.util.Log.d("EsewaPayment", "Response code: " + response.code());
                
                if (response.isSuccessful() && response.body() != null) {
                    android.util.Log.d("EsewaPayment", "Response body: " + response.body().toString());
                    // Payment verified and order marked as paid - navigate to success
                    Intent intent = new Intent(EsewaPaymentActivity.this, OrderSuccessActivity.class);
                    intent.putExtra("order_number", orderNumber);
                    intent.putExtra("total_amount", totalAmount);
                    intent.putExtra("payment_method", "esewa");
                    intent.putExtra("order_id", orderId);
                    intent.putExtra("transaction_id", transactionUuid);
                    
                    startActivity(intent);
                    setResult(RESULT_OK);
                    finish();
                } else {
                    // Log the error response
                    String errorMsg = "Payment verification failed";
                    try {
                        if (response.errorBody() != null) {
                            errorMsg = response.errorBody().string();
                            android.util.Log.e("EsewaPayment", "Error body: " + errorMsg);
                        }
                    } catch (Exception e) {
                        e.printStackTrace();
                    }
                    Toast.makeText(EsewaPaymentActivity.this, "Verification failed. Please contact support.", Toast.LENGTH_LONG).show();
                    finish();
                }
            }

            @Override
            public void onFailure(Call<com.google.gson.JsonObject> call, Throwable t) {
                progressBar.setVisibility(View.GONE);
                
                // Log the actual error
                android.util.Log.e("EsewaPayment", "API call failed: " + t.getMessage(), t);
                
                // Check if it's a JSON parsing error - this usually means the backend returned HTML
                if (t.getMessage() != null && t.getMessage().contains("MalformedJsonException")) {
                    Toast.makeText(EsewaPaymentActivity.this, "Server error: Please check your internet connection and try again", Toast.LENGTH_LONG).show();
                } else {
                    Toast.makeText(EsewaPaymentActivity.this, "Network error: " + t.getMessage(), Toast.LENGTH_LONG).show();
                }
                finish();
            }
        });
    }

    private void handlePaymentFailure(String url) {
        // Navigate to payment failure screen
        Intent intent = new Intent(this, PaymentFailureActivity.class);
        intent.putExtra("order_number", orderNumber);
        intent.putExtra("total_amount", totalAmount);
        intent.putExtra("order_id", orderId);
        startActivity(intent);
        setResult(RESULT_CANCELED);
        finish();
    }

    @Override
    public void onBackPressed() {
        if (webViewEsewa.canGoBack()) {
            webViewEsewa.goBack();
        } else {
            new androidx.appcompat.app.AlertDialog.Builder(this)
                    .setTitle("Cancel Payment")
                    .setMessage("Are you sure you want to cancel the payment?\nYour order will be saved and you can retry later.")
                    .setPositiveButton("Yes, Cancel", (dialog, which) -> {
                        handlePaymentFailure("cancelled");
                    })
                    .setNegativeButton("No, Continue", null)
                    .show();
        }
    }
}
