@extends('layouts.app')

@section('title', 'Accounting')
@section('header', 'Chart of Accounts')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0" style="font-weight:600;"><i class="fas fa-coins mr-2" style="color:#e84393;"></i>Accounts</h5>
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#accountModal" onclick="resetAccountForm()"><i class="fas fa-plus mr-1"></i> Add Account</button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Code</th><th>Name</th><th>Type</th><th class="text-right">Opening Balance</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                @forelse($accounts as $a)
                    <tr>
                        <td><code>{{ $a->code }}</code></td>
                        <td><strong>{{ $a->name }}</strong><br><small class="text-muted">{{ $a->description ?: '' }}</small></td>
                        <td><span class="badge badge-info text-uppercase">{{ $a->type }}</span></td>
                        <td class="text-right" style="font-weight:600; color:{{ $a->opening_balance >= 0 ? '#55efc4' : '#ff7675' }};">{{ number_format($a->opening_balance, 2) }}</td>
                        <td><span class="badge badge-{{ $a->is_active ? 'success' : 'secondary' }}">{{ $a->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="text-right">
                            <button type="button" class="btn btn-sm btn-default" onclick="editAccount({{ json_encode($a) }})"><i class="fas fa-pen"></i></button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="confirmDeleteAcct({{ $a->id }}, '{{ addslashes($a->name) }}')"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5"><i class="fas fa-coins fa-2x mb-3 d-block" style="opacity:0.3;"></i>No accounts yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($accounts->hasPages()) <div class="p-3">{{ $accounts->links() }}</div> @endif
    </div>
</div>

{{-- Account Modal --}}
<div class="modal fade" id="accountModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
    <div class="modal-header" style="background:linear-gradient(135deg,#e84393,#fd79a8) !important; border:0;">
        <h5 class="modal-title" style="color:#fff;"><i class="fas fa-coins mr-2"></i><span id="accountModalTitle">Add Account</span></h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
    </div>
    <form id="accountForm" method="POST" action="{{ route('modules.accounting.store') }}">@csrf
        <input type="hidden" name="_method" id="accountMethod" value="POST">
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6"><div class="form-group"><label>Name *</label><input type="text" name="name" id="acc-name" class="form-control" required></div></div>
                <div class="col-md-6"><div class="form-group"><label>Code *</label><input type="text" name="code" id="acc-code" class="form-control" required maxlength="32"></div></div>
                <div class="col-md-6"><div class="form-group"><label>Type *</label><select name="type" id="acc-type" class="form-control" required>@foreach($types as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select></div></div>
                <div class="col-md-6"><div class="form-group"><label>Opening Balance</label><input type="number" name="opening_balance" id="acc-balance" class="form-control" step="0.01" value="0"></div></div>
                <div class="col-12"><div class="form-group"><label>Description</label><input type="text" name="description" id="acc-desc" class="form-control"></div></div>
                <div class="col-12"><div class="custom-control custom-switch"><input type="checkbox" class="custom-control-input" name="is_active" value="1" id="acc-active" checked><label class="custom-control-label" for="acc-active">Active</label></div></div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save</button></div>
    </form>
</div></div></div>

{{-- Delete Modal --}}
<div class="modal fade" id="deleteAcctModal" tabindex="-1"><div class="modal-dialog modal-sm modal-dialog-centered"><div class="modal-content">
    <div class="modal-header" style="background:linear-gradient(135deg,#ff7675,#d63031) !important; border:0;">
        <h5 class="modal-title" style="color:#fff;">Confirm Delete</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
    </div>
    <div class="modal-body text-center">
        <i class="fas fa-trash-alt fa-3x mb-3" style="color:#ff7675;"></i>
        <p>Delete <strong id="deleteAcctName"></strong>?<br><small class="text-muted">This cannot be undone.</small></p>
    </div>
    <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <form id="deleteAcctForm" method="POST">@csrf @method('DELETE')<button type="submit" class="btn btn-danger"><i class="fas fa-trash mr-1"></i> Delete</button></form>
    </div>
</div></div></div>

<script>
function resetAccountForm(){document.getElementById('accountModalTitle').textContent='Add Account';document.getElementById('accountForm').action='{{ route("modules.accounting.store") }}';document.getElementById('accountMethod').value='POST';document.getElementById('acc-name').value='';document.getElementById('acc-code').value='';document.getElementById('acc-type').value='asset';document.getElementById('acc-balance').value=0;document.getElementById('acc-desc').value='';document.getElementById('acc-active').checked=true;}
function editAccount(a){document.getElementById('accountModalTitle').textContent='Edit Account';document.getElementById('accountForm').action='{{ url("modules/accounting") }}/'+a.id;document.getElementById('accountMethod').value='PUT';document.getElementById('acc-name').value=a.name;document.getElementById('acc-code').value=a.code;document.getElementById('acc-type').value=a.type;document.getElementById('acc-balance').value=a.opening_balance;document.getElementById('acc-desc').value=a.description||'';document.getElementById('acc-active').checked=a.is_active;$('#accountModal').modal('show');}
function confirmDeleteAcct(id,name){document.getElementById('deleteAcctName').textContent=name;document.getElementById('deleteAcctForm').action='{{ url("modules/accounting") }}/'+id;$('#deleteAcctModal').modal('show');}
</script>
@endsection
