<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'roles' => $this->whenLoaded('roles') ? $this->roles->pluck('name') : $this->getRoleNames(),
            'permissions' => $this->when(
                method_exists($this->resource, 'getAllPermissions'),
                fn () => $this->getAllPermissions()->pluck('name')->values()->all(),
                []
            ),
            'klub' => $this->whenLoaded('klub', fn () => $this->klub ? ['id' => $this->klub->id, 'nama' => $this->klub->nama] : null),
            'klub_id' => $this->klub_id,
            'organisasi' => $this->whenLoaded('organisasi', fn () => $this->organisasi ? ['id' => $this->organisasi->id, 'nama' => $this->organisasi->nama] : null),
            'organisasi_id' => $this->organisasi_id,
            'cabor' => $this->whenLoaded('cabor', fn () => $this->cabor ? ['id' => $this->cabor->id, 'nama' => $this->cabor->nama] : null),
            'cabor_id' => $this->cabor_id,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
