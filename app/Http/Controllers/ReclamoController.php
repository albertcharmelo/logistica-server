<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReclamoAttachFilesRequest;
use App\Http\Requests\ReclamoStoreRequest;
use App\Http\Requests\ReclamoUpdateRequest;
use App\Http\Resources\ReclamoResource;
use App\Models\Reclamo;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReclamoController extends Controller
{
    /**
     * Utilidad: clonar la relación 'persona' como 'persona'
     * para que el ReclamoResource pueda exponerla con la clave 'persona'
     * sin modificar el modelo.
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection|array $reclamos
     * @return \Illuminate\Support\Collection
     */
    protected function aliasPersona($reclamos)
    {
        $items = collect($reclamos);
        $items->each(function (\App\Models\Reclamo $rec): void {
            if ($rec->relationLoaded('persona')) {
                /** @var \Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|null $persona */
                $persona = $rec->getRelation('persona');
                if ($persona instanceof \Illuminate\Database\Eloquent\Model || $persona instanceof \Illuminate\Support\Collection) {
                    $rec->setRelation('persona', $persona);
                }
            }
        });
        return $items;
    }

    // GET /api/reclamos
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 0);

        $query = Reclamo::with([
            // mantenemos 'persona' (tu relación actual)
            'persona:id,apellidos,nombres,telefono,email,cliente_id,sucursal_id,unidad_id,agente_id',
            // demás relaciones
            'agente:id,name,email',
            'creator:id,name,email',
            'tipo:id,nombre,slug',
            'archivos:id,nombre_original,ruta,download_url,disk',
        ])
            ->withCount('comments')
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('persona_id')) {
            $query->where('persona_id', (int) $request->persona_id);
        }
        if ($request->filled('reclamo_type_id')) {
            $query->where('reclamo_type_id', (int) $request->reclamo_type_id);
        }
        if ($request->filled('agente_id')) {
            $query->where('agente_id', (int) $request->agente_id);
        }
        if ($request->filled('created_from') || $request->filled('created_to')) {
            $from = $request->filled('created_from')
                ? Carbon::parse($request->string('created_from'))->startOfDay()
                : null;
            $to = $request->filled('created_to')
                ? Carbon::parse($request->string('created_to'))->endOfDay()
                : null;
            if ($from && $to) {
                $query->whereBetween('created_at', [$from, $to]);
            } elseif ($from) {
                $query->where('created_at', '>=', $from);
            } elseif ($to) {
                $query->where('created_at', '<=', $to);
            }
        }

        if ($perPage > 0) {
            $paginator = $query->paginate($perPage);
            $aliased   = $this->aliasPersona($paginator->items());

            return response()->json([
                'success' => true,
                'code'    => 200,
                'data'    => [
                    'reclamos'   => ReclamoResource::collection($aliased),
                    'pagination' => [
                        'total'         => $paginator->total(),
                        'per_page'      => $paginator->perPage(),
                        'current_page'  => $paginator->currentPage(),
                        'last_page'     => $paginator->lastPage(),
                    ],
                ],
            ]);
        }

        $items   = $query->get();
        $aliased = $this->aliasPersona($items);

        return response()->json([
            'success' => true,
            'code'    => 200,
            'data'    => ReclamoResource::collection($aliased),
        ]);
    }

    // POST /api/reclamos
    public function store(ReclamoStoreRequest $request)
    {
        $payload = $request->validated();

        // status inicial por convención
        $payload['status'] = $payload['status'] ?? 'creado';

        // setear creador automáticamente
        $payload['creator_id'] = auth()->id() ?: null;

        $reclamo = Reclamo::create($payload);

        // comentario de sistema inicial
        \App\Models\ReclamoComment::create([
            'reclamo_id'  => $reclamo->id,
            'creator_id'  => $reclamo->creator_id,
            'sender_type' => 'sistema',
            'message'     => 'Reclamo creado inicialmente',
            'meta'        => ['status' => $reclamo->status],
        ]);

        // cargar relaciones para el resource
        $reclamo->load([
            'persona:id,apellidos,nombres,telefono,email,cliente_id,sucursal_id,unidad_id,agente_id',
            'agente:id,name,email',
            'creator:id,name,email',
            'tipo:id,nombre,slug',
            'archivos:id,nombre_original,ruta,download_url,disk',
        ])->loadCount('comments');

        // alias persona
        $this->aliasPersona([$reclamo]);

        return response()->json([
            'success' => true,
            'code'    => 201,
            'data'    => new ReclamoResource($reclamo),
        ], 201);
    }

    // GET /api/reclamos/{id}
    public function show(int $id)
    {
        $reclamo = Reclamo::with([
            'persona:id,apellidos,nombres,telefono,email,cliente_id,sucursal_id,unidad_id,agente_id',
            'agente:id,name,email',
            'creator:id,name,email',
            'tipo:id,nombre,slug',
            'archivos:id,nombre_original,ruta,download_url,disk',
        ])->withCount('comments')->findOrFail($id);

        $this->aliasPersona([$reclamo]);

        return response()->json([
            'success' => true,
            'code'    => 200,
            'data'    => new ReclamoResource($reclamo),
        ]);
    }

    // PATCH /api/reclamos/{id}
    public function update(ReclamoUpdateRequest $request, int $id)
    {
        $reclamo = Reclamo::findOrFail($id);
        $reclamo->fill($request->only(['agente_id', 'reclamo_type_id', 'detalle', 'status']))->save();

        $reclamo->load([
            'persona:id,apellidos,nombres,telefono,email,cliente_id,sucursal_id,unidad_id,agente_id',
            'agente:id,name,email',
            'creator:id,name,email',
            'tipo:id,nombre,slug',
            'archivos:id,nombre_original,ruta,download_url,disk',
        ])->loadCount('comments');

        $this->aliasPersona([$reclamo]);

        return response()->json([
            'success' => true,
            'code'    => 200,
            'data'    => new ReclamoResource($reclamo),
        ]);
    }

    // POST /api/reclamos/{id}/archivos  (attach)
    public function attachFiles(ReclamoAttachFilesRequest $request, int $id)
    {
        $reclamo = Reclamo::findOrFail($id);
        $reclamo->archivos()->syncWithoutDetaching($request->archivo_ids);

        $reclamo->load([
            'persona:id,apellidos,nombres,telefono,email,cliente_id,sucursal_id,unidad_id,agente_id',
            'agente:id,name,email',
            'creator:id,name,email',
            'tipo:id,nombre,slug',
            'archivos:id,nombre_original,ruta,download_url,disk',
        ])->loadCount('comments');

        $this->aliasPersona([$reclamo]);

        return response()->json([
            'success' => true,
            'code'    => 200,
            'data'    => new ReclamoResource($reclamo),
        ]);
    }

    // DELETE /api/reclamos/{id}/archivos  (detach)
    public function detachFiles(ReclamoAttachFilesRequest $request, int $id)
    {
        $reclamo = Reclamo::findOrFail($id);
        $reclamo->archivos()->detach($request->archivo_ids);

        $reclamo->load([
            'persona:id,apellidos,nombres,telefono,email,cliente_id,sucursal_id,unidad_id,agente_id',
            'agente:id,name,email',
            'creator:id,name,email',
            'tipo:id,nombre,slug',
            'archivos:id,nombre_original,ruta,download_url,disk',
        ])->loadCount('comments');

        $this->aliasPersona([$reclamo]);

        return response()->json([
            'success' => true,
            'code'    => 200,
            'data'    => new ReclamoResource($reclamo),
        ]);
    }

    // DELETE /api/reclamos/{id}
    public function destroy(int $id)
    {
        $reclamo = Reclamo::findOrFail($id);
        $reclamo->delete();

        return response()->json([
            'success' => true,
            'code'    => 200,
            'data'    => null,
        ]);
    }
}
