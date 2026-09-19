<?php

namespace Modules\BackOffice\Tests\Unit;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Modules\BackOffice\Entities\Termination;
use Modules\BackOffice\Services\TerminationWorkflow;
use Tests\TestCase;

class TerminationWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    /** A 502 row whose contract has not moved on yet. */
    private function pending(): Termination
    {
        return Termination::where('work_flow_processes_code', 502)->where('termination_type', 1)
            ->whereNotExists(fn ($q) => $q->selectRaw('1')->from('termination as x')
                ->whereColumn('x.contract_id', 'termination.contract_id')->where('x.termination_type', 1)->where('x.work_flow_processes_code', '>', 502))
            ->latest('id')->firstOrFail();
    }

    public function test_assign_handover_moves_502_to_503_for_the_given_user(): void
    {
        Event::fake();
        $t = $this->pending();
        $are = User::role('are')->firstOrFail();
        $admin = User::where('username', 'admin')->firstOrFail();

        $next = TerminationWorkflow::assignHandover($t, $are, $admin);

        $this->assertSame(503, (int) $next->work_flow_processes_code);
        $this->assertSame($t->contract_id, $next->contract_id);
        $this->assertSame($are->id, (int) $next->assigned_to);
        $this->assertSame($t->termination_date?->toDateString(), $next->termination_date?->toDateString());
        $this->assertDatabaseHas('termination_users', ['termination_id' => $next->id, 'user_id' => $are->id, 'status' => 1]);
        $this->assertDatabaseHas('termination_users', ['termination_id' => $t->id, 'status' => 0]);
    }

    public function test_advance_accepts_503_into_504_and_carries_inspection_fields(): void
    {
        Event::fake();
        $t = $this->pending();
        $are = User::role('are')->firstOrFail();
        $draft = TerminationWorkflow::assignHandover($t, $are, $are);
        $draft->update(['termination_total_amount' => 20, 'termination_net_amount' => 17, 'termination_water_acc_no' => 'W-9', 'termination_notes' => 'n']);

        $next = TerminationWorkflow::advance($draft->fresh(), 'ACPT', $are);

        $this->assertSame(504, (int) $next->work_flow_processes_code);
        $this->assertEquals(20, (float) $next->termination_total_amount);
        $this->assertEquals(17, (float) $next->termination_net_amount);
        $this->assertSame('W-9', $next->termination_water_acc_no);
        $this->assertSame($are->id, (int) $next->assigned_to);
        $this->assertDatabaseHas('termination_users', ['termination_id' => $draft->id, 'status' => 0]);
    }
}
