<?php

namespace App\Http\Controllers\Dashboard;

use App\Exceptions\Scheduling\AccountException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRequest;
use App\Http\Requests\Admin\UpdateRequest;
use App\Models\Admin;
use App\Services\AdminService;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AdminController extends Controller implements HasMiddleware
{
    public function __construct(private readonly AdminService $adminService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_admins', only: ['index']),
            new Middleware('permission:create_admins', only: ['create', 'store']),
            new Middleware('permission:edit_admins', only: ['edit', 'update']),
            new Middleware('permission:delete_admins', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        // The roles answer the policy checks of each row's edit and delete buttons (isMainAdmin()).
        $admins = Admin::query()->select(['id', 'email'])
            ->with('roles')
            ->orderBy('id')
            ->paginate(10);

        return view('admin.admins.index', compact('admins'));
    }

    public function create(): View
    {
        return view('admin.admins.create');
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $this->adminService->store($request->account());

        return to_route('admins.index')->with('success', 'Администратор успешно добавлен!');
    }

    public function edit(Admin $admin): View
    {
        Gate::authorize('update', $admin);

        return view('admin.admins.edit', compact('admin'));
    }

    public function update(UpdateRequest $request, Admin $admin, #[CurrentUser(Admin::GUARD)] Admin $actor): RedirectResponse
    {
        $this->adminService->update($admin, $request->account());

        // A new password ends the account's other sessions (AuthenticateSession). The session in
        // use stays when it is the edited account's: the middleware then stores the new hash.
        if ($admin->is($actor)) {
            Auth::guard(Admin::GUARD)->setUser($admin);
        }

        return to_route('admins.index')->with('success', 'Администратор успешно обновлён!');
    }

    public function destroy(Admin $admin, #[CurrentUser(Admin::GUARD)] Admin $actor): RedirectResponse
    {
        Gate::authorize('delete', $admin);

        try {
            $this->adminService->destroy($admin, $actor);
        } catch (AccountException $e) {
            return to_route('admins.index')->with('error', $e->getMessage());
        }

        return to_route('admins.index')->with('success', 'Администратор успешно удалён!');
    }
}
