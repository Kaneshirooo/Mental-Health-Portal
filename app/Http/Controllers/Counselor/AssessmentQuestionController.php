<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\AssessmentQuestion;
use Illuminate\Http\Request;

class AssessmentQuestionController extends Controller
{
    /**
     * List all pre-assessment questions grouped for the counselor.
     */
    public function index(Request $request)
    {
        $category = $request->query('category');

        $query = AssessmentQuestion::orderBy('question_number');
        if ($category) {
            $query->where('category', $category);
        }

        $questions = $query->get();
        $categories = AssessmentQuestion::distinct()->pluck('category');
        $counts = AssessmentQuestion::selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        return view('counselor.questions.index', compact('questions', 'categories', 'counts', 'category'));
    }

    /**
     * Add a new pre-assessment question.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:100',
            'question_text' => 'required|string|max:2000',
            'question_number' => 'nullable|integer|min:1',
        ]);

        $number = $request->question_number
            ?? ((int) AssessmentQuestion::max('question_number') + 1);

        $question = AssessmentQuestion::create([
            'category' => strtolower(trim($request->category)),
            'question_text' => trim($request->question_text),
            'question_number' => $number,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Question added to the pre-assessment.',
                'question' => $question,
            ]);
        }

        return back()->with('success', 'Question added to the pre-assessment.');
    }

    /**
     * Update a pre-assessment question.
     */
    public function update(Request $request, AssessmentQuestion $question)
    {
        $request->validate([
            'category' => 'required|string|max:100',
            'question_text' => 'required|string|max:2000',
            'question_number' => 'required|integer|min:1',
        ]);

        $question->update([
            'category' => strtolower(trim($request->category)),
            'question_text' => trim($request->question_text),
            'question_number' => (int) $request->question_number,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Question updated.',
                'question' => $question->fresh(),
            ]);
        }

        return back()->with('success', 'Question updated.');
    }

    /**
     * Remove a pre-assessment question.
     */
    public function destroy(Request $request, AssessmentQuestion $question)
    {
        $question->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Question removed from the pre-assessment.',
            ]);
        }

        return back()->with('success', 'Question removed from the pre-assessment.');
    }
}
