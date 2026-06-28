<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerDocument extends Model
{
    protected $fillable = [
        'partner_id', 'document_type', 'document_name', 'document_number', 'issued_at',
        'expires_at', 'is_required', 'file_path', 'status',
        'admin_notes', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'expires_at' => 'date',
        'is_required' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    public function partner()
    {
        return $this->belongsTo(PartnerProfile::class, 'partner_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
