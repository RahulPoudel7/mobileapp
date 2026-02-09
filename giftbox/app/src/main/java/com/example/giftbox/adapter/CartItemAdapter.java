package com.example.giftbox.adapter;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.bumptech.glide.Glide;
import com.example.giftbox.R;
import com.example.giftbox.model.CartItem;
import com.google.android.material.button.MaterialButton;

import java.util.List;

public class CartItemAdapter extends RecyclerView.Adapter<CartItemAdapter.CartItemViewHolder> {

    private List<CartItem> cartItems;
    private Context context;
    private OnCartItemListener listener;

    public interface OnCartItemListener {
        void onQuantityChanged(CartItem item);
        void onItemRemoved(CartItem item);
    }

    public CartItemAdapter(List<CartItem> cartItems, Context context, OnCartItemListener listener) {
        this.cartItems = cartItems;
        this.context = context;
        this.listener = listener;
    }

    public void updateItems(List<CartItem> newItems) {
        this.cartItems = newItems;
    }

    @NonNull
    @Override
    public CartItemViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(context).inflate(R.layout.item_cart, parent, false);
        return new CartItemViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull CartItemViewHolder holder, int position) {
        CartItem item = cartItems.get(position);
        holder.bind(item);
    }

    @Override
    public int getItemCount() {
        return cartItems.size();
    }

    public class CartItemViewHolder extends RecyclerView.ViewHolder {
        private ImageView ivGiftImage;
        private TextView tvGiftName;
        private TextView tvGiftPrice;
        private TextView tvQuantity;
        private ImageView ivIncrease;
        private ImageView ivDecrease;
        private MaterialButton btnRemove;
        private TextView tvLineTotal;

        public CartItemViewHolder(@NonNull View itemView) {
            super(itemView);
            ivGiftImage = itemView.findViewById(R.id.ivCartItemImage);
            tvGiftName = itemView.findViewById(R.id.tvCartItemName);
            tvGiftPrice = itemView.findViewById(R.id.tvCartItemPrice);
            tvQuantity = itemView.findViewById(R.id.tvCartItemQuantity);
            ivIncrease = itemView.findViewById(R.id.ivCartItemIncrease);
            ivDecrease = itemView.findViewById(R.id.ivCartItemDecrease);
            btnRemove = itemView.findViewById(R.id.btnCartItemRemove);
            tvLineTotal = itemView.findViewById(R.id.tvCartItemTotal);
        }

        public void bind(CartItem item) {
            // Load image
            if (item.getImageUrl() != null && !item.getImageUrl().isEmpty()) {
                Glide.with(context)
                        .load(item.getImageUrl())
                        .placeholder(R.drawable.baseline_image_24)
                        .error(R.drawable.baseline_image_24)
                        .into(ivGiftImage);
            }

            // Set text
            tvGiftName.setText(item.getName());
            tvGiftPrice.setText(String.format("NPR %.0f", item.getPrice()));
            tvQuantity.setText(String.valueOf(item.getQuantity()));
            tvLineTotal.setText(String.format("NPR %.0f", item.getLineTotal()));

            // Increase quantity
            ivIncrease.setOnClickListener(v -> {
                item.setQuantity(item.getQuantity() + 1);
                tvQuantity.setText(String.valueOf(item.getQuantity()));
                tvLineTotal.setText(String.format("NPR %.0f", item.getLineTotal()));
                listener.onQuantityChanged(item);
            });

            // Decrease quantity
            ivDecrease.setOnClickListener(v -> {
                if (item.getQuantity() > 1) {
                    item.setQuantity(item.getQuantity() - 1);
                    tvQuantity.setText(String.valueOf(item.getQuantity()));
                    tvLineTotal.setText(String.format("NPR %.0f", item.getLineTotal()));
                    listener.onQuantityChanged(item);
                }
            });

            // Remove item
            btnRemove.setOnClickListener(v -> {
                listener.onItemRemoved(item);
            });
        }
    }
}
