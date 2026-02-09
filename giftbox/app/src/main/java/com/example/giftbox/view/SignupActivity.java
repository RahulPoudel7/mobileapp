package com.example.giftbox.view;

import android.app.ProgressDialog;
import android.content.Intent;
import android.os.Bundle;
import android.widget.TextView;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import com.example.giftbox.R;
import com.example.giftbox.VerificationActivity;
import com.example.giftbox.api.ApiClient;
import com.example.giftbox.api.ApiService;
import com.example.giftbox.controllers.SignupController;
import com.example.giftbox.models.RegisterRequest;
import com.example.giftbox.models.RegisterResponse;
import com.google.android.material.button.MaterialButton;
import com.google.android.material.textfield.TextInputEditText;
import com.google.android.material.textfield.TextInputLayout;

import java.util.Objects;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class SignupActivity extends AppCompatActivity {

    private TextInputLayout tilName, tilPhone, tilEmail, tilPassword, tilConfirmPassword;
    private TextInputEditText etName, etPhone, etEmail, etPassword, etConfirmPassword;
    @SuppressWarnings("FieldCanBeLocal")
    private MaterialButton btnSignup;

    // Controller instance
    private SignupController signupController;
    
    // API service
    private ApiService apiService;
    
    // Progress dialog
    private ProgressDialog progressDialog;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (getSupportActionBar() != null) {
            getSupportActionBar().hide();
        }
        setContentView(R.layout.activity_signup);

        // Fixed IDs - now matches your XML
        tilName = findViewById(R.id.tilName);
        tilPhone = findViewById(R.id.tilPhone);
        tilEmail = findViewById(R.id.tilEmail);
        tilPassword = findViewById(R.id.tilPassword);
        tilConfirmPassword = findViewById(R.id.tilConfirmPassword);

        etName = findViewById(R.id.etName);
        etPhone = findViewById(R.id.etPhone);
        etEmail = findViewById(R.id.etEmail);
        etPassword = findViewById(R.id.etPassword);
        etConfirmPassword = findViewById(R.id.etConfirmPassword);

        btnSignup = findViewById(R.id.btnSignup);

        // Error icons & colors (optional but you already used them)
        // NEW: use your own drawable
        tilName.setErrorIconDrawable(R.drawable.ic_error_red_24dp);

        tilPhone.setErrorIconDrawable(R.drawable.ic_error_red_24dp);
        tilEmail.setErrorIconDrawable(R.drawable.ic_error_red_24dp);
        tilPassword.setErrorIconDrawable(R.drawable.ic_error_red_24dp);
        tilConfirmPassword.setErrorIconDrawable(R.drawable.ic_error_red_24dp);

        tilName.setErrorTextColor(
                androidx.core.content.ContextCompat.getColorStateList(this, android.R.color.holo_red_dark));
        tilPhone.setErrorTextColor(
                androidx.core.content.ContextCompat.getColorStateList(this, android.R.color.holo_red_dark));
        tilEmail.setErrorTextColor(
                androidx.core.content.ContextCompat.getColorStateList(this, android.R.color.holo_red_dark));
        tilPassword.setErrorTextColor(
                androidx.core.content.ContextCompat.getColorStateList(this, android.R.color.holo_red_dark));
        tilConfirmPassword.setErrorTextColor(
                androidx.core.content.ContextCompat.getColorStateList(this, android.R.color.holo_red_dark));

        // Init controller
        signupController = new SignupController();
        
        // Init API service
        apiService = ApiClient.getApiService();
        
        // Init progress dialog
        progressDialog = new ProgressDialog(this);
        progressDialog.setMessage("Creating your account...");
        progressDialog.setCancelable(false);

        // Sign Up Button
        btnSignup.setOnClickListener(v -> handleSignup());

        // "Already have an account? Login" link
        TextView tvLoginLink = findViewById(R.id.tvLoginLink);
        tvLoginLink.setOnClickListener(v -> {
            Intent intent = new Intent(SignupActivity.this, LoginActivity.class);
            startActivity(intent);
            finish();
        });

        // Window insets padding (from your original code)
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, 0, systemBars.right, systemBars.bottom);
            return insets;
        });
    }

    private void handleSignup() {
        String name = Objects.requireNonNull(etName.getText()).toString().trim();
        String phone = Objects.requireNonNull(etPhone.getText()).toString().trim();
        String email = Objects.requireNonNull(etEmail.getText()).toString().trim();
        String password = Objects.requireNonNull(etPassword.getText()).toString().trim();
        String confirm = Objects.requireNonNull(etConfirmPassword.getText()).toString().trim();

        // Clear old errors
        tilName.setError(null);
        tilPhone.setError(null);
        tilEmail.setError(null);
        tilPassword.setError(null);
        tilConfirmPassword.setError(null);

        // Ask controller to validate
        SignupController.SignupError error =
                signupController.validateSignup(name, phone, email, password, confirm);

        switch (error) {
            case EMPTY_NAME:
                tilName.setError("Name cannot be empty");
                Toast.makeText(this, "Name cannot be empty", Toast.LENGTH_SHORT).show();
                tilName.requestFocus();
                break;

            case EMPTY_PHONE:
                tilPhone.setError("Phone number cannot be empty");
                Toast.makeText(this, "Phone number cannot be empty", Toast.LENGTH_SHORT).show();
                tilPhone.requestFocus();
                break;

            case INVALID_PHONE:
                tilPhone.setError("Please enter a valid phone number");
                Toast.makeText(this, "Please enter a valid phone number", Toast.LENGTH_SHORT).show();
                tilPhone.requestFocus();
                break;

            case EMPTY_EMAIL:
                tilEmail.setError("Email cannot be empty");
                Toast.makeText(this, "Email cannot be empty", Toast.LENGTH_SHORT).show();
                tilEmail.requestFocus();
                break;

            case INVALID_EMAIL:
                tilEmail.setError("Please enter a valid email");
                Toast.makeText(this, "Please enter a valid email", Toast.LENGTH_SHORT).show();
                tilEmail.requestFocus();
                break;

            case EMPTY_PASSWORD:
                tilPassword.setError("Password cannot be empty");
                Toast.makeText(this, "Password cannot be empty", Toast.LENGTH_SHORT).show();
                tilPassword.requestFocus();
                break;

            case SHORT_PASSWORD:
                tilPassword.setError("Password must be at least 6 characters");
                Toast.makeText(this, "Password must be at least 6 characters", Toast.LENGTH_SHORT).show();
                tilPassword.requestFocus();
                break;

            case EMPTY_CONFIRM_PASSWORD:
                tilConfirmPassword.setError("Please confirm your password");
                Toast.makeText(this, "Please confirm your password", Toast.LENGTH_SHORT).show();
                tilConfirmPassword.requestFocus();
                break;

            case PASSWORD_MISMATCH:
                tilConfirmPassword.setError("Passwords do not match");
                Toast.makeText(this, "Passwords do not match", Toast.LENGTH_SHORT).show();
                tilConfirmPassword.requestFocus();
                break;

            case NONE:
                // All validations passed, proceed with API call
                registerUser(name, email, password, phone);
                break;
        }
    }
    
    private void registerUser(String name, String email, String password, String phone) {
        // Show progress
        progressDialog.show();
        
        // Create register request
        RegisterRequest registerRequest = new RegisterRequest(name, email, password, phone);
        
        // Make API call
        Call<RegisterResponse> call = apiService.register(registerRequest);
        call.enqueue(new Callback<RegisterResponse>() {
            @Override
            public void onResponse(@NonNull Call<RegisterResponse> call, @NonNull Response<RegisterResponse> response) {
                progressDialog.dismiss();
                
                if (response.isSuccessful() && response.body() != null) {
                    RegisterResponse registerResponse = response.body();
                    
                    if (registerResponse.isSuccess()) {
                        // Show success message
                        Toast.makeText(SignupActivity.this, 
                                registerResponse.getMessage(), 
                                Toast.LENGTH_LONG).show();
                        
                        // Navigate to OTP verification screen
                        Intent intent = new Intent(SignupActivity.this, VerificationActivity.class);
                        intent.putExtra("email", email);
                        intent.putExtra("from_signup", true);
                        startActivity(intent);
                        overridePendingTransition(android.R.anim.slide_in_left, android.R.anim.slide_out_right);
                        finish();
                    } else {
                        Toast.makeText(SignupActivity.this, 
                                registerResponse.getMessage(), 
                                Toast.LENGTH_SHORT).show();
                    }
                } else {
                    // Handle error response
                    Toast.makeText(SignupActivity.this, 
                            "Registration failed. Email may already be registered.", 
                            Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(@NonNull Call<RegisterResponse> call, @NonNull Throwable t) {
                progressDialog.dismiss();
                Toast.makeText(SignupActivity.this, 
                        "Network error: " + t.getMessage(), 
                        Toast.LENGTH_LONG).show();
            }
        });
    }
}
