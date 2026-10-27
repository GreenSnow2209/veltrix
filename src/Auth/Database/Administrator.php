<?php

namespace Veltrix\Auth\Database;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Administrator extends Model implements AuthenticatableContract
{
    use Authenticatable;
    use HasPermissions;

    protected $fillable = ['username', 'password', 'name', 'avatar'];

    protected $hidden = ['password', 'remember_token'];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $connection = config('veltrix.database.connection') ?: config('admin.database.connection') ?: config('database.default');
        $this->setConnection($connection);

        $table = config('veltrix.database.users_table') ?: config('admin.database.users_table', 'admin_users');
        $this->setTable($table);

        parent::__construct($attributes);
    }

    /**
     * Get avatar attribute URL.
     *
     * @param string|null $avatar
     * @return string
     */
    public function getAvatarAttribute(?string $avatar): string
    {
        if ($avatar && url()->isValidUrl($avatar)) {
            return $avatar;
        }

        $disk = config('veltrix.upload.disk', config('admin.upload.disk', 'public'));

        if ($avatar && array_key_exists($disk, config('filesystems.disks', []))) {
            return Storage::disk($disk)->url($avatar);
        }

        $default = config('veltrix.default_avatar') ?: config('admin.default_avatar');
        if ($default) {
            return veltrix_asset($default);
        }

        // Clean SVG placeholder avatar encoded as data URI for zero broken images
        return 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="%2364748b"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>';
    }

    /**
     * A user belongs to many roles.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        $pivotTable = config('veltrix.database.role_users_table', config('admin.database.role_users_table', 'admin_role_users'));
        $relatedModel = config('veltrix.database.roles_model', config('admin.database.roles_model', Role::class));

        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'role_id')->withTimestamps();
    }

    /**
     * A user belongs to many permissions.
     *
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        $pivotTable = config('veltrix.database.user_permissions_table', config('admin.database.user_permissions_table', 'admin_user_permissions'));
        $relatedModel = config('veltrix.database.permissions_model', config('admin.database.permissions_model', Permission::class));

        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'permission_id')->withTimestamps();
    }
}
