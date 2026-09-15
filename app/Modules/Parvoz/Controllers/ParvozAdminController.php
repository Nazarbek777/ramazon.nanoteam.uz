<?php

namespace App\Modules\Parvoz\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Parvoz\Models\ParvozBot;
use App\Modules\Parvoz\Models\ParvozGrade;
use App\Modules\Parvoz\Models\ParvozGroup;
use App\Modules\Parvoz\Models\ParvozStudent;
use App\Modules\Parvoz\Models\ParvozSubject;
use App\Modules\Parvoz\Models\ParvozTeacher;
use App\Modules\Parvoz\Services\ParvozBotService;
use Illuminate\Http\Request;

class ParvozAdminController extends Controller
{
    public function __construct(protected ParvozBotService $botService) {}

    public function dashboard()
    {
        $stats = [
            'groups'   => ParvozGroup::count(),
            'students' => ParvozStudent::count(),
            'teachers' => ParvozTeacher::count(),
            'subjects' => ParvozSubject::count(),
            'grades'   => ParvozGrade::count(),
            'linked'   => ParvozStudent::whereNotNull('telegram_id')->count(),
        ];

        $bot = ParvozBot::first();
        $recentGrades = ParvozGrade::with(['student', 'teacher', 'subject'])
            ->latest('graded_at')->limit(10)->get();

        return view('parvoz-admin.dashboard', compact('stats', 'bot', 'recentGrades'));
    }

    // ───────────────────────────────── Bot

    public function botForm()
    {
        $bot = ParvozBot::first();
        return view('parvoz-admin.bot', compact('bot'));
    }

