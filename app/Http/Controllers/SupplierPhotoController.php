<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SupplierPhotoController extends Controller
{
    public function create(Supplier $supplier)
    {
        return view('supplier_photos.create', compact('supplier'));
    }

    public function store(Request $request, Supplier $supplier)
    {
        $request->validate([
            'photo'   => ['required', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        $path = $request->file('photo')->store('supplier-photos/' . $supplier->id, 'public');

        $supplier->photos()->create([
            'path'    => $path,
            'caption' => $request->input('caption'),
        ]);

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Photo uploaded.');
    }

    public function destroy(Supplier $supplier, SupplierPhoto $photo)
    {
        abort_if($photo->supplier_id !== $supplier->id, 404);

        Storage::disk('public')->delete($photo->path);
        $photo->delete();

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Photo deleted.');
    }
}
