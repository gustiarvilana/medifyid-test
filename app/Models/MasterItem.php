<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['kode', 'nama', 'harga_beli', 'laba', 'supplier', 'jenis', 'photo'];

    public function kategoris()
    {
        return $this->belongsToMany(Kategori::class, 'kategori_item', 'master_item_id', 'kategori_id');
    }
}
