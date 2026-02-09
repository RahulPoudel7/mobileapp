package com.example.giftbox;

import android.content.Intent;
import android.os.Bundle;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

public class FeedbackActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_feedback);

        if (getSupportActionBar() != null) {
            getSupportActionBar().hide();
        }

        EditText etFeedback = findViewById(R.id.etFeedback);
        Button btnSubmitFeedback = findViewById(R.id.btnSubmitFeedback);

        ImageView ivBackFeedback = findViewById(R.id.ivBackFeedback);
        ivBackFeedback.setOnClickListener(v -> {
            Intent intent = new Intent(FeedbackActivity.this, MoreActivity.class);
            startActivity(intent);

            finish();
        });


        // Submit button
        btnSubmitFeedback.setOnClickListener(v -> {
            String feedbackText = etFeedback.getText().toString().trim();
            if (!feedbackText.isEmpty()) {
                // You can send feedback to server or save it here
                Toast.makeText(FeedbackActivity.this,
                        "Thank you for your feedback!", Toast.LENGTH_SHORT).show();
                etFeedback.setText("");
            } else {
                Toast.makeText(FeedbackActivity.this,
                        "Please enter your feedback first.", Toast.LENGTH_SHORT).show();
            }
        });
    }
}
