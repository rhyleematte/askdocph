<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfessionalTitle;
use Illuminate\Http\Request;

class ProfessionalTitleController extends Controller
{
    public function index()
    {
        $titles = ProfessionalTitle::orderBy('name')->get();
        return view('admin.professional_titles.index', compact('titles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:professional_titles,name',
        ]);

        ProfessionalTitle::create($request->all());

        return redirect()->route('admin.professional-titles.index')
            ->with('success', 'Professional title added successfully.');
    }

    public function update(Request $request, ProfessionalTitle $professional_title)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:professional_titles,name,' . $professional_title->id,
        ]);

        $professional_title->update($request->all());

        return redirect()->route('admin.professional-titles.index')
            ->with('success', 'Professional title updated successfully.');
    }

    public function destroy(ProfessionalTitle $professional_title)
    {
        $professional_title->delete();

        return redirect()->route('admin.professional-titles.index')
            ->with('success', 'Professional title deleted successfully.');
    }
}
