<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'gender', 'profile_image', 'additional_file','merged_into_contact_id '
    ];

    public function customFieldValues()
    {
        return $this->hasMany(ContactCustomFieldValue::class);
    }

    public function customFields()
    {
        return $this->belongsToMany(CustomField::class, 'contact_custom_field_values')
            ->withPivot('value');
    }
}
