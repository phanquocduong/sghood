<?php
namespace App\Http\Controllers;

use App\Services\NoteService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    protected $noteService;

    public function __construct(NoteService $noteService)
    {
        $this->noteService = $noteService;
    }

    public function index(): View|RedirectResponse
    {
        $result = $this->noteService->getAllNotes();

        if (isset($result['error'])) {
            return redirect()->route('dashboard')->with('error', $result['error']);
        }

        $notes = $result['data']->take(3);
        return view('dashboard', compact('notes'));
    }
}
