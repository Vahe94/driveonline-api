<?php

namespace App\Http\Controllers;

use App\Models\CarMake;
use App\Models\CarModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CarCatalogController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            CarMake::query()
                ->where('is_active', true)
                ->with(['models' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name')])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
        );
    }

    public function adminIndex(): JsonResponse
    {
        return response()->json(
            CarMake::query()
                ->with(['models' => fn ($query) => $query
                    ->orderBy('sort_order')
                    ->orderBy('name')])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
        );
    }

    public function storeMake(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique(CarMake::class, 'name')],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        $make = CarMake::create([
            ...$data,
            'slug' => $this->makeUniqueMakeSlug($data['name']),
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return response()->json($make->load('models'), 201);
    }

    public function updateMake(Request $request, CarMake $make): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique(CarMake::class, 'name')->ignore($make->id)],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        if (isset($data['name']) && $data['name'] !== $make->name) {
            $data['slug'] = $this->makeUniqueMakeSlug($data['name'], $make->id);
        }

        $make->update($data);

        return response()->json($make->fresh()->load('models'));
    }

    public function destroyMake(CarMake $make): Response
    {
        $make->delete();

        return response()->noContent();
    }

    public function storeModel(Request $request, CarMake $make): JsonResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(CarModel::class, 'name')->where('car_make_id', $make->id),
            ],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        $model = $make->models()->create([
            ...$data,
            'slug' => $this->makeUniqueModelSlug($make, $data['name']),
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return response()->json($model, 201);
    }

    public function updateModel(Request $request, CarModel $model): JsonResponse
    {
        $data = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique(CarModel::class, 'name')
                    ->where('car_make_id', $model->car_make_id)
                    ->ignore($model->id),
            ],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        if (isset($data['name']) && $data['name'] !== $model->name) {
            $data['slug'] = $this->makeUniqueModelSlug($model->make, $data['name'], $model->id);
        }

        $model->update($data);

        return response()->json($model->fresh());
    }

    public function destroyModel(CarModel $model): Response
    {
        $model->delete();

        return response()->noContent();
    }

    private function makeUniqueMakeSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'make';
        $slug = $baseSlug;
        $suffix = 2;

        while (
            CarMake::query()
                ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = sprintf('%s-%d', $baseSlug, $suffix);
            $suffix++;
        }

        return $slug;
    }

    private function makeUniqueModelSlug(CarMake $make, string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'model';
        $slug = $baseSlug;
        $suffix = 2;

        while (
            CarModel::query()
                ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('car_make_id', $make->id)
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = sprintf('%s-%d', $baseSlug, $suffix);
            $suffix++;
        }

        return $slug;
    }
}
