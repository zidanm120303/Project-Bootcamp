<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    protected $fillable = [
        'booking_id', 'reporter_id', 'issue_type', 'description', 'evidence_file',
        'status', 'admin_notes', 'resolved_at',
    ];

    protected $casts = ['resolved_at' => 'datetime'];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }
}
