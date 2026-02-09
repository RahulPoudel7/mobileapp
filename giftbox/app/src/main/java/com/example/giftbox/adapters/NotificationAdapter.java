package com.example.giftbox.adapters;

import android.content.Context;
import android.graphics.Typeface;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.cardview.widget.CardView;
import androidx.recyclerview.widget.RecyclerView;

import com.example.giftbox.R;
import com.example.giftbox.models.NotificationItem;

import java.util.ArrayList;
import java.util.List;

public class NotificationAdapter extends RecyclerView.Adapter<NotificationAdapter.NotificationViewHolder> {

    private List<NotificationItem> notifications;
    private Context context;
    private OnNotificationClickListener clickListener;
    private OnNotificationDeleteListener deleteListener;

    public interface OnNotificationClickListener {
        void onNotificationClick(NotificationItem notification);
    }

    public interface OnNotificationDeleteListener {
        void onNotificationDelete(NotificationItem notification, int position);
    }

    public NotificationAdapter(Context context, OnNotificationClickListener clickListener, OnNotificationDeleteListener deleteListener) {
        this.notifications = new ArrayList<>();
        this.context = context;
        this.clickListener = clickListener;
        this.deleteListener = deleteListener;
    }

    @NonNull
    @Override
    public NotificationViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_notification, parent, false);
        return new NotificationViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull NotificationViewHolder holder, int position) {
        NotificationItem notification = notifications.get(position);

        holder.tvTitle.setText(notification.getTitle());
        holder.tvMessage.setText(notification.getMessage());
        holder.tvTime.setText(notification.getTimeAgo());

        // Set icon based on type
        int iconRes = getIconForType(notification.getType());
        holder.ivIcon.setImageResource(iconRes);

        // Style based on read status
        if (notification.isRead()) {
            holder.tvTitle.setTypeface(null, Typeface.NORMAL);
            holder.tvMessage.setTypeface(null, Typeface.NORMAL);
            holder.cardView.setCardBackgroundColor(context.getResources().getColor(android.R.color.white));
        } else {
            holder.tvTitle.setTypeface(null, Typeface.BOLD);
            holder.tvMessage.setTypeface(null, Typeface.NORMAL);
            holder.cardView.setCardBackgroundColor(context.getResources().getColor(R.color.unread_notification_bg));
        }

        // Click listener
        holder.itemView.setOnClickListener(v -> {
            if (clickListener != null) {
                clickListener.onNotificationClick(notification);
            }
        });

        // Delete listener
        holder.ivDelete.setOnClickListener(v -> {
            if (deleteListener != null) {
                deleteListener.onNotificationDelete(notification, position);
            }
        });
    }

    @Override
    public int getItemCount() {
        return notifications.size();
    }

    public void updateNotifications(List<NotificationItem> newNotifications) {
        this.notifications.clear();
        this.notifications.addAll(newNotifications);
        notifyDataSetChanged();
    }

    public void removeNotification(int position) {
        if (position >= 0 && position < notifications.size()) {
            notifications.remove(position);
            notifyItemRemoved(position);
        }
    }

    public void markAsRead(int position) {
        if (position >= 0 && position < notifications.size()) {
            notifications.get(position).setRead(true);
            notifyItemChanged(position);
        }
    }

    private int getIconForType(String type) {
        switch (type.toLowerCase()) {
            case "order":
                return R.drawable.ic_order_notification;
            case "payment":
                return R.drawable.ic_payment_notification;
            case "promo":
                return R.drawable.ic_promo_notification;
            case "delivery":
                return R.drawable.ic_delivery_notification;
            case "account":
                return R.drawable.ic_account_notification;
            default:
                return R.drawable.ic_notification_bell;
        }
    }

    static class NotificationViewHolder extends RecyclerView.ViewHolder {
        CardView cardView;
        ImageView ivIcon;
        TextView tvTitle;
        TextView tvMessage;
        TextView tvTime;
        ImageView ivDelete;

        public NotificationViewHolder(@NonNull View itemView) {
            super(itemView);
            cardView = itemView.findViewById(R.id.cardView);
            ivIcon = itemView.findViewById(R.id.ivIcon);
            tvTitle = itemView.findViewById(R.id.tvTitle);
            tvMessage = itemView.findViewById(R.id.tvMessage);
            tvTime = itemView.findViewById(R.id.tvTime);
            ivDelete = itemView.findViewById(R.id.ivDelete);
        }
    }
}
