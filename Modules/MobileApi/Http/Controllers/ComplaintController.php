<?php

namespace Modules\MobileApi\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Maintenance\Entities\ComplaintChecklist;
use Modules\Maintenance\Services\TicketAttendance;
use Modules\MobileApi\Http\Requests\CheckInRequest;
use Modules\MobileApi\Http\Requests\ItemRequest;
use Modules\MobileApi\Http\Requests\NoteRequest;
use Modules\MobileApi\Http\Requests\PhotoRequest;
use Modules\MobileApi\Http\Requests\SignatureRequest;
use Modules\MobileApi\Http\Requests\StatusRequest;
use Modules\MobileApi\Http\Resources\TicketListResource;
use Modules\MobileApi\Http\Resources\TicketResource;
use Modules\MobileApi\Services\MobileImage;

class ComplaintController extends ApiController
{
    private function ticket(Request $request, int $id): ComplaintChecklist
    {
        $ticket = ComplaintChecklist::with('complaintEnquiry')->findOrFail($id);
        TicketAttendance::assertInScope($ticket, $request->user());
        return $ticket;
    }

    public function index(Request $request)
    {
        $request->validate([
            'tab' => 'required|in:unassigned,mine,attended,completed',
            'building_id' => 'nullable|integer',
            'q' => 'nullable|string|max:100',
            'updated_since' => 'nullable|date',
            'per_page' => 'nullable|integer|max:100',
        ]);
        $building = $request->integer('building_id') ?: null;
        $q = $request->tab === 'unassigned'
            ? TicketAttendance::unassignedTickets($request->user(), $building, $request->q)
            : TicketAttendance::myTickets($request->user(), $request->tab, $building, $request->q);
        if ($request->updated_since) {
            $q->where('complaint_checklists.updated_at', '>=', $request->date('updated_since'));
        }
        return TicketListResource::collection($q->paginate($request->integer('per_page') ?: 25));
    }

    public function show(Request $request, int $id)
    {
        return new TicketResource($this->ticket($request, $id));
    }

    public function claim(Request $request, int $id)
    {
        return new TicketResource(TicketAttendance::claim($this->ticket($request, $id), $request->user()));
    }

    public function checkIn(CheckInRequest $request, int $id)
    {
        $ticket = $this->ticket($request, $id);
        TicketAttendance::checkIn($ticket, $request->user(), $request->lat !== null ? (float) $request->lat : null, $request->lng !== null ? (float) $request->lng : null);
        return new TicketResource($ticket->fresh());
    }

    public function note(NoteRequest $request, int $id)
    {
        $note = TicketAttendance::addNote($this->ticket($request, $id), $request->user(), $request->text, $request->quick_reason);
        return response()->json(['data' => ['id' => $note->id, 'text' => $note->desc, 'created_at' => $note->created_at]], 201);
    }

    public function photo(PhotoRequest $request, int $id)
    {
        $ticket = $this->ticket($request, $id);
        $stored = MobileImage::store($request->file('photo'), "complaints/{$ticket->id}");
        $img = TicketAttendance::addPhoto($ticket, $request->user(), $stored['path'], $stored['thumb'], $request->stage);
        return response()->json(['data' => [
            'id' => $img->id, 'stage' => TicketAttendance::photoStageName((int) $img->stage),
            'url' => FileController::signedUrl($stored['path']), 'thumb_url' => FileController::signedUrl($stored['thumb']),
        ]], 201);
    }

    public function item(ItemRequest $request, int $id)
    {
        $item = TicketAttendance::addItem($this->ticket($request, $id), $request->user(), $request->integer('inventory_id'), (float) $request->quantity);
        return response()->json(['data' => ['id' => $item->id, 'inventory_id' => $item->inventory_id, 'quantity' => (float) $item->quantity]], 201);
    }

    public function removeItem(Request $request, int $id, int $item)
    {
        TicketAttendance::removeItem($this->ticket($request, $id), $request->user(), $item);
        return response()->json(['message' => 'Removed.']);
    }

    public function status(StatusRequest $request, int $id)
    {
        $ticket = $this->ticket($request, $id);
        TicketAttendance::setStatus($ticket, $request->user(), $request->status, $request->reason);
        return new TicketResource($ticket->fresh());
    }

    public function signature(SignatureRequest $request, int $id)
    {
        $ticket = $this->ticket($request, $id);
        TicketAttendance::close($ticket, $request->user(), $request->signature, $request->boolean('skipped'));
        if ($request->tenant_remark) {
            TicketAttendance::addNote($ticket, $request->user(), 'Tenant remark: ' . $request->tenant_remark, null);
        }
        return new TicketResource($ticket->fresh());
    }

    public function pdf(Request $request, int $id)
    {
        $ticket = $this->ticket($request, $id);
        $report = TicketAttendance::reportFor($ticket) ?? abort(404);
        return response()->json(['url' => FileController::signedUrl(TicketAttendance::pdfPath($ticket, $report))]);
    }
}
