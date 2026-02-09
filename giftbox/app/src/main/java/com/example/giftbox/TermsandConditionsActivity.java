package com.example.giftbox;

import android.content.Intent;
import android.os.Bundle;
import android.widget.ImageView;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;

public class TermsandConditionsActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_termsand_conditions);

        if (getSupportActionBar() != null) {
            getSupportActionBar().hide();
        }

        ImageView ivBackTerms = findViewById(R.id.ivBackTerms);
        ivBackTerms.setOnClickListener(v -> {
            Intent intent = new Intent(TermsandConditionsActivity.this, MoreActivity.class);
            startActivity(intent);
            finish();
        });
    }
}

