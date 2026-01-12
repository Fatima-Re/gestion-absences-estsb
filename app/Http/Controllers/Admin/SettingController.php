<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    /**
     * Display all settings grouped by category
     */
    public function index()
    {
        $settings = Setting::all()->groupBy('group');
        $groups = [
            Setting::GROUP_GENERAL => 'Général',
            Setting::GROUP_ATTENDANCE => 'Absences',
            Setting::GROUP_NOTIFICATIONS => 'Notifications',
            Setting::GROUP_SYSTEM => 'Système',
        ];
        
        return view('admin.settings.index', compact('settings', 'groups'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $settings = $request->except('_token', '_method');
        
        foreach ($settings as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            
            if ($setting) {
                // Validate based on setting type
                $validator = $this->validateSettingValue($setting, $value);
                
                if ($validator->fails()) {
                    return back()->withErrors($validator)->withInput();
                }
                
                // Update setting
                $setting->update(['value' => $value]);
            }
        }
        
        return back()->with('success', 'Paramètres mis à jour avec succès.');
    }

    /**
     * Create a new setting
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string|unique:settings,key',
            'value' => 'required',
            'type' => 'required|in:string,integer,boolean,array,json',
            'description' => 'required|string|max:500',
            'group' => 'required|in:general,attendance,notifications,system',
            'is_editable' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Setting::create([
            'key' => $request->key,
            'value' => $request->value,
            'type' => $request->type,
            'description' => $request->description,
            'group' => $request->group,
            'is_editable' => $request->boolean('is_editable', true),
        ]);

        return back()->with('success', 'Paramètre créé avec succès.');
    }

    /**
     * Delete a setting
     */
    public function destroy(Setting $setting)
    {
        // Don't delete system settings
        if (!$setting->is_editable) {
            return back()->withErrors(['Ce paramètre système ne peut pas être supprimé.']);
        }

        $setting->delete();
        
        return back()->with('success', 'Paramètre supprimé avec succès.');
    }

    /**
     * Reset a setting to default
     */
    public function reset(Setting $setting)
    {
        // This would require storing default values somewhere
        // For now, just delete the setting if it's not system
        if (!$setting->is_editable) {
            return back()->withErrors(['Les paramètres système ne peuvent pas être réinitialisés.']);
        }

        $setting->delete();
        
        return back()->with('success', 'Paramètre réinitialisé.');
    }

    /**
     * Import settings from a file
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:json',
        ]);

        $content = file_get_contents($request->file('file')->getRealPath());
        $settings = json_decode($content, true);

        if (!is_array($settings)) {
            return back()->withErrors(['Le fichier JSON est invalide.']);
        }

        $imported = 0;
        $updated = 0;

        foreach ($settings as $settingData) {
            $setting = Setting::where('key', $settingData['key'])->first();
            
            if ($setting) {
                if ($setting->is_editable) {
                    $setting->update(['value' => $settingData['value']]);
                    $updated++;
                }
            } else {
                Setting::create($settingData);
                $imported++;
            }
        }

        return back()->with('success', "Importation terminée: $imported nouveaux paramètres, $updated mis à jour.");
    }

    /**
     * Export settings to a JSON file
     */
    public function export()
    {
        $settings = Setting::where('is_editable', true)->get();
        $json = json_encode($settings, JSON_PRETTY_PRINT);
        
        $headers = [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="settings-' . date('Y-m-d') . '.json"',
        ];
        
        return response($json, 200, $headers);
    }

    /**
     * Validate setting value based on its type
     */
    private function validateSettingValue(Setting $setting, $value)
    {
        $rules = [];
        
        switch ($setting->type) {
            case Setting::TYPE_INTEGER:
                $rules = ['integer'];
                break;
            case Setting::TYPE_BOOLEAN:
                $rules = ['in:true,false,1,0'];
                break;
            case Setting::TYPE_ARRAY:
            case Setting::TYPE_JSON:
                // Try to decode to validate JSON
                if (json_decode($value) === null && json_last_error() !== JSON_ERROR_NONE) {
                    return Validator::make([], [])->after(function($validator) {
                        $validator->errors()->add('value', 'Format JSON invalide.');
                    });
                }
                break;
        }
        
        return Validator::make(['value' => $value], ['value' => $rules]);
    }

    /**
     * Show form to create a new setting
     */
    public function create()
    {
        return view('admin.settings.create');
    }

    /**
     * Show form to edit a setting
     */
    public function edit(Setting $setting)
    {
        if (!$setting->is_editable) {
            return back()->withErrors(['Ce paramètre système ne peut pas être modifié.']);
        }
        
        return view('admin.settings.edit', compact('setting'));
    }
}   