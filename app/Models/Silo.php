<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Silo extends Model
{
    use HasFactory;
    protected $fillable = [
        'numsilo',
        'produit',
        'stocki',
        'entre',
        'consumation',
        'stockf',
        'statut',
        'datevalidation',
    ];
}
