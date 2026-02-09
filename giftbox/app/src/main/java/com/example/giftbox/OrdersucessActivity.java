package com.example.giftbox;

import android.content.Intent;
import android.os.Bundle;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;

import com.example.giftbox.view.HomeActivity;
import com.google.android.material.button.MaterialButton;

public class OrdersucessActivity extends AppCompatActivity {

    private ImageView backButton;
    private TextView txtOrderId;
    private TextView txtOrderDate;
    private TextView txtItemsSummary;
    private TextView txtTotalPaid;
    private TextView txtDeliveryAddress;
    private TextView txtPaymentStatus;

    private MaterialButton btnBackHome, btnViewOrder;

    // keep for forwarding to details
    private String subtotalText;
    private String deliveryFeeText;
    private String paymentStatusForward;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (getSupportActionBar() != null) {
            getSupportActionBar().hide();
        }
        setContentView(R.layout.activity_ordersucess);

        initViews();
        readIntentData();
        setupClicks();
    }

    private void initViews() {
        backButton = findViewById(R.id.backButton);

        txtOrderId = findViewById(R.id.txtOrderId);
        txtOrderDate = findViewById(R.id.txtOrderDate);
        txtItemsSummary = findViewById(R.id.txtItemsSummary);
        txtTotalPaid = findViewById(R.id.txtTotalPaid);
        txtPaymentStatus = findViewById(R.id.txtPaymentStatus);

        txtDeliveryAddress = findViewById(R.id.txtDeliveryAddress);

        btnBackHome = findViewById(R.id.btnBackHome);
        btnViewOrder = findViewById(R.id.btnViewOrder);
    }

    private void readIntentData() {
        Intent intent = getIntent();
        if (intent == null) return;

        String orderId   = intent.getStringExtra("order_id");
        String orderDate = intent.getStringExtra("order_date");
        String totalPaid = intent.getStringExtra("total_paid");
        String itemsSummary = intent.getStringExtra("items_summary");
        String deliveryName = intent.getStringExtra("delivery_name");
        String deliveryAddress = intent.getStringExtra("delivery_address");
        String deliveryPhone = intent.getStringExtra("delivery_phone");

        subtotalText    = intent.getStringExtra("subtotal_text");
        deliveryFeeText = intent.getStringExtra("delivery_fee_text");
        paymentStatusForward = intent.getStringExtra("payment_status");

        if (orderId != null) {
            txtOrderId.setText(orderId);
        }
        if (orderDate != null) {
            txtOrderDate.setText(orderDate);
        }
        if (totalPaid != null) {
            txtTotalPaid.setText(totalPaid);
        }
        if (itemsSummary != null) {
            txtItemsSummary.setText(itemsSummary);
        }
        if (paymentStatusForward != null) {
            txtPaymentStatus.setText("Payment status: " + paymentStatusForward);
        }

        StringBuilder addressBuilder = new StringBuilder();
        if (deliveryName != null && !deliveryName.isEmpty()) {
            addressBuilder.append(deliveryName).append("\n");
        }
        if (deliveryAddress != null && !deliveryAddress.isEmpty()) {
            addressBuilder.append(deliveryAddress).append("\n");
        }
        if (deliveryPhone != null && !deliveryPhone.isEmpty()) {
            addressBuilder.append(deliveryPhone);
        }
        if (addressBuilder.length() > 0) {
            txtDeliveryAddress.setText(addressBuilder.toString());
        }
    }

    private void setupClicks() {
        backButton.setOnClickListener(v -> finish());

        btnBackHome.setOnClickListener(v -> {
            Intent homeIntent = new Intent(OrdersucessActivity.this, HomeActivity.class);
            homeIntent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP | Intent.FLAG_ACTIVITY_NEW_TASK);
            startActivity(homeIntent);
            finish();
        });

        btnViewOrder.setOnClickListener(v -> {
            Intent intent = new Intent(OrdersucessActivity.this, OrderDetailsActivity.class);
            intent.putExtra("order_id", txtOrderId.getText().toString());
            intent.putExtra("order_date", txtOrderDate.getText().toString());
            intent.putExtra("total_paid", txtTotalPaid.getText().toString());
            intent.putExtra("items_list", txtItemsSummary.getText().toString());
            intent.putExtra("delivery_address", txtDeliveryAddress.getText().toString());

            // forward pricing + status
            intent.putExtra("subtotal_text", subtotalText);
            intent.putExtra("delivery_fee_text", deliveryFeeText);
            intent.putExtra("payment_status", paymentStatusForward);

            startActivity(intent);
        });
    }
}
