{{-- Case documents. With $canUpload: the upload form (#legal_document_form, .field_wrapper, .add_button, .remove_button +
     doc_path_id) that the page script drives; otherwise a read-only list. Needs $legal. --}}
@php $docs = $legal->legalDocument ?: collect(); @endphp
<section class="lg-card" aria-labelledby="lg_docs_title">
  <div class="lg-card__head"><h2 class="lg-card__title" id="lg_docs_title">Documents <span class="lg-count">{{ count($docs) }}</span></h2></div>
  @if(!empty($canUpload))
  <form action="{{ route('legalDocumentStore') }}" method="POST" id="legal_document_form" class="lg-card__body" enctype="multipart/form-data" data-toggle="validator">
    {{ csrf_field() }}
    <input type="hidden" name="legal_id" value="{{ $legal->id }}">
    <input type="hidden" name="current_url" value="{{ url()->current() }}">
    <div class="field_wrapper">
      <div class="lg-field" style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap;">
        <div style="flex:1 1 260px;"><label for="legal_documents_file_name" class="lg-label">Add document <span class="muted" style="font-weight:400;">(PDF or Word)</span></label>
          <input type="file" class="legal_documents_file_name lg-input" id="legal_documents_file_name" name="legal_documents_file_name[]" data-rule-extension="pdf|doc|docx" data-msg-extension="Only PDF and Word files"></div>
        <button type="button" class="lg-btn lg-btn--secondary add_button"><i class="fa fa-plus" aria-hidden="true"></i> Another file</button>
      </div>
      @foreach($docs as $doc)
      <div class="ro" style="display:flex;justify-content:space-between;align-items:center;gap:8px;padding:6px 0;border-top:1px solid var(--lg-border);">
        <a class="lg-file" target="_blank" rel="noopener" href="{{ asset('storage/app/' . $doc->legal_documents_file_name) }}"><i class="fa fa-paperclip" aria-hidden="true"></i> {{ $doc->legal_documents_name }}</a>
        <span><input type="hidden" name="doc_path_id" value="{{ $doc->id }}">
          <button type="button" class="lg-btn lg-btn--danger lg-btn--sm remove_button" aria-label="Delete {{ $doc->legal_documents_name }}"><i class="fa fa-trash-o" aria-hidden="true"></i> Delete</button></span>
      </div>
      @endforeach
    </div>
    <button type="submit" class="lg-btn lg-btn--primary" style="margin-top:8px;">Upload</button>
  </form>
  @else
    @if(count($docs))
      <div class="lg-card__body lg-timeline__files" style="margin:0;">
        @foreach($docs as $doc)
          <a class="lg-file" target="_blank" rel="noopener" href="{{ asset('storage/app/' . $doc->legal_documents_file_name) }}"><i class="fa fa-paperclip" aria-hidden="true"></i> {{ $doc->legal_documents_name }}</a>
        @endforeach
      </div>
    @else
      <div class="lg-empty">No documents.</div>
    @endif
  @endif
</section>
