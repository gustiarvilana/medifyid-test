<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kategori extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['nama', 'kode'];

    public function items()
    {
        return $this->belongsToMany(MasterItem::class, 'kategori_item', 'kategori_id', 'master_item_id');
    }
}
