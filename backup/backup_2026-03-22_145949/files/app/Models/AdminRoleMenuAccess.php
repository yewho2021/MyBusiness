<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminRoleMenuAccess extends Model
{
    use HasFactory;

    protected $table = 'tbl_admin_role_menu_access';

    protected $fillable = [
        'role_id',
        'menu_id',
        'can_view',
        'can_create',
        'can_edit',
        'can_delete',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(AdminRole::class, 'role_id');
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(AdminMenu::class, 'menu_id');
    }
}
