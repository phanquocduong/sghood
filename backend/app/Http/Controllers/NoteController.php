<?php

namespace App\Http\Controllers;

use App\Services\NoteService;
use App\Http\Requests\NoteRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\User;

class NoteController extends Controller
{
    protected $noteService;

    public function __construct(NoteService $noteService)
    {
        $this->noteService = $noteService;
    }

    // Hiển thị danh sách ghi chú.
    public function index(Request $request): View|RedirectResponse
    {
        $querySearch = $request->query('query', '');
        $userId = $request->query('user_id', ''); // Thêm user_id filter
        $type = $request->query('type', '');
        $sortOption = $request->query('sortOption', 'created_at_desc');
        $perPage = $request->query('perPage', 10);

        $result = $this->noteService->fetchNotes(
            $querySearch, $userId, $type, $sortOption, $perPage // Thêm $userId
        );

        if (isset($result['error'])) {
            return redirect()->route('dashboard')->with('error', $result['error']);
        }

        $usersResult = $this->noteService->getUsersWithNotes();
        $users = $usersResult['data'] ?? collect([]);

        $typesResult = $this->noteService->getNoteTypes();
        $types = $typesResult['data'] ?? collect([]);

        return view('notes.index', [
            'notes' => $result['data'],
            'querySearch' => $querySearch,
            'userId' => $userId, // Thêm userId cho view
            'type' => $type,
            'sortOption' => $sortOption,
            'perPage' => $perPage,
            'users' => $users,
            'types' => $types,
        ]);
    }

    // Xử lý lỗi từ service và chuyển hướng phù hợp
    private function handleServiceError($result, $inputData = [])
    {
        if (isset($result['error'])) {
            return redirect()->back()->withErrors(['error' => $result['error']])->withInput($inputData);
        }
        return null;
    }

    // Lấy danh sách người dùng đang hoạt động
    private function getActiveUsers()
    {
        return User::where('status', 'Hoạt động')->get();
    }

    // Hiển thị chi tiết ghi chú.
    public function show(string $id): View|RedirectResponse
    {
        $result = $this->noteService->getNote($id);

        if (isset($result['error'])) {
            return redirect()->route('notes.index')->with('error', $result['error']);
        }

        return view('notes.show', ['note' => $result['data']]);
    }

    // Hiển thị form tạo ghi chú mới.
    public function create(): View
    {
        return view('notes.create', [
            'users' => $this->getActiveUsers()
        ]);
    }

    // Tạo ghi chú mới.
    public function store(NoteRequest $request): RedirectResponse
    {
        $result = $this->noteService->createNote($request->validated());

        $errorResponse = $this->handleServiceError($result, $request->validated());
        if ($errorResponse) {
            return $errorResponse;
        }

        return redirect()
            ->route('notes.index')
            ->with('success', 'Ghi chú đã được tạo thành công!');
    }

    public function storeDashboard(NoteRequest $request): RedirectResponse
    {
        $result = $this->noteService->createNote($request->validated());

        $errorResponse = $this->handleServiceError($result, $request->validated());
        if ($errorResponse) {
            return $errorResponse;
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Ghi chú đã được tạo thành công!');
    }

    // Xóa ghi chú.
    public function destroy(string $id): RedirectResponse
    {
        $result = $this->noteService->deleteNote($id);

        $errorResponse = $this->handleServiceError($result);
        if ($errorResponse) {
            return $errorResponse;
        }

        return redirect()
            ->route('notes.index')
            ->with('success', 'Ghi chú đã được xóa thành công!');
    }
}
