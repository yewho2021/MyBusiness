<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\InvalidatesMenuCache;

class AdminUserMenuAccess extends Model
{
    use InvalidatesMenuCache;

    protected $table = 'tbl_admin_user_menu_access';

    protected $fillable = [
        'admin_id',
        'menu_id',
        'can_view',
        'can_create',
        'can_edit',
        'can_delete',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(AdminMenu::class, 'menu_id');
    }
}
