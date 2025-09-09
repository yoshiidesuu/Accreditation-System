<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Parameter;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ParameterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display a listing of parameters.
     */
    public function index(Request $request)
    {
        $query = Parameter::with(['area.college', 'area.academicYear'])
            ->active()
            ->ordered();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by area
        if ($request->filled('area_id')) {
            $query->where('area_id', $request->get('area_id'));
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        // Filter by required status
        if ($request->filled('required')) {
            $query->where('required', $request->get('required') === '1');
        }

        $parameters = $query->paginate(15)->withQueryString();
        $areas = Area::with(['college', 'academicYear'])->get();
        $types = [
            'text' => 'Text Input',
            'textarea' => 'Text Area',
            'number' => 'Number Input',
            'date' => 'Date Input',
            'file' => 'File Upload',
            'select' => 'Select Dropdown',
            'checkbox' => 'Checkbox',
            'radio' => 'Radio Button',
        ];

        return view('admin.parameters.index', compact('parameters', 'areas', 'types'));
    }

    /**
     * Show the form for creating a new parameter.
     */
    public function create(Request $request)
    {
        $areas = Area::with(['college', 'academicYear'])->get();
        $selectedAreaId = $request->get('area_id');
        
        $types = [
            'text' => 'Text Input',
            'textarea' => 'Text Area',
            'number' => 'Number Input',
            'date' => 'Date Input',
            'file' => 'File Upload',
            'select' => 'Select Dropdown',
            'checkbox' => 'Checkbox',
            'radio' => 'Radio Button',
        ];

        return view('admin.parameters.create', compact('areas', 'types', 'selectedAreaId'));
    }

    /**
     * Store a newly created parameter.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:255|unique:parameters,code',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:text,textarea,number,date,file,select,checkbox,radio',
            'area_id' => 'required|exists:areas,id',
            'required' => 'boolean',
            'active' => 'boolean',
            'order' => 'integer|min:0',
            'validation_rules' => 'nullable|array',
            'options' => 'nullable|array',
        ]);

        // Custom validation for options based on type
        if (in_array($request->type, ['select', 'checkbox', 'radio'])) {
            $validator->addRules([
                'options' => 'required|array|min:1',
                'options.*' => 'required|string|max:255',
            ]);
        }

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->only([
            'code', 'title', 'description', 'type', 'area_id', 
            'required', 'active', 'order'
        ]);

        // Process validation rules
        $validationRules = [];
        if ($request->filled('validation_rules')) {
            foreach ($request->validation_rules as $rule => $value) {
                if ($value) {
                    $validationRules[$rule] = $value;
                }
            }
        }
        $data['validation_rules'] = $validationRules;

        // Process options for select, checkbox, radio
        $options = [];
        if (in_array($request->type, ['select', 'checkbox', 'radio']) && $request->filled('options')) {
            foreach ($request->options as $key => $value) {
                if (!empty($value)) {
                    $options[$key] = $value;
                }
            }
        }
        $data['options'] = $options;

        // Set defaults
        $data['required'] = $request->boolean('required');
        $data['active'] = $request->boolean('active', true);
        $data['order'] = $request->integer('order', 0);

        $parameter = Parameter::create($data);

        activity()
            ->performedOn($parameter)
            ->causedBy(auth()->user())
            ->log('Parameter created');

        return redirect()->route('admin.parameters.show', $parameter)
            ->with('success', 'Parameter created successfully.');
    }

    /**
     * Display the specified parameter.
     */
    public function show(Parameter $parameter)
    {
        $parameter->load(['area.college', 'area.academicYear', 'parameterContents']);
        
        return view('admin.parameters.show', compact('parameter'));
    }

    /**
     * Show the form for editing the specified parameter.
     */
    public function edit(Parameter $parameter)
    {
        $parameter->load(['area']);
        $areas = Area::with(['college', 'academicYear'])->get();
        
        $types = [
            'text' => 'Text Input',
            'textarea' => 'Text Area',
            'number' => 'Number Input',
            'date' => 'Date Input',
            'file' => 'File Upload',
            'select' => 'Select Dropdown',
            'checkbox' => 'Checkbox',
            'radio' => 'Radio Button',
        ];

        return view('admin.parameters.edit', compact('parameter', 'areas', 'types'));
    }

    /**
     * Update the specified parameter.
     */
    public function update(Request $request, Parameter $parameter)
    {
        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string', 'max:255', Rule::unique('parameters')->ignore($parameter->id)],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:text,textarea,number,date,file,select,checkbox,radio',
            'area_id' => 'required|exists:areas,id',
            'required' => 'boolean',
            'active' => 'boolean',
            'order' => 'integer|min:0',
            'validation_rules' => 'nullable|array',
            'options' => 'nullable|array',
        ]);

        // Custom validation for options based on type
        if (in_array($request->type, ['select', 'checkbox', 'radio'])) {
            $validator->addRules([
                'options' => 'required|array|min:1',
                'options.*' => 'required|string|max:255',
            ]);
        }

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->only([
            'code', 'title', 'description', 'type', 'area_id', 
            'required', 'active', 'order'
        ]);

        // Process validation rules
        $validationRules = [];
        if ($request->filled('validation_rules')) {
            foreach ($request->validation_rules as $rule => $value) {
                if ($value) {
                    $validationRules[$rule] = $value;
                }
            }
        }
        $data['validation_rules'] = $validationRules;

        // Process options for select, checkbox, radio
        $options = [];
        if (in_array($request->type, ['select', 'checkbox', 'radio']) && $request->filled('options')) {
            foreach ($request->options as $key => $value) {
                if (!empty($value)) {
                    $options[$key] = $value;
                }
            }
        }
        $data['options'] = $options;

        // Set defaults
        $data['required'] = $request->boolean('required');
        $data['active'] = $request->boolean('active', true);
        $data['order'] = $request->integer('order', 0);

        $parameter->update($data);

        activity()
            ->performedOn($parameter)
            ->causedBy(auth()->user())
            ->log('Parameter updated');

        return redirect()->route('admin.parameters.show', $parameter)
            ->with('success', 'Parameter updated successfully.');
    }

    /**
     * Remove the specified parameter.
     */
    public function destroy(Parameter $parameter)
    {
        // Check if parameter has content
        if ($parameter->hasContent()) {
            return redirect()->back()
                ->with('error', 'Cannot delete parameter that has content associated with it.');
        }

        activity()
            ->performedOn($parameter)
            ->causedBy(auth()->user())
            ->log('Parameter deleted');

        $parameter->delete();

        return redirect()->route('admin.parameters.index')
            ->with('success', 'Parameter deleted successfully.');
    }

    /**
     * Toggle active status of parameter.
     */
    public function toggleActive(Parameter $parameter)
    {
        $parameter->update([
            'active' => !$parameter->active
        ]);

        activity()
            ->performedOn($parameter)
            ->causedBy(auth()->user())
            ->log('Parameter status toggled to ' . ($parameter->active ? 'active' : 'inactive'));

        return redirect()->back()
            ->with('success', 'Parameter status updated successfully.');
    }

    /**
     * Get parameters by area (AJAX).
     */
    public function getByArea(Request $request)
    {
        $areaId = $request->get('area_id');
        
        if (!$areaId) {
            return response()->json([]);
        }

        $parameters = Parameter::where('area_id', $areaId)
            ->active()
            ->ordered()
            ->select('id', 'code', 'title', 'type', 'required')
            ->get();

        return response()->json($parameters);
    }

    /**
     * Reorder parameters.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'parameters' => 'required|array',
            'parameters.*.id' => 'required|exists:parameters,id',
            'parameters.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->parameters as $parameterData) {
            Parameter::where('id', $parameterData['id'])
                ->update(['order' => $parameterData['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Parameters reordered successfully.'
        ]);
    }
}