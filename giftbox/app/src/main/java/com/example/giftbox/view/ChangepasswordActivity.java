package com.example.giftbox.view;


import android.os.Bundle;
import android.widget.ImageView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

import com.example.giftbox.R;
import com.example.giftbox.api.ApiClient;
import com.example.giftbox.api.ApiService;
import com.example.giftbox.utils.SessionManager;
import com.google.android.material.button.MaterialButton;
import com.google.android.material.textfield.TextInputEditText;
import com.google.android.material.textfield.TextInputLayout;
import com.google.gson.JsonObject;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class ChangepasswordActivity extends AppCompatActivity {

    private TextInputLayout tilOldPassword, tilNewPassword, tilConfirmPassword;
    private TextInputEditText etOldPassword, etNewPassword, etConfirmPassword;
    private MaterialButton btnChangePassword;
    private ApiService apiService;
    private SessionManager sessionManager;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_changepassword);

        if (getSupportActionBar() != null) {
            getSupportActionBar().hide();
        }

        apiService = ApiClient.getApiService();
        sessionManager = new SessionManager(this);

        tilOldPassword = findViewById(R.id.tilOldPassword);
        tilNewPassword = findViewById(R.id.tilNewPassword);
        tilConfirmPassword = findViewById(R.id.tilConfirmPassword);

        etOldPassword = findViewById(R.id.etOldPassword);
        etNewPassword = findViewById(R.id.etNewPassword);
        etConfirmPassword = findViewById(R.id.etConfirmPassword);

        btnChangePassword = findViewById(R.id.btnChangePassword);
        ImageView ivBack = findViewById(R.id.ivBack);

        ivBack.setOnClickListener(v -> onBackPressed());

        btnChangePassword.setOnClickListener(v -> handlePasswordChange());
    }

    private void handlePasswordChange() {
        String oldPassword = etOldPassword.getText() != null ? etOldPassword.getText().toString().trim() : "";
        String newPassword = etNewPassword.getText() != null ? etNewPassword.getText().toString().trim() : "";
        String confirmPassword = etConfirmPassword.getText() != null ? etConfirmPassword.getText().toString().trim() : "";

        // Clear errors
        tilOldPassword.setError(null);
        tilNewPassword.setError(null);
        tilConfirmPassword.setError(null);

        // Validate inputs
        if (oldPassword.isEmpty()) {
            tilOldPassword.setError("Enter old password");
            return;
        }
        if (newPassword.isEmpty()) {
            tilNewPassword.setError("Enter new password");
            return;
        }
        if (newPassword.length() < 6) {
            tilNewPassword.setError("Password must be at least 6 characters");
            return;
        }
        if (!newPassword.equals(confirmPassword)) {
            tilConfirmPassword.setError("Passwords do not match");
            return;
        }

        // Create request body
        JsonObject requestBody = new JsonObject();
        requestBody.addProperty("old_password", oldPassword);
        requestBody.addProperty("password", newPassword);
        requestBody.addProperty("password_confirmation", confirmPassword);

        // Get bearer token
        String token = sessionManager.getAuthToken();
        if (token == null || token.isEmpty()) {
            Toast.makeText(this, "Authentication failed. Please login again.", Toast.LENGTH_SHORT).show();
            return;
        }

        // Call API
        apiService.changePassword("Bearer " + token, requestBody).enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (response.isSuccessful() && response.body() != null) {
                    Toast.makeText(ChangepasswordActivity.this, "Password changed successfully", Toast.LENGTH_SHORT).show();
                    finish();
                } else {
                    try {
                        String errorBody = response.errorBody() != null ? response.errorBody().string() : "Unknown error";
                        if (errorBody.contains("old password")) {
                            tilOldPassword.setError("Old password is incorrect");
                        } else if (errorBody.contains("validation")) {
                            Toast.makeText(ChangepasswordActivity.this, "Validation error. Please check your input.", Toast.LENGTH_SHORT).show();
                        } else {
                            Toast.makeText(ChangepasswordActivity.this, "Failed to change password", Toast.LENGTH_SHORT).show();
                        }
                    } catch (Exception e) {
                        Toast.makeText(ChangepasswordActivity.this, "Error: " + e.getMessage(), Toast.LENGTH_SHORT).show();
                    }
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                Toast.makeText(ChangepasswordActivity.this, "Network error: " + t.getMessage(), Toast.LENGTH_SHORT).show();
            }
        });
    }
}
