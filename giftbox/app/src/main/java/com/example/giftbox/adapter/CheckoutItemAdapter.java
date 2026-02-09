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

import java.util.List;

public class CheckoutItemAdapter extends RecyclerView.Adapter<CheckoutItemAdapter.CheckoutItemViewHolder> {

    private List<CartItem> items;
    private Context context;

    public CheckoutItemAdapter(List<CartItem> items, Context context) {
        this.items = items;
        this.context = context;
    }

    public void updateItems(List<CartItem> newItems) {
        this.items = newItems;
        notifyDataSetChanged();
    }

    @NonNull
    @Override
    public CheckoutItemViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(context).inflate(R.layout.item_checkout, parent, false);
        return new CheckoutItemViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull CheckoutItemViewHolder holder, int position) {
        CartItem item = items.get(position);
        holder.bind(item);
    }

    @Override
    public int getItemCount() {
        return items.size();
    }

    public class CheckoutItemViewHolder extends RecyclerView.ViewHolder {
        private ImageView ivItemImage;
        private TextView tvItemName;
        private TextView tvItemPrice;
        private TextView tvItemQuantity;
        private TextView tvItemTotal;

        public CheckoutItemViewHolder(@NonNull View itemView) {
            super(itemView);
            ivItemImage = itemView.findViewById(R.id.ivCheckoutItemImage);
            tvItemName = itemView.findViewById(R.id.tvCheckoutItemName);
            tvItemPrice = itemView.findViewById(R.id.tvCheckoutItemPrice);
            tvItemQuantity = itemView.findViewById(R.id.tvCheckoutItemQuantity);
            tvItemTotal = itemView.findViewById(R.id.tvCheckoutItemTotal);
        }

        public void bind(CartItem item) {
            // Load image
            if (item.getImageUrl() != null && !item.getImageUrl().isEmpty()) {
                Glide.with(context)
                        .load(item.getImageUrl())
                        .placeholder(R.drawable.baseline_image_24)
                        .error(R.drawable.baseline_image_24)
                        .into(ivItemImage);
            }

            // Set text
            tvItemName.setText(item.getName());
            tvItemPrice.setText(String.format("NPR %.0f", item.getPrice()));
            tvItemQuantity.setText(String.format("Qty: %d", item.getQuantity()));
            tvItemTotal.setText(String.format("NPR %.0f", item.getLineTotal()));
        }
    }
}
