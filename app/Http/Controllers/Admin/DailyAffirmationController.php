<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyAffirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DailyAffirmationController extends Controller
{
    public function index()
    {
        $affirmations = DailyAffirmation::query()
            ->with('creator')
            ->orderByDesc('is_published')
            ->orderByRaw('CASE WHEN publish_at IS NULL THEN 1 ELSE 0 END')
            ->orderByDesc('publish_at')
            ->orderByDesc('created_at')
            ->get();

        $currentAffirmation = DailyAffirmation::current();

        $affirmations->each(function($aff) use ($currentAffirmation) {
            $aff->is_current_live = ($currentAffirmation && $currentAffirmation->id === $aff->id);
        });

        return view('admin.daily_affirmations.index', compact('affirmations', 'currentAffirmation'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['created_by_admin_id'] = Auth::guard('admin')->id();

        DailyAffirmation::create($data);

        return redirect()
            ->route('admin.daily-affirmations.index')
            ->with('success', 'Daily affirmation created successfully.');
    }

    public function update(Request $request, DailyAffirmation $daily_affirmation)
    {
        $daily_affirmation->update($this->validatedData($request));

        return redirect()
            ->route('admin.daily-affirmations.index')
            ->with('success', 'Daily affirmation updated successfully.');
    }

    public function destroy(DailyAffirmation $daily_affirmation)
    {
        $daily_affirmation->delete();

        return redirect()
            ->route('admin.daily-affirmations.index')
            ->with('success', 'Daily affirmation deleted successfully.');
    }

    public function publishNow(DailyAffirmation $daily_affirmation)
    {
        $daily_affirmation->update([
            'is_published' => true,
            'publish_at' => now(),
        ]);

        return redirect()
            ->route('admin.daily-affirmations.index')
            ->with('success', 'Daily affirmation has been posted for today.');
    }

    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'quote' => 'required|string|max:2000',
            'author' => 'nullable|string|max:255',
            'publish_state' => ['required', Rule::in(['draft', 'publish_now', 'scheduled'])],
            'scheduled_for' => 'nullable|date|after:now|required_if:publish_state,scheduled',
        ]);

        $data = [
            'quote' => trim($validated['quote']),
            'author' => isset($validated['author']) && trim($validated['author']) !== ''
                ? trim($validated['author'])
                : null,
        ];

        if ($validated['publish_state'] === 'draft') {
            $data['is_published'] = false;
            $data['publish_at'] = null;
        } elseif ($validated['publish_state'] === 'publish_now') {
            $data['is_published'] = true;
            $data['publish_at'] = now();
        } else {
            $data['is_published'] = true;
            $data['publish_at'] = $validated['scheduled_for'];
        }

        return $data;
    }
}
