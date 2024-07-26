<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 * @method static findOrFail(string $id)
 * @property mixed $uuid
 */
class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'process',
        'num_lines',
        'num_lines_processed',
        'finish',
    ];
}
