package com.example.giftbox.view;

import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import com.example.giftbox.R;

public class FaqActivity extends AppCompatActivity {


    private final LinearLayout[] faqBlocks = new LinearLayout[8];
    private final TextView[] tvQuestions = new TextView[8];
    private final TextView[] tvAnswers = new TextView[8];

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_faq);

        if (getSupportActionBar() != null) {
            getSupportActionBar().hide();
        }

        setupWindowInsets();
        initFAQViews();
        setupClickListeners();
        setupBackButton();
    }

    private void setupWindowInsets() {
        View main = findViewById(R.id.main_faqs);
        ViewCompat.setOnApplyWindowInsetsListener(main, (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });
    }

    private void initFAQViews() {

        faqBlocks[0] = findViewById(R.id.block_q1);
        faqBlocks[1] = findViewById(R.id.block_q2);
        faqBlocks[2] = findViewById(R.id.block_q3);
        faqBlocks[3] = findViewById(R.id.block_q4);
        faqBlocks[4] = findViewById(R.id.block_q5);
        faqBlocks[5] = findViewById(R.id.block_q6);
        faqBlocks[6] = findViewById(R.id.block_q7);
        faqBlocks[7] = findViewById(R.id.block_q8);


        tvQuestions[0] = findViewById(R.id.tvQ1);
        tvQuestions[1] = findViewById(R.id.tvQ2);
        tvQuestions[2] = findViewById(R.id.tvQ3);
        tvQuestions[3] = findViewById(R.id.tvQ4);
        tvQuestions[4] = findViewById(R.id.tvQ5);
        tvQuestions[5] = findViewById(R.id.tvQ6);
        tvQuestions[6] = findViewById(R.id.tvQ7);
        tvQuestions[7] = findViewById(R.id.tvQ8);


        tvAnswers[0] = findViewById(R.id.tvA1);
        tvAnswers[1] = findViewById(R.id.tvA2);
        tvAnswers[2] = findViewById(R.id.tvA3);
        tvAnswers[3] = findViewById(R.id.tvA4);
        tvAnswers[4] = findViewById(R.id.tvA5);
        tvAnswers[5] = findViewById(R.id.tvA6);
        tvAnswers[6] = findViewById(R.id.tvA7);
        tvAnswers[7] = findViewById(R.id.tvA8);
    }

    private void setupClickListeners() {

        for (int i = 0; i < 8; i++) {
            final int index = i;
            faqBlocks[index].setOnClickListener(v -> toggleAnswerVisibility(index));


            tvQuestions[index].setOnClickListener(v -> toggleAnswerVisibility(index));
        }
    }

    private void setupBackButton() {
        ImageView ivBackFaqs = findViewById(R.id.ivBackFaqs);
        ivBackFaqs.setOnClickListener(v -> onBackPressed());
    }

    private void toggleAnswerVisibility(int index) {

        for (int i = 0; i < 8; i++) {
            if (i != index) {
                tvAnswers[i].setVisibility(View.GONE);
            }
        }


        TextView currentAnswer = tvAnswers[index];
        if (currentAnswer.getVisibility() == View.VISIBLE) {
            currentAnswer.setVisibility(View.GONE);
        } else {
            currentAnswer.setVisibility(View.VISIBLE);
        }
    }
}
