<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'type' => $this->type,
            'type_label' => $this->getTypeLabel(),
            'specifications' => $this->specifications,
            'status' => $this->status,
            'status_label' => $this->status === 'active' ? 'نشط' : 'غير نشط',
            'is_favorite' => $this->when(isset($this->is_favorite), $this->is_favorite),
            'primary_image' => $this->whenLoaded('primaryImage', function () {
                return $this->primaryImage ? new ProductImageResource($this->primaryImage) : null;
            }),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
    
    /**
     * Get the type label in Arabic
     */
    private function getTypeLabel(): string
    {
        switch ($this->type) {
            case 'battery':
                return 'بطارية';
            case 'solar_panel':
                return 'لوح طاقة شمسية';
            case 'inverter':
                return 'انفيرتر';
            default:
                return $this->type;
        }
    }
} 