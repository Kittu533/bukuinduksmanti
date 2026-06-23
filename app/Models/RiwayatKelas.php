<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatKelas extends Model
{
    protected $table = 'riwayat_kelas';
    protected $primaryKey = 'id_riwayat_kelas';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_riwayat_kelas',
        'id_siswa',
        'id_kelas_aktif',
    ];

    protected static function booted(): void
    {
        static::created(function (RiwayatKelas $riwayatKelas) {
            //
        });

        static::updated(function (RiwayatKelas $riwayatKelas) {
            //
        });
    }

    // ===========================
    // RELATIONSHIPS
    // ===========================

    public function siswa()
    {
        return $this->belongsTo(
            Siswa::class,
            'id_siswa',
            'id_siswa'
        );
    }

    public function kelasAktif()
    {
        return $this->belongsTo(
            KelasAktif::class,
            'id_kelas_aktif',
            'id_kelas_aktif'
        );
    }

    public static function generateId()
    {
        $last = self::orderBy(
            'id_riwayat_kelas',
            'desc'
        )->first();

        if (!$last) {
            return 'R230001';
        }

        $nomor = (int) substr(
            $last->id_riwayat_kelas,
            3
        );

        $nomor++;

        return 'R23' .
            str_pad(
                $nomor,
                4,
                '0',
                STR_PAD_LEFT
            );
    }
}