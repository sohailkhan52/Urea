<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Family;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FamilyController extends Controller
{
    /**
     * Store a newly created family (AJAX endpoint for inline creation)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:50',
            'village' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $family = DB::transaction(function () use ($validated) {
                // Generate unique family code
                $familyCode = Family::generateFamilyCode();

                $family = Family::create([
                    'family_code' => $familyCode,
                    'name' => $validated['name'],
                    'address' => $validated['address'] ?? null,
                    'city' => $validated['city'] ?? null,
                    'village' => $validated['village'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'status' => Family::STATUS_ACTIVE,
                ]);

                return $family;
            });

            return response()->json([
                'success' => true,
                'message' => 'Family created successfully',
                'family' => [
                    'id' => $family->id,
                    'family_code' => $family->family_code,
                    'name' => $family->name,
                    'display_name' => $family->name . ' — ' . $family->family_code,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating family: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Search families (AJAX endpoint for dropdown)
     */
    public function search(Request $request): JsonResponse
    {
        $search = $request->input('search', '');
        
        $families = Family::active()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('family_code', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(function ($family) {
                return [
                    'id' => $family->id,
                    'family_code' => $family->family_code,
                    'name' => $family->name,
                    'display_name' => $family->name . ' — ' . $family->family_code,
                    'total_members' => $family->total_members,
                ];
            });

        return response()->json($families);
    }

    /**
     * Get all families (for dropdown)
     */
    public function getAll(): JsonResponse
    {
        $families = Family::active()
            ->orderBy('name')
            ->get()
            ->map(function ($family) {
                return [
                    'id' => $family->id,
                    'family_code' => $family->family_code,
                    'name' => $family->name,
                    'display_name' => $family->name . ' — ' . $family->family_code,
                ];
            });

        return response()->json($families);
    }
}
