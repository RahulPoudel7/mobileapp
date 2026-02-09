package com.example.giftbox.view;

import android.os.Bundle;
import android.widget.ImageView;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;

import com.example.giftbox.R;

public class AboutUSActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        if (getSupportActionBar() != null) {
            getSupportActionBar().hide();
        }
        setContentView(R.layout.activity_about_usactivity);

        ImageView ivBackAbout = findViewById(R.id.ivBackAbout);
        ivBackAbout.setOnClickListener(v -> onBackPressed());

    }
}
