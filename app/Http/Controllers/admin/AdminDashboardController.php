<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Complaint;
use App\Models\RegProvinces;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Get today's date
        $today = now()->format('Y-m-d');
        
        // Get statistics
        $stats = [
            'new_orders_today' => Transaction::whereDate('order_date', $today)->count(),
            'total_products' => Product::count(),
            'total_transactions' => Transaction::count(),
            'total_complaints' => Complaint::count(),
            'total_articles' => \App\Models\Article::count(),
            'orders_need_payment_confirmation' => Transaction::where('order_status', 'menunggu_konfirmasi_pembayaran')->count(),
            'completed_orders' => Transaction::where('order_status', 'selesai')->count(),
            'total_revenue' => Transaction::where('order_status', '!=', 'dibatalkan')->sum('total_price'),
        ];
        
        return view('admin.dashboard', compact('stats'));
    }
    
        public function productDistribution(Request $request)
    {
        // Ambil data untuk filter
        $allProvinces = DB::table('reg_provinces')
            ->select('id as province_id', 'name as province_name')
            ->orderBy('name')
            ->get()
            ->keyBy('province_id');

        $allPlantTypes = DB::table('plant_types')
            ->select('plant_type_id', 'plant_type_name')
            ->orderBy('plant_type_name')
            ->get();

        // Mengambil semua produk untuk filter
        $allProducts = DB::table('products')
            ->select('product_id', 'product_name', 'plant_type_id')
            ->orderBy('product_name')
            ->get();

        // Filter parameters
        $selectedProvince = $request->get('province_id');
        $selectedPlantType = $request->get('plant_type_id');
        $selectedProduct = $request->get('product_id');

        // Base query untuk province data
        $provinceQuery = DB::table('transactions as t')
            ->join('transaction_items as ti', 't.transaction_id', '=', 'ti.transaction_id')
            ->join('products as p', 'ti.product_id', '=', 'p.product_id')
            ->join('plant_types as pt', 'p.plant_type_id', '=', 'pt.plant_type_id')
            ->join('reg_provinces as rp', 't.province_id', '=', 'rp.id')
            ->whereNotNull('t.province_id')
            ->where('t.order_status', 'selesai');

        // Apply filters
        if ($selectedProvince) {
            $provinceQuery->where('t.province_id', $selectedProvince);
        }
        if ($selectedPlantType) {
            $provinceQuery->where('p.plant_type_id', $selectedPlantType);
        }
        if ($selectedProduct) {
            $provinceQuery->where('p.product_id', $selectedProduct);
        }

        // Mengambil data produk per provinsi
        $provinceData = $provinceQuery
            ->select(
                'rp.id as province_id',
                'rp.name as province_name',
                DB::raw('COUNT(DISTINCT ti.product_id) as total_products'),
                DB::raw('SUM(ti.quantity) as total_quantity'),
                DB::raw('SUM(ti.subtotal) as total_value')
            )
            ->groupBy('rp.id', 'rp.name')
            ->get();

        // Mengambil detail produk per provinsi untuk hover
        $provinceProductsQuery = DB::table('transactions as t')
            ->join('transaction_items as ti', 't.transaction_id', '=', 'ti.transaction_id')
            ->join('products as p', 'ti.product_id', '=', 'p.product_id')
            ->join('plant_types as pt', 'p.plant_type_id', '=', 'pt.plant_type_id')
            ->join('reg_provinces as rp', 't.province_id', '=', 'rp.id')
            ->whereNotNull('t.province_id')
            ->where('t.order_status', 'selesai');

        // Apply filters untuk province products
        if ($selectedProvince) {
            $provinceProductsQuery->where('t.province_id', $selectedProvince);
        }
        if ($selectedPlantType) {
            $provinceProductsQuery->where('p.plant_type_id', $selectedPlantType);
        }
        if ($selectedProduct) {
            $provinceProductsQuery->where('p.product_id', $selectedProduct);
        }

        $provinceProducts = $provinceProductsQuery
            ->select(
                'rp.id as province_id',
                'rp.name as province_name',
                'p.product_id',
                'p.product_name as product_name',
                'p.unit',
                'p.plant_type_id',
                DB::raw('SUM(ti.quantity) as total_quantity'),
                DB::raw('SUM(ti.subtotal) as total_value')
            )
            ->groupBy('rp.id', 'rp.name', 'p.product_id', 'p.product_name', 'p.unit', 'p.plant_type_id')
            ->get()
            ->groupBy('province_id');

        // Mengambil data produk per kabupaten/kota
        $regencyDataQuery = DB::table('transactions as t')
            ->join('transaction_items as ti', 't.transaction_id', '=', 'ti.transaction_id')
            ->join('products as p', 'ti.product_id', '=', 'p.product_id')
            ->join('plant_types as pt', 'p.plant_type_id', '=', 'pt.plant_type_id')
            ->join('reg_regencies as rr', 't.regency_id', '=', 'rr.id')
            ->whereNotNull('t.regency_id')
            ->where('t.order_status', 'selesai');

        // Apply filters untuk regency data
        if ($selectedProvince) {
            $regencyDataQuery->where('rr.province_id', $selectedProvince);
        }
        if ($selectedPlantType) {
            $regencyDataQuery->where('p.plant_type_id', $selectedPlantType);
        }
        if ($selectedProduct) {
            $regencyDataQuery->where('p.product_id', $selectedProduct);
        }

        $regencyData = $regencyDataQuery
            ->select(
                'rr.id as regency_id',
                'rr.name as regency_name',
                'rr.province_id',
                DB::raw('COUNT(DISTINCT ti.product_id) as total_products'),
                DB::raw('SUM(ti.quantity) as total_quantity'),
                DB::raw('SUM(ti.subtotal) as total_value')
            )
            ->groupBy('rr.id', 'rr.name', 'rr.province_id')
            ->get();

        // Mengambil detail produk per kabupaten/kota untuk hover
        $regencyProductsQuery = DB::table('transactions as t')
            ->join('transaction_items as ti', 't.transaction_id', '=', 'ti.transaction_id')
            ->join('products as p', 'ti.product_id', '=', 'p.product_id')
            ->join('plant_types as pt', 'p.plant_type_id', '=', 'pt.plant_type_id')
            ->join('reg_regencies as rr', 't.regency_id', '=', 'rr.id')
            ->whereNotNull('t.regency_id')
            ->where('t.order_status', 'selesai');

        // Apply filters untuk regency products
        if ($selectedProvince) {
            $regencyProductsQuery->where('rr.province_id', $selectedProvince);
        }
        if ($selectedPlantType) {
            $regencyProductsQuery->where('p.plant_type_id', $selectedPlantType);
        }
        if ($selectedProduct) {
            $regencyProductsQuery->where('p.product_id', $selectedProduct);
        }

        $regencyProducts = $regencyProductsQuery
            ->select(
                'rr.id as regency_id',
                'rr.name as regency_name',
                'rr.province_id',
                'p.product_id',
                'p.product_name as product_name',
                'p.unit',
                'p.plant_type_id',
                DB::raw('SUM(ti.quantity) as total_quantity'),
                DB::raw('SUM(ti.subtotal) as total_value')
            )
            ->groupBy('rr.id', 'rr.name', 'rr.province_id', 'p.product_id', 'p.product_name', 'p.unit', 'p.plant_type_id')
            ->get()
            ->groupBy('regency_id');



        // Mengambil semua data kabupaten/kota untuk fallback
        $allRegencies = DB::table('reg_regencies')
            ->select('id as regency_id', 'name as regency_name', 'province_id')
            ->get();
        
        return view('admin.product_distribution', compact(
            'provinceData', 
            'provinceProducts', 
            'regencyData', 
            'regencyProducts', 
            'allProvinces', 
            'allRegencies',
            'allPlantTypes',
            'allProducts',
            'selectedProvince',
            'selectedPlantType',
            'selectedProduct'
        ));
    }
    
    
   
    
    public function articles()
    {
        // Untuk sementara, kita akan menggunakan view yang sama dengan dashboard
        // karena belum ada model Article
        return view('admin.articles');
    }
} 