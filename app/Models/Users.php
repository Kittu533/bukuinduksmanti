<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Users extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id_users';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_users',
        'name',
        'username',
        'email',
        'password',
        'role',
        'id_guru',
        'id_siswa',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ===========================
    // RELATIONSHIPS
    // ===========================

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'id_guru', 'id_guru');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }

    // ===========================
    // HELPER
    // ===========================

    public static function generateId(): string
    {
        $last = static::orderBy('id_users', 'desc')->first();

        if ($last) {
            $number = (int) substr($last->id_users, 1) + 1;
        } else {
            $number = 1;
        }

        return 'A' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
