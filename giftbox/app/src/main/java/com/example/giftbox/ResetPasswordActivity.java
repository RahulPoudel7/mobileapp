package com.example.giftbox;

import android.app.ProgressDialog;
import android.content.Intent;
import android.os.Bundle;
import android.text.TextUtils;
import android.widget.ImageView;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;

import com.example.giftbox.api.ApiClient;
import com.example.giftbox.api.ApiService;
import com.example.giftbox.view.LoginActivity;
import com.google.android.material.button.MaterialButton;
import com.google.android.material.textfield.TextInputEditText;
import com.google.android.material.textfield.TextInputLayout;
import com.google.gson.JsonObject;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class ResetPasswordActivity extends AppCompatActivity {
    private TextInputLayout otpLayout, newPasswordLayout, confirmPasswordLayout;
    private TextInputEditText otpEditText, newPasswordEditText, confirmPasswordEditText;
    @SuppressWarnings("FieldCanBeLocal")
    private MaterialButton resetButton;
    @SuppressWarnings("FieldCanBeLocal")
    private ImageView backArrow;
    
    private ApiService apiService;
    private ProgressDialog progressDialog;
    private String email;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_reset_password);
        if (getSupportActionBar() != null) {
            getSupportActionBar().hide();
        }

        // Init API service
        apiService = ApiClient.getApiService();

        // Init progress dialog
        progressDialog = new ProgressDialog(this);
        progressDialog.setMessage("Resetting password...");
        progressDialog.setCancelable(false);

        // Get email from intent
        email = getIntent().getStringExtra("email");

        otpLayout = findViewById(R.id.otpLayout);
        otpEditText = findViewById(R.id.otpEditText);
        newPasswordLayout = findViewById(R.id.newPasswordLayout);
        confirmPasswordLayout = findViewById(R.id.confirmPasswordLayout);
        newPasswordEditText = findViewById(R.id.newPassword);
        confirmPasswordEditText = findViewById(R.id.confirmPassword);
        resetButton = findViewById(R.id.resetButton);
        backArrow = findViewById(R.id.backArrow);

        backArrow.setOnClickListener(v -> {
            Intent intent = new Intent(ResetPasswordActivity.this, LoginActivity.class);
            startActivity(intent);
            finish();
            overridePendingTransition(android.R.anim.slide_in_left, android.R.anim.slide_out_right);
        });

        resetButton.setOnClickListener(v -> {
            if (validateInputs()) {
                resetPassword();
            }
        });
    }

    private boolean validateInputs() {
        String otp = otpEditText.getText() != null
                ? otpEditText.getText().toString().trim() : "";
        String newPass = newPasswordEditText.getText() != null
                ? newPasswordEditText.getText().toString().trim() : "";
        String confirmPass = confirmPasswordEditText.getText() != null
                ? confirmPasswordEditText.getText().toString().trim() : "";

        // Clear previous errors
        if (otpLayout != null) otpLayout.setError(null);
        if (newPasswordLayout != null) newPasswordLayout.setError(null);
        if (confirmPasswordLayout != null) confirmPasswordLayout.setError(null);

        // Validate OTP
        if (TextUtils.isEmpty(otp)) {
            if (otpLayout != null) {
                otpLayout.setError("Please enter OTP");
                otpLayout.requestFocus();
            }
            return false;
        }
        if (otp.length() != 6 || !otp.matches("\\d+")) {
            if (otpLayout != null) {
                otpLayout.setError("OTP must be 6 digits");
                otpLayout.requestFocus();
            }
            return false;
        }

        // Validate passwords
        if (TextUtils.isEmpty(newPass)) {
            if (newPasswordLayout != null) {
                newPasswordLayout.setError("Please enter new password");
                newPasswordLayout.requestFocus();
            }
            return false;
        }
        if (newPass.length() < 8) {
            if (newPasswordLayout != null) {
                newPasswordLayout.setError("Password must be at least 8 characters");
                newPasswordLayout.requestFocus();
            }
            return false;
        }
        if (TextUtils.isEmpty(confirmPass)) {
            if (confirmPasswordLayout != null) {
                confirmPasswordLayout.setError("Please confirm your password");
                confirmPasswordLayout.requestFocus();
            }
            return false;
        }
        if (!newPass.equals(confirmPass)) {
            if (confirmPasswordLayout != null) {
                confirmPasswordLayout.setError("Passwords do not match");
                confirmPasswordLayout.requestFocus();
            }
            return false;
        }

        return true;
    }

    private void resetPassword() {
        progressDialog.show();

        String otp = otpEditText.getText().toString().trim();
        String newPass = newPasswordEditText.getText().toString().trim();

        // Create request body
        JsonObject resetBody = new JsonObject();
        resetBody.addProperty("email", email);
        resetBody.addProperty("otp", otp);
        resetBody.addProperty("password", newPass);

        Call<JsonObject> call = apiService.resetPassword(resetBody);
        call.enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(@NonNull Call<JsonObject> call, @NonNull Response<JsonObject> response) {
                progressDialog.dismiss();

                if (response.isSuccessful() && response.body() != null) {
                    try {
                        JsonObject responseBody = response.body();
                        String message = responseBody.has("message") ? 
                            responseBody.get("message").getAsString() : 
                            "Password reset successfully!";
                        
                        Toast.makeText(ResetPasswordActivity.this, message, Toast.LENGTH_LONG).show();

                        // Navigate back to login
                        Intent intent = new Intent(ResetPasswordActivity.this, LoginActivity.class);
                        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
                        startActivity(intent);
                        finish();
                    } catch (Exception e) {
                        Toast.makeText(ResetPasswordActivity.this, "Password reset successfully!", Toast.LENGTH_LONG).show();
                        Intent intent = new Intent(ResetPasswordActivity.this, LoginActivity.class);
                        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
                        startActivity(intent);
                        finish();
                    }
                } else {
                    Toast.makeText(ResetPasswordActivity.this, "Invalid OTP or error occurred", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(@NonNull Call<JsonObject> call, @NonNull Throwable t) {
                progressDialog.dismiss();
                Toast.makeText(ResetPasswordActivity.this, "Error: " + t.getMessage(), Toast.LENGTH_SHORT).show();
            }
        });
    }
}
