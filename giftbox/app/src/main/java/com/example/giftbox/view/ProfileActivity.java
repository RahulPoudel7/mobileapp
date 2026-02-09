package com.example.giftbox.view;

import android.annotation.SuppressLint;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;

import com.example.giftbox.EditprofileActivity;
import com.example.giftbox.R;
import com.example.giftbox.api.ApiClient;
import com.example.giftbox.api.ApiService;
import com.example.giftbox.utils.SessionManager;
import com.google.gson.JsonObject;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class ProfileActivity extends AppCompatActivity {
    LinearLayout rowChangePassword;
    private SessionManager sessionManager;
    private ApiService apiService;
    
    // UI elements
    private TextView tvName;
    private TextView tvEmail;
    private EditText etFullName;
    private EditText etPhone;
    private ProgressBar progressBar;
    private LinearLayout contentLayout;

    @SuppressLint("SetTextI18n")
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (getSupportActionBar() != null) {
            getSupportActionBar().hide();
        }
        setContentView(R.layout.activity_profile);

        // Initialize SessionManager and ApiService
        sessionManager = new SessionManager(this);
        apiService = ApiClient.getApiService();

        // Initialize views
        initializeViews();
        
        // Set up click listeners
        setupClickListeners();
        
        // Load user profile data from API
        loadUserProfile();
    }

    private void initializeViews() {
        tvName = findViewById(R.id.tvName);
        tvEmail = findViewById(R.id.tvEmail);
        etFullName = findViewById(R.id.etFullName);
        etPhone = findViewById(R.id.etPhone);
        
        // Note: Add a ProgressBar to your layout with id "progressBar" if you want a loading indicator
        // progressBar = findViewById(R.id.progressBar);
    }

    private void setupClickListeners() {
        LinearLayout rowDeliveryAddress = findViewById(R.id.rowDeliveryAddress);
        rowDeliveryAddress.setOnClickListener(v -> {
            Intent intent = new Intent(ProfileActivity.this, DeliveryAddressActivity.class);
            startActivity(intent);
        });

        ImageView ivBack = findViewById(R.id.ivBack);
        ImageView ivEdit = findViewById(R.id.ivEdit);
        LinearLayout rowLogout = findViewById(R.id.rowLogout);
        LinearLayout rowDeleteAccount = findViewById(R.id.rowDeleteAccount);

        rowChangePassword = findViewById(R.id.rowChangePassword);
        rowChangePassword.setOnClickListener(v ->
                startActivity(new Intent(ProfileActivity.this, ChangepasswordActivity.class)));

        // Back to homepage
        ivBack.setOnClickListener(v -> {
            Intent intent = new Intent(ProfileActivity.this, HomeActivity.class);
            startActivity(intent);
            finish();
        });

        // Edit profile
        ivEdit.setOnClickListener(v -> {
            Intent intent = new Intent(ProfileActivity.this, EditprofileActivity.class);
            startActivity(intent);
        });

        // Logout row with confirmation dialog
        rowLogout.setOnClickListener(v -> showLogoutConfirmationDialog());
        
        // Delete account row with confirmation dialog
        rowDeleteAccount.setOnClickListener(v -> showDeleteAccountConfirmationDialog());
    }

    private void loadUserProfile() {
        String token = "Bearer " + sessionManager.getAuthToken();
        
        // Show loading state
        if (progressBar != null) {
            progressBar.setVisibility(View.VISIBLE);
        }
        
        Call<JsonObject> call = apiService.getProfile(token);
        call.enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(@NonNull Call<JsonObject> call, @NonNull Response<JsonObject> response) {
                if (progressBar != null) {
                    progressBar.setVisibility(View.GONE);
                }
                
                if (response.isSuccessful() && response.body() != null) {
                    JsonObject profileData = response.body();
                    updateUIWithProfileData(profileData);
                } else {
                    Toast.makeText(ProfileActivity.this, "Failed to load profile", Toast.LENGTH_SHORT).show();
                    // Set default values on failure
                    setDefaultValues();
                }
            }

            @Override
            public void onFailure(@NonNull Call<JsonObject> call, @NonNull Throwable t) {
                if (progressBar != null) {
                    progressBar.setVisibility(View.GONE);
                }
                Toast.makeText(ProfileActivity.this, "Error: " + t.getMessage(), Toast.LENGTH_SHORT).show();
                // Set default values on failure
                setDefaultValues();
            }
        });
    }

    @SuppressLint("SetTextI18n")
    private void updateUIWithProfileData(JsonObject profileData) {
        try {
            // Get user object from profile response
            JsonObject user = profileData.has("user") ? profileData.getAsJsonObject("user") : profileData;
            
            String name = user.has("name") ? user.get("name").getAsString() : "User";
            String email = user.has("email") ? user.get("email").getAsString() : "user@example.com";
            String phone = user.has("phone") && !user.get("phone").isJsonNull() 
                    ? user.get("phone").getAsString() 
                    : "";
            
            // Update header
            tvName.setText("Hi, " + name);
            
            // Update profile card
            tvEmail.setText(email);
            
            // Update editable fields
            etFullName.setText(name);
            if (!phone.isEmpty()) {
                etPhone.setText(phone);
            } else {
                etPhone.setText("");
                etPhone.setHint("Enter phone number");
            }
            
        } catch (Exception e) {
            e.printStackTrace();
            Toast.makeText(this, "Error parsing profile data", Toast.LENGTH_SHORT).show();
            setDefaultValues();
        }
    }

    @SuppressLint("SetTextI18n")
    private void setDefaultValues() {
        // Fallback to intent data or session data
        String username = getIntent().getStringExtra("username");
        if (username != null && !username.isEmpty()) {
            tvName.setText("Hi, " + username);
            etFullName.setText(username);
        } else {
            tvName.setText("Hi, User");
            etFullName.setText("User");
        }
        tvEmail.setText("user@example.com");
        etPhone.setText("");
        etPhone.setHint("Enter phone number");
    }

    @Override
    protected void onResume() {
        super.onResume();
        // Reload profile data when returning to this activity
        loadUserProfile();
    }

    private void showLogoutConfirmationDialog() {
        new AlertDialog.Builder(this)
                .setTitle("Logout")
                .setMessage("Do you really want to logout?")
                .setPositiveButton("Yes", (dialog, which) -> performLogout())
                .setNegativeButton("No", null)
                .show();
    }

    private void showDeleteAccountConfirmationDialog() {
        new AlertDialog.Builder(this)
                .setTitle("Delete Account")
                .setMessage("Are you sure you want to permanently delete your account?\n\nThis action cannot be undone. All your data will be lost.")
                .setPositiveButton("Delete", (dialog, which) -> performDeleteAccount())
                .setNegativeButton("Cancel", null)
                .show();
    }

    private void performLogout() {
        String token = "Bearer " + sessionManager.getAuthToken();
        
        Call<Void> call = apiService.logout(token);
        call.enqueue(new Callback<Void>() {
            @Override
            public void onResponse(@NonNull Call<Void> call, @NonNull Response<Void> response) {
                if (response.isSuccessful()) {
                    // Clear local session
                    sessionManager.logout();
                    
                    Toast.makeText(ProfileActivity.this, "Logged out successfully", Toast.LENGTH_SHORT).show();
                    
                    // Navigate to Welcome screen
                    Intent intent = new Intent(ProfileActivity.this, WelcomeActivity.class);
                    intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
                    startActivity(intent);
                    finish();
                } else {
                    Toast.makeText(ProfileActivity.this, "Logout failed", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(@NonNull Call<Void> call, @NonNull Throwable t) {
                // Even if API call fails, clear local session
                sessionManager.logout();
                
                Toast.makeText(ProfileActivity.this, "Logged out locally", Toast.LENGTH_SHORT).show();
                
                Intent intent = new Intent(ProfileActivity.this, WelcomeActivity.class);
                intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
                startActivity(intent);
                finish();
            }
        });
    }

    private void performDeleteAccount() {
        String token = "Bearer " + sessionManager.getAuthToken();
        
        Call<JsonObject> call = apiService.deleteAccount(token);
        call.enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(@NonNull Call<JsonObject> call, @NonNull Response<JsonObject> response) {
                if (response.isSuccessful()) {
                    // Clear local session
                    sessionManager.logout();
                    
                    Toast.makeText(ProfileActivity.this, "Account deleted successfully", Toast.LENGTH_SHORT).show();
                    
                    // Navigate to Welcome screen
                    Intent intent = new Intent(ProfileActivity.this, WelcomeActivity.class);
                    intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
                    startActivity(intent);
                    finish();
                } else {
                    Toast.makeText(ProfileActivity.this, "Failed to delete account", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(@NonNull Call<JsonObject> call, @NonNull Throwable t) {
                Toast.makeText(ProfileActivity.this, "Error: " + t.getMessage(), Toast.LENGTH_SHORT).show();
            }
        });
    }
}
