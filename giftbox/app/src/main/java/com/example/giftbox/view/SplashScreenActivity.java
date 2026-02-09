package com.example.giftbox.view;

import android.content.Intent;
import android.os.Bundle;
import android.os.Handler;
import android.view.Window;
import android.view.WindowManager;
import android.view.animation.AlphaAnimation;
import android.view.animation.Animation;
import android.view.animation.ScaleAnimation;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import com.example.giftbox.R;
import com.example.giftbox.controllers.SplashController;
import com.example.giftbox.utils.SessionManager;

public class SplashScreenActivity extends AppCompatActivity {

    private SessionManager sessionManager;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_splash_screen);

        if (getSupportActionBar() != null) {
            getSupportActionBar().hide();
        }

        // Init session manager
        sessionManager = new SessionManager(this);

        // Init controller
        SplashController splashController = new SplashController();

        // Status bar color to match brand
        Window window = getWindow();
        window.clearFlags(WindowManager.LayoutParams.FLAG_TRANSLUCENT_STATUS);
        window.addFlags(WindowManager.LayoutParams.FLAG_DRAWS_SYSTEM_BAR_BACKGROUNDS);
        window.setStatusBarColor(0xFFE91E63); // pink

        ImageView logo = findViewById(R.id.logo);
        TextView appName = findViewById(R.id.appName);

        // Combined scale + fade animation
        ScaleAnimation scale = new ScaleAnimation(
                0.8f, 1f, 0.8f, 1f,
                Animation.RELATIVE_TO_SELF, 0.5f,
                Animation.RELATIVE_TO_SELF, 0.5f
        );
        scale.setDuration(800);
        scale.setFillAfter(true);

        AlphaAnimation fade = new AlphaAnimation(0f, 1f);
        fade.setDuration(800);
        fade.setFillAfter(true);

        logo.startAnimation(scale);
        appName.startAnimation(fade);

        // Use controller for duration
        long delay = splashController.getSplashDurationMs();

        new Handler().postDelayed(() -> {
            // Check if user is already logged in AND Remember Me was selected
            Intent intent;
            if (sessionManager.isLoggedIn() && sessionManager.isRememberMeEnabled()) {
                // User is logged in AND Remember Me was checked, go to homepage
                intent = new Intent(SplashScreenActivity.this, HomeActivity.class);
            } else {
                // User is not logged in or Remember Me was not checked, go to welcome page
                intent = new Intent(SplashScreenActivity.this, WelcomeActivity.class);
            }
            startActivity(intent);
            overridePendingTransition(android.R.anim.fade_in, android.R.anim.fade_out);
            finish();
        }, delay);

        // Edge-to-edge insets
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets bars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(bars.left, bars.top, bars.right, bars.bottom);
            return insets;
        });
    }
}
