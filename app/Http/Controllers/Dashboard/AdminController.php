<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorageRequest;
use App\Http\Requests\Admin\UpdateRequest;
use App\Models\Admin;
use App\Services\AdminService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class AdminController extends Controller

{
    protected AdminService $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->middleware(['permission:list_admins'])->only('index');
        $this->middleware(['permission:create_admins'])->only(['create', 'store']);
        $this->middleware(['permission:edit_admins'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_admins'])->only('destroy');

        $this->adminService = $adminService;
    }

    public function index(): View
    {
        $admins = Admin::query()->select(['id', 'email'])
            ->orderBy('id')
            ->paginate(10);

        return view('admin.admins.index', compact('admins'));
    }

    public function create(): View
    {
        return view('admin.admins.create');
    }

    public function store(StorageRequest $request): RedirectResponse
    {
        $this->adminService->store((array) $request->validated());

        return redirect()->route('admins.index')->with('success', 'Админ успешно добавлен!');
    }

    public function edit(Admin $admin): View
    {
        return view('admin.admins.edit', compact('admin'));
    }

    public function update(UpdateRequest $request, Admin $admin): RedirectResponse
    {
        $this->adminService->update($admin, (array) $request->validated());

        return redirect()->route('admins.index')->with('success', 'Админ успешно обновлен!');
    }

    public function destroy(Admin $admin): RedirectResponse
    {
        if ($admin->email === 'online@gmail.com') {
            return redirect()->route('admins.index')->withErrors('Вы не можете удалить админа.');
        }

        $this->adminService->destroy($admin);

        return redirect()->route('admins.index')->with('success', 'Админ успешно удален!');
    }
}