    public function botSave(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'token' => 'required|string',
        ]);

        $bot = ParvozBot::first();
        $bot ? $bot->update($data) : $bot = ParvozBot::create($data + ['is_active' => true]);

        $result = $this->botService->setupBot($bot);

        if (!($result['ok'] ?? false)) {
            return back()->withInput()->withErrors([
                'token' => 'Telegram token yaroqsiz yoki webhook o\'rnatilmadi: ' . ($result['description'] ?? ''),
            ]);
        }

        return back()->with('success', "Bot @{$bot->username} ulandi va webhook o'rnatildi!");
    }

    public function botToggle()
    {
        $bot = ParvozBot::firstOrFail();
        $bot->update(['is_active' => !$bot->is_active]);

        return back()->with('success', $bot->is_active ? 'Bot yoqildi.' : 'Bot o\'chirildi.');
    }

    // ───────────────────────────────── Guruhlar

    public function groups()
    {
        $groups   = ParvozGroup::withCount('students')->with('teachers')->orderBy('name')->get();
        $teachers = ParvozTeacher::orderBy('full_name')->get();

        return view('parvoz-admin.groups', compact('groups', 'teachers'));
    }

    public function groupStore(Request $request)
    {
        ParvozGroup::create($request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]));

        return back()->with('success', 'Guruh qo\'shildi.');
    }

    public function groupUpdate(Request $request, ParvozGroup $group)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
            'teachers'    => 'nullable|array',
            'teachers.*'  => 'exists:parvoz_teachers,id',
        ]);

        $group->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active'   => $request->boolean('is_active'),
        ]);

        $group->teachers()->sync($data['teachers'] ?? []);

        return back()->with('success', 'Guruh yangilandi.');
    }

    public function groupDestroy(ParvozGroup $group)
    {
        $group->delete();
        return back()->with('success', 'Guruh o\'chirildi.');
    }

    // ───────────────────────────────── Fanlar

    public function subjects()
    {
        $subjects = ParvozSubject::withCount('grades')->orderBy('name')->get();
        return view('parvoz-admin.subjects', compact('subjects'));
    }

    public function subjectStore(Request $request)
    {
        ParvozSubject::create($request->validate(['name' => 'required|string|max:255']));
        return back()->with('success', 'Fan qo\'shildi.');
    }

    public function subjectDestroy(ParvozSubject $subject)
    {
        $subject->delete();
        return back()->with('success', 'Fan o\'chirildi.');
    }

    // ───────────────────────────────── O'qituvchilar

    public function teachers()
    {
        $teachers = ParvozTeacher::with('groups')->orderBy('full_name')->get();
        $groups   = ParvozGroup::orderBy('name')->get();

        return view('parvoz-admin.teachers', compact('teachers', 'groups'));
    }

    public function teacherStore(Request $request)
    {
        $data = $request->validate([
            'full_name'  => 'required|string|max:255',
            'phone'      => 'required|string|max:30',
            'groups'     => 'nullable|array',
            'groups.*'   => 'exists:parvoz_groups,id',
        ]);

        $teacher = ParvozTeacher::create([
            'full_name' => $data['full_name'],
            'phone'     => $data['phone'],
        ]);

        $teacher->groups()->sync($data['groups'] ?? []);

        return back()->with('success', 'O\'qituvchi qo\'shildi.');
    }

    public function teacherUpdate(Request $request, ParvozTeacher $teacher)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone'     => 'required|string|max:30',
            'groups'    => 'nullable|array',
            'groups.*'  => 'exists:parvoz_groups,id',
        ]);

        $teacher->update([
            'full_name' => $data['full_name'],
            'phone'     => $data['phone'],
            'is_active' => $request->boolean('is_active'),
        ]);

        $teacher->groups()->sync($data['groups'] ?? []);

        return back()->with('success', 'O\'qituvchi yangilandi.');
    }

    public function teacherDestroy(ParvozTeacher $teacher)
    {
        $teacher->delete();
        return back()->with('success', 'O\'qituvchi o\'chirildi.');
    }

    // ───────────────────────────────── O'quvchilar

    public function students(Request $request)
    {
        $query = ParvozStudent::with('groups')->withCount('grades');

        if ($groupId = $request->input('group')) {
            $query->whereHas('groups', fn ($q) => $q->where('parvoz_groups.id', $groupId));
        }

        if ($search = $request->input('q')) {
            $query->where(fn ($q) => $q->where('full_name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%"));
        }

        $students = $query->orderBy('full_name')->paginate(50)->withQueryString();
        $groups   = ParvozGroup::orderBy('name')->get();

        return view('parvoz-admin.students', compact('students', 'groups'));
    }

    public function studentStore(Request $request)
    {
        $data = $request->validate([
            'full_name'       => 'required|string|max:255',
            'phone'           => 'required|string|max:30',
            'parvoz_group_id' => 'nullable|exists:parvoz_groups,id',
        ]);

        $student = ParvozStudent::create([
            'full_name' => $data['full_name'],
            'phone'     => $data['phone'],
        ]);

        if (!empty($data['parvoz_group_id'])) {
            $student->groups()->syncWithoutDetaching([$data['parvoz_group_id']]);
        }

        return back()->with('success', 'O\'quvchi qo\'shildi.');
    }

    public function studentUpdate(Request $request, ParvozStudent $student)
    {
        $data = $request->validate([
            'full_name'       => 'required|string|max:255',
            'phone'           => 'required|string|max:30',
            'parvoz_group_id' => 'nullable|exists:parvoz_groups,id',
        ]);

        $student->update([
            'full_name' => $data['full_name'],
            'phone'     => $data['phone'],
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->has('parvoz_group_id')) {
            $student->groups()->sync(array_filter([$data['parvoz_group_id'] ?? null]));
        }

        return back()->with('success', 'O\'quvchi yangilandi.');
    }

    public function studentDestroy(ParvozStudent $student)
    {
        $student->delete();
        return back()->with('success', 'O\'quvchi o\'chirildi.');
    }

    /** Ko'p o'quvchini birdan qo'shish: "Ism Familiya, +998901234567" har qatorda */
    public function studentBulk(Request $request)
    {
        $data = $request->validate([
            'parvoz_group_id' => 'required|exists:parvoz_groups,id',
            'rows'            => 'required|string',
        ]);

        $added = 0;
        foreach (preg_split('/\r\n|\r|\n/', $data['rows']) as $line) {
            $line = trim($line);
            if ($line === '') continue;

            $parts = array_map('trim', explode(',', $line));
            if (count($parts) < 2 || $parts[0] === '' || $parts[1] === '') continue;

            $st = ParvozStudent::create([
                'full_name' => $parts[0],
                'phone'     => $parts[1],
            ]);
            $st->groups()->syncWithoutDetaching([$data['parvoz_group_id']]);
            $added++;
        }

        return back()->with('success', "{$added} ta o'quvchi qo'shildi.");
    }

    // ───────────────────────────────── Baholar

    public function grades(Request $request)
    {
        $query = ParvozGrade::with(['student.group', 'teacher', 'subject']);

        if ($groupId = $request->input('group')) {
            $query->whereHas('student.groups', fn ($q) => $q->where('parvoz_groups.id', $groupId));
        }

        if ($subjectId = $request->input('subject')) {
            $query->where('parvoz_subject_id', $subjectId);
        }

        $grades   = $query->latest('graded_at')->paginate(50)->withQueryString();
        $groups   = ParvozGroup::orderBy('name')->get();
        $subjects = ParvozSubject::orderBy('name')->get();

        return view('parvoz-admin.grades', compact('grades', 'groups', 'subjects'));
    }

    public function gradeDestroy(ParvozGrade $grade)
    {
        $grade->delete();
        return back()->with('success', 'Baho o\'chirildi.');
    }
}
