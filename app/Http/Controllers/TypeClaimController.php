<?php


namespace App\Http\Controllers;

use App\Models\ReclamoType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TypeClaimController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // ...existing code...
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 0);

        $query = ReclamoType::query()->orderBy('id', 'desc');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($perPage > 0) {
            $paginator = $query->paginate($perPage);
            return response()->json([
                'success' => true,
                'code' => 200,
                'data' => [
                    'tipos' => $paginator->items(),
                    'pagination' => [
                        'total' => $paginator->total(),
                        'per_page' => $paginator->perPage(),
                        'current_page' => $paginator->currentPage(),
                        'last_page' => $paginator->lastPage(),
                    ],
                ],
            ], 200);
        }

        $tipos = $query->get();
        return response()->json(['success' => true, 'code' => 200, 'data' => $tipos], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // ...existing code...
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:reclamo_types,nombre'],
            'slug'   => ['nullable', 'string', 'max:255', 'unique:reclamo_types,slug'],
        ]);

        if (empty($data['slug'])) {
            $base = Str::slug($data['nombre']);
            $data['slug'] = $this->ensureUniqueSlug($base);
        }

        return DB::transaction(function () use ($data) {
            $tipo = ReclamoType::create($data);
            return response()->json(['success' => true, 'code' => 201, 'data' => $tipo], 201);
        });
        // ...existing code...
    }

    /**
     * Display the specified resource.
     */
    public function show(ReclamoType $reclamoType)
    {
        // ...existing code...
        return response()->json(['success' => true, 'code' => 200, 'data' => $reclamoType], 200);
        // ...existing code...
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReclamoType $reclamoType)
    {
        // ...existing code...
        $validated = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:255', Rule::unique('reclamo_types', 'nombre')->ignore($reclamoType->id)],
            'slug'   => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('reclamo_types', 'slug')->ignore($reclamoType->id)],
        ]);

        // Si no envían slug pero sí cambiaron nombre, regenerar slug
        if (!array_key_exists('slug', $validated) && array_key_exists('nombre', $validated)) {
            $base = Str::slug($validated['nombre']);
            $validated['slug'] = $this->ensureUniqueSlug($base, $reclamoType->id);
        }

        // Si envían slug vacío/null, regenerarlo a partir del nombre actual/nuevo
        if (array_key_exists('slug', $validated) && ($validated['slug'] === null || $validated['slug'] === '')) {
            $nombreBase = $validated['nombre'] ?? $reclamoType->nombre;
            $validated['slug'] = $this->ensureUniqueSlug(Str::slug($nombreBase), $reclamoType->id);
        }

        return DB::transaction(function () use ($reclamoType, $validated) {
            $reclamoType->update($validated);
            $reclamoType->refresh();
            return response()->json(['success' => true, 'code' => 200, 'data' => $reclamoType], 200);
        });
        // ...existing code...
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReclamoType $reclamoType)
    {
        $reclamoType->delete();
        return response()->json(['success' => true, 'code' => 200, 'data' => null], 200);
    }

    /**
     * Genera un slug único (considerando soft deletes) y permitiendo ignorar un ID.
     */
    private function ensureUniqueSlug(string $desiredSlug, ?int $ignoreId = null): string
    {
        $slug = $desiredSlug !== '' ? $desiredSlug : Str::random(8);
        $i = 1;

        $exists = function (string $s) use ($ignoreId) {
            return ReclamoType::withTrashed()
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $s)
                ->exists();
        };

        while ($exists($slug)) {
            $slug = $desiredSlug . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
