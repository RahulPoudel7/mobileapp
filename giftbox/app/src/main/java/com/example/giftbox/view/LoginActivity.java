package com.example.giftbox.view;

import android.app.ProgressDialog;
import android.content.Intent;
import android.os.Bundle;
import android.widget.CheckBox;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;

import com.example.giftbox.R;
import com.example.giftbox.api.ApiClient;
import com.example.giftbox.api.ApiService;
import com.example.giftbox.controllers.LoginController;
import com.example.giftbox.forgotpassword;
import com.example.giftbox.models.LoginRequest;
import com.example.giftbox.models.LoginResponse;
import com.example.giftbox.utils.SessionManager;
import com.google.android.material.button.MaterialButton;
import com.google.android.material.textfield.TextInputEditText;
import com.google.android.material.textfield.TextInputLayout;

import java.util.Objects;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class LoginActivity extends AppCompatActivity {

    private TextInputLayout tilEmail, tilPassword;
    private TextInputEditText etEmail, etPassword;
    @SuppressWarnings("FieldCanBeLocal")
    private MaterialButton btnLogin;
    private CheckBox cbRememberMe;

    // Controller instance
    private LoginController loginController;
    
    // API service
    private ApiService apiService;
    
    // Session manager
    private SessionManager sessionManager;
    
    // Progress dialog
    private ProgressDialog progressDialog;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (getSupportActionBar() != null) {
            getSupportActionBar().hide();
        }
        setContentView(R.layout.activity_login);

        // Init views
        tilEmail = findViewById(R.id.tilEmail);
        tilPassword = findViewById(R.id.tilPassword);
        etEmail = findViewById(R.id.etEmail);
        etPassword = findViewById(R.id.etPassword);
        btnLogin = findViewById(R.id.btnLogin);
        cbRememberMe = findViewById(R.id.RememberMe);

        // Init controller
        loginController = new LoginController();
        
        // Init API service
        apiService = ApiClient.getApiService();
        
        // Init session manager
        sessionManager = new SessionManager(this);
        
        // Init progress dialog
        progressDialog = new ProgressDialog(this);
        progressDialog.setMessage("Logging in...");
        progressDialog.setCancelable(false);
        
        // Check if coming from verification screen
        String verifiedEmail = getIntent().getStringExtra("verified_email");
        if (verifiedEmail != null && !verifiedEmail.isEmpty()) {
            etEmail.setText(verifiedEmail);
            Toast.makeText(this, "Email verified! Please login to continue.", Toast.LENGTH_LONG).show();
        }

        // Sign Up
        findViewById(R.id.tvSignup).setOnClickListener(v -> {
            startActivity(new Intent(LoginActivity.this, SignupActivity.class));
            overridePendingTransition(android.R.anim.slide_in_left, android.R.anim.slide_out_right);
        });

        // Forgot Password
        findViewById(R.id.forgotPassword).setOnClickListener(v -> {
            startActivity(new Intent(LoginActivity.this, forgotpassword.class));
            overridePendingTransition(android.R.anim.slide_in_left, android.R.anim.slide_out_right);
        });

        // Login Button
        btnLogin.setOnClickListener(v -> handleLogin());
    }

    private void handleLogin() {
        String email = Objects.requireNonNull(etEmail.getText()).toString().trim();
        String password = Objects.requireNonNull(etPassword.getText()).toString().trim();

        // Clear previous errors
        tilEmail.setError(null);
        tilPassword.setError(null);

        // Ask controller to validate
        LoginController.LoginError error = loginController.validateCredentials(email, password);

        switch (error) {
            case EMPTY_EMAIL:
                tilEmail.setError("Email cannot be empty");
                Toast.makeText(this, "Email cannot be empty", Toast.LENGTH_SHORT).show();
                etEmail.requestFocus();
                break;

            case INVALID_EMAIL:
                tilEmail.setError("Please enter a valid email address");
                Toast.makeText(this, "Please enter a valid email address", Toast.LENGTH_SHORT).show();
                etEmail.requestFocus();
                break;

            case EMPTY_PASSWORD:
                tilPassword.setError("Password cannot be empty");
                Toast.makeText(this, "Password cannot be empty", Toast.LENGTH_SHORT).show();
                etPassword.requestFocus();
                break;

            case SHORT_PASSWORD:
                tilPassword.setError("Password must be at least 6 characters");
                Toast.makeText(this, "Password must be at least 6 characters", Toast.LENGTH_SHORT).show();
                etPassword.requestFocus();
                break;

            case NONE:
                // Validation passed, proceed with API call
                loginUser(email, password);
                break;
        }
    }
    
    private void loginUser(String email, String password) {
        // Show progress
        progressDialog.show();
        
        // Create login request
        LoginRequest loginRequest = new LoginRequest(email, password);
        
        // Make API call
        Call<LoginResponse> call = apiService.login(loginRequest);
        call.enqueue(new Callback<LoginResponse>() {
            @Override
            public void onResponse(@NonNull Call<LoginResponse> call, @NonNull Response<LoginResponse> response) {
                progressDialog.dismiss();
                
                if (response.isSuccessful() && response.body() != null) {
                    LoginResponse loginResponse = response.body();
                    
                    // Save user data and token
                    sessionManager.saveAuthToken(loginResponse.getToken(), cbRememberMe.isChecked());
                    sessionManager.saveUserData(
                            loginResponse.getUser().getId(),
                            loginResponse.getUser().getName(),
                            loginResponse.getUser().getEmail(),
                            loginResponse.getUser().getPhone() != null ? loginResponse.getUser().getPhone() : ""
                    );
                    sessionManager.setRememberMe(cbRememberMe.isChecked());
                    
                    // Show success message
                    Toast.makeText(LoginActivity.this, "Login successful!", Toast.LENGTH_SHORT).show();
                    
                    // Navigate to homepage
                    Intent intent = new Intent(LoginActivity.this, HomeActivity.class);
                    intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
                    startActivity(intent);
                    overridePendingTransition(android.R.anim.slide_in_left, android.R.anim.slide_out_right);
                    finish();
                    finish();
                } else {
                    // Handle error response
                    Toast.makeText(LoginActivity.this, "Invalid credentials. Please try again.", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(@NonNull Call<LoginResponse> call, @NonNull Throwable t) {
                progressDialog.dismiss();
                Toast.makeText(LoginActivity.this, "Network error: " + t.getMessage(), Toast.LENGTH_LONG).show();
            }
        });
    }
}
