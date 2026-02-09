package com.example.giftbox;

import android.content.Intent;
import android.os.Bundle;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.appcompat.app.AppCompatActivity;

import com.example.giftbox.view.AboutUSActivity;
import com.example.giftbox.view.FaqActivity;

public class MoreActivity extends AppCompatActivity {

    ImageView ivBack;
    LinearLayout rowAboutUs,  rowFeedback, rowNotification, rowDeliveryCharge, rowPrivacyPolicy, rowTerms, rowFaqs;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (getSupportActionBar() != null) {
            getSupportActionBar().hide();
        }


        setContentView(R.layout.activity_more);



        ivBack = findViewById(R.id.ivBack);
        rowAboutUs = findViewById(R.id.rowAboutUs);
        rowPrivacyPolicy = findViewById(R.id.rowPrivacyPolicy);
        rowTerms = findViewById(R.id.rowTerms);
        rowFaqs = findViewById(R.id.rowFaqs);
        rowFeedback = findViewById(R.id.rowFeedback);
        rowNotification = findViewById(R.id.rowNotification);
        rowDeliveryCharge = findViewById(R.id.rowDeliveryCharge);


        ivBack.setOnClickListener(v -> finish());
        LinearLayout rowDeliveryCharge = findViewById(R.id.rowDeliveryCharge);

        rowDeliveryCharge.setOnClickListener(v ->
                startActivity(new Intent(MoreActivity.this, DeliveryChargeActivity.class)));

        rowNotification.setOnClickListener(v->
        startActivity(new Intent(MoreActivity.this,NotificationActivity.class)));

        rowFeedback.setOnClickListener(v ->
                startActivity(new Intent(MoreActivity.this, FeedbackActivity.class)));

        rowAboutUs.setOnClickListener(v ->
                startActivity(new Intent(MoreActivity.this, AboutUSActivity.class)));

        rowPrivacyPolicy.setOnClickListener(v ->
                startActivity(new Intent(MoreActivity.this, PrivacyPolicyActivity.class)));

        rowTerms.setOnClickListener(v ->
                startActivity(new Intent(MoreActivity.this, TermsandConditionsActivity.class)));

        rowFaqs.setOnClickListener(v ->
                startActivity(new Intent(MoreActivity.this, FaqActivity.class)));
    }
}
