@extends('layouts.plms-app')

@section('css')
@include('backoffice::MessageTemplate._styles')
@endsection

@section('content')
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class="pull-left"><div class="page-title">Message Templates</div></div>
    {{ Breadcrumbs::render('message-templates.index') }}
  </div>
</div>

<div class="mt">
<div class="card card-box">
  <div class="card-body">
    <div class="mt-bar">
      <form method="GET" action="{{ route('message-templates.index') }}" class="mt-filters" role="search">
        <div>
          <label for="mt-q">Search</label>
          <input type="text" id="mt-q" name="q" value="{{ $filters['q'] }}" class="mt-input" placeholder="Name or subject">
        </div>
        <div>
          <label for="mt-kind">Kind</label>
          <select id="mt-kind" name="kind" class="mt-input">
            <option value="">All</option>
            <option value="system" {{ $filters['kind'] === 'system' ? 'selected' : '' }}>System</option>
            <option value="bulk" {{ $filters['kind'] === 'bulk' ? 'selected' : '' }}>Bulk</option>
          </select>
        </div>
        <div>
          <label for="mt-audience">Audience</label>
          <select id="mt-audience" name="audience" class="mt-input">
            <option value="">All</option>
            @foreach($audiences as $value => $label)
              <option value="{{ $value }}" {{ $filters['audience'] === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="mt-row">
          <button type="submit" class="mt-btn"><i class="fa fa-search" aria-hidden="true"></i> Filter</button>
          @if($filters['q'] !== '' || $filters['kind'] !== '' || $filters['audience'] !== '')
            <a href="{{ route('message-templates.index') }}" class="mt-btn">Clear</a>
          @endif
        </div>
      </form>
      @if($canManage)
        <a href="{{ route('message-templates.create') }}" class="mt-btn mt-btn-primary"><i class="fa fa-plus" aria-hidden="true"></i> New template</a>
      @endif
    </div>

    @if($templates->count())
      <div style="overflow-x:auto">
      <table>
        <thead>
          <tr>
            <th>Name</th><th>Kind</th><th>Audience</th><th>Channels</th><th>Status</th><th class="mt-hide-sm">Updated</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($templates as $t)
            <tr>
              <td>{{ $t->name }} @if($t->key)<span class="mt-key">{{ $t->key }}</span>@endif</td>
              <td><span class="mt-badge {{ $t->isSystem() ? 'mt-badge-system' : '' }}">{{ $t->isSystem() ? 'System' : 'Bulk' }}</span></td>
              <td>{{ isset($audiences[$t->audience]) ? $audiences[$t->audience] : $t->audience }}</td>
              <td>
                @if($t->email_subject)<span class="mt-badge"><i class="fa fa-envelope-o" aria-hidden="true"></i> Email</span>@endif
                @if($t->sms_body)
                  @if(!$t->isSystem())
                    <span class="mt-badge"><i class="fa fa-mobile" aria-hidden="true"></i> SMS</span>
                  @elseif($t->sms_enabled)
                    <span class="mt-badge mt-badge-on"><i class="fa fa-mobile" aria-hidden="true"></i> SMS on</span>
                  @else
                    <span class="mt-badge"><i class="fa fa-mobile" aria-hidden="true"></i> SMS off</span>
                  @endif
                @endif
              </td>
              <td>
                @if($t->is_active)
                  <span class="mt-badge mt-badge-on"><i class="fa fa-check" aria-hidden="true"></i> Active</span>
                @else
                  <span class="mt-badge"><i class="fa fa-pause" aria-hidden="true"></i> Off</span>
                @endif
              </td>
              <td class="mt-hide-sm">{{ optional($t->updated_at)->format('d/m/Y') }}</td>
              <td>
                <div class="mt-actions">
                  <button type="button" class="mt-btn mt-btn-sm mt-preview" data-id="{{ $t->id }}"><i class="fa fa-eye" aria-hidden="true"></i> Preview</button>
                  @if($canManage)
                    <a href="{{ route('message-templates.edit', $t->id) }}" class="mt-btn mt-btn-sm"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</a>
                    @unless($t->isSystem())
                      <form method="POST" action="{{ route('message-templates.destroy', $t->id) }}" class="mt-delete" data-name="{{ $t->name }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="mt-btn mt-btn-sm mt-btn-danger"><i class="fa fa-trash" aria-hidden="true"></i> Delete</button>
                      </form>
                    @endunless
                  @endif
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
      </div>
      <div style="margin-top:16px">{{ $templates->links() }}</div>
    @else
      <div class="mt-empty">
        @if($filters['q'] !== '' || $filters['kind'] !== '' || $filters['audience'] !== '')
          <strong>No templates match these filters.</strong>
          <a href="{{ route('message-templates.index') }}">Clear the filters</a> to see all templates.
        @else
          <strong>No templates yet.</strong>
          Create one to reuse it when sending emails and SMS.
          @if($canManage)
            <div style="margin-top:16px"><a href="{{ route('message-templates.create') }}" class="mt-btn mt-btn-primary"><i class="fa fa-plus" aria-hidden="true"></i> New template</a></div>
          @endif
        @endif
      </div>
    @endif
  </div>
</div>
</div>
@endsection

@section('scripts')
@include('backoffice::MessageTemplate._preview_modal')
<script>
  $(function () {
    $('.mt-preview').on('click', function () { mtPreview({ id: $(this).data('id') }); });
    $('.mt-delete').on('submit', function () { return confirm('Delete template "' + $(this).data('name') + '"?'); });
  });
</script>
@endsection
