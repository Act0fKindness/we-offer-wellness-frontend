<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStatus extends Model
{
    use HasFactory;

    protected $table = 'product_status'; // Existing table name — keep if unchanged

    protected $fillable = [
        'status',
    ];

    /**
     * Table data:
     *
     * +----+------------------+---------------------+---------------------+
     * | id | status           | created_at          | updated_at          |
     * +----+------------------+---------------------+---------------------+
     * |  1 | draft            | 2025-01-28 13:36:58 | 2025-01-28 13:36:58 |
     * |  2 | pending_review   | 2025-01-28 13:36:58 | 2025-01-28 13:36:58 |
     * |  3 | needs_changes    | 2025-01-28 13:36:58 | 2025-01-28 13:36:58 |
     * |  4 | approved         | 2025-01-28 13:36:58 | 2025-01-28 13:36:58 |
     * |  5 | live             | 2025-01-28 13:36:58 | 2025-01-28 13:36:58 |
     * |  6 | edit_requested   | 2025-01-28 13:36:58 | 2025-01-28 13:36:58 |
     * |  7 | in_edit          | 2025-01-28 13:36:58 | 2025-01-28 13:36:58 |
     * |  8 | rejected         | 2025-01-28 13:36:58 | 2025-01-28 13:36:58 |
     * |  9 | archived         | 2025-01-28 13:36:58 | 2025-01-28 13:36:58 |
     * +----+------------------+---------------------+---------------------+
     */

    public function products()
    {
        return $this->hasMany(Product::class, 'product_status_id');
    }
}
