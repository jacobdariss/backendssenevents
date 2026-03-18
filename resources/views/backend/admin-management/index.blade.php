@extends('backend.layouts.app')

@section('title') {{ __('messages.admin_management') }} @endsection

@section('content')

{{-- ─────────────────────────────────────────────────────────────
     SECTION 1 : AUTORISATION & RÔLE
────────────────────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header">
        <h4 class="card-title mb-0">{{ __('messages.permission_role') }}</h4>
    </div>
    <div class="card-body p-0">
        @forelse ($roles as $role)
            @if ($role->name !== 'admin' && $role->name !== 'user')
                <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                    <h6 class="mb-0 fw-semibold">{{ ucfirst($role->title) }}</h6>
                    <a href="{{ route('backend.permission-role.list') }}"
                       class="btn btn-sm btn-primary">
                        {{ __('messages.permission') }}
                    </a>
                </div>
            @endif
        @empty
            <div class="px-4 py-3 text-muted">{{ __('messages.no_record_found') }}</div>
        @endforelse
    </div>
</div>

{{-- ─────────────────────────────────────────────────────────────
     SECTION 2 : GESTIONNAIRE DE RÔLES
────────────────────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">{{ __('messages.role_manager') }}</h4>
        @can('add_users')
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
            <i class="fas fa-plus me-1"></i> {{ __('messages.add_role') }}
        </button>
        @endcan
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="role-table">
                <thead>
                    <tr>
                        <th>{{ __('messages.role') }}</th>
                        <th>{{ __('messages.users') }}</th>
                        <th class="text-end">{{ __('labels.backend.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr id="role-row-{{ $role->id }}">
                            <td>
                                <strong>{{ ucfirst($role->title) }}</strong>
                                @if($role->is_fixed)
                                    <span class="badge bg-secondary ms-1">{{ __('messages.fixed') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $role->users_count }} {{ __('messages.users') }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('backend.permission-role.list') }}"
                                   class="btn btn-sm btn-outline-primary" title="{{ __('messages.permission') }}">
                                    <i class="fas fa-shield-alt"></i>
                                </a>
                                @if(!$role->is_fixed)
                                @can('delete_users')
                                <button class="btn btn-sm btn-outline-danger ms-1"
                                        onclick="deleteRole({{ $role->id }}, this)"
                                        title="{{ __('messages.delete') }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">
                                {{ __('messages.no_record_found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ─────────────────────────────────────────────────────────────
     SECTION 3 : UTILISATEURS ADMIN
────────────────────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">{{ __('messages.admin_users') }}</h4>
        @can('add_users')
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
            <i class="fas fa-user-plus me-1"></i> {{ __('messages.add_admin_user') }}
        </button>
        @endcan
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="admin-users-table">
                <thead>
                    <tr>
                        <th>{{ __('messages.name') }}</th>
                        <th>{{ __('messages.email') }}</th>
                        <th>{{ __('messages.role') }}</th>
                        <th class="text-end">{{ __('labels.backend.action') }}</th>
                    </tr>
                </thead>
                <tbody id="admin-users-body">
                    @forelse ($adminUsers as $adminUser)
                        <tr id="admin-user-row-{{ $adminUser->id }}">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-xs rounded-circle bg-primary d-flex align-items-center justify-content-center text-white"
                                         style="width:32px;height:32px;font-size:13px;flex-shrink:0;">
                                        {{ strtoupper(substr($adminUser->full_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong>{{ $adminUser->full_name }}</strong>
                                        @if($adminUser->id === auth()->id())
                                            <span class="badge bg-success ms-1">{{ __('messages.you') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted">{{ $adminUser->email }}</td>
                            <td>
                                @php $userRole = $adminUser->getRoleNames()->first() @endphp
                                <select class="form-select form-select-sm role-select"
                                        style="min-width:130px;"
                                        data-user-id="{{ $adminUser->id }}"
                                        onchange="updateUserRole({{ $adminUser->id }}, this.value)">
                                    @foreach($availableRoles as $r)
                                        <option value="{{ $r->name }}"
                                            {{ $userRole === $r->name ? 'selected' : '' }}>
                                            {{ ucfirst($r->title) }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="text-end">
                                @if($adminUser->id !== auth()->id() && !$adminUser->hasRole('admin'))
                                @can('delete_users')
                                <button class="btn btn-sm btn-outline-danger"
                                        onclick="deleteAdmin({{ $adminUser->id }}, this)"
                                        title="{{ __('messages.delete') }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                                @else
                                    <span class="text-muted small">–</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr id="no-admin-row">
                            <td colspan="4" class="text-center text-muted py-3">
                                {{ __('messages.no_record_found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ─────────────────────────────────────────────────────────────
     MODAL : AJOUTER UN RÔLE
────────────────────────────────────────────────────────────── --}}
<div class="modal fade" id="addRoleModal" tabindex="-1" aria-labelledby="addRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRoleModalLabel">
                    {{ __('messages.add_role') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('page.lbl_name') }} <span class="text-danger">*</span></label>
                    <input type="text" id="role-title" class="form-control"
                           placeholder="{{ __('messages.enter_role_name') }}">
                    <div id="role-title-error" class="text-danger small mt-1 d-none"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('page.lbl_import') }}</label>
                    <select id="import-role" class="form-select">
                        <option value="">— {{ __('messages.none') }} —</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}">{{ ucfirst($r->title) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ __('messages.cancel') }}
                </button>
                <button type="button" class="btn btn-primary" id="save-role-btn" onclick="saveRole()">
                    {{ __('messages.save') }}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ─────────────────────────────────────────────────────────────
     MODAL : AJOUTER UN ADMIN
────────────────────────────────────────────────────────────── --}}
<div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAdminModalLabel">
                    {{ __('messages.add_admin_user') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('messages.name') }} <span class="text-danger">*</span></label>
                    <input type="text" id="admin-name" class="form-control"
                           placeholder="{{ __('messages.enter_name') }}">
                    <div id="admin-name-error" class="text-danger small mt-1 d-none"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('messages.email') }} <span class="text-danger">*</span></label>
                    <input type="email" id="admin-email" class="form-control"
                           placeholder="{{ __('messages.enter_email') }}">
                    <div id="admin-email-error" class="text-danger small mt-1 d-none"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('messages.role') }} <span class="text-danger">*</span></label>
                    <select id="admin-role" class="form-select">
                        <option value="">— {{ __('messages.select_role') }} —</option>
                        @foreach($availableRoles as $r)
                            @if($r->name !== 'admin')
                            <option value="{{ $r->name }}">{{ ucfirst($r->title) }}</option>
                            @endif
                        @endforeach
                    </select>
                    <div id="admin-role-error" class="text-danger small mt-1 d-none"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('messages.password') }} <span class="text-danger">*</span></label>
                    <input type="password" id="admin-password" class="form-control"
                           placeholder="{{ __('messages.min_8_chars') }}">
                    <div id="admin-password-error" class="text-danger small mt-1 d-none"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ __('messages.cancel') }}
                </button>
                <button type="button" class="btn btn-primary" id="save-admin-btn" onclick="saveAdmin()">
                    {{ __('messages.save') }}
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('after-scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// ─── RÔLES ───────────────────────────────────────────
function saveRole() {
    const title       = document.getElementById('role-title').value.trim();
    const importRole  = document.getElementById('import-role').value;
    const btn         = document.getElementById('save-role-btn');
    const errEl       = document.getElementById('role-title-error');

    errEl.classList.add('d-none');
    if (!title) { errEl.textContent = '{{ __("validation.required", ["attribute" => "name"]) }}'; errEl.classList.remove('d-none'); return; }

    btn.disabled = true;
    btn.textContent = '...';

    fetch('{{ route("backend.admin-management.roles.store") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ title, import_role: importRole })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status) {
            successSnackbar(res.message);
            bootstrap.Modal.getInstance(document.getElementById('addRoleModal')).hide();
            document.getElementById('role-title').value = '';
            document.getElementById('import-role').value = '';
            addRoleRow(res.data);
        } else {
            errorSnackbar(res.message || '{{ __("messages.error_occurred") }}');
        }
    })
    .catch(() => errorSnackbar('{{ __("messages.error_occurred") }}'))
    .finally(() => { btn.disabled = false; btn.textContent = '{{ __("messages.save") }}'; });
}

function addRoleRow(role) {
    const tbody = document.querySelector('#role-table tbody');
    const noRow = document.getElementById('no-role-row');
    if (noRow) noRow.remove();

    const tr = document.createElement('tr');
    tr.id = 'role-row-' + role.id;
    tr.innerHTML = `
        <td><strong>${role.title}</strong></td>
        <td><span class="badge bg-light text-dark border">0 {{ __('messages.users') }}</span></td>
        <td class="text-end">
            <a href="{{ route('backend.permission-role.list') }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-shield-alt"></i>
            </a>
            <button class="btn btn-sm btn-outline-danger ms-1" onclick="deleteRole(${role.id}, this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>`;
    tbody.appendChild(tr);

    // add to admin modal role select
    const selects = document.querySelectorAll('#admin-role, #import-role');
    selects.forEach(sel => {
        const opt = document.createElement('option');
        opt.value = role.name;
        opt.textContent = role.title;
        sel.appendChild(opt);
    });

    // add to role select in table
    document.querySelectorAll('.role-select').forEach(sel => {
        const opt = document.createElement('option');
        opt.value = role.name;
        opt.textContent = role.title;
        sel.appendChild(opt);
    });
}

function deleteRole(id, btn) {
    if (!confirm('{{ __("messages.are_you_sure") }}')) return;
    btn.disabled = true;

    fetch('{{ url("app/admin-management/roles") }}/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF }
    })
    .then(r => r.json())
    .then(res => {
        if (res.status) {
            document.getElementById('role-row-' + id)?.remove();
            successSnackbar(res.message);
        } else {
            errorSnackbar(res.message);
            btn.disabled = false;
        }
    })
    .catch(() => { errorSnackbar('{{ __("messages.error_occurred") }}'); btn.disabled = false; });
}

// ─── ADMIN USERS ─────────────────────────────────────
function saveAdmin() {
    const name     = document.getElementById('admin-name').value.trim();
    const email    = document.getElementById('admin-email').value.trim();
    const role     = document.getElementById('admin-role').value;
    const password = document.getElementById('admin-password').value;
    const btn      = document.getElementById('save-admin-btn');

    ['name','email','role','password'].forEach(f => {
        document.getElementById('admin-' + f + '-error').classList.add('d-none');
    });

    let valid = true;
    if (!name)     { showErr('admin-name-error', '{{ __("validation.required", ["attribute" => "name"]) }}');     valid = false; }
    if (!email)    { showErr('admin-email-error', '{{ __("validation.required", ["attribute" => "email"]) }}');   valid = false; }
    if (!role)     { showErr('admin-role-error', '{{ __("validation.required", ["attribute" => "role"]) }}');     valid = false; }
    if (!password) { showErr('admin-password-error', '{{ __("validation.required", ["attribute" => "password"]) }}'); valid = false; }
    if (!valid) return;

    btn.disabled = true;
    btn.textContent = '...';

    fetch('{{ route("backend.admin-management.users.store") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ name, email, role, password })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status) {
            successSnackbar(res.message);
            bootstrap.Modal.getInstance(document.getElementById('addAdminModal')).hide();
            ['name','email','password'].forEach(f => document.getElementById('admin-' + f).value = '');
            document.getElementById('admin-role').value = '';
            addAdminRow(res.data);
        } else {
            errorSnackbar(res.message || '{{ __("messages.error_occurred") }}');
        }
    })
    .catch(() => errorSnackbar('{{ __("messages.error_occurred") }}'))
    .finally(() => { btn.disabled = false; btn.textContent = '{{ __("messages.save") }}'; });
}

