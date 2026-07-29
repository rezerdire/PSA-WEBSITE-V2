<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberPicture extends Model
{
    protected $table = 'member_pictures';

    protected $fillable = ['psa_id', 'mem_pic'];

 public function member()
    {
        return $this->belongsTo(Member::class, 'psa_id', 'member_id_no');
    }

}

