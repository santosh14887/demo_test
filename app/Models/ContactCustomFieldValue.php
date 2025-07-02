<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactCustomFieldValue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contact_id', 'custom_field_id', 'value',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function customField()
    {
        return $this->belongsTo(CustomField::class);
    }
}
