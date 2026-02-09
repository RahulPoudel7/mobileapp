package com.example.giftbox;

import android.content.Intent;
import android.os.Bundle;
import android.widget.ImageView;

import androidx.appcompat.app.AppCompatActivity;

import com.example.giftbox.view.ProfileActivity;

public class OrderHistoryActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_order_history);

        ImageView ivBackOrder = findViewById(R.id.ivBackOrder);
        ivBackOrder.setOnClickListener(v -> {
            Intent intent = new Intent(OrderHistoryActivity.this, ProfileActivity.class);
            startActivity(intent);
            finish();
        });
    }
}
