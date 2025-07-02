<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomField extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'label', 'type', 'options',
    ];

    public function values()
    {
        return $this->hasMany(ContactCustomFieldValue::class);
    }

    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'contact_custom_field_values')
            ->withPivot('value');
    }
}
