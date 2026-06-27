<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerDocument extends Model
{
    protected $fillable = [
        'partner_id', 'document_type', 'document_number', 'file_path', 'status',
        'admin_notes', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = ['reviewed_at' => 'datetime'];

    public function partner()
    {
        return $this->belongsTo(PartnerProfile::class, 'partner_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
