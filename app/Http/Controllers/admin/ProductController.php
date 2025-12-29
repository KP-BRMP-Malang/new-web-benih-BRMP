<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PlantTypes;
use App\Models\ProductHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $query = Product::with('plantType');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('certificate_number', 'like', "%{$search}%")
                  ->orWhere('certificate_class', 'like', "%{$search}%")
                  ->orWhereHas('plantType', function($plantQuery) use ($search) {
                      $plantQuery->where('plant_type_name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by plant type
        if ($request->filled('plant_type_id')) {
            $query->where('plant_type_id', $request->plant_type_id);
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'available':
                    $query->whereRaw('stock > minimum_stock');
                    break;
                case 'low_stock':
                    $query->whereRaw('stock <= minimum_stock AND stock > 0');
                    break;
                case 'out_of_stock':
                    $query->where('stock', 0);
                    break;
            }
        }

        // Filter by price range
        if ($request->filled('price_min')) {
            $query->where('price_per_unit', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price_per_unit', '<=', $request->price_max);
        }

        // Filter by certificate
        if ($request->filled('has_certificate')) {
            if ($request->has_certificate == 'yes') {
                $query->whereNotNull('certificate_number');
            } elseif ($request->has_certificate == 'no') {
                $query->whereNull('certificate_number');
            }
        }

        // Sort functionality
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSortFields = ['product_name', 'price_per_unit', 'stock', 'created_at'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'created_at';
        }
        
        $query->orderBy($sortBy, $sortOrder);

        // Export functionality
        if ($request->has('export') && $request->export === 'excel') {
            $products = $query->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Header
            $headers = [
                'ID', 'Nama Produk', 'Kategori', 'Deskripsi', 'Stok', 'Stok Minimum',
                'Satuan', 'Harga per Unit', 'Min. Pembelian', 'No. Sertifikat',
                'Kelas Sertifikat', 'Berlaku Dari', 'Berlaku Sampai', 'Dibuat'
            ];
            $sheet->fromArray($headers, null, 'A1');

            // Data
            $rowNum = 2;
            foreach ($products as $product) {
                $row = [
                    $product->product_id,
                    $product->product_name,
                    $product->plantType->plant_type_name ?? 'N/A',
                    $product->description,
                    $product->stock,
                    $product->minimum_stock,
                    $product->unit,
                    $product->price_per_unit,
                    $product->minimum_purchase,
                    $product->certificate_number ?? 'N/A',
                    $product->certificate_class ?? 'N/A',
                    $product->valid_from ? $product->valid_from->format('Y-m-d') : 'N/A',
                    $product->valid_until ? $product->valid_until->format('Y-m-d') : 'N/A',
                    $product->created_at->format('Y-m-d H:i:s')
                ];
                $sheet->fromArray($row, null, 'A' . $rowNum);
                $rowNum++;
            }

            $filename = 'products_' . date('Y-m-d_H-i-s') . '.xlsx';
            $writer = new Xlsx($spreadsheet);

            // Output to browser
            return response()->streamDownload(function() use ($writer) {
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        $products = $query->paginate(15);
        $plantTypes = PlantTypes::all();

        // If AJAX request, return only the table body
        if ($request->ajax()) {
            return view('admin.products.partials.table-body', compact('products'));
        }

        return view('admin.products.index', compact('products', 'plantTypes'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $plantTypes = PlantTypes::all();
        return view('admin.products.create', compact('plantTypes'));
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255',
            'plant_type_id' => 'required|exists:plant_types,plant_type_id',
            'description' => 'required|string',
            'stock' => 'required|numeric|min:0',
            'minimum_stock' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'price_per_unit' => 'required|numeric|min:0',
            'minimum_purchase' => 'required|numeric|min:0',
            'image1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'image2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'image_certificate' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'certificate_number' => 'nullable|string|max:255',
            'certificate_class' => 'nullable|string|max:100',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();

        // Handle image uploads
        if ($request->hasFile('image1')) {
            $data['image1'] = $request->file('image1')->store('products', 'public');
        }
        if ($request->hasFile('image2')) {
            $data['image2'] = $request->file('image2')->store('products', 'public');
        }
        if ($request->hasFile('image_certificate')) {
            $data['image_certificate'] = $request->file('image_certificate')->store('certificates', 'public');
        }

        $product = Product::create($data);

        // Create initial history record
        $product->createHistoryRecord();

        return redirect()->route('admin.products.index')
            ->with('success', 'Produk berhasil ditambahkan!');
    }

    /**
     * Display the specified product.
     */
    public function show($id)
    {
        $product = Product::with(['plantType', 'histories'])->findOrFail($id);
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $plantTypes = PlantTypes::all();
        return view('admin.products.edit', compact('product', 'plantTypes'));
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255',
            'plant_type_id' => 'required|exists:plant_types,plant_type_id',
            'description' => 'required|string',
            'stock' => 'required|numeric|min:0',
            'minimum_stock' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'price_per_unit' => 'required|numeric|min:0',
            'minimum_purchase' => 'required|numeric|min:0',
            'image1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'image2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'image_certificate' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'certificate_number' => 'nullable|string|max:255',
            'certificate_class' => 'nullable|string|max:100',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();

        // Handle image uploads
        if ($request->hasFile('image1')) {
            // Delete old image if exists
            if ($product->image1) {
                // Storage::disk('public')->delete($product->image1); // Keep old image for history
            }
            $data['image1'] = $request->file('image1')->store('products', 'public');
        }
        if ($request->hasFile('image2')) {
            if ($product->image2) {
                // Storage::disk('public')->delete($product->image2); // Keep old image for history
            }
            $data['image2'] = $request->file('image2')->store('products', 'public');
        }
        if ($request->hasFile('image_certificate')) {
            if ($product->image_certificate) {
                // Storage::disk('public')->delete($product->image_certificate); // Keep old image for history
            }
            $data['image_certificate'] = $request->file('image_certificate')->store('certificates', 'public');
        }

        $product->update($data);

        return redirect()->route('admin.products.index')
            ->with('success', 'Produk berhasil diperbarui!');
    }

    /**
     * Remove the specified product.
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Delete associated images
        if ($product->image1) {
            Storage::disk('public')->delete($product->image1);
        }
        if ($product->image2) {
            Storage::disk('public')->delete($product->image2);
        }
        if ($product->image_certificate) {
            Storage::disk('public')->delete($product->image_certificate);
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Produk berhasil dihapus!');
    }

    /**
     * Show product history.
     */
    public function history($id)
    {
        $product = Product::with(['plantType', 'histories' => function($query) {
            $query->orderBy('recorded_at', 'desc');
        }])->findOrFail($id);

        return view('admin.products.history', compact('product'));
    }
} 