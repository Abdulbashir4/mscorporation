<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockAttribute extends Model
{
    public const TYPES = [
        'brand' => 'Brand',
        'category' => 'Category',
        'subcategory' => 'Subcategory',
        'model' => 'Model',
    ];

    protected $fillable = ['name', 'type', 'parent_id'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('name');
    }
}
