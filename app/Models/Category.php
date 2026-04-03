<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'type', 'color', 'parent_id'];

    /**
     * Scope for filtering by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Parent category/structure.
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Child categories/structures.
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function templates()
    {
        return $this->hasMany(Template::class);
    }
}
