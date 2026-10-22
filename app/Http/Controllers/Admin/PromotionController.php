<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PromotionRequest;
use App\Models\Promotion;
use App\Services\ActivityLogger;

class PromotionController extends Controller
{
    public function index()
    {
        return view('admin.promotions.index', ['promotions' => Promotion::latest()->paginate(15)]);
    }

    public function create()
    {
        return view('admin.promotions.form', ['promotion' => new Promotion(['type' => Promotion::TYPE_PERCENT, 'is_active' => true, 'min_purchase' => 0])]);
    }

    public function store(PromotionRequest $request, ActivityLogger $logger)
    {
        $promotion = Promotion::create($request->validated());
        $logger->log('promotion.created', "Membuat promo {$promotion->code}", $promotion);

        return redirect()->route('admin.promotions.index')->with('success', 'Promo berhasil dibuat.');
    }

    public function edit(Promotion $promotion)
    {
        return view('admin.promotions.form', compact('promotion'));
    }

    public function update(PromotionRequest $request, Promotion $promotion, ActivityLogger $logger)
    {
        $promotion->update($request->validated());
        $logger->log('promotion.updated', "Memperbarui promo {$promotion->code}", $promotion);

        return redirect()->route('admin.promotions.index')->with('success', 'Promo berhasil diperbarui.');
    }

    public function destroy(Promotion $promotion, ActivityLogger $logger)
    {
        $logger->log('promotion.deleted', "Menghapus promo {$promotion->code}", null, ['code' => $promotion->code]);
        $promotion->delete();

        return redirect()->route('admin.promotions.index')->with('success', 'Promo dihapus.');
    }
}
