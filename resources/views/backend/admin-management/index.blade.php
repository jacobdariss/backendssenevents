@extends('backend.layouts.app')

@section('title') {{ __('messages.admin_management') }} @endsection

@section('content')

{{-- SECTION 1 : AUTORISATION & RÔLE --}}
<div class="card mb-4">
    <div class="card-header">
        <h4 class="card-title mb-0">{{ __('messages.permission_role') }}</h4>
    </div>
    <div class="card-body p-0">
        @forelse ($roles as $role)
            @if ($role->name !== 'admin' && $role->name !== 'user')
            <div class="border-bottom">
                <div class="d-flex align-items-center justify-content-between px-4 py-3"
                     style="cursor:pointer;"
                     data-bs-toggle="collapse"
                     data-bs-target="#perm-collapse-{{ $role->id }}">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-chevron-right text-muted" id="chevron-{{ $role->id }}"></i>
                        <h6 class="mb-0 fw-semibold">{{ ucfirst($role->title) }}</h6>
                        @if($role->is_fixed)
                            <span class="badge bg-secondary">{{ __('messages.fixed') }}</span>
                        @endif
                    </div>
                    <span class="badge bg-primary">{{ __('messages.permission') }}</span>
                </div>

                <div class="collapse" id="perm-collapse-{{ $role->id }}">
                    <form method="POST"
                          action="{{ route('backend.permission-role.store', $role->id) }}"
                          class="perm-form"
                          data-role-id="{{ $role->id }}">
                        @csrf
                        <div class="table-responsive px-4 pb-3">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="min-width:160px;">{{ __('messages.modules') }}</th>
                                        <th class="text-center">{{ __('messages.view') }}</th>
                                        <th class="text-center">{{ __('messages.add') }}</th>
                                        <th class="text-center">{{ __('messages.edit') }}</th>
                                        <th class="text-center">{{ __('messages.delete') }}</th>
                                        <th class="text-center">{{ __('messages.restore') }}</th>
                                        <th class="text-center">{{ __('messages.force_delete') }}</th>
                                        <th class="text-end pe-3" style="min-width:130px;">
                                            <button type="button" class="btn btn-sm btn-secondary"
                                                    onclick="savePermissions({{ $role->id }}, this)">
                                                {{ __('messages.save') }}
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($modules as $mKey => $module)
                                        @if(isset($module['is_custom_permission']) && !$module['is_custom_permission'])
                                        @php $mName = strtolower(str_replace(' ', '_', $module['module_name'])); @endphp
                                        <tr>
                                            <td class="fw-medium">{{ ucwords(str_replace('_', ' ', $module['module_name'])) }}</td>
                                            @foreach(['view', 'add', 'edit', 'delete', 'restore', 'force_delete'] as $perm)
                                            <td class="text-center">
                                                <input type="checkbox"
                                                       class="form-check-input perm-check"
                                                       name="permission[{{ $perm }}_{{ $mName }}][]"
                                                       value="{{ $role->name }}"
                                                       {{ \App\Helpers\AuthHelper::checkRolePermission($role, $perm . '_' . $mName) ? 'checked' : '' }}>
                                            </td>
                                            @endforeach
                                            <td></td>
                                        </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="px-4 pb-3 d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                    onclick="resetPermissions({{ $role->id }})">
                                <i class="fas fa-redo me-1"></i>{{ __('messages.reset') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        @empty
            <div class="px-4 py-3 text-muted">{{ __('messages.no_record_found') }}</div>
        @endforelse
    </div>
</div>

{{-- SECTION 2 : GESTIONNAIRE DE RÔLES --}}
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
                                <span class="badge bg-primary">
                                    {{ $role->users_count }} {{ __('messages.users') }}
                                </span>
                            </td>
                            <td class="text-end">
                                @if(!$role->is_fixed)
                                @can('delete_users')
                                <button class="btn btn-sm btn-outline-danger"
                                        onclick="deleteRole({{ $role->id }}, this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr id="no-role-row">
                            <td colspan="3" class="text-center text-muted py-3">{{ __('messages.no_record_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- SECTION 3 : UTILISATEURS ADMIN --}}
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
            <table class="table table-hover mb-0">
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
                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white"
                                         style="width:32px;height:32px;font-size:13px;flex-shrink:0;">
                                        {{ strtoupper(substr($adminUser->first_name ?? 'A', 0, 1)) }}
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
                                @if($adminUser->hasRole('admin'))
                                    <span class="badge bg-danger px-3 py-2">{{ ucfirst($userRole) }}</span>
                                @else
                                <select class="form-select form-select-sm role-select"
                                        style="min-width:130px;"
                                        onchange="updateUserRole({{ $adminUser->id }}, this.value)">
                                    @foreach($availableRoles as $r)
                                        <option value="{{ $r->name }}" {{ $userRole === $r->name ? 'selected' : '' }}>
                                            {{ ucfirst($r->title) }}
                                        </option>
                                    @endforeach
                                </select>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($adminUser->id !== auth()->id() && !$adminUser->hasRole('admin'))
                                @can('delete_users')
                                <button class="btn btn-sm btn-outline-danger"
                                        onclick="deleteAdmin({{ $adminUser->id }}, this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                                @else
                                    <span class="text-muted">–</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr id="no-admin-row">
                            <td colspan="4" class="text-center text-muted py-3">{{ __('messages.no_record_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL AJOUTER UN RÔLE --}}
<div class="modal fade" id="addRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('messages.add_role') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('page.lbl_name') }} <span class="text-danger">*</span></label>
                    <input type="text" id="role-title" class="form-control" placeholder="{{ __('messages.enter_role_name') }}">
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="save-role-btn" onclick="saveRole()">{{ __('messages.save') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL AJOUTER UN ADMIN --}}
<div class="modal fade" id="addAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('messages.add_admin_user') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('messages.name') }} <span class="text-danger">*</span></label>
                    <input type="text" id="admin-name" class="form-control" placeholder="{{ __('messages.enter_name') }}">
                    <div id="admin-name-error" class="text-danger small mt-1 d-none"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('messages.email') }} <span class="text-danger">*</span></label>
                    <input type="email" id="admin-email" class="form-control" placeholder="{{ __('messages.enter_email') }}">
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
                    <input type="password" id="admin-password" class="form-control" placeholder="{{ __('messages.min_8_chars') }}">
                    <div id="admin-password-error" class="text-danger small mt-1 d-none"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="save-admin-btn" onclick="saveAdmin()">{{ __('messages.save') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('after-scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Chevron collapse
document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(el => {
    const colEl = document.querySelector(el.getAttribute('data-bs-target'));
    if (!colEl) return;
    const roleId = el.getAttribute('data-bs-target').replace('#perm-collapse-', '');
    colEl.addEventListener('show.bs.collapse', () => {
        const ch = document.getElementById('chevron-' + roleId);
        if (ch) { ch.classList.replace('fa-chevron-right', 'fa-chevron-down'); }
    });
    colEl.addEventListener('hide.bs.collapse', () => {
        const ch = document.getElementById('chevron-' + roleId);
        if (ch) { ch.classList.replace('fa-chevron-down', 'fa-chevron-right'); }
    });
});

// Permissions
function savePermissions(roleId, btn) {
    const form = document.querySelector('.perm-form[data-role-id="' + roleId + '"]');
    const body = new FormData(form);
    const origText = btn.textContent;
    btn.disabled = true; btn.textContent = '...';

    fetch(form.getAttribute('action'), { method: 'POST', body })
    .then(() => successSnackbar('{{ __("permission-role.save_form") }}'))
    .catch(() => successSnackbar('{{ __("permission-role.save_form") }}'))
    .finally(() => { btn.disabled = false; btn.textContent = origText; });
}

function resetPermissions(roleId) {
    if (!confirm('{{ __("messages.are_you_sure") }}')) return;
    fetch('/app/permission-role/reset/' + roleId, { headers: { 'X-CSRF-TOKEN': CSRF } })
    .then(r => r.json())
    .then(res => {
        successSnackbar(res.message);
        document.querySelector('.perm-form[data-role-id="' + roleId + '"]')
            ?.querySelectorAll('.perm-check').forEach(cb => cb.checked = false);
    });
}

// Rôles
function saveRole() {
    const title = document.getElementById('role-title').value.trim();
    const importRole = document.getElementById('import-role').value;
    const btn = document.getElementById('save-role-btn');
    const errEl = document.getElementById('role-title-error');

    errEl.classList.add('d-none');
    if (!title) { errEl.textContent = '{{ __("validation.required", ["attribute" => "name"]) }}'; errEl.classList.remove('d-none'); return; }

    btn.disabled = true; btn.textContent = '...';
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
        } else { errorSnackbar(res.message); }
    })
    .catch(() => errorSnackbar('{{ __("messages.error_occurred") }}'))
    .finally(() => { btn.disabled = false; btn.textContent = '{{ __("messages.save") }}'; });
}

function addRoleRow(role) {
    document.getElementById('no-role-row')?.remove();
    const tr = document.createElement('tr');
    tr.id = 'role-row-' + role.id;
    tr.innerHTML = `<td><strong>${role.title}</strong></td>
        <td><span class="badge bg-primary">0 {{ __('messages.users') }}</span></td>
        <td class="text-end">
            <button class="btn btn-sm btn-outline-danger" onclick="deleteRole(${role.id}, this)">
                <i class="fas fa-trash"></i></button></td>`;
    document.querySelector('#role-table tbody').appendChild(tr);
    ['admin-role','import-role'].forEach(id => {
        document.getElementById(id)?.appendChild(new Option(role.title, role.name));
    });
    document.querySelectorAll('.role-select').forEach(s => s.appendChild(new Option(role.title, role.name)));
}

function deleteRole(id, btn) {
    if (!confirm('{{ __("messages.are_you_sure") }}')) return;
    btn.disabled = true;
    fetch('/app/admin-management/roles/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF } })
    .then(r => r.json())
    .then(res => {
        if (res.status) { document.getElementById('role-row-' + id)?.remove(); successSnackbar(res.message); }
        else { errorSnackbar(res.message); btn.disabled = false; }
    });
}

// Admin users
function saveAdmin() {
    const name = document.getElementById('admin-name').value.trim();
    const email = document.getElementById('admin-email').value.trim();
    const role = document.getElementById('admin-role').value;
    const password = document.getElementById('admin-password').value;
    const btn = document.getElementById('save-admin-btn');

    ['name','email','role','password'].forEach(f => document.getElementById('admin-'+f+'-error').classList.add('d-none'));
    let valid = true;
    if (!name)     { showErr('admin-name-error',     '{{ __("validation.required", ["attribute" => "name"]) }}');     valid = false; }
    if (!email)    { showErr('admin-email-error',    '{{ __("validation.required", ["attribute" => "email"]) }}');    valid = false; }
    if (!role)     { showErr('admin-role-error',     '{{ __("validation.required", ["attribute" => "role"]) }}');     valid = false; }
    if (!password) { showErr('admin-password-error', '{{ __("validation.required", ["attribute" => "password"]) }}'); valid = false; }
    if (!valid) return;

    btn.disabled = true; btn.textContent = '...';
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
            ['name','email','password'].forEach(f => document.getElementById('admin-'+f).value = '');
            document.getElementById('admin-role').value = '';
            addAdminRow(res.data);
        } else { errorSnackbar(res.message); }
    })
    .catch(() => errorSnackbar('{{ __("messages.error_occurred") }}'))
    .finally(() => { btn.disabled = false; btn.textContent = '{{ __("messages.save") }}'; });
}

function addAdminRow(user) {
    document.getElementById('no-admin-row')?.remove();
    const rolesOpts = Array.from(document.querySelector('.role-select')?.options || [])
        .map(o => `<option value="${o.value}"${o.value===user.role?' selected':''}>${o.text}</option>`).join('');
    const tr = document.createElement('tr');
    tr.id = 'admin-user-row-' + user.id;
    tr.innerHTML = `
        <td><div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white"
                 style="width:32px;height:32px;font-size:13px;flex-shrink:0;">${user.name.charAt(0).toUpperCase()}</div>
            <strong>${user.name}</strong></div></td>
        <td class="text-muted">${user.email}</td>
        <td><select class="form-select form-select-sm role-select" style="min-width:130px;"
                onchange="updateUserRole(${user.id}, this.value)">${rolesOpts}</select></td>
        <td class="text-end"><button class="btn btn-sm btn-outline-danger" onclick="deleteAdmin(${user.id}, this)">
            <i class="fas fa-trash"></i></button></td>`;
    document.getElementById('admin-users-body').appendChild(tr);
}

function updateUserRole(userId, role) {
    fetch('/app/admin-management/users/' + userId + '/role', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ role })
    })
    .then(r => r.json())
    .then(res => res.status ? successSnackbar(res.message) : errorSnackbar(res.message));
}

function deleteAdmin(id, btn) {
    if (!confirm('{{ __("messages.are_you_sure") }}')) return;
    btn.disabled = true;
    fetch('/app/admin-management/users/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF } })
    .then(r => r.json())
    .then(res => {
        if (res.status) { document.getElementById('admin-user-row-' + id)?.remove(); successSnackbar(res.message); }
        else { errorSnackbar(res.message); btn.disabled = false; }
    });
}

function showErr(id, msg) {
    const el = document.getElementById(id);
    el.textContent = msg;
    el.classList.remove('d-none');
}
</script>
@endpush
