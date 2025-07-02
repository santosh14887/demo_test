<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomField;
use Illuminate\Support\Facades\Validator;
use App\Models\ContactCustomFieldValue;

class CustomFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $fields = CustomField::orderBy('id', 'desc')->get();
        return response()->json(['fields' => $fields]);
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
        $validator = Validator::make($request->all(), [
            'label' => 'required|string|max:255',
            'type' => 'required|string|max:50',
        ],[
            'label.required' => 'Field name is required',
            'type.required' => 'The field type is required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $field = CustomField::create($validator->validated());
        return response()->json(['message' => 'Custom field created successfully', 'field' => $field]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $field = CustomField::findOrFail($id);
        return response()->json(['field' => $field]);
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
        $field = CustomField::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'label' => 'required|string|max:255',
            'type' => 'required|string|max:50',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $field->update($validator->validated());
        return response()->json(['message' => 'Custom field updated successfully', 'field' => $field]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $field = CustomField::findOrFail($id);
        $field->delete();
       // ContactCustomFieldValue::where('custom_field_id', $id)->delete();
        return response()->json(['message' => 'Custom field deleted successfully']);
    }
}