function addAdminRow(user) {
    const tbody = document.getElementById('admin-users-body');
    const noRow = document.getElementById('no-admin-row');
    if (noRow) noRow.remove();

    const rolesOptions = Array.from(document.querySelector('.role-select')?.options || [])
        .map(o => `<option value="${o.value}"${o.value === user.role ? ' selected' : ''}>${o.text}</option>`).join('');

    const tr = document.createElement('tr');
    tr.id = 'admin-user-row-' + user.id;
    tr.innerHTML = `
        <td>
            <div class="d-flex align-items-center gap-2">
                <div class="avatar-xs rounded-circle bg-primary d-flex align-items-center justify-content-center text-white"
                     style="width:32px;height:32px;font-size:13px;flex-shrink:0;">
                    ${user.name.charAt(0).toUpperCase()}
                </div>
                <strong>${user.name}</strong>
            </div>
        </td>
        <td class="text-muted">${user.email}</td>
        <td>
            <select class="form-select form-select-sm role-select" style="min-width:130px;"
                    data-user-id="${user.id}"
                    onchange="updateUserRole(${user.id}, this.value)">
                ${rolesOptions}
            </select>
        </td>
        <td class="text-end">
            <button class="btn btn-sm btn-outline-danger" onclick="deleteAdmin(${user.id}, this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>`;
    tbody.appendChild(tr);
}

function updateUserRole(userId, role) {
    fetch('{{ url("app/admin-management/users") }}/' + userId + '/role', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ role })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status) successSnackbar(res.message);
        else errorSnackbar(res.message);
    })
    .catch(() => errorSnackbar('{{ __("messages.error_occurred") }}'));
}

function deleteAdmin(id, btn) {
    if (!confirm('{{ __("messages.are_you_sure") }}')) return;
    btn.disabled = true;

    fetch('{{ url("app/admin-management/users") }}/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF }
    })
    .then(r => r.json())
    .then(res => {
        if (res.status) {
            document.getElementById('admin-user-row-' + id)?.remove();
            successSnackbar(res.message);
        } else {
            errorSnackbar(res.message);
            btn.disabled = false;
        }
    })
    .catch(() => { errorSnackbar('{{ __("messages.error_occurred") }}'); btn.disabled = false; });
}

function showErr(id, msg) {
    const el = document.getElementById(id);
    el.textContent = msg;
    el.classList.remove('d-none');
}
</script>
@endpush
