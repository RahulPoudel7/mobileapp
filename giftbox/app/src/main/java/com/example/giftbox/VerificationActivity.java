package com.example.giftbox;

import android.annotation.SuppressLint;
import android.app.ProgressDialog;
import android.content.Intent;
import android.os.Bundle;
import android.os.CountDownTimer;
import android.text.Editable;
import android.text.TextWatcher;
import android.view.KeyEvent;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import com.example.giftbox.api.ApiClient;
import com.example.giftbox.api.ApiService;
import com.example.giftbox.models.ResendOtpRequest;
import com.example.giftbox.models.ResendOtpResponse;
import com.example.giftbox.models.VerifyOtpRequest;
import com.example.giftbox.models.VerifyOtpResponse;
import com.example.giftbox.utils.SessionManager;
import com.example.giftbox.view.LoginActivity;
import com.google.android.material.button.MaterialButton;

import java.util.Locale;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class VerificationActivity extends AppCompatActivity {

    private EditText otp1, otp2, otp3, otp4, otp5, otp6;
    private TextView tvOtpError, timerText, resendText, subText;
    private MaterialButton verifyButton;
    private String email;
    private boolean fromSignup = false;
    
    private ApiService apiService;
    private SessionManager sessionManager;
    private ProgressDialog progressDialog;
    private CountDownTimer countDownTimer;
    private long timeLeftInMillis = 180000; // 3 minutes

    @SuppressLint("MissingInflatedId")
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        if (getSupportActionBar() != null) {
            getSupportActionBar().hide();
        }

        setContentView(R.layout.activity_verification);
        
        // Get email from intent
        email = getIntent().getStringExtra("email");
        fromSignup = getIntent().getBooleanExtra("from_signup", false);
        
        if (email == null || email.isEmpty()) {
            Toast.makeText(this, "Error: Email not provided", Toast.LENGTH_SHORT).show();
            finish();
            return;
        }

        // Initialize views
        otp1 = findViewById(R.id.otp1);
        otp2 = findViewById(R.id.otp2);
        otp3 = findViewById(R.id.otp3);
        otp4 = findViewById(R.id.otp4);
        otp5 = findViewById(R.id.otp5);
        otp6 = findViewById(R.id.otp6);
        tvOtpError = findViewById(R.id.tvOtpError);
        timerText = findViewById(R.id.timerText);
        resendText = findViewById(R.id.resendText);
        subText = findViewById(R.id.subText);
        verifyButton = findViewById(R.id.verifyButton);
        ImageView backArrow = findViewById(R.id.backArrow);

        // Set email in subtitle
        subText.setText("We have sent the code to " + email);
        
        // Initialize API and session
        apiService = ApiClient.getApiService();
        sessionManager = new SessionManager(this);
        
        // Initialize progress dialog
        progressDialog = new ProgressDialog(this);
        progressDialog.setMessage("Verifying OTP...");
        progressDialog.setCancelable(false);

        // Setup OTP input
        setupOtpInputs();
        
        // Start countdown timer
        startTimer();

        backArrow.setOnClickListener(v -> finish());
        
        resendText.setOnClickListener(v -> resendOtp());

        verifyButton.setOnClickListener(v -> {
            String enteredOtp = getEnteredOtp();
            if (enteredOtp.length() == 6) {
                verifyOtp(enteredOtp);
            } else {
                tvOtpError.setText("Please enter complete OTP");
            }
        });

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, 0, systemBars.right, systemBars.bottom);
            return insets;
        });
    }

    private void setupOtpInputs() {
        EditText[] otpFields = {otp1, otp2, otp3, otp4, otp5, otp6};
        
        for (int i = 0; i < otpFields.length; i++) {
            final int index = i;
            otpFields[i].addTextChangedListener(new TextWatcher() {
                @Override
                public void beforeTextChanged(CharSequence s, int start, int count, int after) {}

                @Override
                public void onTextChanged(CharSequence s, int start, int before, int count) {
                    if (s.length() == 1 && index < otpFields.length - 1) {
                        otpFields[index + 1].requestFocus();
                    }
                    tvOtpError.setText("");
                }

                @Override
                public void afterTextChanged(Editable s) {}
            });
            
            otpFields[i].setOnKeyListener((v, keyCode, event) -> {
                if (keyCode == KeyEvent.KEYCODE_DEL && event.getAction() == KeyEvent.ACTION_DOWN) {
                    if (otpFields[index].getText().toString().isEmpty() && index > 0) {
                        otpFields[index - 1].requestFocus();
                    }
                }
                return false;
            });
        }
    }

    private void startTimer() {
        countDownTimer = new CountDownTimer(timeLeftInMillis, 1000) {
            @Override
            public void onTick(long millisUntilFinished) {
                timeLeftInMillis = millisUntilFinished;
                updateTimerText();
            }

            @Override
            public void onFinish() {
                timerText.setText("OTP expired");
                resendText.setEnabled(true);
            }
        }.start();
    }

    private void updateTimerText() {
        int minutes = (int) (timeLeftInMillis / 1000) / 60;
        int seconds = (int) (timeLeftInMillis / 1000) % 60;
        String timeFormatted = String.format(Locale.getDefault(), "Resend code in %d:%02d", minutes, seconds);
        timerText.setText(timeFormatted);
    }

    private String getEnteredOtp() {
        return otp1.getText().toString() +
               otp2.getText().toString() +
               otp3.getText().toString() +
               otp4.getText().toString() +
               otp5.getText().toString() +
               otp6.getText().toString();
    }

    private void verifyOtp(String otp) {
        progressDialog.show();
        
        VerifyOtpRequest request = new VerifyOtpRequest(email, otp);
        
        Call<VerifyOtpResponse> call = apiService.verifyOtp(request);
        call.enqueue(new Callback<VerifyOtpResponse>() {
            @Override
            public void onResponse(@NonNull Call<VerifyOtpResponse> call, @NonNull Response<VerifyOtpResponse> response) {
                progressDialog.dismiss();
                
                if (response.isSuccessful() && response.body() != null) {
                    VerifyOtpResponse verifyResponse = response.body();
                    
                    if (verifyResponse.isSuccess() && verifyResponse.getData() != null) {
                        Toast.makeText(VerificationActivity.this, 
                                verifyResponse.getMessage(), 
                                Toast.LENGTH_SHORT).show();
                        
                        // Navigate to login screen
                        Intent intent = new Intent(VerificationActivity.this, LoginActivity.class);
                        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
                        intent.putExtra("verified_email", email);
                        startActivity(intent);
                        finish();
                    } else {
                        tvOtpError.setText(verifyResponse.getMessage());
                    }
                } else {
                    tvOtpError.setText("Invalid OTP. Please try again.");
                }
            }

            @Override
            public void onFailure(@NonNull Call<VerifyOtpResponse> call, @NonNull Throwable t) {
                progressDialog.dismiss();
                tvOtpError.setText("Network error: " + t.getMessage());
            }
        });
    }

    private void resendOtp() {
        progressDialog.setMessage("Resending OTP...");
        progressDialog.show();
        
        ResendOtpRequest request = new ResendOtpRequest(email);
        
        Call<ResendOtpResponse> call = apiService.resendOtp(request);
        call.enqueue(new Callback<ResendOtpResponse>() {
            @Override
            public void onResponse(@NonNull Call<ResendOtpResponse> call, @NonNull Response<ResendOtpResponse> response) {
                progressDialog.dismiss();
                
                if (response.isSuccessful() && response.body() != null) {
                    ResendOtpResponse resendResponse = response.body();
                    
                    if (resendResponse.isSuccess()) {
                        Toast.makeText(VerificationActivity.this, 
                                resendResponse.getMessage(), 
                                Toast.LENGTH_SHORT).show();
                        
                        // Reset timer
                        if (countDownTimer != null) {
                            countDownTimer.cancel();
                        }
                        timeLeftInMillis = 180000;
                        startTimer();
                        
                        // Clear OTP fields
                        otp1.setText("");
                        otp2.setText("");
                        otp3.setText("");
                        otp4.setText("");
                        otp5.setText("");
                        otp6.setText("");
                        otp1.requestFocus();
                        tvOtpError.setText("");
                    } else {
                        Toast.makeText(VerificationActivity.this, 
                                resendResponse.getMessage(), 
                                Toast.LENGTH_SHORT).show();
                    }
                } else {
                    Toast.makeText(VerificationActivity.this, 
                            "Failed to resend OTP", 
                            Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(@NonNull Call<ResendOtpResponse> call, @NonNull Throwable t) {
                progressDialog.dismiss();
                Toast.makeText(VerificationActivity.this, 
                        "Network error: " + t.getMessage(), 
                        Toast.LENGTH_SHORT).show();
            }
        });
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        if (countDownTimer != null) {
            countDownTimer.cancel();
        }
    }
}
