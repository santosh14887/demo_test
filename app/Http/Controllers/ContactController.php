<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\ContactMerge;
use App\Models\CustomField;
use App\Models\ContactCustomFieldValue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Filtering
            $query = Contact::query();
            if ($request->filled('name')) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }
            if ($request->filled('email')) {
                $query->where('email', 'like', '%' . $request->email . '%');
            }
            if ($request->filled('gender')) {
                $query->where('gender', $request->gender);
            }
            $contacts = $query->whereNull('merged_into_contact_id')->with(['customFieldValues.customField'])->orderBy('id', 'desc')->get();
           
            return response()->json(['contacts' => $contacts]);
        }

        return view('contacts.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Base validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,other',
            'profile_image' => 'nullable|mimes:jpg,png|max:2048',
            'additional_file' => 'nullable|mimes:pdf,docx|max:5120',
        ];

        // Add dynamic validation for custom fields
        $customFields = CustomField::all();
        foreach ($customFields as $field) {
            $rule = 'nullable|string'; // Default rule
            if ($field->type === 'number') {
                $rule = 'nullable|numeric';
            } elseif ($field->type === 'date') {
                $rule = 'nullable|date';
            }
            $rules['custom_fields.' . $field->id] = $rule;
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();
        // Handle file uploads using helpers
        
         if ($request->hasFile('profile_image')) {
            $profile_img = $request->file('profile_image');
            $img_name = uploadFiles($profile_img);
            $data['profile_image'] = $img_name;
        }
        if ($request->hasFile('additional_file')) {
            $additional_img = $request->file('additional_file');
            $img_name = uploadFiles($additional_img);
            $data['additional_file'] = $img_name;
        }
        $contact = Contact::create($data);
        
        // Handle custom fields
        if ($request->has('custom_fields')) {
            foreach ($request->custom_fields as $fieldId => $value) {
                if ($value !== null) {
                    ContactCustomFieldValue::create([
                        'contact_id' => $contact->id,
                        'custom_field_id' => $fieldId,
                        'value' => $value,
                    ]);
                }
            }
        }
        
        return response()->json(['message' => 'Contact created successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $contact = Contact::with(['customFieldValues.customField'])->findOrFail($id);
        return response()->json(['contact' => $contact]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);
        
        // Base validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,other',
            'profile_image' => 'nullable|mimes:jpg,png|max:2048',
            'additional_file' => 'nullable|mimes:pdf,docx|max:5120',
        ];

        // Add dynamic validation for custom fields
        $customFields = CustomField::all();
        foreach ($customFields as $field) {
            $rule = 'nullable|string'; // Default rule
            if ($field->type === 'number') {
                $rule = 'nullable|numeric';
            } elseif ($field->type === 'date') {
                $rule = 'nullable|date';
            }
            $rules['custom_fields.' . $field->id] = $rule;
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();
        // Handle file uploads
        if ($request->hasFile('profile_image')) {
            $profile_img = $request->file('profile_image');
            $img_name = uploadFiles($profile_img,$contact->profile_image);
            $data['profile_image'] = $img_name;
        }
        if ($request->hasFile('additional_file')) {
            $additional_img = $request->file('additional_file');
            $img_name = uploadFiles($additional_img,$contact->additional_file);
            $data['additional_file'] = $img_name;
        }
        $contact->update($data);
        
        // Handle custom fields
        if ($request->has('custom_fields')) {
            foreach ($request->custom_fields as $fieldId => $value) {
                $cfv = ContactCustomFieldValue::firstOrNew([
                    'contact_id' => $contact->id,
                    'custom_field_id' => $fieldId,
                ]);
                if ($value !== null) {
                    $cfv->value = $value;
                    $cfv->save();
                } elseif ($cfv->exists) {
                    $cfv->delete();
                }
            }
        }
        
        return response()->json(['message' => 'Contact updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();
        return response()->json(['message' => 'Contact deleted successfully']);
    }

    /**
     * Return a list of contacts for merging (excluding the given contact).
     */
    public function mergelist(Request $request)
    {
        $excludeId = $request->input('exclude');

        $contacts = Contact::where('id', '!=', $excludeId)
            ->whereNull('merged_into_contact_id') // Only active contacts
            ->get(['id', 'name', 'email']);
        return response()->json(['contacts' => $contacts]); // ✅ Return actual data
    }

    /**
     * Return data for both contacts for merge preview.
     */
    public function mergePreview(Request $request)
    {
        $primaryId = $request->input('primary');
        $secondaryId = $request->input('secondary');

        $primary = Contact::with(['customFieldValues.customField'])->find($primaryId);
        $secondary = Contact::with(['customFieldValues.customField'])->find($secondaryId);

        if (!$primary || !$secondary) {
            return response()->json(['message' => 'Contact not found.'], 404);
        }

        return response()->json([
            'primary' => $primary,
            'secondary' => $secondary,
        ]);
    }

    /**
     * Perform the merge of two contacts.
     */
    public function merge(Request $request)
    {
        $masterId = $request->input('master_id');
        $secondaryId = $request->input('secondary_id');

        $master = Contact::with('customFieldValues.customField')->find($masterId);
        $secondary = Contact::with('customFieldValues.customField')->find($secondaryId);

        if (!$master || !$secondary) {
            return response()->json(['message' => 'Contact not found.'], 404);
        }

        // --- Emails ---
        $merged_emails = [];
        if ($master->email) {
            $merged_emails[] = [
                'value' => $master->email,
                'source' => 'master',
                'is_final' => true
            ];
        }
        if ($secondary->email && $secondary->email !== $master->email) {
            $merged_emails[] = [
                'value' => $secondary->email,
                'source' => 'secondary',
                'is_final' => false
            ];
        }

        // --- Phones ---
        $merged_phones = [];
        if ($master->phone) {
            $merged_phones[] = [
                'value' => $master->phone,
                'source' => 'master',
                'is_final' => true
            ];
        }
        if ($secondary->phone && $secondary->phone !== $master->phone) {
            $merged_phones[] = [
                'value' => $secondary->phone,
                'source' => 'secondary',
                'is_final' => false
            ];
        }

        // --- Custom Fields ---
        $masterFields = $master->customFieldValues->keyBy('custom_field_id');
        $secondaryFields = $secondary->customFieldValues->keyBy('custom_field_id');
        $allFieldIds = $masterFields->keys()->merge($secondaryFields->keys())->unique();
        $merged_custom_fields = [];
        foreach ($allFieldIds as $fieldId) {
            $masterValue = $masterFields[$fieldId]->value ?? null;
            $secondaryValue = $secondaryFields[$fieldId]->value ?? null;
            $fieldLabel = $masterFields[$fieldId]->customField->label ?? $secondaryFields[$fieldId]->customField->label ?? '';
            $conflict = $masterValue !== null && $secondaryValue !== null && $masterValue !== $secondaryValue;
            $finalValue = $masterValue ?? $secondaryValue;
            $merged_custom_fields[] = [
                'field_id' => $fieldId,
                'field_label' => $fieldLabel,
                'master_value' => $masterValue,
                'secondary_value' => $secondaryValue,
                'final_value' => $finalValue,
                'conflict' => $conflict
            ];
            // If master lacks the field, copy from secondary
            if ($masterValue === null && $secondaryValue !== null) {
                $newValue = $secondaryFields[$fieldId]->replicate();
                $newValue->contact_id = $master->id;
                $newValue->save();
            }
        }

        // Save merge record using Eloquent model
        $mergeRecord = ContactMerge::create([
            'master_contact_id' => $master->id,
            'secondary_contact_id' => $secondary->id,
            'merged_emails' => $merged_emails,
            'merged_phones' => $merged_phones,
            'merged_custom_fields' => $merged_custom_fields,
        ]);

        // Mark secondary as merged
        $secondary->merged_into_contact_id = $master->id;
        $secondary->save();

        return response()->json([
            'success' => true,
            'merge_id' => $mergeRecord->id,
            'merged_emails' => $merged_emails,
            'merged_phones' => $merged_phones,
            'merged_custom_fields' => $merged_custom_fields,
        ]);
    }
    
    /**
     * Show merged data for a given master contact.
     */
    public function mergedData($master_contact_id)
    {
        $merges = ContactMerge::where('master_contact_id', $master_contact_id)->orderByDesc('id')->get();
        if ($merges->isEmpty()) {
            return response()->json(['message' => 'No merged data found.'], 404);
        }
        // Collect all emails and phones, remove duplicates
        $all_emails = collect();
        $all_phones = collect();
        $all_custom_fields = collect();
        foreach ($merges as $merge) {
            $all_emails = $all_emails->merge($merge->merged_emails ?? []);
            $all_phones = $all_phones->merge($merge->merged_phones ?? []);
            $all_custom_fields = $all_custom_fields->merge($merge->merged_custom_fields ?? []);
        }
        // Remove duplicate emails by value
        $unique_emails = $all_emails->unique('value')->values()->map(function($item) {
            return [
                'value' => $item['value'],
                'is_final' => $item['is_final'] ?? false,
                'source' => $item['source'] ?? '',
            ];
        });
        // Remove duplicate phones by value
        $unique_phones = $all_phones->unique('value')->values()->map(function($item) {
            return [
                'value' => $item['value'],
                'is_final' => $item['is_final'] ?? false,
                'source' => $item['source'] ?? '',
            ];
        });
        // Keep all custom fields as is (array of all merged_custom_fields from all merges)
        $all_custom_fields = $all_custom_fields->values();

        // Group custom fields by field_label and collect unique values
        $grouped_custom_fields = $all_custom_fields->groupBy('field_label')->map(function($items, $label) {
            $values = collect();
            $conflict = false;
            foreach ($items as $item) {
                // Collect all possible values
                if (isset($item['master_value']) && $item['master_value'] !== null) $values->push($item['master_value']);
                if (isset($item['secondary_value']) && $item['secondary_value'] !== null) $values->push($item['secondary_value']);
                if (isset($item['final_value']) && $item['final_value'] !== null) $values->push($item['final_value']);
                if (!empty($item['conflict'])) $conflict = true;
            }
            $unique_values = $values->unique()->values();
            return [
                'field_label' => $label,
                'values' => $unique_values,
                'conflict' => $conflict,
            ];
        })->values();
        return response()->json([
            'merged_emails' => $unique_emails,
            'merged_phones' => $unique_phones,
            'merged_custom_fields' => $grouped_custom_fields,
        ]);
    }
}
