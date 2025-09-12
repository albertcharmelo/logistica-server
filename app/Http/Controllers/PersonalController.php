<?php

namespace App\Http\Controllers;

use App\Http\Requests\PersonaStoreRequest;
use App\Http\Resources\PersonaResource;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersonalController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 0);

        $query = Persona::with(['unidad', 'cliente', 'sucursal', 'dueno', 'transporteTemporal', 'estado'])->orderBy('id', 'desc');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('apellidos', 'like', "%$search%")
                    ->orWhere('nombres', 'like', "%$search%")
                    ->orWhere('cuil', 'like', "%$search%")
                    ->orWhere('telefono', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('cbu_alias', 'like', "%$search%");
            });
        }

        // Filtros opcionales
        if ($request->has('combustible')) {
            $combustibleRaw = $request->query('combustible');
            $combustible = filter_var($combustibleRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if (!is_null($combustible)) {
                $query->where('combustible', $combustible);
            }
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', (int) $request->query('tipo'));
        }

        if ($request->filled('unidad')) {
            $term = trim((string) $request->query('unidad'));
            $query->whereHas('unidad', function ($uq) use ($term) {
                $uq->where('matricula', 'like', "%$term%")
                    ->orWhere('marca', 'like', "%$term%")
                    ->orWhere('modelo', 'like', "%$term%");
            });
        }

        if ($request->filled('cliente')) {
            $term = trim((string) $request->query('cliente'));
            $query->whereHas('cliente', function ($cq) use ($term) {
                $cq->where('nombre', 'like', "%$term%")
                    ->orWhere('codigo', 'like', "%$term%")
                    ->orWhere('direccion', 'like', "%$term%");
            });
        }

        if ($request->filled('sucursal')) {
            $term = trim((string) $request->query('sucursal'));
            $query->whereHas('sucursal', function ($sq) use ($term) {
                $sq->where('nombre', 'like', "%$term%")
                    ->orWhere('direccion', 'like', "%$term%");
            });
        }

        if ($request->has('estado')) {
            $estado = $request->query('estado');
            if (is_numeric($estado)) {
                $query->where('estado_id', (int) $estado);
            } elseif (is_string($estado) && trim($estado) !== '') {
                $term = trim((string) $estado);
                $query->whereHas('estado', function ($eq) use ($term) {
                    $eq->where('nombre', 'like', "%$term%");
                });
            }
        }

        if ($perPage > 0) {
            $paginator = $query->paginate($perPage);
            return response()->json([
                'success' => true,
                'code' => 200,
                'data' => [
                    'personas' => PersonaResource::collection($paginator->items()),
                    'pagination' => [
                        'total' => $paginator->total(),
                        'per_page' => $paginator->perPage(),
                        'current_page' => $paginator->currentPage(),
                        'last_page' => $paginator->lastPage(),
                    ],
                ],
            ], 200);
        }

        $personas = $query->get();
        return response()->json(['success' => true, 'code' => 200, 'data' => PersonaResource::collection($personas)], 200);
    }

    public function store(PersonaStoreRequest $request)
    {
        $data = $request->validated();
        // Permitir que el frontend envíe 'estado' en lugar de 'estado_id'
        if (array_key_exists('estado', $data) && !array_key_exists('estado_id', $data)) {
            $estadoValor = $data['estado'];
            unset($data['estado']);
            if ($estadoValor !== null && $estadoValor !== '') {
                $data['estado_id'] = (int) $estadoValor;
            }
        }

        return DB::transaction(function () use ($data) {
            $dueno = $data['dueno'] ?? null;
            $transporteTmp = $data['transporte_temporal'] ?? null;
            unset($data['dueno'], $data['transporte_temporal']);

            $persona = Persona::create($data);

            if (is_array($dueno)) {
                $persona->dueno()->create($dueno);
            }
            if (is_array($transporteTmp)) {
                $persona->transporteTemporal()->create($transporteTmp);
            }

            $persona->load(['unidad', 'cliente', 'sucursal', 'dueno', 'transporteTemporal']);

            return response()->json(['success' => true, 'code' => 201, 'data' => new PersonaResource($persona)], 201);
        });
    }

    public function show(string $id)
    {
        $persona = Persona::with(['unidad', 'cliente', 'sucursal', 'dueno', 'transporteTemporal'])->findOrFail($id);
        return response()->json(['success' => true, 'code' => 200, 'data' => new PersonaResource($persona)], 200);
    }

    public function update(PersonaStoreRequest $request, string $id)
    {
        $persona = Persona::findOrFail($id);

        $validated = $request->validated();

        // Extraer bloques relacionales antes del update principal
        $duenoInput = $validated['dueno'] ?? null;
        $transporteTmpInput = $validated['transporte_temporal'] ?? null;
        unset($validated['dueno'], $validated['transporte_temporal']);

        // Normalizar alias 'estado' -> 'estado_id' (siempre que venga 'estado')
        if (array_key_exists('estado', $validated)) {
            $estadoValor = $validated['estado'];
            unset($validated['estado']);
            if ($estadoValor !== null && $estadoValor !== '') {
                $validated['estado_id'] = (int) $estadoValor;
            } else {
                // Permitir limpiar estado si se envía null / vacío explícito
                $validated['estado_id'] = null;
            }
        }

        return DB::transaction(function () use ($request, $persona, $validated, $duenoInput, $transporteTmpInput) {

            // Actualizar atributos simples solo si hay algo
            if (!empty($validated)) {
                $persona->fill($validated);
                if ($persona->isDirty()) {
                    $persona->save();
                }
            }

            // ------- Relación DUENO (hasOne) -------
            if ($request->exists('dueno')) {
                if (is_array($duenoInput) && count(array_filter($duenoInput, fn($v) => $v !== null && $v !== '')) > 0) {
                    // updateOrCreate por persona_id (clave única lógica)
                    $persona->dueno()->updateOrCreate(
                        ['persona_id' => $persona->id],
                        $duenoInput
                    );
                } else {
                    // null, array vacío o limpieza explícita
                    $persona->dueno()->delete();
                }
            }

            // ------- Relación TRANSPORTE TEMPORAL (hasOne) -------
            if ($request->exists('transporte_temporal')) {
                if (is_array($transporteTmpInput) && count(array_filter($transporteTmpInput, fn($v) => $v !== null && $v !== '')) > 0) {
                    $persona->transporteTemporal()->updateOrCreate(
                        ['persona_id' => $persona->id],
                        $transporteTmpInput
                    );
                } else {
                    $persona->transporteTemporal()->delete();
                }
            }

            $persona->load(['unidad', 'cliente', 'sucursal', 'dueno', 'transporteTemporal', 'estado']);

            return response()->json([
                'success' => true,
                'code' => 200,
                'data' => new PersonaResource($persona),
            ], 200);
        });
    }

    public function destroy(string $id)
    {
        $persona = Persona::findOrFail($id);
        $persona->delete();
        return response()->json(['success' => true, 'code' => 200, 'data' => null], 200);
    }
}
