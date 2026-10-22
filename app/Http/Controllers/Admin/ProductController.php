<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Game;
use App\Models\Product;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $gameId = $request->integer('game_id') ?: null;

        $products = Product::with('game')
            ->when($gameId, fn ($q) => $q->where('game_id', $gameId))
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('game_id')->orderBy('sort_order')->orderBy('price')
            ->paginate(20)->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'games' => Game::orderBy('name')->get(['id', 'name']),
            'search' => $search,
            'gameId' => $gameId,
        ]);
    }

    public function create(Request $request)
    {
        $product = new Product(['is_active' => true, 'sort_order' => 0, 'game_id' => $request->integer('game_id') ?: null]);

        return view('admin.products.form', ['product' => $product, 'games' => Game::orderBy('name')->get(['id', 'name'])]);
    }

    public function store(ProductRequest $request, ActivityLogger $logger)
    {
        $data = $request->validated();
        $product = Product::create(array_merge($data, ['sort_order' => $data['sort_order'] ?? 0]));
        $logger->log('product.created', "Menambah produk {$product->name}", $product);

        return redirect()->route('admin.products.index', ['game_id' => $product->game_id])->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.form', ['product' => $product, 'games' => Game::orderBy('name')->get(['id', 'name'])]);
    }

    public function update(ProductRequest $request, Product $product, ActivityLogger $logger)
    {
        $data = $request->validated();
        $oldPrice = $product->price;
        $product->update(array_merge($data, ['sort_order' => $data['sort_order'] ?? 0]));

        // Perubahan harga hanya berlaku untuk pesanan BARU; pesanan lama memakai snapshot.
        $logger->log('product.updated', "Memperbarui produk {$product->name}", $product, $oldPrice !== $product->price ? ['price_before' => $oldPrice, 'price_after' => $product->price] : []);

        return redirect()->route('admin.products.index', ['game_id' => $product->game_id])->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product, ActivityLogger $logger)
    {
        $logger->log('product.deleted', "Menghapus produk {$product->name}", null, ['product_id' => $product->id]);
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Produk dihapus.');
    }
}
