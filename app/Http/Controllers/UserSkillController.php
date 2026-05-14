<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\UserSkill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserSkillController extends Controller
{
    public function index()
    {
        $userSkills = Auth::user()->userSkills()->with('skill.category')->get();
        return view('user_skills.index', compact('userSkills'));
    }

    public function create()
    {
        $skills = Skill::where('is_active', true)->orderBy('title')->get();
        return view('user_skills.create', compact('skills'));
    }

    public function store(Request $request)
    {
        if (empty(Auth::user()->profile->resume_url)) {
            return back()->withErrors(['error' => 'You must upload a resume to your Profile before you can list yourself as a mentor.'])->withInput();
        }

        $request->validate([
            'skill_id' => ['required_without:custom_skill', 'nullable', 'exists:skills,id'],
            'custom_skill' => ['required_without:skill_id', 'nullable', 'string', 'max:255'],
            'custom_category' => ['required_with:custom_skill', 'nullable', 'string', 'max:255'],
            'experience_level' => ['required', 'in:beginner,intermediate,advanced,expert'],
            'credits_per_hour' => ['required', 'numeric', 'min:0.5', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $skillId = $request->skill_id;

        if ($request->filled('custom_skill')) {
            $category = \App\Models\SkillCategory::firstOrCreate(
                ['name' => $request->custom_category],
                ['slug' => \Illuminate\Support\Str::slug($request->custom_category)]
            );

            $skill = \App\Models\Skill::firstOrCreate(
                ['title' => $request->custom_skill],
                [
                    'category_id' => $category->id,
                    'slug' => \Illuminate\Support\Str::slug($request->custom_skill),
                    'description' => 'User defined skill',
                    'is_active' => true
                ]
            );

            $skillId = $skill->id;
        }

        $exists = Auth::user()->userSkills()->where('skill_id', $skillId)->exists();
        if ($exists) {
            return back()->withErrors(['skill_id' => 'You are already teaching this skill.'])->withInput();
        }

        Auth::user()->userSkills()->create([
            'skill_id' => $skillId,
            'experience_level' => $request->experience_level,
            'credits_per_hour' => $request->credits_per_hour,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return redirect()->route('user-skills.index')->with('success', 'Skill listed successfully!');
    }

    public function edit($id)
    {
        $userSkill = Auth::user()->userSkills()->findOrFail($id);
        return view('user_skills.edit', compact('userSkill'));
    }

    public function update(Request $request, $id)
    {
        $userSkill = Auth::user()->userSkills()->findOrFail($id);

        $request->validate([
            'experience_level' => ['required', 'in:beginner,intermediate,advanced,expert'],
            'credits_per_hour' => ['required', 'numeric', 'min:0.5', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ]);

        $userSkill->update([
            'experience_level' => $request->experience_level,
            'credits_per_hour' => $request->credits_per_hour,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('user-skills.index')->with('success', 'Skill listing updated!');
    }

    public function destroy($id)
    {
        $userSkill = Auth::user()->userSkills()->findOrFail($id);
        $userSkill->delete();

        return redirect()->route('user-skills.index')->with('success', 'Skill removed from your profile.');
    }
}
