<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMerge extends Model
{
    use HasFactory;

    protected $table = 'contact_merges';

    protected $fillable = [
        'master_contact_id',
        'secondary_contact_id',
        'merged_emails',
        'merged_phones',
        'merged_custom_fields',
    ];

    protected $casts = [
        'merged_emails' => 'array',
        'merged_phones' => 'array',
        'merged_custom_fields' => 'array',
    ];

    public function masterContact()
    {
        return $this->belongsTo(Contact::class, 'master_contact_id');
    }

    public function secondaryContact()
    {
        return $this->belongsTo(Contact::class, 'secondary_contact_id');
    }
} 