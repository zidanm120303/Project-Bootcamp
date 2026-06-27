<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.users.admin');
    }

    public function admins()
    {
        return $this->renderRolePage('admin');
    }

    public function partners()
    {
        return $this->renderRolePage('mitra');
    }

    public function customers()
    {
        return $this->renderRolePage('customer');
    }

    public function update(User $user)
    {
        abort_if($user->id === auth()->id(), 422, 'Status akun sendiri tidak dapat diubah.');
        $data = request()->validate(['status' => ['required', 'in:active,inactive,suspended']]);
        $user->update($data);

        return back()->with('success', 'Status pengguna diperbarui.');
    }

    private function renderRolePage(string $role)
    {
        $query = User::query()
            ->where('role', $role)
            ->when(request('q'), fn ($q, $term) => $q->where(
                fn ($sub) => $sub->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
            ))
            ->when(request('status'), fn ($q, $status) => $q->where('status', $status));

        if ($role === 'mitra') {
            $query->with(['partnerProfile' => fn ($partner) => $partner->withCount(['products', 'bookings'])]);
        }

        if ($role === 'customer') {
            $query
                ->withCount('bookings')
                ->withSum(
                    ['bookings as transaction_total' => fn ($bookings) => $bookings->where('payment_status', 'paid')],
                    'total_amount'
                );
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $stats = [
            'total' => User::where('role', $role)->count(),
            'active' => User::where('role', $role)->where('status', 'active')->count(),
            'suspended' => User::where('role', $role)->where('status', 'suspended')->count(),
        ];
        $roleLabels = [
            'admin' => ['Admin', 'Kelola akun administrator dan akses operasional platform.'],
            'mitra' => ['Mitra', 'Kelola akun, verifikasi, dan aktivitas usaha mitra.'],
            'customer' => ['Customer', 'Kelola akun dan pantau aktivitas transaksi customer.'],
        ];

        return view('admin.users.role', [
            'users' => $users,
            'managedRole' => $role,
            'roleLabel' => $roleLabels[$role][0],
            'roleDescription' => $roleLabels[$role][1],
            'stats' => $stats,
        ]);
    }
}
