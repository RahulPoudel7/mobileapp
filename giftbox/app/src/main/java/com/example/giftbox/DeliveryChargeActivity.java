package com.example.giftbox;

import android.os.Bundle;
import android.widget.ImageView;

import androidx.appcompat.app.AppCompatActivity;

public class DeliveryChargeActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_delivery_charge);

        if (getSupportActionBar() != null) {
            getSupportActionBar().hide();
        }

        ImageView ivBackDelivery = findViewById(R.id.ivBackDelivery);
        ivBackDelivery.setOnClickListener(v -> onBackPressed());
    }
}
