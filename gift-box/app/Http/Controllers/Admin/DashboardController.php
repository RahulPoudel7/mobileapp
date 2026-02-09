<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\Gift;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // === OVERVIEW STATISTICS ===
        $totalOrders = Order::count();
        $totalUsers = User::where('is_admin', false)->count();
        $totalGifts = Gift::count();
        $totalRevenue = Order::where('payment_status', 'paid')->sum('total_amount');
        
        // === ORDER STATISTICS ===
        $pendingOrders = Order::where('status', 'pending')->count();
        $confirmedOrders = Order::where('status', 'confirmed')->count();
        $deliveredOrders = Order::where('status', 'delivered')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();
        
        // === PAYMENT STATISTICS ===
        $paidOrders = Order::where('payment_status', 'paid')->count();
        $unpaidOrders = Order::where('payment_status', 'unpaid')->count();
        $esewaOrders = Order::where('payment_method', 'esewa')->count();
        $codOrders = Order::where('payment_method', 'cod')->count();
        
        // === REVENUE ANALYTICS ===
        $todayRevenue = Order::where('payment_status', 'paid')
            ->whereDate('created_at', Carbon::today())
            ->sum('total_amount');
            
        $weekRevenue = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->sum('total_amount');
            
        $monthRevenue = Order::where('payment_status', 'paid')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total_amount');
        
        // === TOP SELLING GIFTS ===
        $topGifts = DB::table('carts_items')
            ->join('gifts', 'carts_items.gift_id', '=', 'gifts.id')
            ->join('orders', 'carts_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'paid')
            ->select(
                'gifts.id',
                'gifts.name',
                DB::raw('SUM(carts_items.quantity) as total_sold'),
                DB::raw('SUM(carts_items.quantity * carts_items.price) as total_revenue')
            )
            ->groupBy('gifts.id', 'gifts.name')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();
        
        // === RECENT ORDERS ===
        $recentOrders = Order::with(['user', 'items.gift'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // === MONTHLY REVENUE TREND (Last 6 months) ===
        $monthlyRevenue = Order::where('payment_status', 'paid')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as order_count')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();
        
        // === DAILY ORDERS TREND (Last 7 days) ===
        $dailyOrders = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(CASE WHEN payment_status = "paid" THEN total_amount ELSE 0 END) as revenue')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        
        // === AVERAGE ORDER VALUE ===
        $avgOrderValue = Order::where('payment_status', 'paid')
            ->avg('total_amount');
        
        // === ADDITIONAL REVENUE BREAKDOWN ===
        $totalDeliveryCharges = Order::where('payment_status', 'paid')->sum('delivery_charge');
        $totalGiftWrapping = Order::where('payment_status', 'paid')->sum('gift_wrapping_fee');
        $totalPersonalNotes = Order::where('payment_status', 'paid')->sum('personal_note_fee');
        
        // === CUSTOMER ANALYTICS ===
        $newUsersToday = User::whereDate('created_at', Carbon::today())->count();
        $newUsersWeek = User::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        $newUsersMonth = User::whereMonth('created_at', Carbon::now()->month)->count();
        
        // === TOP CUSTOMERS (by total spent) ===
        $topCustomers = Order::with('user')
            ->where('payment_status', 'paid')
            ->select('user_id', DB::raw('SUM(total_amount) as total_spent'), DB::raw('COUNT(*) as order_count'))
            ->groupBy('user_id')
            ->orderBy('total_spent', 'desc')
            ->limit(5)
            ->get();
        
        return view('admin.dashboard', compact(
            'totalOrders',
            'totalUsers',
            'totalGifts',
            'totalRevenue',
            'pendingOrders',
            'confirmedOrders',
            'deliveredOrders',
            'cancelledOrders',
            'paidOrders',
            'unpaidOrders',
            'esewaOrders',
            'codOrders',
            'todayRevenue',
            'weekRevenue',
            'monthRevenue',
            'topGifts',
            'recentOrders',
            'monthlyRevenue',
            'dailyOrders',
            'avgOrderValue',
            'totalDeliveryCharges',
            'totalGiftWrapping',
            'totalPersonalNotes',
            'newUsersToday',
            'newUsersWeek',
            'newUsersMonth',
            'topCustomers'
        ));
    }
}
